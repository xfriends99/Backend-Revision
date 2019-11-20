<?php

namespace App\Console\Commands\Cards;

use App\Models\Transaction;
use App\Repositories\TransactionRepository;
use App\Services\Cards\GatewayFactory;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Exception;

class RefundTransactionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transaction:refund {transaction_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refund transaction';

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
     * @throws Exception
     */
    public function handle()
    {
        $this->info('Starting to refund transaction...');

        /** @var TransactionRepository $transactionRepository */
        $transactionRepository = app(TransactionRepository::class);

        /** @var Transaction $transaction */
        if (!$transaction = $transactionRepository->getById($this->argument('transaction_id')) ) {
            throw new Exception('Transaction not found');
        }

        try {
            $response = GatewayFactory::refund($transaction);

            $this->info($response);
        } catch (Exception $exception) {
            logger("[Refund transaction] Exception in refund transaction");
            logger($exception->getMessage());
            logger($exception->getTraceAsString());
        }
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['code', InputArgument::REQUIRED, 'Transaction ID']
        ];
    }
}
