<?php

namespace App\Mail;

use App\Models\User;
use App\Models\Coupon;
use Illuminate\Mail\Mailable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class CouponUserReceived extends Mailable
{
    use Queueable, SerializesModels;

    /** @var User */
    public $user;

    /** @var Coupon */
    public $coupon;

    /**
     * CouponUserReceived constructor.
     * @param Coupon $coupon
     */
    public function __construct(Coupon $coupon)
    {
        $this->user = $coupon->user;
        $this->coupon = $coupon;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
    	return $this->view('emails.casillerosmailamericas.coupons.mail');
                    ->to($this->user->email)
                    ->subject('Has recibido un Cupón');
    }
}
