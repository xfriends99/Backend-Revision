<?php

namespace App\Http\Controllers\Api\Transformers;

use App\Models\Coupon;
use League\Fractal\TransformerAbstract;

class CouponTransformer extends TransformerAbstract
{
    /**
     * @param Coupon $coupon
     * @return array
     */
    public function transform(Coupon $coupon)
    {
        return [
            'id'          => $coupon->id,
            'user'        => $coupon->user->full_name,
            'description' => $coupon->description,
            'status'      => $coupon->active,
            'uses'        => $coupon->getTotalUses(),
            'amount'      => $coupon->amount,
            'discount'    => $coupon->discout,
            'valid_from'  => $coupon->valid_from ? $coupon->valid_from->format('d/m/Y') : '-',
            'valid_to'    => $coupon->valid_to ? $coupon->valid_to->format('d/m/Y') : '-',
            'date_of_use' => $coupon->getLastDateOfUse()
        ];
    }
}