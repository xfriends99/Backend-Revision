<?php

namespace App\Services\CorreosEcuador\Requests;

use App\Models\Purchase;
use App\Repositories\PackageRepository;
use App\Services\CorreosEcuador\BaseRequestService;
use Carbon\Carbon;
use GuzzleHttp\Psr7\Request;
use App\Models\Package;

use App\Services\HttpRequests\HttpRequest as HttpRequestEntity;
use App\Services\CorreosEcuador\Entities\UpdateUserInfoResponse;

class PreAlertsCreateRequestService extends BaseRequestService
{
    /** @var Package  */
    private $package;

    /** @var  PackageRepository */
    private $packageRepository;

    /**
     * PreAlertsService constructor.
     * @param Package $package
     */
    public function __construct(Package $package)
    {
        parent::__construct();

        $this->package = $package;
        $this->packageRepository = app(PackageRepository::class);
    }

    /**
     * @return Request
     */
    protected function createRequest()
    {
        $this->package->load(['user', 'workOrder.purchases']);

        /** @var Carbon $date */
        $date = $this->package->created_at->clone();
        $date->setTimezone('America/Bogota');


        $params = [
            "id_persona" => $this->package->getUserExternalId(),
            "codigo_envio" => $this->package->tracking,
            "descripcion_contenido" => "envioConsolidado",
            "valor_fob" => $this->package->value,
            "cantidad_paquetes" => $this->package->getWorkOrderPurchasesCount(),
            "peso_total" => $this->package->weight,
            "pais" => "United States",
            "oficina" => "Miami",
            "fecha_evento" => $date->setTimezone('America/Bogota')->toDateTimeString(),
            "lista_paquetes" => []
        ];

        /** @var Purchase $purchase */
        foreach($this->package->getWorkOrderPurchases() as $purchase){
            $params["lista_paquetes"][] = [
                "codigo_tienda" => $purchase->tracking,
                "descripcion" => $purchase->getPurchaseItemsDescriptions(),
                "valor_fob" => $purchase->value,
                "tienda_origen" => $purchase->getMarketplaceName(),
                "peso" => $purchase->getWeight()
            ];
        }

        return new Request('POST', 'api/prealerta', ['Content-Type' => 'application/json'], json_encode($params));
    }

    /**
     * @param HttpRequestEntity $httpRequestEntity
     * @return UpdateUserInfoResponse
     */
    protected function parse(HttpRequestEntity $httpRequestEntity)
    {
        $response_parse = $httpRequestEntity->getResponseContentsAsJson();

        if($httpRequestEntity->getStatusCode() == 200){
            $preAlertResponse = new UpdateUserInfoResponse($response_parse->mensaje, false, $response_parse->id, $response_parse->estado);

            // Update prealert_id in Packages
            $this->packageRepository->update($this->package, [
                'id_prealert' => $response_parse->id
            ]);
        } else {
            $preAlertResponse = new UpdateUserInfoResponse($response_parse->Message, true);
        }

        $preAlertResponse->setHttpRequest($httpRequestEntity);

        return $preAlertResponse;
    }
}