<?php

namespace App\Services\Cards\Entities;

use App\Models\CardBrand;
use App\Models\User;

class CardEntity
{

    /** @var User */
    private $user;

    /** @var CardBrand */
    private $cardBrand;

    /** @var string */
    private $name;

    /** @var string */
    private $token;

    /** @var int */
    private $expiryYear;

    /** @var int */
    private $expiryMonth;

    /** @var int */
    private $number;

    /** @var string */
    private $details;

    /** @var int|null */
    private $bin;

    /** @var string|null */
    private $status;

    /** @var bool|null */
    private $default;

    /**
     * CardEntity constructor.
     * @param User $user
     * @param CardBrand $cardBrand
     * @param string $name
     * @param string $token
     * @param int $expiryYear
     * @param int $expiryMonth
     * @param int $number
     * @param string $details
     * @param int|null $bin
     * @param int|null $status
     * @param int|null $default
     */
    public function __construct($user, $cardBrand, $name, $token, $expiryYear, $expiryMonth, $number, $details, $bin = null, $status = null, $default = null)
    {
        $this->user = $user;
        $this->cardBrand = $cardBrand;
        $this->name = $name;
        $this->token = $token;
        $this->expiryYear = $expiryYear;
        $this->expiryMonth = $expiryMonth;
        $this->number = $number;
        $this->details = $details;
        $this->bin = $bin;
        $this->status = $status;
        $this->default = $default;
    }

    /**
     * @return int|null
     */
    public function getUserId()
    {
        return $this->user ? $this->user->id : null;
    }

    /**
     * @param User $user
     */
    public function setUser($user): void
    {
        $this->user = $user;
    }

    /**
     * @return int|null
     */
    public function getCardBrandId()
    {
        return $this->cardBrand ? $this->cardBrand->id : null;
    }

    /**
     * @param CardBrand $cardBrand
     */
    public function setCardBrand($cardBrand): void
    {
        $this->cardBrand = $cardBrand;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param string $token
     */
    public function setToken($token): void
    {
        $this->token = $token;
    }

    /**
     * @return int|null
     */
    public function getBin()
    {
        return $this->bin;
    }

    /**
     * @param int $bin
     */
    public function setBin($bin): void
    {
        $this->bin = $bin;
    }

    /**
     * @return string|null
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus($status): void
    {
        $this->status = $status;
    }

    /**
     * @return string|null
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * @param string $default
     */
    public function setDefault($default): void
    {
        $this->default = $default;
    }

    /**
     * @return int
     */
    public function getExpiryYear()
    {
        return $this->expiryYear;
    }

    /**
     * @param int $expiryYear
     */
    public function setExpiryYear($expiryYear): void
    {
        $this->expiryYear = $expiryYear;
    }

    /**
     * @return int
     */
    public function getExpiryMonth()
    {
        return $this->expiryMonth;
    }

    /**
     * @param int $expiryMonth
     */
    public function setExpiryMonth($expiryMonth): void
    {
        $this->expiryMonth = $expiryMonth;
    }

    /**
     * @return int
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param int $number
     */
    public function setNumber($number): void
    {
        $this->number = $number;
    }

    /**
     * @return string
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * @param string $details
     */
    public function setDetails($details): void
    {
        $this->details = $details;
    }

}