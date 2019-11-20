<?php

namespace App\Http\Controllers\Api\Transformers;

use App\Models\Purchase;
use League\Fractal\TransformerAbstract;

class PurchaseTransformer extends TransformerAbstract
{
    /**
     * @param Purchase $purchase
     * @return array
     */
    public function transform(Purchase $purchase)
    {
        return [
            'id'            => $purchase->id,
            'tracking'      => $purchase->tracking,
            'invoice_url'   => $purchase->invoice_url,
            'purchased_at'  => $purchase->purchased_at,
            'created_at'    => $purchase->created_at->diffForHumans()
        ];
    }
}