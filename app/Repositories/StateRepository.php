<?php

namespace App\Repositories;

use App\Models\Country;
use App\Models\State;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class StateRepository
 * @package App\Repositories
 */
class StateRepository extends AbstractRepository
{
    /**
     * PurchaseRepository constructor.
     * @param State $model
     */
    function __construct(State $model)
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

        $query = $query->select('states.*');

        $joins = collect();

        if (isset($filters['name']) && $filters['name']) {
            $query = $query->ofName($filters['name']);
        }

        if (isset($filters['country_id']) && $filters['country_id']) {
            $query = $query->ofCountryId($filters['country_id']);
        }

        if (isset($filters['country_code']) && $filters['country_code']) {
            $this->addJoin($joins, 'countries', 'countries.id', 'states.country_id');
            $query = $query->where('countries.code', $filters['country_code']);
        }

        $joins->each(function ($item, $key) use (&$query) {
            $item = json_decode($item);
            $query->join($key, $item->first, '=', $item->second, $item->join_type);
        });

        return $query->orderBy('states.name', 'asc');
    }

    public function getByNameAndCountry(Country $country, $name)
    {
        return $this->filter(['country_id' => $country->id, 'name' => $name])->first();
    }

    private function addJoin(Collection &$joins, $table, $first, $second, $join_type = 'inner')
    {
        if (!$joins->has($table)) {
            $joins->put($table, json_encode(compact('first', 'second', 'join_type')));
        }
    }

}