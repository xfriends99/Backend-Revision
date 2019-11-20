<?php

namespace App\Services\Cards\Gateways\Paymentez;

use App\Models\Card;
use App\Models\Invoice;
use App\Models\PaymentGateway;
use App\Models\Transaction;
use App\Services\Cards\Entities\AddCardEntity;
use App\Services\Cards\Interfaces\RequestInterface;
use GuzzleHttp\Client;

class RequestService implements RequestInterface
{
    /**
     * @var Client
     */
    private $client;

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
        /**
         * creating token
         */
        $unix_timestamp = strval(time());
        $paymentez_app_key = env('VUE_PAYMENTEZ_KEY_SERVER');
        $paymentez_app_code = env('VUE_PAYMENTEZ_CODE_SERVER');
        $uniq_token_string = $paymentez_app_key . $unix_timestamp;
        $uniq_token_hash  = hash('sha256', $uniq_token_string);
        $auth_token = base64_encode("{$paymentez_app_code};{$unix_timestamp};{$uniq_token_hash}");

        /**
         * creating Guzzle Client
         */
        $headers = [
            'Content-Type' => 'application/json',
            'Auth-Token' => $auth_token
        ];
        $this->client = new Client(['headers' => $headers]);


        $this->paymentGateway = $paymentGateway;

        $this->responseService = new ResponseService();
    }

    public function createDebit(Card $card, Invoice $invoice)
    {
        $request = $this->ParamsToRequestDebit($card, $invoice->amount);

        try {
            $response = $this->client->post(env('PAYMENTEZ_DEBIT_CARD'), [
                'body' => json_encode($request)
            ]);
        } catch (Exception $exception) {
            logger($exception->getMessage());
            logger($exception->getTraceAsString());

            throw new Exception ('[Casilleros] Paymentez - Comunicación fallida con la plataforma');
        }


        return $response;

    }

    /**
     * @param Card $card
     * @return mixed|\Psr\Http\Message\ResponseInterface|string
     */
    public function makeDeleteCard(Card $card)
    {

        $request = [
            'card' => [
                'token' => $card->getTokenByPaymentGateway($this->paymentGateway),
            ],
            'user' => [
                'id' => "{$card->user_id}"
            ]
        ];


        try {
            // Call API
            $response = $this->client->post(env('PAYMENTEZ_DELETE_CARD'), [
                'body' => json_encode($request)
            ]);
        } catch (Exception $exception) {
            logger($exception->getMessage());
            logger($exception->getTraceAsString());

            throw new Exception ('[Casilleros] Paymentez - Comunicación fallida con la plataforma');
        }


        return $response;

    }


    /**
     * @param Transaction $transaction
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function makeRefund(Transaction $transaction)
    {
        $request = $this->ParamsToRequestRefund($transaction);

        try {
            // Call API
            $response = $this->client->post(env('PAYMENTEZ_REFUND'), [
                'body' => json_encode($request)
            ]);
        } catch (Exception $exception) {
            logger($exception->getMessage());
            logger($exception->getTraceAsString());

            throw new Exception ('[Casilleros] Paymentez - Comunicación fallida con la plataforma');
        }

        return $response;
    }

    public function createPaymentMethod(AddCardEntity $addCardEntity)
    {
        // TODO: Implement createPaymentMethod() method.
    }

    /**
     * @param Card $card
     * @param $amount
     * @return array
     */
    private function ParamsToRequestDebit(Card $card, $amount): array
    {
        $user = $card->user;

        // Get card token by Gateway
        $token = $card->getTokenByPaymentGateway($this->paymentGateway);

        // Total amount of debit
        $total_amount = round(floatval($amount), 2);

        // Calculate amount without IVA
        $taxable_amount = round($total_amount / 1.12, 2);

        // IVA of amount (12%)
        $vat = round($taxable_amount * 0.12, 2);

        // Checking if there are differences by rounding
        $round_diff = $total_amount - $taxable_amount - $vat;

        // Adjust rounding differences
        $taxable_amount = $taxable_amount + $round_diff;

        $request = [
            'user' => [
                'id' => "{$user->id}",
                'email' => $user->email
            ],
            'order' => [
                'amount' => $total_amount,
                'description' => 'Order description',
                'dev_reference' => 'Ref',
                'taxable_amount' => $taxable_amount,
                'tax_percentage' => 12,
                'vat' => $vat

            ],
            'card' => [
                'token' => $token
            ]
        ];
        return $request;
    }

    /**
     * @param Transaction $transaction
     * @return array
     */
    private function ParamsToRequestRefund(Transaction $transaction): array
    {
        $request = [
            'transaction' => [
                'id' => $transaction->id,
            ]
        ];
        return $request;
    }
}
