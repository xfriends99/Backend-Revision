<?php

namespace App\Repositories;

use App\Models\Warehouse;
use Laravel\Passport\Client as OauthClient;
use Illuminate\Support\Collection;

/**
 * Class WarehouseRepository
 * @package App\Repositories
 */
class WarehouseRepository extends AbstractRepository
{
    /**
     * WarehouseRepository constructor.
     * @param Warehouse $model
     */
    function __construct(Warehouse $model)
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
        $query = $this->model->select('warehouses.*');

        $joins = collect();

        if (isset($filters['code']) && $filters['code']) {
            $query->ofCode($filters['code']);
        }

        if(isset($filters['platform_id']) && $filters['platform_id']){
            $this->addJoin($joins, 'platform_warehouse', 'platform_warehouse.warehouse_id', 'warehouses.id');
            $query->where('platform_warehouse.platform_id', $filters['platform_id']);
        }

        $joins->each(function ($item, $key) use (&$query) {
            $item = json_decode($item);
            $query->join($key, $item->first, '=', $item->second, $item->join_type);
        });

        return $query;
    }

    public function addOauthClient(Warehouse $warehouse, OauthClient $client)
    {
        $warehouse->oauthClients()->attach($client->id);

        return true;
    }

    public function getByCode($code)
    {
        return $this->filter(compact('code'))->first();
    }
}