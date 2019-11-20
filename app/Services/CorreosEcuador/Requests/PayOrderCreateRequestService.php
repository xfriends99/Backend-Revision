<?php

namespace App\Services\CorreosEcuador\Requests;

use App\Models\Additional;
use App\Models\Invoice;
use App\Models\Package;
use App\Models\Transaction;
use App\Models\User;
use App\Services\CorreosEcuador\BaseRequestService;
use GuzzleHttp\Psr7\Request;

use App\Services\HttpRequests\HttpRequest as HttpRequestEntity;
use App\Services\CorreosEcuador\Entities\UpdateUserInfoResponse;

class PayOrderCreateRequestService extends BaseRequestService
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
        /** @var Invoice $invoice */
        $invoice = $this->package->invoice;

        /** @var User $user */
        $user = $this->package->user;

        /** @var Transaction $transaction */
        $transaction = $invoice->getSuccessTransaction();

        $transaction_details = json_decode($transaction->details, true);

        $shipping_cost_iva = $invoice->shipping_cost * 0.12;
        $shipping_cost_subtotal = $invoice->shipping_cost - $shipping_cost_iva;

        $params = [
            "id_persona" => $user->external_id,
            "subtotal" => $invoice->subtotal,
            "impuesto" => $invoice->iva,
            "total" => $invoice->total_amount,
            "metodo_pago" => 0,
            "status" => 1,
            "status_detail" => $transaction_details['status_detail'],
            "fecha_pago" => $invoice->charged_at,
            "dev_referencia" => $transaction_details['dev_reference'],
            "codigo_autorizacion" => $transaction_details['authorization_code'],
            "id_transaccion" => $transaction_details['id'],
            "lista_orden_pago_prealerta" => [
                [
                    "id_prealerta" => $this->package->id_prealert,
                    "tarifa_codigo" => $invoice->tariff_code,
                    "subtotal_rubro" => $shipping_cost_subtotal,
                    "impuesto_rubro" => $shipping_cost_iva,
                    "total_rubro" => $invoice->shipping_cost
                ]
            ]
        ];
        
        /** @var Additional $additional */
        foreach ($this->package->getWorkOrderAdditionals() as $additional) {

            $additional_iva = $additional->pivot->value * 0.12;
            $additional_subtotal = $additional->pivot->value - $additional_iva;

            $params["lista_orden_pago_prealerta"][] = [
                "id_prealerta" => $this->package->id_prealert,
                "tarifa_codigo" => $additional->external_key,
                "subtotal_rubro" => $additional_subtotal,
                "impuesto_rubro" => $additional_iva,
                "total_rubro" => $additional->pivot->value
            ];
        }

        return new Request('POST', 'api/ordenpago', ['Content-Type' => 'application/json'], json_encode($params));
    }

    /**
     * @param HttpRequestEntity $httpRequestEntity
     * @return UpdateUserInfoResponse
     */
    protected function parse(HttpRequestEntity $httpRequestEntity)
    {
        $response_parse = $httpRequestEntity->getResponseContentsAsJson();

        if($httpRequestEntity->getStatusCode() == 201 && $response_parse->estado == 1){
            $response = new UpdateUserInfoResponse($response_parse->mensaje, false, $response_parse->id, $response_parse->estado);
        } else {
            $response = new UpdateUserInfoResponse($response_parse->Mensaje, true);
        }

        $response->setHttpRequest($httpRequestEntity);

        return $response;
    }

}