<?php

namespace App\Services\Warehouses\Lars;

use App\Models\User;
use App\Repositories\LockerRepository;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;

class LockerService
{
    protected $lockerRepository;

    public function __construct(LockerRepository $lockerRepository)
    {
        $this->lockerRepository = $lockerRepository;
    }

    public function registerUser(User $user)
    {
        /** @var string $code */
        $code = $user->getLockerCode();

        // Prepare data
        $data = [
            'code'        => $code,
            'first_name'  => $user->first_name,
            'last_name'   => $user->last_name,
            'email'       => "{$code}@pickabox.me", // Fake email,
            'iso_country' => $user->getCountryCode()
        ];

        // Perform request
        try {
            $response = $this->performRequest('/PoBox/CreateLocker', 'POST', $data);

            if ($response->getStatusCode() != 200) {
                return false;
            }

            return true;
        } catch (Exception $e) {
            logger($e->getMessage());
            logger($e->getTraceAsString());

            return false;
        }
    }

    /**
     * @param $endpoint
     * @param string $method
     * @param array $data
     * @return ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function performRequest($endpoint, $method, array $data)
    {
        $base_uri = env('LARS_WAREHOUSE_API_URL');
        $client = new Client([
            'timeout' => 60,
            'headers' => [
                'Access' => env('LARS_WAREHOUSE_ACCESS_TOKEN'),
                'Accept' => 'application/json',
            ]
        ]);

        $clientHandler = $client->getConfig('handler');
        $tapMiddleware = Middleware::tap(function (Request $request) {
            logger('[LARS] Request');
            logger($request->getMethod());
            logger($request->getHeaders());
            logger($request->getBody());
        });

        $responseInterface = $client->request($method, $base_uri . $endpoint, [
            'json'    => $data,
            'handler' => $tapMiddleware($clientHandler)
        ]);

        logger('[LARS] Response');
        logger($responseInterface->getBody()->getContents());

        return $responseInterface;
    }
}
