<?php

namespace App\Repositories;

use App\Models\Address;

/**
 * Class AddressRepository
 * @package App\Repositories
 */
class AddressRepository extends AbstractRepository
{
    /**
     * AddressRepository constructor.
     * @param Address $model
     */
    function __construct(Address $model)
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
        $query = $this->model->select('addresses.*');

        if (isset($filters['user_id']) && $filters['user_id']) {
            $query = $query->ofUserId($filters['user_id']);
        }

        return $query;
    }

}