<?php

namespace App\Services\Cards\Gateways\BrainTree;

use App\Models\CardBrand;
use App\Models\TransactionStatus;
use App\Models\User;
use App\Repositories\CardBrandRepository;
use App\Repositories\PaymentGatewayRepository;
use App\Repositories\TransactionStatusRepository;
use App\Services\Cards\Entities\BrainTree\ProcessConfirmationEntity;
use App\Services\Cards\Entities\ProcessConfirmationEntity as BaseProcessConfirmationEntity;
use App\Services\Cards\Entities\CardEntity;
use App\Services\Cards\Entities\TransactionEntity;
use App\Services\Cards\Exceptions\GatewayException;
use App\Services\Cards\Exceptions\ParseResponseException;
use App\Services\Cards\Interfaces\ResponseInterface;
use Braintree;

use Carbon\Carbon;
use Exception;

class ResponseService implements ResponseInterface
{
    /** @var \App\Models\PaymentGateway|\Illuminate\Database\Eloquent\Model|null|object  */
    private $paymentGateway;

    /**
     * @var CardBrandRepository
     */
    private $cardBrandRepository;

    /**
     * @var TransactionStatusRepository
     */
    private $transactionStatusRepository;

    /**
     * RequestService constructor.
     */
    public function __construct()
    {
        /** @var PaymentGatewayRepository $paymentGatewayRepository */
        $paymentGatewayRepository = app(PaymentGatewayRepository::class);

        /** @var CardBrandRepository $cardBrandRepository */
        $this->cardBrandRepository = app(CardBrandRepository::class);

        /** @var transactionStatusRepository */
        $this->transactionStatusRepository = app(TransactionStatusRepository::class);

        $this->paymentGateway = $paymentGatewayRepository->getByKey('braintree');
    }

    /**
     * @param User $user
     * @param $response
     * @return CardEntity
     * @throws ParseResponseException|GatewayException|\Exception
     */
    public function parseCreatePaymentMethod(User $user, $response)
    {
        if (!$response->success) {
            foreach($response->errors->deepAll() AS $error) {
                logger($error->code . ': ' . $error->message . '\n');
            }
            throw new ParseResponseException($this->paymentGateway, $response->message);
        }

        /** @var Braintree\PaymentMethod $customer */
        $paymentMethod = $response->paymentMethod;

        /** @var CardBrand $cardBrand */
        if (!$cardBrand = $this->cardBrandRepository->getByType($this->_parseCardBrand($paymentMethod->cardType))) {
            throw new GatewayException($this->paymentGateway, 'Card brand not found');
        }

        return new CardEntity(
            $user,
            $cardBrand,
            $paymentMethod->cardholderName,
            $paymentMethod->token,
            $paymentMethod->expirationYear,
            $paymentMethod->expirationMonth,
            $paymentMethod->last4,
            json_encode($paymentMethod),
            null,
            null,
            null
        );
    }

    /**
     * @param array|mixed|object $response
     * @return TransactionEntity|mixed
     * @throws ParseResponseException|Exception
     */
    public function parseCreateDebit($response)
    {
        if (!$response->success) {
            logger('Validation errors:');
            logger($response->errors->deepAll());

            throw new ParseResponseException($this->paymentGateway, 'Transaction validate errors');
        } else if (!$response->transaction) {
            logger('[BrainTree] Error processing transaction:');
            logger('code: ' . $response->transaction->processorResponseCode);
            logger('text: ' . $response->transaction->processorResponseText);

            throw new ParseResponseException($this->paymentGateway, 'Error processing transaction');
        }

        /** @var Braintree\Transaction $transaction */
        $transaction = $response->transaction;
        return $this->getTransactionEntity($transaction);

    }

