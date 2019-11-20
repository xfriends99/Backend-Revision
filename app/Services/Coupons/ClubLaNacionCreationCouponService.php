<?php

namespace  App\Services\Coupons;

use App\Services\Coupons\WebServices\ClubLaNacionService;
use App\Repositories\CouponClassificationRepository;
use App\Models\User;
use App\Models\Coupon;
use Exception;

class ClubLaNacionCreationCouponService
{
    /**
     * @param User $user
     * @param string $coupon_code
     * @param float $percent
     * @param float $amount
     * @param bool $active
     * @return Coupon
     * @throws Exception
     */
    public function generateCoupon(User $user, string $coupon_code, float $percent,float $amount, bool $active)
    {
        $service = new ClubLaNacionService($user, $coupon_code);

        if(!$service->checkCouponcode($coupon_code)){
            throw new Exception("Código {$coupon_code} no encontrado o inválido.");
        }

        $response = $service->request();

        return $this->makeCoupon($coupon_code, "Club la Nación {$response->crmid}", $percent, $amount, $active);
    }

    /**
     * @param string $coupon_code
     * @param string $description
     * @param float $percent
     * @param float $amount
     * @param bool $active
     * @return Coupon
     */
    private function makeCoupon(string $coupon_code, string $description, float $percent,float $amount, bool $active)
    {
        $couponClassificationRepository = app(CouponClassificationRepository::class);

        $couponClassification = $couponClassificationRepository->getByKey('club');

        $coupon = new Coupon();
        $coupon->percent = $percent;
        $coupon->description = $description;
        $coupon->code = $coupon_code;
        $coupon->max_amount = $amount;
        $coupon->active = $active;
        $coupon->couponClassification = $couponClassification;

        return $coupon;
    }

}