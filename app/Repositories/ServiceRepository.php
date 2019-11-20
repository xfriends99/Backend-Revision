<?php

namespace App\Repositories;

use App\Models\Service;
use Illuminate\Support\Collection;

class ServiceRepository extends AbstractRepository
{
    function __construct(Service $model)
    {
        $this->model = $model;
    }

    public function getByCode($code)
    {
        return $this->model->ofCode($code)->first();
    }

    /**
     * @param array $filters
     *
     * @return mixed
     */
    public function filter(array $filters = [])
    {
        $joins = collect();

        $query = $this->model
            ->distinct()
            ->select('services.*');

        if (isset($filters['code']) && $filters['code']) {
            $query->ofCode($filters['code']);
        }

        if (isset($filters['service_type_id']) && $filters['service_type_id']) {
            $query->ofServiceTypeId($filters['service_type_id']);
        }

        if (isset($filters['origin_country_id']) && $filters['origin_country_id']) {
            $this->addJoin($joins, 'countries as origin_country', 'origin_country.id', 'services.origin_country_id');
            $query->ofOriginCountryId($filters['origin_country_id']);
        }

        if (isset($filters['destination_country_id']) && $filters['destination_country_id']) {
            $this->addJoin($joins, 'countries as destination_country', 'destination_country.id', 'services.destination_country_id');
            $query->ofDestinationCountryId($filters['destination_country_id']);
        }

        $joins->each(function ($item, $key) use (&$query) {
            $item = json_decode($item);
            $query->join($key, $item->first, '=', $item->second, $item->join_type);
        });

        return $query->orderBy('code');
    }

    /**
     * @param Collection $joins
     * @param $table
     * @param $first
     * @param $second
     * @param string $join_type
     */
    private function addJoin(Collection &$joins, $table, $first, $second, $join_type = 'inner')
    {
        if (!$joins->has($table)) {
            $joins->put($table, json_encode(compact('first', 'second', 'join_type')));
        }
    }
}