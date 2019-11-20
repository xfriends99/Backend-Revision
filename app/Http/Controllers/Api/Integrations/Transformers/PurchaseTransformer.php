<?php

namespace App\Http\Controllers\Api\Integrations\Transformers;

use App\Models\Purchase;
use App\Models\WeightUnit;
use App\Models\WorkOrder;
use App\Services\Purchases\WeightUnitConverter;
use League\Fractal\TransformerAbstract;

class PurchaseTransformer extends TransformerAbstract
{
    /**
     * @param Purchase $purchase
     * @return array
     */
    public function transform(Purchase $purchase)
    {
        /** @var WeightUnit $weightUnit */
        $weightUnit = $purchase->weightUnit;

        $items = [];
        foreach ($purchase->purchaseItems as $item) {
            $items[] = [
                'quantity'    => intval($item->quantity),
                'description' => $item->description,
                'value'       => number_format($item->amount, 2),
                'weight'      => number_format((new WeightUnitConverter($weightUnit, $item->weight))->getWeightAsKg(), 3),
                'width'       => number_format($item->width, 2),
                'height'      => number_format($item->height, 2),
                'length'      => number_format($item->length, 2),
            ];
        }

        /** @var WorkOrder $workOrder */
        $workOrder = $purchase->workOrder;

        return [
            'tracking'         => $purchase->tracking,
            'locker'           => $purchase->getUserLockerCode(),
            'weight'           => number_format((new WeightUnitConverter($weightUnit, $purchase->getWeight()))->getWeightAsKg(), 3),
            'value'            => number_format($purchase->value, 2),
            'currency_code'    => 'USD',
            'action'           => $workOrder->type,
            'purchased_at'     => $purchase->purchased_at->toIso8601String(),
            'is_mobile_device' => $purchase->is_mobile_device,
            'items'            => $items
        ];
    }
}
