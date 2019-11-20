<?php

namespace App\Services\CorreosEcuador;

use App\Models\HttpRequest;
use App\Services\HttpRequests\AbstractResponse;
use App\Services\HttpRequests\HttpRequest as HttpRequestEntity;
use App\Services\HttpRequests\HttpRequestCreationService;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;

abstract class BaseRequestService
{
    /** @var HttpRequestCreationService */
    protected $httpRequestCreationService;

    /**
     * @var string
     */
    private $endpoint;

    /**
     * @var string
     */
    private $user;

    /**
     * @var string
     */
    private $password;

    /**
     * AbstractRequestsService constructor.
     */
    public function __construct()
    {
        $this->httpRequestCreationService = app(HttpRequestCreationService::class);

        $this->endpoint = env('ECUADOR_API_ENDPOINT');
        $this->user = env('ECUADOR_API_USER');
        $this->password = env('ECUADOR_API_PASSWORD');
    }

    /**
     * @return string
     * @throws Exception
     */
    private function getToken()
    {
        $client = new Client(['base_uri' => $this->endpoint, 'headers' => ['Content-Type' => 'application/json']]);

        $params = [
            'usuario'    => $this->user,
            'contrasena' => $this->password
        ];

        try {
            $response = $client->post("api/login/auth", [
                'form_params' => $params
            ]);
        } catch (Exception $e) {
            logger($e->getMessage());
            logger($e->getTraceAsString());

            throw new Exception("Error getting access token");
        }

        return str_replace('"', '', $response->getBody()->getContents());
    }

    /**
     * @return AbstractResponse
     * @throws Exception
     */
    public function request()
    {
        $token = $this->getToken();

        $client = new Client([
            'base_uri' => $this->endpoint,
            'headers'  => [
                'Content-Type'  => 'application/json',
                'Authorization' => "Bearer {$token}"
            ]
        ]);

        /** @var Request */
        $request = $this->createRequest();

        try {
            /** @var Response $response */
            $response = $client->send($request, [
                RequestOptions::HTTP_ERRORS => false
            ]);
        } catch (RequestException $e) {
            logger($e->getMessage());
            logger($e->getTraceAsString());

            throw new Exception("Error consultando api de ecuador");
        } catch (GuzzleException $e) {
            logger($e->getMessage());
            logger($e->getTraceAsString());

            throw new Exception("Error consultando api de ecuador");
        }

        /** @var HttpRequestEntity $httpRequestEntity */
        $httpRequestEntity = new HttpRequestEntity($request, $response);
        $httpRequestEntity->setConfig($client->getConfig());

        /** @var AbstractResponse $abstractResponse */
        $abstractResponse = $this->parse($httpRequestEntity);

        /** @var HttpRequest $httpRequest */
        $httpRequest = $this->httpRequestCreationService->create($abstractResponse);
        $abstractResponse->setRequest($httpRequest);

        return $abstractResponse;
    }

    /**
     * @return Request
     */
    abstract protected function createRequest();

    /**
     * @param HttpRequestEntity $httpRequestEntity
     * @return AbstractResponse
     */
    abstract protected function parse(HttpRequestEntity $httpRequestEntity);
}