<?php

namespace App\Services\Cards\Interfaces;

use App\Models\Card;
use App\Services\Cards\Entities\AddCardEntity as BaseAddCardEntity;
use App\Services\Cards\Exceptions\GatewayException;
use Illuminate\Http\Request;
use App\Services\Cards\Entities\ProcessConfirmationEntity as BaseProcessConfirmationEntity;

interface ValidateInterface
{
    /**
     * @return array
     */
    public function validateRequest();

    /**
     * @return array
     */
    public function validateWebHookRequest();

    /**
     * @param array $attributes
     * @return BaseAddCardEntity
     */
    public function validateAddCard(array $attributes);

    /**
     * @param Request $request
     * @return BaseProcessConfirmationEntity
     * @throws GatewayException
     */
    public function validateProcessConfirmation(Request $request);

    /**
     * @param Card $card
     * @return mixed
     * $throws GatewayException
     */
    public function validateDeleteCard(Card $card);
}
