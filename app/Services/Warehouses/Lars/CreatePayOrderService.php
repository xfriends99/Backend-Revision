<?php

namespace App\Services\Warehouses\Lars;

use App\Models\Purchase;
use App\Models\User;
use App\Models\WorkOrder;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;

class CreatePayOrderService
{

    /**
     * @param WorkOrder $workOrder
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function consolidate(WorkOrder $workOrder)
    {
        /** @var Purchase $puchase */
        $puchase = $workOrder->purchases->first();

        // Prepare data
        $data = [
            'CasilleroMailAmericas' => $puchase->getUserLockerCode(),
            'Comment' => $workOrder->getAdditionalsDescriptions(),
            'Description' => $workOrder->getPurchasesDescription(),
            'DeclaredValue' => floatval($workOrder->value),
            'TrackingNumbers' => $workOrder->getPurchasesTracking(),
            'WorkOrderId'   => $workOrder->id
        ];

        // Perform request
        try {
            $response = $this->performRequest('/PoBox/CreateOrdenEnvio', 'POST', $data);

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