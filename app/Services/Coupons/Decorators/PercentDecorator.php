<?php

namespace App\Services\Coupons\Decorators;

use App\Services\Coupons\BaseCouponDecorator;

class PercentDecorator extends BaseCouponDecorator
{
	public function totalAmount(float $amount): float
    {
       	$discount = ( $amount * ($this->couponEntity->getPercent() / 100) );
       	return $amount - $discount;
    }
}
