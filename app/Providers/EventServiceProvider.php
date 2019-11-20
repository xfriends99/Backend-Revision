<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return true;
    }

    /**
     * Get the listener directories that should be used to discover events.
     *
     * @return array
     */
    protected function discoverEventsWithin()
    {
        $paths = [
            $this->app->path('Listeners/Shared'),
            $this->app->path('Listeners/CasillerosEcuador'),
            $this->app->path('Listeners/CasillerosMailamericas')
        ];

        return $paths;
    }
}
