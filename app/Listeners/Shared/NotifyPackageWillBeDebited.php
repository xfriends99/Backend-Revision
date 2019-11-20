<?php

namespace App\Listeners\Shared;

use App\Events\PackageWasInvoiced;
use App\Listeners\PlatformListener;
use App\Models\Package;
use App\Models\User;
use App\Notifications\PackageWillBeDebitedNotification;
use App\Services\Packages\PackageService;
use Exception;

class NotifyPackageWillBeDebited extends PlatformListener
{
    /** @var  PackageService */
    private $packageService;

    /**
     * NotifyPackageWillBeDebited constructor.
     * @param PackageService $packageService
     */
    public function __construct(PackageService $packageService)
    {
        $this->packageService = $packageService;
    }

    /**
     * Handle the event.
     *
     * @param object $event
     * @return void
     */
    public function handle(PackageWasInvoiced $event)
    {
        /** @var User $user */
        $user = $event->package->user;

        /** @var Package $package */
        $package = $event->package;

        try {
            if (!$user->getDefaultCard()) {
                if (!$package->invoiceExceededAttemptsWithoutCard()) {
                    $this->packageService->sendAddCardNotification($package);
                }
            } else {
                $user->notify(new PackageWillBeDebitedNotification($package));
            }
        } catch (Exception $exception) {
            logger("[Notify Package Will Be Debit Exception] Exception in package {$event->package->tracking}");
            logger($exception->getMessage());
            logger($exception->getTraceAsString());
        }
    }
}
