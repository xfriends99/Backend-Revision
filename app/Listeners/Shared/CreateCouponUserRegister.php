<?php

namespace App\Listeners\Shared;

use Illuminate\Auth\Events\Verified;
use App\Listeners\PlatformListener;
use App\Models\Coupon;
use App\Services\Coupons\CouponService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Exception;

class CreateCouponUserRegister extends PlatformListener
{
    /** @var  CouponService */
    protected $couponService;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(CouponService $couponService)
    {
        $this->couponService = $couponService;
    }

    /**
     * Handle the event.
     *
     * @param Verified $event
     * @return void
     * @throws Exception
     */
    public function handle(Verified $event)
    {
    	/** @var User $user */
        $user = $event->user;

        if ($user->referrer) {
			try {
				/** @var CouponEntity $couponEntity */
				$couponEntity = $this->couponService->generateReferralCoupon($user);

	            /** @var Coupon $coupon */
            	$coupon = $this->couponService->createCoupon($couponEntity);
	        } catch (Exception $exception) {
	            logger("[Create Referrer Coupon Exception] Error creating coupon referrer");
	            logger($exception->getMessage());
	            logger($exception->getTraceAsString());
	        }
        }        
    }
}
