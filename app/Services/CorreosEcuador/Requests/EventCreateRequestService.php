<?php

namespace App\Services\CorreosEcuador\Requests;

use App\Models\Package;
use App\Services\CorreosEcuador\BaseRequestService;
use App\Services\CorreosEcuador\Entities\UpdateUserInfoResponse;
use App\Services\HttpRequests\HttpRequest as HttpRequestEntity;
use App\Services\Packages\Events\PackageEventEntity;
use GuzzleHttp\Psr7\Request;

class EventCreateRequestService extends BaseRequestService
{
    /** @var PackageEventEntity */
    protected $packageEventEntity;

    /** @var Package */
    protected $package;

    /**
     * EventCreateRequestService constructor.
     * @param PackageEventEntity $packageEventEntity
     * @param Package $package
     */
    public function __construct(PackageEventEntity $packageEventEntity, Package $package)
    {
        parent::__construct();

        $this->packageEventEntity = $packageEventEntity;
        $this->package = $package;
    }

    /**
     * @return Request
     */
    protected function createRequest()
    {
        $params = [
            "id_prealerta"  => $this->package->id_prealert,
            "codigo_envio"  => $this->package->tracking,
            "evento_cc"     => $this->packageEventEntity->getCode(),
            "pais"          => "Estados Unidos",
            "oficina"       => "Miami, FL",
            "observacion_1" => $this->packageEventEntity->getDescription(),
            "observacion_2" => $this->packageEventEntity->getDescriptionAlt(),
            "fecha"         => $this->packageEventEntity->getParseDate()
        ];

        return new Request('POST', 'api/evento', ['Content-Type' => 'application/json'], json_encode($params));
    }

    /**
     * @param HttpRequestEntity $httpRequestEntity
     * @return UpdateUserInfoResponse
     */
    protected function parse(HttpRequestEntity $httpRequestEntity)
    {
        $response_parse = $httpRequestEntity->getResponseContentsAsJson();

        if ($httpRequestEntity->getStatusCode() == 201 && $response_parse->ESTADO == 1) {
            $response = new UpdateUserInfoResponse($response_parse->MENSAJE, false, $response_parse->ESTADO, 1);
        } else {
            $response = new UpdateUserInfoResponse($response_parse->MENSAJE, true);
        }

        $response->setHttpRequest($httpRequestEntity);

        return $response;
    }
}