<?php

namespace App\Http\Controllers\Api\Transformers;

use App\Models\WorkOrder;
use League\Fractal\TransformerAbstract;

class WorkOrderTransformer extends TransformerAbstract
{
    /**
     * @param WorkOrder $workOrder
     * @return array
     */
    public function transform(WorkOrder $workOrder)
    {
        return [
            'id'            => $workOrder->id,
            'type'          => $workOrder->type,
            'value'         => $workOrder->value,
            'state'         => $workOrder->state,
            'created_at'    => $workOrder->created_at->diffForHumans()
        ];
    }
}