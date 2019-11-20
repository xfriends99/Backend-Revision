<?php

namespace App\Repositories;

use App\Models\Country;
use App\Models\IdentificationType;
use Illuminate\Support\Collection;

class IdentificationTypeRepository extends AbstractRepository
{
    function __construct(IdentificationType $model)
    {
        $this->model = $model;
    }

    public function filter(array $filters = [])
    {
        $query = $this->model->select('identification_types.*');

        $joins = collect();

        // Perform joins
        $joins->each(function ($item, $key) use (&$query) {
            $item = json_decode($item);
            $query->join($key, $item->first, '=', $item->second, $item->join_type);
        });

        if (isset($filters['country_id']) && $filters['country_id']) {
            $query->ofCountry($filters['country_id']);
        }

        if (isset($filters['key']) && $filters['key']) {
            $query->ofKey($filters['key']);
        }

        return $query;
    }

    public function getByCountryAndKey(Country $country, $key)
    {
        return $this->model->where('identification_types.country_id', $country->id)->where('identification_types.key', $key)->first();
    }

    /**
     * @param Collection $joins
     * @param string $table
     * @param string $first
     * @param string $second
     * @param string $join_type
     */
    private function addJoin(Collection &$joins, $table, $first, $second, $join_type = 'inner')
    {
        if (!$joins->has($table)) {
            $joins->put($table, json_encode(compact('first', 'second', 'join_type')));
        }
    }
}