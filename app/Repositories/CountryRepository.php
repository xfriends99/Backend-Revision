<?php

namespace App\Repositories;

use App\Models\Country;

/**
 * Class CountryRepository
 * @package App\Repositories
 *
 * @property string code
 * @property string name
 * @property bool tenant
 */
class CountryRepository extends AbstractRepository
{
    /**
     * CountryRepository constructor.
     * @param Country $model
     */
    function __construct(Country $model)
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
        $query = $this->model->select('countries.*');

        if (isset($filters['code']) && $filters['code']) {
            $query = $query->ofCode($filters['code']);
        }

        if (isset($filters['tenant']) && $filters['tenant']) {
            $query = $query->ofTenant();
        }

        if (isset($filters['tenant']) && !$filters['tenant']) {
            $query = $query->ofNotTenant();
        }

        return $query;
    }

    public function getByCode($code)
    {
        return $this->filter(compact('code'))->first();
    }
}
