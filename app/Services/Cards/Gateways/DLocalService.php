<?php

namespace App\Services\Cards\Gateways;

use App\Models\Card;
use App\Models\Invoice;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Cards\Entities\CardEntity;
use App\Repositories\PaymentGatewayRepository;
use App\Services\Cards\AbstractCardService;
use App\Services\Cards\Entities\TransactionEntity;
use App\Services\Cards\Exceptions\GatewayException;
use Illuminate\Http\Request;
use Exception;

use App\Services\Cards\Entities\DLocal\AddCardEntity;
use App\Services\Cards\Entities\AddCardEntity as BaseAddCardEntity;
use App\Services\Cards\Entities\DLocal\ProcessConfirmationEntity;
use App\Services\Cards\Entities\ProcessConfirmationEntity as BaseProcessConfirmationEntity;

use App\Services\Cards\Gateways\DLocal\RequestService;
use App\Services\Cards\Gateways\DLocal\ResponseService;
use App\Services\Cards\Gateways\DLocal\ValidateService;

class DLocalService extends AbstractCardService
{
    /** @var ValidateService|\App\Services\Cards\Interfaces\ValidateInterface  */
    private $validateInterface;

    /** @var RequestService|\App\Services\Cards\Interfaces\RequestInterface  */
    private $requestInterface;

    /** @var ResponseService|\App\Services\Cards\Interfaces\ResponseInterface  */
    private $responseInterface;
    /**
     * DLocalService constructor.
     */
    public function __construct()
    {
        /** @var PaymentGatewayRepository $paymentGatewayRepository */
        $paymentGatewayRepository = app(PaymentGatewayRepository::class);

        parent::__construct($paymentGatewayRepository->getByKey('dlocal'));

        $this->validateInterface = $this->getValidateInstance();
        $this->requestInterface = $this->getRequestInstance();
        $this->responseInterface = $this->getResponseInstance();
    }

    /**
     * @return bool|mixed|string
     * @throws GatewayException
     */
    public function makeGenerateClientToken()
    {
        throw new GatewayException($this->paymentGateway, 'Generate Client Token is not implemented');
    }

    /**
     * @param array $attributes
     * @return BaseAddCardEntity|AddCardEntity
     */
    protected function validateAddCard(array $attributes)
    {
        return $this->validateInterface->validateAddCard($attributes);
    }

    /**
     * @param User $user
     * @param BaseAddCardEntity|AddCardEntity $addCardEntity
     * @return mixed|object|array
     * @throws Exception
     */
    protected function makeAddCard(User $user, BaseAddCardEntity $addCardEntity)
    {
        $addCardEntity->setUser($user);

        return $this->requestInterface->createPaymentMethod($addCardEntity);
    }

    /**
     * @param User $user
     * @param $response
     * @return CardEntity
     * @throws GatewayException|Exception
     */
    protected function parseAddCard(User $user, $response)
    {
        return $this->responseInterface->parseCreatePaymentMethod($user, $response);
    }

    /**
     * @param Card $card
     * @param Invoice $invoice
     * @return array|mixed|object
     * @throws GatewayException
     */
    protected function makeDebit(Card $card, Invoice $invoice)
    {
        return $this->requestInterface->createDebit($card, $invoice);
    }

    /**
     * @param array|mixed|object $response
     * @return TransactionEntity
     * @throws \App\Services\Cards\Exceptions\ParseResponseException
     */
    protected function parseDebit($response)
    {
        return $this->responseInterface->parseCreateDebit($response);
    }

    /**
     * @param Request $request
     * @return BaseProcessConfirmationEntity|ProcessConfirmationEntity
     * @throws GatewayException
     */
    protected function validateProcessConfirmation(Request $request)
    {
        return $this->validateInterface->validateProcessConfirmation($request);
    }

    /**
     * @param BaseProcessConfirmationEntity|ProcessConfirmationEntity $processConfirmationEntity
     * @return TransactionEntity
     * @throws GatewayException
     */
    protected function parseProcessConfirmation(BaseProcessConfirmationEntity $processConfirmationEntity)
    {
        return $this->responseInterface->parseMakeProcessConfirmation($processConfirmationEntity);
    }

    /**
     * @param Card $card
     * @return mixed|void
     * @throws Exception
     */
    protected function validateDeleteCard(Card $card)
    {
        //
    }

    /**
     * @param Card $card
     * @return mixed
     * @throws GatewayException
     */
    protected function makeDeleteCard(Card $card)
    {
        return $this->requestInterface->makeDeleteCard($card);
    }

    /**
     * @param mixed|array|object $response
     * @return true
     * @throws GatewayException|Exception
     */
    protected function parseMakeDeleteCard($response)
    {
        return $this->responseInterface->parseMakeDeleteCard($response);
    }

    /**
     * @param Transaction $transaction
     * @return array|mixed|object
     * @throws GatewayException
     */
    protected function makeRefund(Transaction $transaction)
    {
        return $this->requestInterface->makeRefund($transaction);
    }

    /**
     * @param $response
     * @return TransactionEntity|mixed
     * @throws GatewayException
     */
    protected function parseMakeRefund($response)
    {
        return $this->responseInterface->parseMakeRefund($response);
    }

    /**
     * @return ValidateService|\App\Services\Cards\Interfaces\ValidateInterface
     */
    public function getValidateInstance()
    {
        return new ValidateService($this->paymentGateway);
    }

    /**
     * @return RequestService|\App\Services\Cards\Interfaces\RequestInterface
     */
    public function getRequestInstance()
    {
        return new RequestService($this->paymentGateway);
    }

    /**
     * @return ResponseService|\App\Services\Cards\Interfaces\ResponseInterface
     */
    public function getResponseInstance()
    {
        return new ResponseService($this->paymentGateway);
    }

    /**
     * @param float $amount
     * @return float
     */
    public function getAmount($amount)
    {
        return $amount;
    }
}