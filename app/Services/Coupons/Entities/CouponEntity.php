<?php

namespace App\Services\Coupons\Entities;

use App\Models\CouponClassification;
use App\Models\User;
use App\Services\Coupons\Interfaces\CouponInterface;

class CouponEntity implements CouponInterface
{
    /** @var string */
    private $description;

    /** @var string */
    private $code;

    /** @var CouponClassification */
    private $couponClassification;

    /** @var float */
    private $max_amount;

    /** @var int|null */
    private $max_uses;

    /** @var User|null */
    private $user;

    /** @var float|null */
    private $amount;

    /** @var float|null */
    private $percent;

    /** @var string|null */
    private $valid_from;

    /** @var string|null */
    private $valid_to;

    /**
     * CouponEntity constructor.
     * @param string $description
     * @param string $code
     * @param CouponClassification $couponClassification
     * @param int|null $max_uses
     * @param float|null $max_amount
     * @param User|null $user
     * @param float|null $amount
     * @param float|null $percent
     * @param string|null $valid_from
     * @param string|null $valid_to
     */
    public function __construct(CouponClassification $couponClassification, $description, $code, $max_uses = null, $max_amount = null, User $user = null, $amount = null, $percent = null, $valid_from = null, $valid_to = null)
    {
        $this->couponClassification = $couponClassification;
        $this->description = $description;
        $this->code = $code;
        $this->max_uses = $max_uses;
        $this->max_amount = $max_amount;
        $this->user = $user;
        $this->amount = $amount;
        $this->percent = $percent;
        $this->valid_from = $valid_from;
        $this->valid_to = $valid_to;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description): void
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode($code): void
    {
        $this->code = $code;
    }

    /**
     * @return int|null
     */
    public function getCouponClassificationId()
    {
        return $this->couponClassification ? $this->couponClassification->id : null;
    }

    /**
     * @param CouponClassification $couponClassification
     */
    public function setCouponClassification($couponClassification): void
    {
        $this->couponClassification = $couponClassification;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     */
    public function setAmount($amount): void
    {
        $this->amount = $amount;
    }

    /**
     * @return float
     */
    public function getPercent()
    {
        return $this->percent;
    }

    /**
     * @param float $percent
     */
    public function setPercent($percent): void
    {
        $this->percent = $percent;
    }

    /**
     * @return float
     */
    public function getMaxAmount()
    {
        return $this->max_amount;
    }

    /**
     * @param float $max_amount
     */
    public function setMaxAmount($max_amount): void
    {
        $this->max_amount = $max_amount;
    }

    /**
     * @return int
     */
    public function getMaxUses()
    {
        return $this->max_uses;
    }

    /**
     * @param int $max_uses
     */
    public function setMaxUses($max_uses): void
    {
        $this->max_uses = $max_uses;
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
     * @return string|null
     */
    public function getValidFrom()
    {
        return $this->valid_from;
    }

    /**
     * @param string $valid_from
     */
    public function setValidFrom($valid_from): void
    {
        $this->valid_from = $valid_from;
    }

    /**
     * @return string|null
     */
    public function getValidTo()
    {
        return $this->valid_to;
    }

    /**
     * @param string $valid_to
     */
    public function setValidTo($valid_to): void
    {
        $this->valid_to = $valid_to;
    }

    public function totalAmount(float $amount): float
    {
        return $amount;
    }  

}