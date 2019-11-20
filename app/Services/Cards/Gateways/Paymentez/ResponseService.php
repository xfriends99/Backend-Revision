<?php

namespace App\Services\Cards\Gateways\Paymentez;

use App\Models\TransactionStatus;
use App\Models\User;
use App\Repositories\CardBrandRepository;
use App\Repositories\PaymentGatewayRepository;
use App\Repositories\TransactionStatusRepository;
use App\Services\Cards\Entities\ProcessConfirmationEntity;
use App\Services\Cards\Entities\TransactionEntity;
use App\Services\Cards\Interfaces\ResponseInterface;
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

        $this->paymentGateway = $paymentGatewayRepository->getByKey('paymentez');
    }

    /**
     * @param array|mixed|object $response
     * @return TransactionEntity|mixed
     * @throws Exception
     */
    public function parseCreateDebit($response)
    {
        $response = json_decode($response->getBody()->getContents(), true);

        // Log response
        logger('[Casilleros] Paymentez - Debit Card Response');
        logger($response);


        // Parse status informed to Paymentez
        return $this->getTransactionEntity($response);
    }

    /**
     * @param array|mixed|object $response
     * @return mixed
     */
    public function parseMakeDeleteCard($response)
   {
       // Log response
       logger('[Casilleros] Paymentez - Delete Card Response');
       logger($response);

       return $response->getBody()->getContents();
   }

    /**
     * @param array|mixed|object $response
     * @return TransactionEntity|mixed
     * @throws Exception
     */
   public function parseMakeRefund($response)
   {
       $response = json_decode($response->getBody()->getContents(), true);

       // Log response
       logger('[Casilleros] Paymentez - Refund Transaction Response');
       logger($response);

       // Parse status informed to Paymentez
       return $this->getTransactionEntity($response);
   }

    /**
     * @param $brand
     * @return mixed|string
     */
    public function _parseCardBrand($brand)
    {
        return strtolower($brand);
    }

    public function parseMakeProcessConfirmation(ProcessConfirmationEntity $processConfirmationEntity)
    {
        // TODO: Implement parseMakeProcessConfirmation() method.
    }

    public function parseCreatePaymentMethod(User $user, $response)
    {
        // TODO: Implement parseCreatePaymentMethod() method.
    }

    /**
     * @param $status
     * @return string
     */
    private function _parseTransactionStatus($status)
    {
        switch ($status) {
            case 'success':
                return 'approved';
            case 'failure':
                return 'rejected';
            case 'pending';
                return 'pending';
            default:
                return 'pending';
        }
    }


    /**
     * @param $response
     * @return TransactionEntity
     * @throws Exception
     */
    private function getTransactionEntity($response): TransactionEntity
    {
        // Parse status
        $status = $this->_parseTransactionStatus($response['transaction']['status']);

        /** @var TransactionStatus $transactionStatus */
        if (!$transactionStatus = $this->transactionStatusRepository->getByKey($status)) {
            throw new Exception("Status {$status} not found");
        }

        return new TransactionEntity(
            $transactionStatus,
            $response['transaction']['id'],
            $response['transaction']['amount'],
            json_encode($response['transaction'])
        );
    }

}
