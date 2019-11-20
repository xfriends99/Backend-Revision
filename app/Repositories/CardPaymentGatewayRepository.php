<?php
/**
 * Created by PhpStorm.
 * User: plabin
 * Date: 16/4/2019
 * Time: 5:18 PM
 */

namespace App\Repositories;


use App\Models\CardPaymentGateway;

class CardPaymentGatewayRepository extends AbstractRepository
{
    public function __construct(CardPaymentGateway $model)
    {
        $this->model = $model;
    }
}