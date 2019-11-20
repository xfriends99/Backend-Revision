<?php

namespace App\Services\Tariffs;

use App\Services\Tariffs\Exception\InvalidZipCodeException;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;

class TariffRateQuoteService
{
    /**
     * @param float $weight
     * @param string $country_code
     * @param string $admin_level_1
     * @param string $admin_level_2
     * @param string $admin_level_3
     * @param string $zip_code
     * @param string $service
     * @return float
     * @throws InvalidZipCodeException|Exception
     */
    public function quote($weight, $country_code, $admin_level_1, $admin_level_2, $admin_level_3, $zip_code, $service)
    {
        $url = env('TRACKING_API_URL') . '/v1/tariff-rates/quote';
        logger("[Tariff Rates] URL: {$url}");

        $client = new Client([
            RequestOptions::HTTP_ERRORS => false,
            'verify'                    => !app()->isLocal()
        ]);

        /** @var ResponseInterface $response */
        $response = null;

        try {
            $params = [
                'weight'        => $weight,
                'country_code'  => $country_code,
                'admin_level_1' => $admin_level_1,
                'admin_level_2' => $admin_level_2,
                'admin_level_3' => $admin_level_3,
                'zip_code'      => $zip_code,
                'service'       => $service,
                'access_token'  => env('TRACKING_API_ACCESS_TOKEN')
            ];

            logger("[Tariff Rates] Request");
            logger(print_r($params, true));

            $response = $client->request('GET', $url, [
                'query' => $params
            ]);
        } catch (GuzzleException $e) {
            logger($e->getMessage());
            logger($e->getTraceAsString());

            throw new Exception("Request error.");
        }

        $response = json_decode($response->getBody()->getContents(), true);
        logger("[Tariff Rates] Response");
        logger(print_r($response, true));

        if (isset($response['error']) && $response['error']) {
            $message = $response['message'];
            if ($message == 'Zip code not found') {
                throw new InvalidZipCodeException($message);
            }
            throw new Exception($message);
        }

        /** @var float $tariff */
        $tariff = isset($response['data']['calculated_tariff_usd']) ? floatval($response['data']['calculated_tariff_usd']) : null;
        if (!$tariff) {
            throw new Exception('No tariff available');
        }

        return $tariff;
    }
}
