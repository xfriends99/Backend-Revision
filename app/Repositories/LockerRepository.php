<?php

namespace App\Repositories;

use App\Models\Locker;

/**
 * Class LockerRepository
 * @package App\Repositories
 */
class LockerRepository extends AbstractRepository
{

    /**
     * LockerRepository constructor.
     * @param Locker $model
     */
    function __construct(Locker $model)
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
        $query = $this->model->select('lockers.*');

        if (isset($filters['code']) && $filters['code']) {
            $query = $query->ofCode($filters['code']);
        }

        return $query;
    }

    public function getByCode($code)
    {
        return $this->filter(compact('code'))->first();
    }
}