    /**
     * @param BaseProcessConfirmationEntity|ProcessConfirmationEntity $processConfirmationEntity
     * @return TransactionEntity
     * @throws GatewayException
     */
    public function parseMakeProcessConfirmation(BaseProcessConfirmationEntity $processConfirmationEntity)
    {
        /**
         * @var Braintree\WebhookNotification
         */
        $webHookNotification = $processConfirmationEntity->getWebHookNotification();

        // Parse BrainTree status
        $status = $this->_parseTransactionStatus($webHookNotification->transaction->status);

        /** @var TransactionStatus $transactionStatus */
        if (!$transactionStatus = $this->transactionStatusRepository->getByKey($status)) {
            throw new GatewayException($this->paymentGateway, 'Status {$status} not found');
        }


        return new TransactionEntity($transactionStatus,
            $webHookNotification->transaction->id,
            $webHookNotification->transaction->amount,
            json_encode($webHookNotification->transaction),
            Carbon::parse($webHookNotification->transaction->updatedAt)
        );
    }

    /**
     * @param $response
     * @throws GatewayException
     */
    public function parseMakeDeleteCard($response) : void {
        if (!$response->success) {
            foreach($response->errors->deepAll() AS $error) {
                logger($error->code . ': ' . $error->message . '\n');
            }
            throw new GatewayException($this->paymentGateway, 'Card no delete in Braintree');
        }
    }

    /**
     * @param array|mixed|object $response
     * @return TransactionEntity|mixed
     * @throws Exception
     */
    public function parseMakeRefund($response) {
        if (!$response->success) {
            logger('Validation errors:');
            logger($response->errors->deepAll());
        }

        /** @var Braintree\Transaction $transaction */
        $transaction = $response->transaction;
        return $this->getTransactionEntity($transaction);
    }

    /**
     * @param Braintree\Result\Successful|Braintree\Result\Error
     * @return Braintree\Customer
     * @throws ParseResponseException
     */
    public function parseCreateCustomer($response)
    {
        if (!$response->success) {
            foreach($response->errors->deepAll() AS $error) {
                logger($error->code . ': ' . $error->message . '\n');
            }

            throw new ParseResponseException($this->paymentGateway, 'Error creating customer');
        }

        return $response->customer;
    }


    /**
     * @param $brand
     * @return string
     */
    private function _parseCardBrand($brand)
    {
        $brand = strtolower($brand);

        switch ($brand) {
            case 'ae':
                return 'ax';
            case 'dc':
                return 'di';
            case 'ds':
                return 'dc';
            case 'visa':
                return 'vi';
            case 'mastercard':
                return 'mc';
            case 'american express':
                return 'ax';
            case 'diners club':
                return 'di';
            case 'discover':
                return 'dc';
            case 'jcb':
                return 'jc';
            case 'elo':
                return 'el';
            case 'carte blanche':
                return 'cb';
            case 'china unionPay':
                return 'cu';
            case 'laser':
                return 'la';
            case 'maestro':
                return 'mt';
            case 'solo':
                return 'sl';
            case 'switch':
                return 'sw';
        }

        return $brand;
    }

    /**
     * @param $status
     * @return string
     * @throws GatewayException
     */
    private function _parseTransactionStatus($status)
    {
        $status = strtoupper($status);

        switch ($status) {
            case 'SETTLED':
                return 'approved';
            case 'FAILED':
            case 'GATEWAY_REJECTED':
                return 'rejected';
            case 'SETTLEMENT_PENDING':
            case 'AUTHORIZED':
            case 'AUTHORIZING':
            case 'SUBMITTED_FOR_SETTLEMENT':
            case 'SETTLING':
                return 'pending';
            case 'AUTHORIZATION_EXPIRED':
            case 'SETTLEMENT_DECLINED':
            case 'PROCESSOR_DECLINED':
                return 'cancelled';
            default:
                throw new GatewayException($this->paymentGateway, 'Unrecognized transaction status');
        }
    }

    /**
     * @param Braintree\Transaction $transaction
     * @return TransactionEntity
     * @throws Exception
     */
    private function getTransactionEntity(Braintree\Transaction $transaction): TransactionEntity
    {
        // Parse Braintree status
        $status = $this->_parseTransactionStatus($transaction->status);

        /** @var TransactionStatus $transactionStatus */
        if (!$transactionStatus = $this->transactionStatusRepository->getByKey($status)) {
            throw new Exception ("Status {$status} not found");
        }

        return new TransactionEntity(
            $transactionStatus,
            $transaction->id,
            $transaction->amount,
            json_encode($transaction)
        );
    }
}
