<?php

namespace App\Repositories;

use App\Models\PaymentGateway;
use App\Models\PaymentMethod;

class PaymentMethodRepository extends AbstractRepository
{
    function __construct(PaymentMethod $model)
    {
        $this->model = $model;
    }

    public function filter(array $filters = [])
    {
        $query = $this->model->select('payment_methods.*');

        if (isset($filters['payment_gateway_id']) && $filters['payment_gateway_id']) {
            $query = $query->ofPaymentGatewayId($filters['payment_gateway_id']);
        }

        if (isset($filters['key']) && $filters['key']) {
            $query = $query->ofKey($filters['key']);
        }

        return $query;
    }

    public function getByKey($key)
    {
        return $this->filter(['key' => $key])->first();
    }

    public function getByPaymentGatewayAndKey(PaymentGateway $paymentGateway, $key)
    {
        return $this->filter(['payment_gateway_id' => $paymentGateway->id, 'key' => $key])->first();
    }
}