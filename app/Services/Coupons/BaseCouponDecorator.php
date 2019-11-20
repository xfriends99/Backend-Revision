<?php

namespace App\Services\Coupons;

use App\Services\Coupons\Entities\CouponEntity;
use App\Services\Coupons\Interfaces\CouponInterface;

abstract class BaseCouponDecorator implements CouponInterface
{
    /**
     * @var CouponInterface
     */
    protected $couponEntity;

    public function __construct(CouponInterface $couponEntity)
    {
        $this->couponEntity = $couponEntity;
    }

    /**
     * Decorator delegates all work to a wrapped component.
     */
    public function totalAmount(float $amount): float
    {
        return $this->couponEntity->totalAmount($amount);
    }
    
}
