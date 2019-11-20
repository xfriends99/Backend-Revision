<?php

namespace App\Services\CorreosEcuador\Requests;

use App\Models\Package;
use App\Services\CorreosEcuador\BaseRequestService;
use App\Services\CorreosEcuador\Entities\GetInvoiceInfoResponse;
use GuzzleHttp\Psr7\Request;

use App\Services\HttpRequests\HttpRequest as HttpRequestEntity;

class GetPayOrderRequestService extends BaseRequestService
{
    /** @var  Package */
    private $package;

    /**
     * PreAlertsService constructor.
     */
    public function __construct(Package $package)
    {
        parent::__construct();

        $this->package = $package;
    }

    /**
     * @return Request
     */
    protected function createRequest()
    {
        $params = [
            "identificador_prealerta" => $this->package->id_prealert,
            "codigo_envio" => '',
            "opcion" => 1
        ];

        return new Request('GET', 'api/ordenpago', ['Content-Type' => 'application/json'], json_encode($params));
    }

    /**
     * @param HttpRequestEntity $httpRequestEntity
     * @return GetInvoiceInfoResponse
     */
    protected function parse(HttpRequestEntity $httpRequestEntity)
    {
        $response_parse = $httpRequestEntity->getResponseContentsAsJson();

        if($httpRequestEntity->getStatusCode() == 200){
            $response = new GetInvoiceInfoResponse('', false, $response_parse->numero_factura, $response_parse->fecha_factura, $response_parse->portal_facturacion, $response_parse->credenciales_portal);
        } else {
            $response = new GetInvoiceInfoResponse($response_parse->Message, true);
        }

        $response->setHttpRequest($httpRequestEntity);

        return $response;
    }

}