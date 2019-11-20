<?php

namespace App\Services\Cards\Exceptions;

use App\Models\PaymentGateway;
use Exception;

class GatewayException extends Exception
{
    /**
     * GatewayException constructor.
     * @param PaymentGateway $paymentGateway
     * @param $message
     */
    public function __construct(PaymentGateway $paymentGateway, $message)
    {
        parent::__construct("[$paymentGateway->name] {$message}");
    }
}