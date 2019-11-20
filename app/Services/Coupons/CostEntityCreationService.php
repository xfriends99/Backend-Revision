<?php

namespace App\Services\Coupons;

use App\Services\CostsEstimates\CostEntity;
use App\Models\Coupon;

class CostEntityCreationService
{
    /**
     * @param Coupon $coupon
     * @param float $amount
     * @return CostEntity
     */
    public function make(Coupon $coupon, float $amount)
    {
        /** @var CouponService $couponService */
        $couponService = app(CouponService::class);

        $total = $couponService->applyDiscount($coupon, $amount);

        return new CostEntity(
            $coupon->description,
            0 - ($amount - $total),
            'coupon',
            isset($coupon->couponClassification) ? $coupon->getCouponClassificationKey() : null
        );
    }

}