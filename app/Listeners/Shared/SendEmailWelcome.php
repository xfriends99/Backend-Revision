<?php

namespace App\Listeners\Shared;

use App\Listeners\PlatformListener;
use App\Models\User;
use App\Notifications\VerificationSuccess;
use Illuminate\Auth\Events\Verified;

class SendEmailWelcome extends PlatformListener
{
    /**
     * @param Verified $event
     */
    public function handle(Verified $event)
    {
        /** @var User $user */
        $user = $event->user;

        $user->notify(new VerificationSuccess($user));
    }
}
