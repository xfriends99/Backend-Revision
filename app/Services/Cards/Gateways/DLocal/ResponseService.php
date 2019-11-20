<?php

namespace App\Services\Cards\Gateways\DLocal;

use App\Models\CardBrand;
use App\Models\PaymentGateway;
use App\Models\TransactionStatus;
use App\Models\User;
use App\Repositories\CardBrandRepository;
use App\Repositories\TransactionStatusRepository;
use App\Services\Cards\Entities\DLocal\ProcessConfirmationEntity;
use App\Services\Cards\Entities\ProcessConfirmationEntity as BaseProcessConfirmationEntity;
use App\Services\Cards\Entities\CardEntity;
use App\Services\Cards\Entities\TransactionEntity;
use App\Services\Cards\Exceptions\GatewayException;
use App\Services\Cards\Exceptions\ParseResponseException;
use App\Services\Cards\Interfaces\ResponseInterface;

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
     * @param PaymentGateway $paymentGateway
     */
    public function __construct(PaymentGateway $paymentGateway)
    {
        /** @var CardBrandRepository $cardBrandRepository */
        $this->cardBrandRepository = app(CardBrandRepository::class);

        /** @var transactionStatusRepository */
        $this->transactionStatusRepository = app(TransactionStatusRepository::class);

        $this->paymentGateway = $paymentGateway;
    }

    /**
     * @param User $user
     * @param $response
     * @return CardEntity
     * @throws GatewayException|Exception
     */
    public function parseCreatePaymentMethod(User $user, $response)
    {
        $response = json_decode($response, true);

        /** @var CardBrand $cardBrand */
        if (!$cardBrand = $this->cardBrandRepository->getByType($this->_parseCardBrand($response['brand']))) {
            throw new GatewayException($this->paymentGateway, 'Card brand not found');
        }

        return new CardEntity(
            $user,
            $cardBrand,
            $response['holder_name'],
            $response['card_id'],
            $response['expiration_year'],
            $response['expiration_month'],
            $response['last4'],
            json_encode($response),
            null,
            null,
            null
        );
    }

    /**
     * @param array|mixed|object $response
     * @return TransactionEntity
     * @throws Exception
     */
    public function parseCreateDebit($response)
    {
        /** @var $response */
        $response = json_decode($response, true);

        if(!isset($response['status'])){
            logger('[DLocal] Error creating debit');
            logger(json_encode($response));

            throw new ParseResponseException($this->paymentGateway, 'Error creating debit');
        }
        // Parse DLocal status
        /** @var string $status */
        $status = $this->_parseTransactionStatus($response['status']);

        /** @var TransactionStatus $transactionStatus */
        if (!$transactionStatus = $this->transactionStatusRepository->getByKey($status)) {
            throw new Exception ("Status {$status} not found");
        }

        return new TransactionEntity(
            $transactionStatus,
            $response['id'],
            $response['amount'],
            json_encode($response),
            isset($response['approved_date']) ? Carbon::parse($response['approved_date']) : Carbon::parse($response['created_date'])
        );
    }

    /**
     * @param BaseProcessConfirmationEntity|ProcessConfirmationEntity $processConfirmationEntity
     * @return TransactionEntity
     * @throws GatewayException
     */
    public function parseMakeProcessConfirmation(BaseProcessConfirmationEntity $processConfirmationEntity)
    {
        // Parse BrainTree status
        /** @var string $status */
        $status = $this->_parseTransactionStatus($processConfirmationEntity->getStatus());

        /** @var TransactionStatus $transactionStatus */
        if (!$transactionStatus = $this->transactionStatusRepository->getByKey($status)) {
            throw new GatewayException($this->paymentGateway, "Status {$status} not found");
        }

        return new TransactionEntity($transactionStatus,
            $processConfirmationEntity->getId(),
            null,
            json_encode($processConfirmationEntity->getDetails()),
            $processConfirmationEntity->getDate() ? Carbon::parse($processConfirmationEntity->getDate()) : null
        );
    }

    /**
     * @param $response
     * @throws GatewayException
     * @return bool
     */
    public function parseMakeDeleteCard($response)
    {
        $response = json_decode($response, true);

        if (isset($response['deleted']) and $response['deleted']) {
            return true;
        }

        logger('Card not deleted in DLocal');
        logger(json_encode($response));
        throw new GatewayException($this->paymentGateway, 'Card not deleted in DLocal');
    }

    /**
     * @param array|mixed|object $response
     * @return TransactionEntity|mixed
     * @throws Exception
     */
    public function parseMakeRefund($response)
    {
        $response = json_decode($response, true);

        if (!isset($response['status_code']) || $response['status_code'] == '200') {
            logger('[DLocal] Refund not processed correctly');
            logger(json_encode($response));

            throw new GatewayException($this->paymentGateway, 'Refund not processed correctly');
        }

        // Parse Braintree status
        $status = $this->_parseTransactionStatus($response['status']);

        /** @var TransactionStatus $transactionStatus */
        if (!$transactionStatus = $this->transactionStatusRepository->getByKey($status)) {
            throw new Exception ("Status {$status} not found");
        }

        return new TransactionEntity(
            $transactionStatus,
            $response['id'],
            $response['amount'],
            json_encode($response)
        );
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
        switch ($status) {
            case 'PAID':
                return 'approved';
            case 'SUCCESS':
                return 'approved';
            case 'REJECTED':
                return 'rejected';
            case 'PENDING':
                return 'pending';
            case 'CANCELLED':
                return 'cancelled';
            case 'EXPIRED':
                return 'cancelled';
            default:
                throw new GatewayException($this->paymentGateway, 'Unrecognized transaction status');
        }
    }
}
