<?php

namespace App\Services\Cards\Gateways\Paymentez;

use App\Models\Card;
use App\Models\PaymentGateway;
use App\Services\Cards\Interfaces\ValidateInterface;
use Illuminate\Http\Request;

class ValidateService implements ValidateInterface
{

    /** @var RequestService  */
    private $requestService;

    /** @var \App\Models\PaymentGateway|\Illuminate\Database\Eloquent\Model|null|object  */
    private $paymentGateway;

    /**
     * ValidateService constructor.
     * @param PaymentGateway $paymentGateway
     */
    public function __construct(PaymentGateway $paymentGateway)
    {
        $this->paymentGateway = $paymentGateway;

        $this->requestService = new RequestService($this->paymentGateway);
    }

    /**
     * @return array
     */
    public function validateRequest()
    {
        return [
            'token' => 'required',
            //'key' => 'required|exists:payment_gateways,key'
        ];
    }

    public function validateAddCard(array $attributes)
    {
        // TODO: Implement validateAddCard() method.
    }

    public function validateWebHookRequest()
    {
        // TODO: Implement validateWebHookRequest() method.
    }

    public function validateProcessConfirmation(Request $request)
    {
        // TODO: Implement validateProcessConfirmation() method.
    }

    public function validateDeleteCard(Card $card)
    {
        // TODO: Implement validateDeleteCard() method.
    }

}
