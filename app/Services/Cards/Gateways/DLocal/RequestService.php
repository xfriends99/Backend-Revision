<?php

namespace App\Services\Cards\Gateways\DLocal;

use App\Models\Card;
use App\Models\Invoice;
use App\Models\PaymentGateway;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Cards\Entities\DLocal\AddCardEntity;
use App\Services\Cards\Entities\AddCardEntity as BaseAddCardEntity;
use App\Services\Cards\Exceptions\GatewayException;
use App\Services\Cards\Interfaces\RequestInterface;

use Exception;

class RequestService implements RequestInterface
{
    /** @var mixed */
    private $x_login;

    /** @var mixed */
    private $x_trans_key;

    /** @var mixed  */
    private $x_login_for_webpaystatus;

    /** @var mixed  */
    private $x_trans_key_for_webpaystatus;

    /** @var mixed  */
    private $secret_key;

    /** @var mixed  */
    private $sandbox;

    /** @var array  */
    private $url = array(
        'secure_cards' => '',
        'payments' => ''
    );

    /** @var int  */
    private $errors = 0;

    /** @var \App\Models\PaymentGateway|\Illuminate\Database\Eloquent\Model|null|object  */
    private $paymentGateway;

    /**
     * RequestService constructor.
     * @param PaymentGateway $paymentGateway
     */
    public function __construct(PaymentGateway $paymentGateway)
    {
        $this->paymentGateway = $paymentGateway;

        // Set Credentials
        $this->x_login = env('DLOCAL_X_LOGIN');
        $this->x_trans_key = env('DLOCAL_X_TRANS_KEY');
        $this->x_login_for_webpaystatus = env('DLOCAL_X_LOGIN_FOR_WEBPAYSTATUS');
        $this->x_trans_key_for_webpaystatus = env('DLOCAL_X_TRANS_KEY_FOR_WEBPAYSTATUS');
        $this->secret_key = env('DLOCAL_SECRET_KEY');

        // Set Sandbox mode
        $this->sandbox = env('DLOCAL_SANDBOX');

        // Add and delete credit card URL
        $this->url['secure_cards'] = 'https://api.dlocal.com/secure_cards';

        // Debit credit card URL
        $this->url['payments'] = 'https://api.dlocal.com/payments';

        // Refund transaction URL
        $this->url['refunds'] = ' https://api.dlocal.com/refunds';

        if ($this->sandbox){
            $this->url['secure_cards'] = 'https://sandbox.dlocal.com/secure_cards';
            $this->url['payments'] = 'https://sandbox.dlocal.com/payments';
            $this->url['refunds'] = ' https://sandbox.dlocal.com/refunds';
        }

        $this->errors = 0;
    }

    /**
     * @param BaseAddCardEntity|AddCardEntity $addCardEntity
     * @return array|mixed|object
     * @throws GatewayException|Exception
     */
    public function createPaymentMethod(BaseAddCardEntity $addCardEntity)
    {
        /** @var string $request */
        $request = $this->_prepareAddCardRequest($addCardEntity);

        /** @var $date */
        $date = date('c');

        /** @var string $auth_key */
        $auth_key = $this->getAuthorizationKey($request, $date);

        $headers = [
            "Content-Type: application/json",
            "X-Date: " . $date,
            "X-Login: " . $this->x_login,
            "X-Trans-Key: " . $this->x_trans_key,
            "X-Version: 2.1",
            "User-Agent: MerchantTest / 1.0 ",
            "Authorization: V2-HMAC-SHA256, Signature: " . $auth_key
        ];

        /** @var $curl */
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->url['secure_cards'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $request,
            CURLOPT_HTTPHEADER => $headers,
        ));

