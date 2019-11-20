<?php

namespace App\Services\Cards\Gateways\BrainTree;

use App\Models\Card;
use App\Models\Invoice;
use App\Models\PaymentGateway;
use App\Models\Transaction;
use App\Models\User;
use App\Repositories\PaymentMethodRepository;
use App\Services\Cards\Entities\BrainTree\AddCardEntity;
use App\Services\Cards\Entities\AddCardEntity as BaseAddCardEntity;
use App\Services\Cards\Exceptions\GatewayException;
use App\Services\Cards\Interfaces\RequestInterface;

use Exception;
use Braintree;

class RequestService implements RequestInterface
{
    /**
     * @var Braintree\Gateway
     */
    private $brainTreeClient;

    /** @var \App\Models\PaymentGateway|\Illuminate\Database\Eloquent\Model|null|object  */
    private $paymentGateway;

    /**
     * @var ResponseService $responseService;
     */
    private $responseService;

    /**
     * RequestService constructor.
     * @param PaymentGateway $paymentGateway
     */
    public function __construct(PaymentGateway $paymentGateway)
    {
        $this->brainTreeClient = new Braintree\Gateway([
            'environment' => env('BRAINTREE_ENVIRONMENT'),
            'merchantId' => env('BRAINTREE_MERCHANT_ID'),
            'publicKey' => env('BRAINTREE_PUBLIC_KEY'),
            'privateKey' => env('BRAINTREE_PRIVATE_KEY')
        ]);

        $this->paymentGateway = $paymentGateway;

        $this->responseService = new ResponseService();
    }

    /**
     * @param BaseAddCardEntity|AddCardEntity $addCardEntity
     * @return array|mixed|object
     * @throws GatewayException|Exception
     */
    public function createPaymentMethod(BaseAddCardEntity $addCardEntity)
    {
        /** @var Braintree\Customer $customer */
        $customer = $addCardEntity->getCustomer();

        // If no have cards mark as default
        if (empty($customer->creditCards)) {
            $isDefault = true;
        }

        try{
            $response = $this->brainTreeClient->paymentMethod()->create([
                'customerId' => $customer->id,
                'cardholderName' => $addCardEntity->getCardHolderName(),
                'paymentMethodNonce' => $addCardEntity->getToken(),
                'options' => [
                    'failOnDuplicatePaymentMethod' => true,
                    'makeDefault' => $isDefault ?? false
                ]
            ]);
        } catch (Braintree\Exception\Unexpected $exception){
            logger('[BrainTree] Error Creating card');
            logger($exception->getTraceAsString());

            throw new GatewayException($this->paymentGateway, 'Error Creating card');
        } catch (Exception $exception){
            logger($exception->getTraceAsString());
            logger($exception->getMessage());

            throw new Exception('Internal server error');
        }

        return $response;
    }

    /**
     * @param Card $card
     * @param Invoice $invoice
     * @return array|Braintree\Result\Error|Braintree\Result\Successful|mixed|object
     * @throws GatewayException|Exception
     */
    public function createDebit(Card $card, Invoice $invoice)
    {
        try{
            $response = $this->brainTreeClient->transaction()->sale([
                'paymentMethodToken' => $card->token,
                'amount' => $invoice->total_amount,
                'options' => [
                    'submitForSettlement' => True
                ]
            ]);
        } catch (Braintree\Exception\Unexpected $exception){
            logger('[BrainTree] Error Creating card');
            logger($exception->getTraceAsString());

            throw new GatewayException($this->paymentGateway, 'Error processing transaction');
        } catch (Exception $exception){
            logger($exception->getTraceAsString());
            logger($exception->getMessage());

            throw new Exception('Internal server error');
        }

        return $response;
    }

