<?php

namespace App\Services\Cards\Entities\DLocal;

use App\Models\User;
use App\Services\Cards\Entities\AddCardEntity as BaseAddCardEntity;

class AddCardEntity extends BaseAddCardEntity
{
    /** @var string */
    protected $cardHolderName;

    /** @var string */
    protected $token;

    /** @var User */
    protected $user;

    /**
     * @return string
     */
    public function getCardHolderName()
    {
        return $this->cardHolderName;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user)
    {
        $this->user = $user;
    }

}