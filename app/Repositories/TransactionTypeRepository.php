<?php

namespace App\Repositories;


use App\Models\TransactionType;

class TransactionTypeRepository extends AbstractRepository
{
    public function __construct(TransactionType $model)
    {
        $this->model = $model;
    }

    public function filter(array $filters = [])
    {
        $query = $this->model->select('transaction_types.*');

        if (isset($filters['key']) && $filters['key']) {
            $query = $query->ofKey($filters['key']);
        }

        return $query;
    }

    public function getByKey($key)
    {
        return $this->filter(['key' => $key])->first();
    }
}