<?php

namespace App\Repositories;

use App\Models\WarehouseUnknown;
use Illuminate\Support\Collection;

/**
 * Class WarehouseUnknownRepository
 * @package App\Repositories
 */
class WarehouseUnknownRepository extends AbstractRepository
{
    /**
     * WarehouseUnknownRepository constructor.
     * @param WarehouseUnknown $model
     */
    function __construct(WarehouseUnknown $model)
    {
        $this->model = $model;
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

    /**
     * @param array $filters
     *
     * @return mixed
     */
    public function filter(array $filters = [])
    {
        $query = $this->model->select('warehouse_unknowns.*');

        $joins = collect();

        if (isset($filters['id']) && $filters['id']) {
            $query->ofId($filters['id']);
        }

        if (isset($filters['tracking']) && $filters['tracking']) {
            $query->ofTracking($filters['tracking']);
        }

        if (isset($filters['created_at_newer_than']) && $filters['created_at_newer_than']) {
            $query->ofCreatedAtAfterThan($filters['created_at_newer_than']);
        }

        if (isset($filters['created_at_older_than']) && $filters['created_at_older_than']) {
            $query->ofCreatedAtBeforeThan($filters['created_at_older_than']);
        }

        if (isset($filters['found']) && $filters['found']) {
            $query->where('warehouse_unknowns.found', true);
        }

        if (isset($filters['found']) && !$filters['found']) {
            $query->where('warehouse_unknowns.found', false);
        }

        $joins->each(function ($item, $key) use (&$query) {
            $item = json_decode($item);
            $query->join($key, $item->first, '=', $item->second, $item->join_type);
        });

        return $query;
    }

    /**
     * @param $tracking
     * @return WarehouseUnknown
     */
    public function getByTracking($tracking)
    {
        return $this->filter(compact('tracking'))->first();
    }
}