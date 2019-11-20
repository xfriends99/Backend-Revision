<?php

namespace App\Http\Controllers\Api\Transformers;

use League\Fractal\TransformerAbstract;
use App\Services\CostsEstimates\CostEntity;

class CostEntityTransformer extends TransformerAbstract
{
    /**
     * @param CostEntity $costEntity
     * @return array
     */
    public function transform(CostEntity $costEntity)
    {
        return [
            'title' => $costEntity->getTitle(),
            'amount' => $costEntity->getAmount(),
            'type' => $costEntity->getType(),
            'classification' => $costEntity->getClassification()
        ];
    }
}