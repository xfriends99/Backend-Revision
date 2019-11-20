<?php

namespace App\Services\Cards\Gateways\BrainTree;

use App\Models\Card;
use App\Models\PaymentGateway;
use App\Services\Cards\Entities\BrainTree\AddCardEntity;
use App\Services\Cards\Entities\AddCardEntity as BaseAddCardEntity;
use App\Services\Cards\Entities\BrainTree\ProcessConfirmationEntity;
use App\Services\Cards\Entities\ProcessConfirmationEntity as BaseProcessConfirmationEntity;
use App\Services\Cards\Exceptions\GatewayException;
use App\Services\Cards\Interfaces\ValidateInterface;
use Braintree\WebhookNotification;
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
            'bt_signature' => 'required|string',
            'bt_payload' => 'required|string'
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
        /** @var WebhookNotification $webhookNotification */
        $webHookNotification = $this->requestService->getWebHookNotification($request->get('bt_signature'), $request->get('bt_payload'));

        // Validate if this is the kind of notificaction from braintree
        if (!$this->_isValidKind($webHookNotification->kind)) {
            throw new GatewayException($this->paymentGateway, 'Invalid kind of notification');
        }

        // Check required object
        if (!isset($webHookNotification->transaction)) {
            throw new GatewayException($this->paymentGateway, 'Transaction is required');
        }

        $processConfirmationEntity = new ProcessConfirmationEntity();

        $processConfirmationEntity->setWebHookNotification($webHookNotification);

        return $processConfirmationEntity;
    }


    /**
     * @param Card $card
     * @throws GatewayException
     */
    public function validateDeleteCard(Card $card)
    {
        $this->requestService->getPaymentMethod($card);
    }

    /**
     * @param string $kind
     * @return bool
     */
    private function _isValidKind($kind)
    {
        if ($kind == 'transaction_settlement_declined' || $kind == 'transaction_settled') {
            return true;
        }

        return false;
    }
}
