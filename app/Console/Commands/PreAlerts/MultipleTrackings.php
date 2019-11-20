<?php

namespace App\Console\Commands\PreAlerts;

use Illuminate\Console\Command;

class MultipleTrackings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prealert:multiple-packages';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command for pre alert multiple tracking numbers to warehouses';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //
    }
}
