<?php

namespace App\Repositories;

use App\Models\ServiceType;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;

class ServiceTypeRepository extends AbstractRepository
{
    /**
     * ServiceTypeRepository constructor.
     * @param ServiceType $model
     */
    function __construct(ServiceType $model)
    {
        $this->model = $model;
    }

    /**
     * @param $key
     * @return \Illuminate\Database\Eloquent\Model|Builder|null|object
     */
    public function getByCode($key)
    {
        return $this->filter(compact('key'))->first();
    }

    /**
     * @param array $filters
     * @return Builder
     */
    public function filter(array $filters = [])
    {
        $joins = collect();
        $query = $this->model
            ->distinct()
            ->select('service_types.*');

        if (isset($filters['key']) && $filters['key']) {
            $query->ofKey($filters['key']);
        }

        if (isset($filters['with_services']) && $filters['with_services']) {
            $this->addJoin($joins, 'services', 'services.service_type_id', 'service_types.id');
            $query->whereNotNull('services.service_type_id');
        }

        if (isset($filters['country_id']) && $filters['country_id']) {
            $this->addJoin($joins, 'services', 'service_types.id', 'services.service_type_id');
            $query = $query->where('services.destination_country_id', $filters['country_id']);
        }

        if (isset($filters['country_code']) && $filters['country_code']) {
            $this->addJoin($joins, 'services', 'service_types.id', 'services.service_type_id');
            $this->addJoin($joins, 'countries', 'countries.id', 'services.destination_country_id');
            $query = $query->where('countries.code', $filters['country_code']);
        }

        $joins->each(function ($item, $key) use (&$query) {
            $item = json_decode($item);
            $query->join($key, $item->first, '=', $item->second, $item->join_type);
        });

        return $query;
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