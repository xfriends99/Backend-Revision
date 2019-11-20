<?php

namespace App\Listeners\Shared;

use App\Events\PackageWasDebited;
use App\Listeners\PlatformListener;
use App\Models\User;
use App\Notifications\DebitPackageNotification;
use Exception;

class NotifyPackageDebited extends PlatformListener
{
    /**
     * Handle the event.
     *
     * @param object $event
     * @return void
     */
    public function handle(PackageWasDebited $event)
    {
        /** @var User $user */
        $user = $event->package->user;

        try {
            $user->notify(new DebitPackageNotification($event->package));
        } catch (Exception $exception) {
            logger("[Notify Package Debit Exception] Exception in package {$event->package->tracking}");
            logger($exception->getMessage());
            logger($exception->getTraceAsString());
        }
    }
}
