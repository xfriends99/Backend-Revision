<?php

namespace App\Repositories;

use App\Models\Purchase;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Class PurchaseRepository
 * @package App\Repositories
 */
class PurchaseRepository extends AbstractRepository
{
    /**
     * PurchaseRepository constructor.
     * @param Purchase $model
     */
    function __construct(Purchase $model)
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

        $query = $query->select('purchases.*');

        $joins = collect();

        if (isset($filters['id']) && $filters['id']) {
            $query = $query->ofId($filters['id']);
        }

        if (isset($filters['user_id']) && $filters['user_id']) {
            $query = $query->ofUserId($filters['user_id']);
        }

        if (isset($filters['address_id']) && $filters['address_id']) {
            $query = $query->ofAddressId($filters['address_id']);
        }

        if (isset($filters['tracking'])) {
            $query->ofTracking($filters['tracking']);
        }

        if (isset($filters['platform_id'])) {
            $this->addJoin($joins, 'users', 'users.id', 'purchases.user_id');

            if (is_array($filters['platform_id'])) {
                $query = $query->whereIn('users.platform_id', $filters['platform_id']);
            } else {
                $query = $query->where('users.platform_id', '=', $filters['platform_id']);
            }
        }

        if (isset($filters['work_order_id']) && $filters['work_order_id']) {
            $query->ofWorkOrderId($filters['work_order_id']);
        }

        if (isset($filters['created_at_newer_than']) && $filters['created_at_newer_than']) {
            $query->ofCreatedAtAfterThan($filters['created_at_newer_than']);
        }

        if (isset($filters['created_at_older_than']) && $filters['created_at_older_than']) {
            $query->ofCreatedAtBeforeThan($filters['created_at_older_than']);
        }

        if (isset($filters['marketplace_id']) && $filters['marketplace_id']) {
            $query->ofMarketplaceId($filters['marketplace_id']);
        }

        if (isset($filters['informed_by_user']) && $filters['informed_by_user']) {
            $this->addJoin($joins, 'marketplaces', 'marketplaces.id', 'purchases.marketplace_id');
            $query->where('marketplaces.informed_by_user', true);
        }

        if (isset($filters['warehouse_id']) && $filters['warehouse_id']) {
            $query->ofWarehouseId($filters['warehouse_id']);
        }

        if (isset($filters['destination_country_code']) && $filters['destination_country_code']) {
            $this->addJoin($joins, 'addresses', 'addresses.id', 'purchases.address_id');
            $this->addJoin($joins, 'countries', 'countries.id', 'addresses.country_id');

            if (is_array($filters['destination_country_code'])) {
                $query = $query->whereIn('countries.code', $filters['destination_country_code']);
            } else {
                $query = $query->where('countries.code', '=', $filters['destination_country_code']);
            }
        }

        if (isset($filters['checkpoint_code_id']) && $filters['checkpoint_code_id']) {
            $this->addJoin($joins, 'purchase_checkpoints', 'purchase_checkpoints.purchase_id', 'purchases.id');
            $query->groupBy('purchases.id');
            $query->havingRaw('MAX(purchase_checkpoints.checkpoint_code_id) = ?', [$filters['checkpoint_code_id']]);
        }

        if (isset($filters['state']) && $filters['state']) {
            $query->where('state', $filters['state']);
        }

        if (isset($filters['consolidate']) && $filters['consolidate']) {
            $this->addJoin($joins, 'work_orders', 'work_orders.id', 'purchases.work_order_id');
            $this->addJoin($joins, 'packages', 'work_orders.id', 'packages.work_order_id', 'left');
            $query->whereNull('packages.id');
            $query->where('work_orders.type', 'SHIP');
        }

        if (isset($filters['consolidatable']) && $filters['consolidatable']) {
            $this->addJoin($joins, 'work_orders', 'work_orders.id', 'purchases.work_order_id');
            $query->where('work_orders.type', 'CONSOLIDATE');
        }

        if (isset($filters['shippable']) && $filters['shippable']) {
            $this->addJoin($joins, 'work_orders', 'work_orders.id', 'purchases.work_order_id');
            $query->where('work_orders.type', 'SHIP');
        }

        if (isset($filters['user_name']) && $filters['user_name']) {
            $pattern = $filters['user_name'];
            $this->addJoin($joins, 'users', 'users.id', 'purchases.user_id');
            $query->where(function ($q2) use ($pattern) {
                $q2->where('users.first_name', 'ilike', "%{$pattern}%");
                $q2->orWhere('users.last_name', 'ilike', "%{$pattern}%");
            });
        }

        if (isset($filters['locker_code']) && $filters['locker_code']) {
            $this->addJoin($joins, 'users', 'users.id', 'purchases.user_id');
            $this->addJoin($joins, 'lockers', 'lockers.user_id', 'users.id');
            $query->where('lockers.code', Str::upper($filters['locker_code']));
        }

        // Perform joins
        $joins->each(function ($item, $key) use (&$query) {
            $item = json_decode($item);
            $query->join($key, $item->first, '=', $item->second, $item->join_type);
        });

        if (isset($filters['sort_by']) && $filters['sort_by']) {
            $column = $filters['sort_by'];
            $direction = 'asc';
            if (isset($filters['sort_direction']) && $filters['sort_direction']) {
                $direction = $filters['sort_direction'];
            }

            return $query->orderBy($column, $direction);
        } else {
            return $query->orderBy('purchases.created_at', 'desc');
        }
    }

    /**
     * @param User $user
     * @return Builder
     */
    public function getCountPurchasesUserByState(User $user)
    {
        /** @var Builder $query */
        $query = $this->model->query();

        return $query->select(DB::raw("sum(case when (purchases.state = 'created') then 1 else 0 end) as prealerts_count"))
            ->addSelect(DB::raw("sum(case when (purchases.state = 'processed') then 1 else 0 end) as lockers_count"))
            ->ofUserId($user->id);
    }

    public function addItem(Purchase $package, array $input)
    {
        return $package->purchaseItems()->create($input);
    }

    /**
     * @param string $tracking
     * @return Purchase|null
     */
    public function getByTracking($tracking)
    {
        return $this->filter(compact('tracking'))->first();
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
