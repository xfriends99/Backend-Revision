<?php

namespace App\Repositories;

use App\Models\Marketplace;

/**
 * Class MarketplaceRepository
 * @package App\Repositories
 */
class MarketplaceRepository extends AbstractRepository
{

    /**
     * MarketplaceRepository constructor.
     * @param Marketplace $model
     */
    function __construct(Marketplace $model)
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
        $query = $this->model->select('marketplaces.*');

        if (isset($filters['name']) && $filters['name']) {
            $query = $query->ofName($filters['name']);
        }

        if (isset($filters['informed_by_user']) && $filters['informed_by_user']) {
            $query->where('marketplaces.informed_by_user', true);
        }

        if (isset($filters['informed_by_user']) && !$filters['informed_by_user']) {
            $query->where('marketplaces.informed_by_user', false);
        }

        return $query->orderBy('marketplaces.name', 'asc');
    }

    public function getByName($name)
    {
        return $this->filter(compact('name'))->first();
    }

}