        try{
            $response = curl_exec($curl);

            curl_close($curl);

            return $response;

        } catch(Exception $exception){
            logger('[DLocal] Error Creating card');
            logger($exception->getTraceAsString());

            throw new GatewayException($this->paymentGateway, 'Error Creating card');
        }
    }

    /**
     * @param Card $card
     * @param Invoice $invoice
     * @return array|mixed|object
     * @throws GatewayException|Exception
     */
    public function createDebit(Card $card, Invoice $invoice)
    {
        /** @var string $request */
        $request = $this->_prepareDebitCardRequest($card, $invoice);

        /** @var $date */
        $date = date('c');

        /** @var string $auth_key */
        $auth_key = $this->getAuthorizationKey($request, $date);

        $headers = [
            "Content-Type: application/json",
            "X-Date: " . $date,
            "X-Login: " . $this->x_login,
            "X-Trans-Key: " . $this->x_trans_key,
            "X-Version: 2.1",
            "User-Agent: MerchantTest / 1.0 ",
            "Authorization: V2-HMAC-SHA256, Signature: " . $auth_key
        ];

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->url['payments'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $request,
            CURLOPT_HTTPHEADER => $headers,
        ));

        try{
            $response = curl_exec($curl);

            curl_close($curl);

            return $response;

        } catch(Exception $exception){
            logger('[DLocal] Error processing transaction');
            logger($exception->getTraceAsString());

            throw new GatewayException($this->paymentGateway, 'Error processing transaction');
        }
    }

    /**
     * @param Card $card
     * @return mixed
     * @throws GatewayException|Exception
     */
    public function makeDeleteCard(Card $card)
    {
        /** @var  $request */
        $request = $this->_prepareDeleteCardRequest($card);

        /** @var  $date */
        $date = date('c');

        /** @var string $auth_key */
        $auth_key = $this->getAuthorizationKey($request, $date);

        $headers = [
            "Content-Type: application/json",
            "X-Date: " . $date,
            "X-Login: " . $this->x_login,
            "X-Trans-Key: " . $this->x_trans_key,
            "X-Version: 2.1",
            "User-Agent: MerchantTest / 1.0 ",
            "Authorization: V2-HMAC-SHA256, Signature: " . $auth_key
        ];

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->url['secure_cards'] . "/" . $card->getTokenByPaymentGateway($this->paymentGateway),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CUSTOMREQUEST => "DELETE",
            CURLOPT_POSTFIELDS => $request,
            CURLOPT_HTTPHEADER => $headers,
        ));

        try{
            $response = curl_exec($curl);

            curl_close($curl);

            return $response;

        } catch(Exception $exception){
            logger('[DLocal] Error deleting card');
            logger($exception->getTraceAsString());

            throw new GatewayException($this->paymentGateway, 'Error deleting card');
        }
    }

    /**
     * @param Transaction $transaction
     * @return array|mixed|object
     * @throws GatewayException|Exception
     */
    public function makeRefund(Transaction $transaction)
    {
        /** @var  $request */
        $request = $this->_prepareRefundCardRequest($transaction);

        /** @var $date */
        $date = date('c');

        /** @var string $auth_key */
        $auth_key = $this->getAuthorizationKey($request, $date);

        $headers = [
            "Content-Type: application/json",
            "X-Date: " . $date,
            "X-Login: " . $this->x_login,
            "X-Trans-Key: " . $this->x_trans_key,
            "X-Version: 2.1",
            "User-Agent: MerchantTest / 1.0 ",
            "Authorization: V2-HMAC-SHA256, Signature: " . $auth_key
        ];

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->url['refunds'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $request,
            CURLOPT_HTTPHEADER => $headers,
        ));

        try{
            $response = curl_exec($curl);

            curl_close($curl);

            return $response;

        } catch(Exception $exception){
            logger('[DLocal] Error processing transaction');
            logger($exception->getTraceAsString());

            throw new GatewayException($this->paymentGateway, 'Error processing transaction');
        }
    }

    /**
     * @param $request
     * @param $date
     * @return string
     */
    public function getAuthorizationKey($request, $date)
    {
        return hash_hmac("sha256", $this->x_login . $date . $request, $this->secret_key);
    }

    /**
     * @param BaseAddCardEntity|AddCardEntity $addCardEntity
     * @return false|string
     */
    private function _prepareAddCardRequest(BaseAddCardEntity $addCardEntity)
    {
        /** @var User $user */
        $user = $addCardEntity->getUser();

        return json_encode([
            'country' => $user->getCountryCode(),
            'card' => [
                'token' => $addCardEntity->getToken()
            ],
            'payer' => [
                'name' => $addCardEntity->getCardHolderName(),
                'document' => $user->identification,
                'email' => $user->email
            ]
        ]);
    }

    /**
     * @param Card $card
     * @param Invoice $invoice
     * @return false|string
     */
    private function _prepareDebitCardRequest(Card $card, Invoice $invoice)
    {
        /** @var User $user */
        $user = $card->user;

        $token = $card->getTokenByPaymentGateway($this->paymentGateway);

        $request = [
            'amount' => $invoice->total_amount,
            'currency' => 'USD',
            'country' => $user->getCountryCode(),
            'payment_method_id' => 'CARD',
            'payment_method_flow' => 'DIRECT',
            'payer' =>
                [
                    'name' => $user->full_name,
                    'email' => $user->email,
                    'document' => $user->identification
                ],
            'card' =>
                [
                    'token' => $token,
                ],
            'order_id' => $invoice->id,
            'notification_url' => route('api.ipn.notifications', 'dlocal'),
        ];

        return json_encode($request);
    }

    private function _prepareDeleteCardRequest(Card $card)
    {
        $token = $card->getTokenByPaymentGateway($this->paymentGateway);

        $request = [
            'card_id' => $token
        ];

        return json_encode($request);
    }

    /**
     * @param Transaction $transaction
     * @return false|string
     */
    private function _prepareRefundCardRequest(Transaction $transaction)
    {
        $request = [
            'payment_id' => $transaction->external_id,
            'notification_url' => route('api.ipn.notifications', 'dlocal'),
        ];

        return json_encode($request);
    }

}
