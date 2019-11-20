<?php

namespace App\Providers;

use App\Http\ViewComposers\AlertsComposer;
use App\Http\ViewComposers\NavbarComposer;
use App\Http\ViewComposers\FooterComposer;
use App\Http\ViewComposers\ReferralsComposer;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class ComposerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        View::composer('shared.alerts', AlertsComposer::class);
        View::composer('layouts.casillerosmailamericas.navbar', NavbarComposer::class);
        View::composer('layouts.casillerosmailamericas.footer', FooterComposer::class);
        View::composer(
            ['casillerosmailamericas.register', 'casillerosecuador.register'],
            ReferralsComposer::class
        );
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
