<?php

namespace App\Services\Cards\Gateways;

use App\Models\Card;
use App\Models\Invoice;
use App\Models\PaymentMethod;
use App\Models\Transaction;
use App\Models\TransactionStatus;
use App\Models\User;
use App\Services\Cards\CardEntity;
use App\Repositories\PaymentGatewayRepository;
use App\Services\Cards\AbstractCardService;
use App\Repositories\InvoiceRepository;
use App\Repositories\PaymentMethodRepository;
use App\Repositories\TransactionRepository;
use App\Repositories\TransactionStatusRepository;
use App\Repositories\TransactionTypeRepository;
use App\Services\Cards\Entities\AddCardEntity;
use App\Services\Cards\Entities\ProcessConfirmationEntity;
use App\Services\Cards\Gateways\Paymentez\RequestService;
use App\Services\Cards\Gateways\Paymentez\ResponseService;
use App\Services\Cards\Gateways\Paymentez\ValidateService;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Exception;
use Illuminate\Http\Request;

/**
 * Created by PhpStorm.
 * User: plabin
 * Date: 16/4/2019
 * Time: 3:07 PM
 */
class PaymentezService extends AbstractCardService
{
    /** @var  TransactionStatusRepository */
    protected $transactionStatusRepository;

    /** @var  TransactionTypeRepository */
    protected $transactionTypeRepository;

    /** @var  PaymentMethodRepository */
    protected $paymentMethodRepository;

    /** @var  InvoiceRepository */
    protected $invoiceRepository;

    /** @var  TransactionRepository */
    protected $transactionRepository;

    /** @var ValidateService|\App\Services\Cards\Interfaces\ValidateInterface  */
    private $validateInterface;

    /** @var RequestService|\App\Services\Cards\Interfaces\RequestInterface  */
    private $requestInterface;

    /** @var ResponseService|\App\Services\Cards\Interfaces\ResponseInterface  */
    private $responseInterface;

    /**
     * PaymentezService constructor.
     */
    public function __construct()
    {
        /** @var PaymentGatewayRepository $paymentGatewayRepository */
        $paymentGatewayRepository = app(PaymentGatewayRepository::class);

        parent::__construct($paymentGatewayRepository->getByKey('paymentez'));


        $this->transactionStatusRepository = app(TransactionStatusRepository::class);
        $this->transactionTypeRepository = app(TransactionTypeRepository::class);
        $this->paymentMethodRepository = app(PaymentMethodRepository::class);
        $this->invoiceRepository = app(InvoiceRepository::class);
        $this->transactionRepository = app(TransactionRepository::class);

        $this->validateInterface = $this->getValidateInstance();
        $this->requestInterface = $this->getRequestInstance();
        $this->responseInterface = $this->getResponseInstance();
    }

    /**
     * @param Card $card
     * @param Invoice $invoice
     * @return mixed|void
     * @throws \App\Services\Cards\Exceptions\GatewayException
     */
    protected function makeDebit(Card $card, Invoice $invoice)
    {
        $this->requestInterface->createDebit($card, $invoice);
    }

    /**
     * @param array|mixed|object $response
     * @return \App\Services\Cards\Entities\TransactionEntity|void
     * @throws \App\Services\Cards\Exceptions\ParseResponseException
     */
    protected function parseDebit($response)
    {
        $this->responseInterface->parseCreateDebit($response);
    }

    /**
     * @param Card $card
     * @return mixed|\Psr\Http\Message\ResponseInterface|string
     * @throws \App\Services\Cards\Exceptions\GatewayException
     */
    protected function makeDeleteCard(Card $card)
    {
        return $this->requestInterface->makeDeleteCard($card);
    }

    /**
     * @param $response
     * @return mixed|void
     * @throws \App\Services\Cards\Exceptions\GatewayException
     */
    protected function parseMakeDeleteCard($response)
    {
        $this->responseInterface->parseMakeDeleteCard($response);
    }

    /**
     * @param Transaction $transaction
     * @return array|mixed|object|void
     * @throws \App\Services\Cards\Exceptions\GatewayException
     */
    protected function makeRefund(Transaction $transaction)
    {
        $this->requestInterface->makeRefund($transaction);
    }

    /**
     * @param array|mixed|object $response
     * @return mixed|void
     * @throws \App\Services\Cards\Exceptions\GatewayException
     */
    protected function parseMakeRefund($response)
    {
        $this->responseInterface->parseMakeRefund($response);
    }

    protected function makeAddCard(User $user, AddCardEntity $addCardEntity)
    {
        // TODO: Implement makeAddCard() method.
    }

    protected function makeGenerateClientToken()
    {
        // TODO: Implement makeGenerateClientToken() method.
    }

    protected function parseAddCard(User $user, $response)
    {
        // TODO: Implement parseAddCard() method.
    }

    protected function parseProcessConfirmation(ProcessConfirmationEntity $processConfirmationEntity)
    {
        // TODO: Implement parseProcessConfirmation() method.
    }

    protected function validateAddCard(array $attributes)
    {
        // TODO: Implement validateAddCard() method.
    }

    protected function validateDeleteCard(Card $card)
    {
        // TODO: Implement validateDeleteCard() method.
    }

    protected function validateProcessConfirmation(Request $request)
    {
        // TODO: Implement validateProcessConfirmation() method.
    }


    public function getValidateInstance()
    {
        return new ValidateService($this->paymentGateway);
    }

    public function getRequestInstance()
    {
        return new RequestService($this->paymentGateway);
    }

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
