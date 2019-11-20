<?php

namespace App\Listeners\CasillerosMailamericas;

use App\Events\UserOptedIntoNewsletter;
use App\Listeners\PlatformListener;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Newsletter;

class SubscribeToNewsletter extends PlatformListener
{
    /**
     * @param Verified $event
     */
    public function handle(UserOptedIntoNewsletter $event)
    {
        /** @var User $user */
        $user = $event->user;

        // Check if platform is Pickabox
        if (!$user->isPlatformMailamericas()) {
            return;
        }

        // Subscribe user to Mailchimp
        Newsletter::subscribeOrUpdate($user->email, [
            'firstName' => $user->first_name,
            'lastName'  => $user->last_name
        ]);
    }
}
