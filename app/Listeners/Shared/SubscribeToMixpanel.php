<?php

namespace App\Listeners\Shared;

use App\Models\User;
use App\Services\Cloud\MultiAnalyticsFactory;
use Illuminate\Auth\Events\Registered;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class SubscribeToMixpanel extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Handle the event.
     *
     * @param \Illuminate\Auth\Events\Registered $event
     * @return void
     */
    public function handle(Registered $event)
    {
        /** @var User $user */
        $user = $event->user;

        // Register user and track signup event
        MultiAnalyticsFactory::trackUser($user, 'Signup', MultiAnalyticsFactory::MIXPANEL);
    }
}
