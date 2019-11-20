<?php

namespace App\Console\Commands\Package;

use App\Jobs\Packages\UpdatePackageStatusJob;
use App\Models\Package;
use App\Repositories\PackageRepository;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Exception;

class PackageSyncEvents extends Command
{
    use DispatchesJobs;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'package:sync-events {code?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command for sync event packages in process, update state and pre alert new events';

    /** @var PackageRepository $packageRepository */
    protected $packageRepository;

    /**
     * Create a new command instance.
     *
     * @param PackageRepository $packageRepository)
     * @return void
     */
    public function __construct(PackageRepository $packageRepository)
    {
        parent::__construct();

        $this->packageRepository = $packageRepository;
    }

    /**
     * @throws Exception
     */
    public function handle()
    {
        $time_start = microtime(true);

        if ($code = $this->argument('code')) {
            if (!$package = $this->packageRepository->getByTrackingNumber($code)) {
                $this->error("No package found with code: {$code}");
                return;
            }

            (new UpdatePackageStatusJob($package))->handle();
        } else {
            $chunk_count = 0;
            $chunk_size = 1000;

            $this->packageRepository->filter(['work_order_state' => 'processed', 'state' => 'created'])->chunk($chunk_size, function ($packages) use (&$chunk_count, $chunk_size) {
                ++$chunk_count;

                /** @var Package $package */
                foreach ($packages as $package) {

                    $job = (new UpdatePackageStatusJob($package))->onQueue('casilleros-packages-sync');
                    $this->dispatch($job);
                }
                $this->info("Dispatched " . $chunk_count * $chunk_size . " jobs.");
            });
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
            ['code', InputArgument::OPTIONAL, 'Tracking #'],
        ];
    }
}