    /**
     * @param Card $card
     * @return mixed
     * @throws GatewayException|Exception
     */
    public function makeDeleteCard(Card $card) 
    {

        try {
            $response = $this->brainTreeClient->paymentMethod()->delete($card->token);

        } catch (Braintree\Exception\Unexpected $exception) {
            logger('[BrainTree] Error Creating Customer');
            logger($exception->getTraceAsString());

            throw new GatewayException($this->paymentGateway, 'Error Delete card');
        } catch (Exception $exception) {
            logger($exception->getTraceAsString());
            logger($exception->getMessage());

            throw new Exception('Internal server error');
        }

        return $response;
    }

    /**
     * @param Transaction $transaction
     * @return Braintree\Result\Error|Braintree\Result\Successful
     * @throws GatewayException|Exception
     */
    public function makeRefund(Transaction $transaction)
    {

        try {
            $response = $this->brainTreeClient->transaction()->refund($transaction->external_id);
        } catch (Braintree\Exception\Unexpected $exception) {
            logger('[BrainTree] Error processing refund');
            logger($exception->getTraceAsString());

            throw new GatewayException($this->paymentGateway, 'Error processing refund');
        } catch (Exception $exception) {
            logger($exception->getTraceAsString());
            logger($exception->getMessage());

            throw new Exception('Internal server error');
        }

        return $response;
    }

    /**
     * @return string
     * @throws GatewayException
     */
    public function generateClientToken()
    {
        try {
            $clientToken = $this->brainTreeClient->clientToken()->generate();
        } catch (Braintree\Exception $e) {
            logger($e->getMessage());
            throw new GatewayException($this->paymentGateway, 'Could no generate client token');
        }

        return $clientToken;
    }

    /**
     * @param User $user
     * @return bool|Braintree\Customer
     */
    public function getCustomer(User $user)
    {
        try {
            /** @var Braintree\Customer $customer */
            $customer = $this->brainTreeClient->customer()->find($user->id);
        } catch (Braintree\Exception\NotFound $e) {
            logger('[BrainTree] Customer not found');
            logger($e->getMessage());

            return false;
        }

        return $customer;
    }

    /**
     * @param User $user
     * @return Braintree\Customer
     * @throws GatewayException|Exception
     */
    public function createCustomer(User $user)
    {
        try{
            $response = $this->brainTreeClient->customer()->create([
                'id'        => $user->id,
                'firstName' => $user->first_name,
                'lastName'  => $user->last_name
            ]);
        } catch (Braintree\Exception\Unexpected $exception){
            logger('[BrainTree] Error Creating Customer');
            logger($exception->getTraceAsString());

            throw new GatewayException($this->paymentGateway, '[BrainTree] Error Creating Customer');
        } catch (Exception $exception){
            logger($exception->getTraceAsString());
            logger($exception->getMessage());

            throw new Exception('Internal server error');
        }

        return $this->responseService->parseCreateCustomer($response);
    }

    /**
     * @param string $bt_signature
     * @param string $bt_payload
     * @throws Exception|GatewayException
     * @return Braintree\WebhookNotification
     */
    public function getWebHookNotification($bt_signature, $bt_payload)
    {
        try {
            $webhookNotification = $this->brainTreeClient->webhookNotification()->parse(
                $bt_signature, $bt_payload
            );
        } catch (Braintree\Exception\InvalidSignature $e) {
            logger('[BrainTree] Invalid signature');
            logger($e->getMessage());

            throw new GatewayException($this->paymentGateway, '[BrainTree] Invalid signature');
        }

        return $webhookNotification;
    }

    /**
     * @param Card $card
     * @return mixed|Braintree\PaymentMethod
     * @throws GatewayException
     */
    public function getPaymentMethod(Card $card)
    {
        try {
            /** @var Braintree\PaymentMethod $paymentMethod */
            $paymentMethod = $this->brainTreeClient->paymentMethod()->find($card->token);
        } catch (Braintree\Exception\NotFound $e) {
            logger('[BrainTree] Payment Method no exits');
            logger($e->getMessage());
            throw new GatewayException($this->paymentGateway, 'Payment Method no exits');
        }

        return $paymentMethod;
    }
}
