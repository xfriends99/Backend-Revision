<?php

namespace App\Services\Coupons\Requests\Process;

use App\Models\Coupon;
use App\Models\CouponClassification;
use App\Models\User;
use App\Services\Coupons\Entities\CouponEntity;
use App\Services\Coupons\WebServices\ClubLaNacionService;
use Exception;

class ClubLaNacion extends AbstractCouponClassification
{
    /**
     * ClubLaNacion constructor.
     *
     * @param CouponClassification $couponClassification;
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
     * @throws Exception
     */
    public function getCouponEntity(User $user, $code, array $data = [])
    {
        /** @var ClubLaNacionService $clubLaNacionService */
        $clubLaNacionService = new ClubLaNacionService($user, $code);

        $response = $clubLaNacionService->request();

        return new CouponEntity(
            $this->couponClassification,
            "Club la Nación {$response->crmid}",
            $code,
            null,
            null,
            $user,
            null,
            20,
            null,
            null
        );
    }

    /**
     * @param Coupon $coupon
     * @param User $user
     * @return mixed|object
     * @throws Exception
     */
    public function validateCoupon(Coupon $coupon, User $user)
    {
        /** @var ClubLaNacionService $clubLaNacionService */
        $clubLaNacionService = new ClubLaNacionService($user, $coupon->code);

        return $clubLaNacionService->request();
    }
}