<?php

namespace App\Repositories;

use App\Models\Additional;
use Illuminate\Support\Collection;

/**
 * Class AdditionalRepository
 * @package App\Repositories
 */
class AdditionalRepository extends AbstractRepository
{
    /**
     * AdditionalRepository constructor.
     * @param Additional $model
     */
    function __construct(Additional $model)
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
        $query = $this->model->select('additionals.*')->distinct();
        $joins = collect();

        if (isset($filters['required'])) {
            $query = $query->ofRequired($filters['required']);
        }

        if (isset($filters['active'])) {
            $query = $query->ofActive($filters['active']);
        }

        if (isset($filters['key'])) {
            $query = $query->ofKey($filters['key']);
        }

        if (isset($filters['country_code']) && $filters['country_code']) {
            $this->addJoin($joins, 'additional_options', 'additional_options.additional_id', 'additionals.id');
            $this->addJoin($joins, 'countries', 'additional_options.country_id', 'countries.id');
            $query->where('countries.code', $filters['country_code']);
        }

        if (isset($filters['platform_key']) && $filters['platform_key']) {
            $this->addJoin($joins, 'additional_options', 'additional_options.additional_id', 'additionals.id');
            $this->addJoin($joins, 'platforms', 'additional_options.platform_id', 'platforms.id');
            $query->where('platforms.key', $filters['platform_key']);
        }

        if (isset($filters['enabled']) && $filters['enabled']) {
            $this->addJoin($joins, 'additional_options', 'additional_options.additional_id', 'additionals.id');
            $query->where('additional_options.enabled', true);
        }

        // Perform joins
        $joins->each(function ($item, $key) use (&$query) {
            $item = json_decode($item);
            $query->join($key, $item->first, '=', $item->second, $item->join_type);
        });

        return $query;
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