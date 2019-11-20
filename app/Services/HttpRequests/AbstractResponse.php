<?php

namespace App\Services\HttpRequests;

use App\Models\HttpRequest;
use App\Services\HttpRequests\HttpRequest as HttpRequestEntity;

abstract class AbstractResponse
{
    /** @var HttpRequestEntity */
    protected $httpRequestEntity;

    /** @var HttpRequest */
    protected $httpRequest;

    /**
     * @param HttpRequestEntity $httpRequestEntity
     */
    public function setHttpRequest(HttpRequestEntity $httpRequestEntity)
    {
        $this->httpRequestEntity = $httpRequestEntity;
    }

    /**
     * @param HttpRequest $httpRequest
     */
    public function setRequest(HttpRequest $httpRequest)
    {
        $this->httpRequest = $httpRequest;
    }

    /**
     * @return HttpRequestEntity
     */
    public function getHttpRequest()
    {
        return $this->httpRequestEntity;
    }

    /**
     * @return HttpRequest
     */
    public function getRequest()
    {
        return $this->httpRequest;
    }

    /**
     * @return bool
     */
    abstract public function hasErrors();

    /**
     * @return string
     */
    abstract public function getErrors();

}