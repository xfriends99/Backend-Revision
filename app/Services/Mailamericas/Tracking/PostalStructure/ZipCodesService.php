<?php

namespace App\Services\Mailamericas\Tracking\PostalStructure;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Exception;

class ZipCodesService
{
    private $apiEndpoint;
    private $zip_codes = [];

    /**
     * TownsService constructor.
     */
    public function __construct()
    {
        $this->apiEndpoint = env('TRACKING_API_URL');
    }

    /**
     * @param array $filters
     * @return $this
     * @throws Exception
     */
    public function search($filters = [])
    {
        try {
            // Call API
            $client = new Client();
            $response = $client->get(env('TRACKING_API_URL') . '/v1/postal-structure/zip-codes', [
                'query' => $filters
            ]);

            // Log response
            $response = $response->getBody()->getContents();

            $response = json_decode($response);

            $this->zip_codes = $response->data;
        } catch (ClientException $e) {
            throw new Exception($e->getMessage());
        }

        return $this;
    }

    /**
     * @return array
     */
    public function get()
    {
        return $this->zip_codes;
    }
}