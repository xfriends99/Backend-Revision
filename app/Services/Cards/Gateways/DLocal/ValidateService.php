<?php

namespace App\Services\Cards\Gateways\DLocal;

use App\Models\Card;
use App\Models\PaymentGateway;
use App\Services\Cards\Entities\DLocal\AddCardEntity;
use App\Services\Cards\Entities\AddCardEntity as BaseAddCardEntity;
use App\Services\Cards\Entities\DLocal\ProcessConfirmationEntity;
use App\Services\Cards\Entities\ProcessConfirmationEntity as BaseProcessConfirmationEntity;
use App\Services\Cards\Exceptions\GatewayException;
use App\Services\Cards\Interfaces\ValidateInterface;
use Illuminate\Http\Request;

class ValidateService implements ValidateInterface
{
    /**
     * @var PaymentGateway
     */
    private $paymentGateway;

    /**
     * @var RequestService
     */
    private $requestService;

    /**
     * ValidateService constructor.
     * @param PaymentGateway $paymentGateway
     */
    public function __construct(PaymentGateway $paymentGateway)
    {
        $this->paymentGateway = $paymentGateway;

        $this->requestService = new RequestService($paymentGateway);
    }

    /**
     * @return array
     */
    public function validateRequest()
    {
        return [
            'token' => 'required',
            'key' => 'required|exists:payment_gateways,key',
            'cardHolderName' => 'required'
        ];
    }

    /**
     * @return array
     */
    public function validateWebHookRequest()
    {
        return [
            'id' => 'required',
            'status' => 'required',
            'approved_date' => 'string'
        ];
    }

    /**
     * @param array $attributes
     * @return BaseAddCardEntity|AddCardEntity
     */
    public function validateAddCard(array $attributes)
    {
        /** @var AddCardEntity $addCardEntity */
        $addCardEntity = new AddCardEntity();

        $addCardEntity->setParams($attributes);

        return $addCardEntity;
    }

    /**
     * @param Request $request
     * @return BaseProcessConfirmationEntity|ProcessConfirmationEntity
     * @throws GatewayException
     */
    public function validateProcessConfirmation(Request $request)
    {
        // Validate signature to assume that this is a valid message from dLocal
        if (!$this->_isValidSignature($request)) {
            throw new GatewayException($this->paymentGateway, 'Unauthorized');
        }

        $processConfirmationEntity = new ProcessConfirmationEntity();

        $processConfirmationEntity->setParams([
            'id' => $request->get('id'),
            'status' => $request->get('status'),
            'date' => $request->get('approved_date', $request->get('created_date')),
            'details' => $request->all()
        ]);

        return $processConfirmationEntity;
    }


    /**
     * @param Card $card
     */
    public function validateDeleteCard(Card $card)
    {
        //
    }

    /**
     * @param Request $request
     * @return bool
     */
    private function _isValidSignature(Request $request)
    {
        /** @var $date */
        $date = $request->header('X-Date');

        /** @var string $auth_key */
        $auth_key = $this->requestService->getAuthorizationKey($request->getContent(), $date);

        /** @var $authorization_header */
        $authorization_header = $request->header('Authorization');

        return strcmp($auth_key, $authorization_header) == 0;
    }

}