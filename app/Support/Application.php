<?php

namespace App\Support;

use Illuminate\Foundation\Application as BaseApplication;
use Illuminate\Log\LogServiceProvider;
use Illuminate\Routing\RoutingServiceProvider;

class Application extends BaseApplication
{
    /**
     * Register all of the base service providers.
     *
     * @return void
     */
    protected function registerBaseServiceProviders()
    {
        $this->register(new CustomEventServiceProvider($this));
        $this->register(new LogServiceProvider($this));
        $this->register(new RoutingServiceProvider($this));
    }
}