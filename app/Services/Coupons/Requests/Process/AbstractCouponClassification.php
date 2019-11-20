<?php

namespace App\Services\Coupons\Requests\Process;

use App\Models\CouponClassification;
use App\Models\User;
use App\Repositories\CouponRepository;
use App\Services\Coupons\CouponService;
use App\Services\Coupons\Entities\CouponEntity;
use App\Models\Coupon;
use Exception;

abstract class AbstractCouponClassification
{
    /** @var CouponRepository */
    protected $couponRepository;

    /** @var CouponService */
    protected $couponService;

    /** @var CouponClassification $couponClassification */
    protected $couponClassification;

    /**
     * AbstractCouponClassification constructor.
     * @param CouponClassification $couponClassification
     */
    public function __construct(CouponClassification $couponClassification)
    {
        $this->couponRepository = app(CouponRepository::class);
        $this->couponService = app(CouponService::class);
        $this->couponClassification = $couponClassification;
    }

    /**
     * @param CouponEntity $couponEntity
     * @return Coupon
     * @throws Exception
     */
    public function createCoupon(CouponEntity $couponEntity)
    {
        return $this->couponService->createCoupon($couponEntity);
    }

    /**
     * @param CouponClassification $couponClassification
     * @param $code
     * @return mixed
     */
    public function search(CouponClassification $couponClassification, $code)
    {
        return $this->couponRepository->getByCodeAndClassification($couponClassification, $code);
    }

    /**
     * @param Coupon $coupon
     * @param User $user
     * @return mixed
     * @throws Exception
     */
    public abstract function validateCoupon(Coupon $coupon, User $user);

    /**
     * @param User $user
     * @param $code
     * @param array $data
     * @return CouponEntity
     */
    public abstract function getCouponEntity(User $user, $code, array $data = []);
}