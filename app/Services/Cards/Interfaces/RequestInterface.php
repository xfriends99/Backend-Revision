<?php

namespace App\Services\Cards\Interfaces;

use App\Models\Card;
use App\Models\Invoice;
use App\Models\Transaction;
use App\Services\Cards\Entities\AddCardEntity;
use App\Services\Cards\Exceptions\GatewayException;

interface RequestInterface
{

    /**
     * @param AddCardEntity $addCardEntity
     * @return mixed|array|object
     */
    public function createPaymentMethod(AddCardEntity $addCardEntity);

    /**
     * @param Card $card
     * @param Invoice $invoice
     * @return mixed|object|array
     * @throws GatewayException|\Exception
     */
    public function createDebit(Card $card, Invoice $invoice);


    /**
     * @param Card $card
     * @return mixed
     * @throws GatewayException
     */
    public function makeDeleteCard(Card $card);

    /**
     * @param Transaction $transaction
     * @return mixed
     * @throws GatewayException
     */
    public function makeRefund(Transaction $transaction);
}
