<?php

namespace App\Repositories;

use App\Models\CheckpointCode;
use Illuminate\Support\Collection;

class CheckpointCodeRepository extends AbstractRepository
{
    function __construct(CheckpointCode $model)
    {
        $this->model = $model;
    }

    public function filter(array $filters = [])
    {
        $query = $this->model->select('checkpoint_codes.*');

        $joins = collect();

        if (isset($filters['key']) && $filters['key']) {
            $query->ofKey($filters['key']);
        }

        // Perform joins
        $joins->each(function ($item, $key) use (&$query) {
            $item = json_decode($item);
            $query->join($key, $item->first, '=', $item->second, $item->join_type);
        });


        return $query;
    }

    public function getByKey($key)
    {
        return $this->filter(['key' => $key])->first();
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