<?php

namespace App\Services\HttpRequests;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

class HttpRequest
{
    /** @var Request */
    private $request;

    /** @var string */
    private $contents;

    /** @var int */
    private $status_code;

    /** @var array */
    private $config;

    /**
     * HttpRequest constructor.
     * @param Request $request
     * @param Response $response
     */
    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;

        $this->contents = $response->getBody()->getContents();
        $this->status_code = $response->getStatusCode();
    }

    /**
     * @param array $config
     */
    public function setConfig(array $config)
    {
        $this->config = $config;
    }

    /**
     * @return object
     */
    public function getResponseContentsAsJson()
    {
        return json_decode($this->contents);
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->status_code;
    }

    /**
     * @return null|string
     */
    public function getRequestPath()
    {
        return $this->request->getRequestTarget();
    }

    /**
     * @return string
     */
    public function getRequestAsString()
    {
        return (string)$this->request->getBody();
    }

    /**
     * @return string
     */
    public function getResponseContentsAsString()
    {
        return $this->contents;
    }

    /**
     * @return string
     */
    public function getRequestHttpMethod()
    {
        return $this->request->getMethod();
    }

    /**
     * @return false|string
     */
    public function getHeadersAsString()
    {
        return json_encode($this->config['headers']);
    }
}