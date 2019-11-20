<?php

namespace App\Services\Mailamericas\Tracking\Checkpoints;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Collection;

class EventsService
{
    /** @var string */
    private $api_endpoint;

    /** @var string */
    private $api_access_token;

    /** @var Collection */
    public $data;

    /**
     * TownsService constructor.
     */
    public function __construct()
    {
        $this->api_endpoint = env('TRACKING_API_URL');
        $this->api_access_token = env('TRACKING_API_ACCESS_TOKEN');
        $this->data = collect();
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
            $client = new Client(['verify' => !app()->isLocal()]);

            $response = $client->get($this->api_endpoint . '/v2/events', [
                'query' => array_merge($filters, ['access_token' => $this->api_access_token])
            ]);

            $response = $response->getBody()->getContents();
            $response = json_decode($response);

            $this->data = collect($response->data);
        } catch (ClientException $e) {
            throw new Exception($e->getMessage());
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getEvents()
    {
        return $this->data->pluck('events')->first();
    }

    public function getOrigin()
    {
        return $this->data->pluck('origin');
    }

    public function getDestination()
    {
        return $this->data->pluck('destination');
    }
}
