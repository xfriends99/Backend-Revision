<?php

namespace App\Providers;

use App\Models\Platform;
use App\Models\Site;
use Illuminate\Support\ServiceProvider;
use Schema;

class ConfigServiceProvider extends ServiceProvider
{
    public function register()
    {
        $useAcceptLanguageHeader = false;
        $hideDefaultLocaleInURL = false;
        $default_locales = [
            'es_AR' => ['name' => 'Español Argentina', 'script' => 'Ltn', 'native' => 'español', 'regional' => 'AR']
        ];

        if (env('APP_LOCALE') == 'es_EC') {
            $hideDefaultLocaleInURL = true;
            $default_locales = [
                'es_EC' => ['name' => 'Español Ecuador', 'script' => 'Ltn', 'native' => 'español', 'regional' => 'EC']
            ];
        }

        config([
            'laravellocalization.useAcceptLanguageHeader' => $useAcceptLanguageHeader,
            'laravellocalization.hideDefaultLocaleInURL'  => $hideDefaultLocaleInURL,
            'laravellocalization.supportedLocales'        => $default_locales
        ]);
    }

    public function boot()
    {
        /** @var Platform $platform */
        $platform = current_platform();

        // Load from platform
        $supportedLocales = collect();
        $supportedLocales->put('es_AR', [
            'name'     => 'Argentina',
            'script'   => 'Ltn',
            'native'   => 'español',
            'regional' => 'AR'
        ]);

        if ($platform) {
            if (Schema::hasTable('sites')) {
                $platform->sites->each(function (Site $item) use (&$supportedLocales) {
                    if ($item->getCountryCode() != 'AR') {
                        $supportedLocales->put($item->locale, [
                            'name'     => $item->getCountryName(),
                            'script'   => 'Ltn',
                            'native'   => 'español',
                            'regional' => $item->getCountryCode()
                        ]);
                    }
                });
            }
        }

        // Set supported from available Sites
        \LaravelLocalization::setSupportedLocales($supportedLocales->toArray());
    }
}
