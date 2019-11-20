<?php

namespace App\Services\HttpRequests;

use App\Repositories\HttpRequestRepository;

class HttpRequestCreationService
{
    /** @var HttpRequestRepository  */
    private $httpRequestRepository;

    /**
     * CreateService constructor.
     * @param HttpRequestRepository $httpRequestRepository
     */
    public function __construct(HttpRequestRepository $httpRequestRepository)
    {
        $this->httpRequestRepository = $httpRequestRepository;
    }

    /**
     * @param \App\Services\HttpRequests\AbstractResponse $abstractResponse
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function create(AbstractResponse $abstractResponse)
    {
        /** @var HttpRequest $httpRequest */
        $httpRequest = $abstractResponse->getHttpRequest();

        return $this->httpRequestRepository->create([
            'path'  => $httpRequest->getRequestPath(),
            'request' => $httpRequest->getRequestAsString(),
            'response' => $httpRequest->getResponseContentsAsString(),
            'success' => !$abstractResponse->hasErrors(),
            'http_code' => $httpRequest->getStatusCode(),
            'http_method' => $httpRequest->getRequestHttpMethod(),
            'headers' => $httpRequest->getHeadersAsString(),
            'errors' => $abstractResponse->hasErrors() ? $abstractResponse->getErrors() : null
        ]);
    }
}