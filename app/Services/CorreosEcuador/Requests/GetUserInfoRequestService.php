<?php

namespace App\Services\CorreosEcuador\Requests;

use App\Services\CorreosEcuador\BaseRequestService;
use App\Services\CorreosEcuador\Entities\GetUserInfoResponse;
use App\Services\CorreosEcuador\Entities\UpdateUserInfoResponse;
use App\Services\HttpRequests\HttpRequest as HttpRequestEntity;
use GuzzleHttp\Psr7\Request;

class GetUserInfoRequestService extends BaseRequestService
{
    /** @var int */
    private $user_identification;

    /**
     * UserService constructor.
     * @param $user_identification
     */
    public function __construct($user_identification)
    {
        parent::__construct();

        $this->user_identification = $user_identification;
    }

    /**
     * @return Request
     */
    protected function createRequest()
    {
        return new Request('GET', "api/usuario/{$this->user_identification}", ['Content-Type' => 'application/json']);
    }

    /**
     * @param HttpRequestEntity $httpRequestEntity
     * @return UpdateUserInfoResponse|GetUserInfoResponse
     */
    protected function parse(HttpRequestEntity $httpRequestEntity)
    {
        /** @var GetUserInfoResponse $getUserInfoResponse */
        $getUserInfoResponse = new GetUserInfoResponse();
        $getUserInfoResponse->setHttpRequest($httpRequestEntity);

        if ($httpRequestEntity->getStatusCode() == 200) {
            // Get Response as Json
            $response = $httpRequestEntity->getResponseContentsAsJson();
            $getUserInfoResponse->initialize((array)$response);
        } else {
            // Get response as string
            $response = $httpRequestEntity->getResponseContentsAsString();
            $getUserInfoResponse->setError($response);
        }

        return $getUserInfoResponse;
    }
}