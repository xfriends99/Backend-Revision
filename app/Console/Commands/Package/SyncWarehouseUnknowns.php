<?php

namespace App\Console\Commands\Package;

use App\Models\Purchase;
use App\Repositories\PurchaseRepository;
use App\Repositories\WarehouseUnknownRepository;
use App\Services\Warehouses\Entities\UnknownPackage;
use App\Services\Warehouses\Lars\UnknownPackagesService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Symfony\Component\Console\Input\InputArgument;

class SyncWarehouseUnknowns extends Command
{
    use DispatchesJobs;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'warehouse:sync-unknowns {hours?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command for sync unknown packages and update data from Warehouses';

    /** @var WarehouseUnknownRepository $warehouseUnknownRepository */
    protected $warehouseUnknownRepository;

    /** @var UnknownPackagesService $unknownPackagesService */
    protected $unknownPackagesService;

    /** @var PurchaseRepository $purchaseRepository */
    protected $purchaseRepository;

    /**
     * UnknownPackageSync constructor.
     * @param WarehouseUnknownRepository $warehouseUnknownRepository
     * @param UnknownPackagesService $unknownPackagesService
     */
    public function __construct(
        WarehouseUnknownRepository $warehouseUnknownRepository,
        UnknownPackagesService $unknownPackagesService,
        PurchaseRepository $purchaseRepository
    ) {
        parent::__construct();

        $this->warehouseUnknownRepository = $warehouseUnknownRepository;
        $this->unknownPackagesService = $unknownPackagesService;
        $this->purchaseRepository = $purchaseRepository;
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle()
    {
        $time_start = microtime(true);

        /** @var int $hours */
        $hours = $this->argument('hours') ? $this->argument('hours') : 3;

        /** @var Carbon $finalDate */
        $finalDate = Carbon::now();

        /** @var Carbon $initialDate */
        $initialDate = $finalDate->copy()->subHours($hours);

        if ($response_json = $this->unknownPackagesService->getUnknownPackages($initialDate, $finalDate)) {
            foreach ($response_json as $response) {
                /** @var UnknownPackage $unknownPackage */
                $unknownPackage = new UnknownPackage();

                // Initialize object from json response
                $unknownPackage->initialize((array)$response);

                $found = false;
                if (!empty($unknownPackage->getTracking())) {
                    // Convert LARS tracking to uppercase
                    $tracking = strtoupper($unknownPackage->getTracking());

                    // Check if purchase exists
                    /** @var Purchase $purchase */
                    if ($purchase = $this->purchaseRepository->getByTracking($tracking)) {
                        $found = true;
                    }

                    // Update record in warehouse unknowns repository
                    $this->warehouseUnknownRepository->updateOrCreate(compact('tracking'), [
                        'found'   => $found,
                        'details' => json_encode($unknownPackage->getProperties())
                    ]);

                    if ($found) {
                        $this->warn("Tracking {$tracking} found in Warehouse!");
                    }
                }
            }
        }

        $time_end = microtime(true);
        $time = $time_end - $time_start;
        $this->info("Request completed in {$time} seconds.");
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['hours', InputArgument::OPTIONAL, 'Hours of events for search'],
        ];
    }
}
