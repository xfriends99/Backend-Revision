<?php

namespace App\Services\Coupons\Decorators;

use App\Services\Coupons\BaseCouponDecorator;

class AmountDecorator extends BaseCouponDecorator
{
	public function totalAmount(float $amount): float
    {
    	return $amount - $this->couponEntity->getAmount();
    }
}
