<?php

namespace App\Repositories;

use App\Models\Timezone;

/**
 * Class TimezoneRepository
 * @package App\Repositories
 */
class TimezoneRepository extends AbstractRepository
{
    /**
     * TimezoneRepository constructor.
     * @param Timezone $model
     */
    function __construct(Timezone $model)
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
        $query = $this->model->select('timezones.*');

        if (isset($filters['offset']) && $filters['offset']) {
            $query->ofTimezoneOffset($filters['offset']);
        }

        if (!empty($filters['name'])) {
            $query->ofName($filters['name']);
        }

        if (!empty($filters['description'])) {
            $query->ofDescription($filters['description']);
        }

        return $query;
    }

    public function getByName($name)
    {
        return $this->filter(compact('name'))->first();
    }

    public function getByNameAndDescription($name, $description)
    {
        return $this->filter(compact('name', 'description'))->first();
    }
}