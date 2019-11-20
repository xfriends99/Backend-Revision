<?php

namespace App\Services\Coupons\Requests;

use App\Models\CouponClassification;
use App\Services\Coupons\Requests\Process\AbstractCouponClassification;
use App\Services\Coupons\Requests\Process\ClubLaNacion;
use App\Services\Coupons\Requests\Process\General;
use App\Services\Coupons\Requests\Process\Referrer;
use Exception;

class CouponClassificationFactory
{
    /**
     * @param CouponClassification $couponClassification
     * @return AbstractCouponClassification
     * @throws Exception
     */
    public static function detectClassificationProcessCoupon(CouponClassification $couponClassification)
    {
        if ($couponClassification->isClubNacion()){
            return new ClubLaNacion($couponClassification);
        } else if($couponClassification->isReferrer()){
            return new Referrer($couponClassification);
        } else if($couponClassification->isGeneral()){
            return new General($couponClassification);
        }

        throw new Exception('Not Classification process implemented');
    }
}