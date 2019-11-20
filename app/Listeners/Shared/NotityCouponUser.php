<?php

namespace App\Listeners\Shared;

use App\Listeners\PlatformListener;
use App\Models\Coupon;
use App\Events\UserHasCoupon;
use App\Mail\CouponUserReceived;
use Illuminate\Support\Facades\Mail;
use Exception;

class NotityCouponUser extends PlatformListener
{
    /**
     * Handle the event.
     *
     * @param object $event
     * @return void
     */
    public function handle(UserHasCoupon $event)
    {
        /** @var Coupon $coupon */
        $coupon = $event->coupon;

        try {
            Mail::send(new CouponUserReceived($coupon));
        } catch (Exception $exception) {
            logger("[Notify User Has New Coupon] Exception in coupon {$event->coupon->code}");
            logger($exception->getMessage());
            logger($exception->getTraceAsString());
        }
    }
}
