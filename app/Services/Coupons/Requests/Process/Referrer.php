<?php

namespace App\Services\Coupons\Requests\Process;

use App\Models\Coupon;
use App\Models\CouponClassification;
use App\Models\User;
use App\Services\Coupons\CouponValidation;
use App\Services\Coupons\Entities\CouponEntity;
use Exception;

class Referrer extends AbstractCouponClassification
{
    /**
     * General constructor.
     * @param CouponClassification $couponClassification
     */
    public function __construct(CouponClassification $couponClassification)
    {
        parent::__construct($couponClassification);
    }

    /**
     * @param User $user
     * @param $code
     * @param array $data
     * @return CouponEntity
     */
    public function getCouponEntity(User $user, $code, array $data = [])
    {
        return new CouponEntity(
            $this->couponClassification,
            "Cupón de referido {$code}",
            $code,
            isset($data['max_uses']) ? $data['max_uses'] : null,
            isset($data['max_amount']) ? $data['max_amount'] : null,
            $user,
            isset($data['amount']) ? $data['amount'] : null,
            5,
            isset($data['valid_from']) ? $data['valid_from'] : null,
            isset($data['valid_to']) ? $data['valid_to'] : null
        );
    }

    /**
     * @param Coupon $coupon
     * @param User $user
     * @return mixed|void
     * @throws Exception
     */
    public function validateCoupon(Coupon $coupon, User $user)
    {
        /** @var CouponValidation $couponValidation */
        $couponValidation = new CouponValidation();

        if(!$couponValidation->validate($coupon, $user)){
            throw new Exception("Error validating cupon {$coupon->code} para el usuario {$user->first_name}");
        }
    }

}