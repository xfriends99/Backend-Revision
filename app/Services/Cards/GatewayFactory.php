<?php

namespace App\Services\Cards;

use App\Models\Card;
use App\Models\Invoice;
use App\Models\PaymentGateway;
use App\Models\Transaction;
use App\Models\User;
use App\Repositories\CardRepository;
use App\Repositories\TransactionRepository;
use App\Repositories\PaymentGatewayRepository;
use App\Services\Cards\Gateways\DLocalService;
use App\Services\Cards\Gateways\PaymentezService;
use App\Services\Cards\Gateways\BrainTreeService;
use Illuminate\Http\Request;
use Exception;

abstract class GatewayFactory
{
    
    /** @var  CardRepository */
    protected $cardRepository;

    /** @var  TransactionRepository */
    protected $transactionRepository;

    /** @var PaymentGatewayRepository  */
    protected $paymentGatewayRepository;

    /**
     * GatewayFactory constructor.
     * @param CardRepository $cardRepository
     * @param TransactionRepository $transactionRepository
     * @param PaymentGatewayRepository $paymentGatewayRepository
     */
    public function __construct(CardRepository $cardRepository, 
        TransactionRepository $transactionRepository, 
        PaymentGatewayRepository $paymentGatewayRepository)
    {
        $this->cardRepository = $cardRepository;
        $this->transactionRepository = $transactionRepository;
        $this->paymentGatewayRepository = $paymentGatewayRepository;
    }

    /**
     * @param PaymentGateway $paymentGateway
     * @return AbstractCardService
     * @throws Exception
     */
    public static function detectGateway(PaymentGateway $paymentGateway)
    {
        if ($paymentGateway->isDlocal()) {
            return new DLocalService();
        } else if ($paymentGateway->isBrainTree()) {
            return new BrainTreeService();
        } else if ($paymentGateway->isPaymentez()) {
            return new PaymentezService();
        }
        
        throw new Exception('PaymentService not implemented');         
        
    }

    /**
     * @param Card $card
     * @param Invoice $invoice
     * @return Transaction|bool|mixed
     * @throws Exception
     */
    public static function debit(Card $card, Invoice $invoice)
    {
        /** @var PaymentGateway $paymentGateway */
        if ( !$paymentGateway = $card->getFirstPaymentGateway() ) {
            throw new Exception('PaymentGateway not found');
        }

        /** @var AbstractCardService $gatewayService */
        $gatewayService = self::detectGateway($paymentGateway);

        return $gatewayService->debit($card, $invoice);
    }

    /**
     * @param User $user
     * @param PaymentGateway $paymentGateway
     * @param array $attributes
     * @return Card
     * @throws Exception
     */
    public static function addCard(User $user, PaymentGateway $paymentGateway, $attributes = [])
    {
        /** @var AbstractCardService $gatewayService */
        $gatewayService = self::detectGateway($paymentGateway);
        
        return $gatewayService->addCard($user, $attributes);
    }

    /**
     * @param Request $request
     * @param PaymentGateway $paymentGateway
     * @return bool|mixed
     * @throws Exception
     */
    public static function processConfirmation(Request $request, PaymentGateway $paymentGateway)
    {
        /** @var AbstractCardService $gatewayService */
        $gatewayService = self::detectGateway($paymentGateway);

        $gatewayService->processConfirmation($request);
    }

    /**
     * @param Transaction $transaction
     * @return bool|mixed
     * @throws Exception
     */
    public static function refund(Transaction $transaction)
    {
        /** @var Card $card */
        if (!$card = $transaction->card ) {
            throw new Exception('Card not found');
        }

        if ( !$paymentGateway = $card->getFirstPaymentGateway() ) {
            throw new Exception('PaymentGateway not found');
        }

        /** @var AbstractCardService $gatewayService */
        $gatewayService = self::detectGateway($paymentGateway);
        
        return $gatewayService->refund($transaction);
        
    }

    /**
     * @param Card $card
     * @return bool|mixed
     * @throws Exception
     */
    public static function deleteCard(Card $card)
    {
        if ( !$paymentGateway = $card->getFirstPaymentGateway() ) {
            throw new Exception('PaymentGateway not found');
        }

        /** @var AbstractCardService $gatewayService */
        $gatewayService = self::detectGateway($paymentGateway);
        
        return $gatewayService->deleteCard($card);
    }

    /**
     * @param PaymentGateway $paymentGateway
     * @return string
     * @throws Exception
     */
    public static function token(PaymentGateway $paymentGateway)
    {
        /** @var AbstractCardService $gatewayService */
        $gatewayService = self::detectGateway($paymentGateway);

        return $gatewayService->generateClientToken();
    }

    /**
     * @param Card $card
     * @return bool|mixed
     * @throws Exception
     */
    public static function markAsDefault(Card $card)
    {
        if ( !$paymentGateway = $card->getFirstPaymentGateway() ) {
            throw new Exception('PaymentGateway not found');
        }

        /** @var AbstractCardService $gatewayService */
        $gatewayService = self::detectGateway($paymentGateway);
        
        return $gatewayService->markAsDefault($card);
    }

    /**
     * @param PaymentGateway $paymentGateway
     * @throws Exception
     * @return array
     */
    public static function validateRequest(PaymentGateway $paymentGateway)
    {
        /** @var AbstractCardService $gatewayService */
        $gatewayService = self::detectGateway($paymentGateway);

        return $gatewayService->getValidateInstance()->validateRequest();
    }

    /**
     * @param PaymentGateway $paymentGateway
     * @throws Exception
     * @return array
     */
    public static function validateWebHookRequest(PaymentGateway $paymentGateway)
    {
        /** @var AbstractCardService $gatewayService */
        $gatewayService = self::detectGateway($paymentGateway);

        return $gatewayService->getValidateInstance()->validateWebHookRequest();
    }

    /**
     * @param PaymentGateway $paymentGateway
     * @param $amount
     * @return float
     * @throws Exception
     */
    public static function getAmount(PaymentGateway $paymentGateway, $amount)
    {
        /** @var AbstractCardService $gatewayService */
        $gatewayService = self::detectGateway($paymentGateway);

        return $gatewayService->getAmount($amount);
    }

}