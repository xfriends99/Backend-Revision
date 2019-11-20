<?php

namespace App\Repositories;

use App\Models\Platform;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class PlatformRepository
 * @package App\Repositories
 */
class PlatformRepository extends AbstractRepository
{
    /**
     * PlatformRepository constructor.
     * @param Platform $model
     */
    function __construct(Platform $model)
    {
        $this->model = $model;
    }

    /**
     * @param array $filters
     * @return Builder
     */
    public function filter(array $filters = [])
    {
        /** @var Builder $query */
        $query = $this->model->query();

        $query = $query->select('platforms.*');

        if (isset($filters['key']) && $filters['key']) {
            $query = $query->ofKey($filters['key']);
        }

        if (isset($filters['name']) && $filters['name']) {
            $query = $query->ofName($filters['name']);
        }

        if (isset($filters['domain']) && $filters['domain']) {
            $query = $query->ofDomain($filters['domain']);
        }

        return $query;
    }

    public function getByKey($key)
    {
        return $this->filter(compact('key'))->first();
    }

    public function getByDomain($domain)
    {
        return $this->filter(compact('domain'))->first();
    }

    public function addWarehouse(Platform $platform, Warehouse $warehouse)
    {
        return $platform->warehouses()->attach($warehouse->id);
    }
}