<?php

namespace App\Services\Coupons;

use App\Models\Coupon;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class CouponValidation
{
    /**
     * @param Coupon $coupon
     * @param User $user
     * @return bool
     */
    public function validate(Coupon $coupon, User $user)
    {   
    	if ($coupon->valid_from != null || $coupon->valid_to != null) {
    		if (!$this->validateDate($coupon)) {
                return false;
	        }
    	}

        if ($coupon->max_uses != 0) {
            if (!$this->validateUses($coupon)) {
                return false;
            }
        }

        if (!$coupon->active) {
            return false;            
        }

    	if ($coupon->user_id) {
            if (!$this->validateUser($coupon, $user)) {
                return false;
            }
        }

        return true;

    }

    /**
     * @param Coupon $coupon
     * @return bool
     */
    private function validateDate(Coupon $coupon)
    {   
    	$now = Carbon::now();
    	$valid_from =  Carbon::parse($coupon->valid_from);
    	$valid_to =  Carbon::parse($coupon->valid_to);

    	if ( ($valid_from && $valid_to) != null ) {
    		if ($valid_from <= $now && $valid_to >= $now) {
    			return true;
    		}
    	} else if($valid_from != null) {
    		if ($valid_from <= $now) {
    			return true;
    		}
    	} else {
    		if ($valid_to >= $now) {
    			return true;
    		}
    	}       

        return false;
    }

    /**
     * @param Coupon $coupon
     * @return bool
     */
    private function validateUses(Coupon $coupon)
    {        
        if ($coupon->getTotalUses() < $coupon->max_uses) {
            return true;
        }

        return false;
    }

    /**
     * @param Coupon $coupon
     * @param User $user
     * @return bool
     */
    private function validateUser(Coupon $coupon, User $user)
    {        
        if ($coupon->user_id === $user->id) {
            return true;
        }

        return false;
    }    
}