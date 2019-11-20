<?php

namespace App\Services\Platform;

use App\Models\Country;
use App\Models\Platform;
use App\Models\Site;
use App\Repositories\CountryRepository;
use App\Repositories\PlatformRepository;
use App\Repositories\SiteRepository;
use Exception;
use Torann\GeoIP\Location;

class DetectionService
{
    /** @var PlatformRepository */
    protected $platformRepository;

    /** @var SiteRepository */
    protected $siteRepository;

    /** @var CountryRepository */
    protected $countryRepository;

    public function __construct(PlatformRepository $platformRepository, SiteRepository $siteRepository, CountryRepository $countryRepository)
    {
        $this->platformRepository = $platformRepository;
        $this->siteRepository = $siteRepository;
        $this->countryRepository = $countryRepository;
    }

    /**
     * @return Platform
     */
    public function detectPlatform()
    {
        /** @var Platform $platform */
        $platform = null;

        $key = env('APP_PLATFORM');
        try {
            if ($key) {
                $platform = $this->platformRepository->getByKey($key);
                if (!$platform) {
                    $platform = $this->platformRepository->getByDomain(request()->getHost());
                }
            } else {
                $platform = $this->platformRepository->getByDomain(request()->getHost());
            }
        } catch (Exception $e) {
            logger($e->getMessage());
            logger($e->getTraceAsString());
        }

        return $platform;
    }

    /**
     * @return Site|null
     */
    public function detectSite(Platform $platform)
    {
        $country_code = null;

        // First, check session
        if (session()->has('locale')) {
            if ($locale = session()->get('locale')) {
                $country_code = collect(\LaravelLocalization::getSupportedLocales())->filter(function ($i, $k) use ($locale) {
                    return ($k == $locale);
                })->pluck('regional')->first();
            }
        }

        // Then, check cookie
        if (!$country_code && request()->hasCookie('locale')) {
            if ($locale = request()->cookie('locale')) {
                $country_code = collect(\LaravelLocalization::getSupportedLocales())->filter(function ($i, $k) use ($locale) {
                    return ($k == $locale);
                })->pluck('regional')->first();
            }
        }

        // Also, try detecting from decoded IP
        if (!$country_code) {
            /** @var Location $location */
//            $location = geoip()->getLocation('5.182.120.1'); // CO
//            $location = geoip()->getLocation('2.17.12.0'); // AR
            $location = geoip()->getLocation();

            $country_code = $location->getAttribute('default') ? null : $location->getAttribute('iso_code');
        }

        // Finally, get country from DB or fetch default by platform
        try {
            /** @var Site $site */
            $site = null;

            if ($country_code) {
                /** @var Country $country */
                $country = $this->countryRepository->getByCode($country_code);
                $site = $this->siteRepository->getByPlatformAndCountry($platform, $country);
            } else {
                $site = $this->siteRepository->getPlatformDefault($platform);
            }

            return $site;
        } catch (Exception $e) {
            return null;
        }
    }
}
