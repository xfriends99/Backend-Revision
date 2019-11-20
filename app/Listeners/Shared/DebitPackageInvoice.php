<?php

namespace App\Listeners\Shared;

use App\Events\PackageWasDebited;
use App\Events\PackageWasInvoiced;
use App\Listeners\PlatformListener;
use App\Models\Package;
use App\Services\Packages\PackageService;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;

class DebitPackageInvoice extends PlatformListener
{
    /**
     * The time (seconds) before the job should be processed.
     *
     * @var int
     */
    public $delay = 86400;

    /** @var  PackageService */
    private $packageService;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(PackageService $packageService)
    {
        $this->packageService = $packageService;
    }

    /**
     * Handle the event.
     *
     * @param PackageWasInvoiced $event
     * @return void
     * @throws Exception
     */
    public function handle(PackageWasInvoiced $event)
    {
        /** @var Package $package */
        $package = $event->package;
        $package->fresh(['invoice']);

        try {
            $this->packageService->debitPackage($package);
        } catch (Exception $exception) {
            logger("[Debit Package Exception] Exception in package {$package->tracking}");
            logger($exception->getMessage());
            logger($exception->getTraceAsString());
        }
    }
}
