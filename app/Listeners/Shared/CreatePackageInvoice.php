<?php

namespace App\Listeners\Shared;

use App\Events\PackageWasInvoiced;
use App\Events\ShipmentWasProcessed;
use App\Listeners\PlatformListener;
use App\Services\Packages\PackageService;
use Exception;
use Illuminate\Support\Facades\DB;

class CreatePackageInvoice extends PlatformListener
{
    /**
     * The time (seconds) before the job should be processed.
     *
     * @var int
     */
    public $delay = 180;

    /** @var  PackageService */
    protected $packageService;

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
     * @param ShipmentWasProcessed $event
     * @return void
     * @throws Exception
     */
    public function handle(ShipmentWasProcessed $event)
    {
        $invoice = null;

        try {
            $invoice = $this->packageService->generateInvoice($event->package);

            if ($invoice) {
                event(new PackageWasInvoiced($event->package));
            } else {
                throw new Exception('Error creando factura del paquete');
            }
        } catch (Exception $exception) {
            logger("[Create Package Invoice] Exception in package {$event->package->tracking}");
            logger($exception->getMessage());
            logger($exception->getTraceAsString());
        }
    }

//    public function __get($name)
//    {
//        // TODO: Implement __get() method.
//
//        if ($name == 'queue') {
//            return env('APP_PLATFORM') . '-default';
//        }
//    }
}
