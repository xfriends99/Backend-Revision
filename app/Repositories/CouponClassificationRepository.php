<?php

namespace App\Repositories;

use App\Models\CouponClassification;

class CouponClassificationRepository extends AbstractRepository
{
    /**
     * CouponClassificationRepository constructor.
     * @param CouponClassification $model
     */
    public function __construct(CouponClassification $model)
    {
        $this->model = $model;
    }

    /**
     * @param array $filters
     *
     * @return mixed
     */
    public function filter(array $filters = [])
    {
        $query = $this->model->select('coupon_classifications.*');

        if (isset($filters['id']) && $filters['id']) {
            $query = $query->ofId($filters['id']);
        }

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