<?php

namespace App\Console\Commands\Passport\Warehouse;

use App\Models\Warehouse;
use App\Repositories\WarehouseRepository;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Laravel\Passport\Client as OauthClient;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\TokenRepository;

class GenerateAccessToken extends Command
{
    /** @var ClientRepository */
    protected $clientRepository;

    /** @var WarehouseRepository */
    protected $warehouseRepository;

    /** @var TokenRepository */
    protected $tokenRepository;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'passport:warehouse:generate-access-token {code}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates a new access token for the clients';

    /**
     * GenerateAccessToken constructor.
     * @param ClientRepository $clientRepository
     * @param WarehouseRepository $warehouseRepository
     * @param TokenRepository $tokenRepository
     */
    public function __construct(ClientRepository $clientRepository, WarehouseRepository $warehouseRepository, TokenRepository $tokenRepository)
    {
        parent::__construct();

        $this->clientRepository = $clientRepository;
        $this->warehouseRepository = $warehouseRepository;
        $this->tokenRepository = $tokenRepository;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $time_start = microtime(true);

        /** @var Warehouse $warehouse */
        if (!$warehouse = $this->warehouseRepository->getByCode($this->argument('code'))) {
            $this->error('Warehouse not found');
            exit();
        }

        /** @var OauthClient $oauthClient */
        if (!$oauthClient = $warehouse->getFirstOauthClient()) {
            $redirect = (app()->environment() == 'local') ? 'https://localhost' : env('APP_URL');

            /** @var OauthClient $oauthClient */
            $oauthClient = $this->clientRepository->createPersonalAccessClient(null, $warehouse->name, $redirect);
            $this->warehouseRepository->addOauthClient($warehouse, $oauthClient);
        }

        $response = self::generateAccessToken($oauthClient);

        $this->info('Personal access token created successfully.');
        $this->line('<comment>Access Token:</comment> ' . $response->access_token);

        $time_end = microtime(true);
        $time = $time_end - $time_start;

        $this->info("Request completed in {$time} seconds");
    }

    private function generateAccessToken(OauthClient $oauthClient)
    {
        /** @var Client $clientHttp */
        $clientHttp = new Client();

        $base_uri = (app()->environment() == 'local') ? 'https://casilleros_nginx_mailamericas' : env('APP_URL');
        $url = $base_uri . '/oauth/token';

        $response = $clientHttp->post($url, [
            'headers'     => [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'form_params' => [
                'grant_type'    => 'client_credentials',
                'client_id'     => $oauthClient->id,
                'client_secret' => $oauthClient->secret,
                'scope'         => '*',
            ],
        ]);

        return json_decode($response->getBody());
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['code', InputArgument::REQUIRED, 'Provider Code']
        ];
    }
}
