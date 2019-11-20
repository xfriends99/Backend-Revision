<?php

namespace App\Jobs;

use App\Events\PackageWasDebited;
use App\Models\Card;
use App\Models\Invoice;
use App\Models\Package;
use App\Models\User;
use App\Repositories\TransactionRepository;
use App\Services\Cards\GatewayFactory;
use App\Services\Packages\PackageService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Exception;
use Illuminate\Support\Facades\DB;

class DebitPackageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var  Package */
    protected $package;

    /** @var PackageService */
    protected $packageService;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Package $package)
    {
        $this->package = $package;
        $this->packageService = app(PackageService::class);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $this->packageService->debitPackage($this->package);
        } catch (Exception $exception) {
            logger("[Debit Package Exception] Exception in package {$this->package->tracking}");
            logger($exception->getMessage());
            logger($exception->getTraceAsString());
        }
    }
}
