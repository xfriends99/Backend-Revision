<?php
/**
 * Created by PhpStorm.
 * User: plabin
 * Date: 28/3/2019
 * Time: 5:55 PM
 */

namespace App\Repositories;


use App\Models\TransactionStatus;

class TransactionStatusRepository extends AbstractRepository
{
    public function __construct(TransactionStatus $model)
    {
        $this->model = $model;
    }

    public function filter(array $filters = [])
    {
        $query = $this->model->select('transaction_statuses.*');

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