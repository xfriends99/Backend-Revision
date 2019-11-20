<?php
/**
 * Created by PhpStorm.
 * User: plabin
 * Date: 28/3/2019
 * Time: 12:36 PM
 */

namespace App\Repositories;


use App\Models\PaymentGateway;

class PaymentGatewayRepository extends AbstractRepository
{
    function __construct(PaymentGateway $model)
    {
        $this->model = $model;
    }

    public function filter(array $filters = [])
    {
        $query = $this->model->select('payment_gateways.*');

        if (isset($filters['key']) && $filters['key']) {
            $query = $query->ofKey($filters['key']);
        }

        return $query;
    }

    /**
     * @param $key
     * @return PaymentGateway|\Illuminate\Database\Eloquent\Model|object|null
     */
    public function getByKey($key)
    {
        return $this->filter(['key' => $key])->first();
    }
}