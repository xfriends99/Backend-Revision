<?php

namespace App\Services\Platform;

use App\Models\Country;
use App\Models\Platform;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\View;

/**
 * Class EmailTemplatingService
 * @package App\Services\Platform
 */
class EmailTemplatingService
{
    /**
     * @param Platform $platform
     * @param string $section
     * @param Country|null $country
     * @return string
     */
    public static function getViewByPlatformAndCountry(Platform $platform, string $section, Country $country = null): string
    {
        $view = "emails.{$platform->key}.{$section}.mail";
        if ($country) {
            $custom = "emails.{$platform->key}.{$section}." . strtolower($country->code) . ".mail";
            if (View::exists($custom)) {
                $view = $custom;
            }
        }

        return $view;
    }

    /**
     * @param Platform $platform
     * @param string $phrase
     * @param Country|null $country
     * @return string
     */
    public static function getSubjectByPlatformAndCountry(Platform $platform, string $phrase, Country $country = null): string
    {
        $subject = Lang::get("{$phrase}.{$platform->key}.subject");

        if ($country) {
            $custom_key = "{$phrase}.{$platform->key}." . strtolower($country->code) . ".subject";
            if (Lang::get($custom_key) != $custom_key) {
                $subject = Lang::get($custom_key);
            }
        }

        return $subject;
    }
}
