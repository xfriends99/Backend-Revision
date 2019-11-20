<?php

namespace App\Listeners\Shared;

use App\Events\PackageWasDebited;
use App\Listeners\PlatformListener;
use App\Models\Package;
use App\Models\Coupon;
use App\Services\Coupons\CouponService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Exception;

class CreateCouponReferred extends PlatformListener
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
     * @param PackageWasDebited $event
     * @return void
     * @throws Exception
     */
    public function handle(PackageWasDebited $event)
    {
        /** @var Package $package */
        $package = $event->package;

        /** @var User $user */
        $user = $package->user;

        if ($user->isFirstPackage() && $user->referrer) {

        	/** @var User $userReferrer */
        	$userReferrer = $user->referrer;
        	
			try {
				/** @var CouponEntity $couponEntity */
				$couponEntity = $this->couponService->generateReferralCoupon($userReferrer);

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
