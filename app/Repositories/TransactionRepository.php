<?php
/**
 * Created by PhpStorm.
 * User: plabin
 * Date: 28/3/2019
 * Time: 5:54 PM
 */

namespace App\Repositories;


use App\Models\Transaction;

class TransactionRepository extends AbstractRepository
{
    public function __construct(Transaction $model)
    {
        $this->model = $model;
    }

    public function filter(array $filters = [])
    {
        $query = $this->model->select('transactions.*');

        if (isset($filters['external_id']) && $filters['external_id']) {
            $query = $query->ofExternalId($filters['external_id']);
        }

        return $query;
    }
}