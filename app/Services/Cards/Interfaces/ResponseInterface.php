<?php

namespace App\Services\Cards\Interfaces;

use App\Models\User;
use App\Services\Cards\Entities\ProcessConfirmationEntity;
use App\Services\Cards\Entities\TransactionEntity;
use App\Services\Cards\Exceptions\GatewayException;
use App\Services\Cards\Exceptions\ParseResponseException;

use Exception;

interface ResponseInterface
{
    /**
     * @param User $user
     * @param mixed|object|array $response
     * @return mixed
     */
    public function parseCreatePaymentMethod(User $user, $response);

    /**
     * @param mixed|object|array $response
     * @return mixed
     * @throws ParseResponseException|Exception
     */
    public function parseCreateDebit($response);

    /**
     * @param ProcessConfirmationEntity $processConfirmationEntity
     * @return TransactionEntity
     * @throws GatewayException
     */
    public function parseMakeProcessConfirmation(ProcessConfirmationEntity $processConfirmationEntity);

    /**
     * @param mixed|array|object $response
     * @return void|bool
     * @throws GatewayException|Exception
     */
    public function parseMakeDeleteCard($response);

    /**
     * @param mixed|array|object $response
     * @return mixed
     * @throws GatewayException|Exception
     */
    public function parseMakeRefund($response);
}
