<?php

namespace App\Console\Commands\Cards;

use App\Jobs\DebitPackageJob;
use App\Models\Package;
use App\Models\User;
use App\Notifications\AddCardNotification;
use App\Repositories\PackageRepository;
use App\Services\Packages\PackageService;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Symfony\Component\Console\Input\InputArgument;

class DebitPackagesCommand extends Command
{
    use DispatchesJobs;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'packages:debit {code?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Debit unpaid packages';

    /** @var  PackageRepository */
    protected $packageRepository;

    /** @var PackageService */
    protected $packageService;

    /**
     * DebitPackagesCommand constructor.
     * @param PackageRepository $packageRepository
     * @param PackageService $packageService
     */
    public function __construct(PackageRepository $packageRepository, PackageService $packageService)
    {
        parent::__construct();

        $this->packageRepository = $packageRepository;
        $this->packageService = $packageService;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $start = microtime(true);

        $this->info('Starting debit packages process...');

        $filters = [];

        if ($tracking_number = $this->argument('code')) {
            // Filter by tracking number if specified as option
            array_push($filters, ['tracking' => $tracking_number]);
        } else {
            // Debit packages that have rejected invoices
            array_push($filters, ['invoice_state' => 'rejected']);
            // Debit pending packages
            array_push($filters, ['invoice_state' => 'pending']);
        }

        // Packages ready to debit count
        $to_debit_count = 0;

        foreach ($filters as $filter) {
            $this->packageRepository->filter($filter)->chunk(50, function ($packages) use (&$to_debit_count) {
                /** @var Package $package */
                foreach ($packages as $package) {
                        $job = (new DebitPackageJob($package));
                        $this->dispatch($job);
                        ++$to_debit_count;
                }
            });
        }

        $total = microtime(true) - $start;

        $this->info('Debit packages process finished');
        $this->info("{$to_debit_count} packages was sent to the queue to debit.");
        $this->info("Total time: {$total} seconds.");
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['code', InputArgument::OPTIONAL, 'Tracking Number Code']
        ];
    }
}
