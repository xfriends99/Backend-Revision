<?php

namespace App\Services\Warehouses\Lars;

use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;

class UnknownPackagesService
{
    /**
     * @param Carbon $initialDate
     * @param Carbon $finalDate
     * @return bool|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getUnknownPackages(Carbon $initialDate, Carbon $finalDate)
    {
        // Prepare data
        $data = [
            'initialDate' => $initialDate->toDateTimeString(),
            'finalDate'   => $finalDate->toDateTimeString()
        ];

        // Perform request
        try {
            /** @var ResponseInterface $response */
            $response = $this->performRequest('/PoBox/GetPackagesOnDesconocido', 'GET', $data);

            if ($response->getStatusCode() != 200) {
                return false;
            }

            $content = $response->getBody()->getContents();

            logger('[LARS] Response');
            logger($content);

            return json_decode($content);
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
            'query'    => $data,
            'handler'  => $tapMiddleware($clientHandler)
        ]);

        return $responseInterface;
    }
}