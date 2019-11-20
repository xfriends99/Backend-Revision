<?php

namespace App\Repositories;

use App\Models\PurchaseCheckpoint;

class PurchaseCheckpointRepository extends AbstractRepository
{
    function __construct(PurchaseCheckpoint $model)
    {
        $this->model = $model;
    }

    public function filter(array $filters = [])
    {
        $query = $this->model->select('purchase_checkpoints.*');

        return $query;
    }
}