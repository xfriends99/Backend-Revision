<?php

namespace App\Repositories;

use App\Models\Additional;
use App\Models\WorkOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Class WorkOrderRepository
 * @package App\Repositories
 */
class WorkOrderRepository extends AbstractRepository
{
    /**
     * WorkOrderRepository constructor.
     * @param WorkOrder $model
     */
    function __construct(WorkOrder $model)
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
        $query = $query->select('work_orders.*');
        $joins = collect();
        $filters = collect($filters);


        // Filters
        if ($filters->get('id')) {
            $query->ofId($filters->get('id'));
        }
        
        if ($filters->get('coupon_id')) {
            $query->OfCouponId($filters->get('coupon_id'));
        }
        
        if ($filters->has('orphaned')) {
            if (!$filters->get('orphaned')) {
                $this->addJoin($joins, 'purchases', 'work_orders.id', 'purchases.work_order_id');
            } else {
                $this->addJoin($joins, 'purchases', 'work_orders.id', 'purchases.work_order_id', 'left');
                $query->whereNull('purchases.work_order_id');
            }
        }

        if (isset($filters['tracking']) && $filters['tracking']) {

            logger("tracking:".$filters['tracking']);

            $this->addJoin($joins, 'purchases', 'work_orders.id', 'purchases.work_order_id');

            if (is_array($filters['tracking'])) {
                $query = $query->whereIn('purchases.tracking', $filters['tracking']);
            } else {
                $query = $query->where('purchases.tracking', '=', $filters['tracking']);
            }
        }

        if (isset($filters['platform_id']) && $filters['platform_id']) {

            $this->addJoin($joins, 'purchases', 'work_orders.id', 'purchases.work_order_id');
            $this->addJoin($joins, 'users', 'users.id', 'purchases.user_id');

            if (is_array($filters['platform_id'])) {
                $query = $query->whereIn('users.platform_id', $filters['platform_id']);
            } else {
                $query = $query->where('users.platform_id', '=', $filters['platform_id']);
            }
        }

        if (isset($filters['created_at_newer_than']) && $filters['created_at_newer_than']) {
            $query->ofCreatedAtAfterThan($filters['created_at_newer_than']);
        }

        if (isset($filters['created_at_older_than']) && $filters['created_at_older_than']) {
            $query->ofCreatedAtBeforeThan($filters['created_at_older_than']);
        }

        if (isset($filters['marketplace_id']) && $filters['marketplace_id']) {
             $this->addJoin($joins, 'purchases', 'work_orders.id', 'purchases.work_order_id');

             if (is_array($filters['marketplace_id']) && !empty($filters['marketplace_id'])) {
                $query = $query->whereIn('purchases.marketplace_id', $filters['marketplace_id']);
            } else {
                $query = $query->where('purchases.marketplace_id', $filters['marketplace_id']);
            }
        }

        if (isset($filters['locker_code']) && $filters['locker_code']) {
            $this->addJoin($joins, 'purchases', 'work_orders.id', 'purchases.work_order_id');
            $this->addJoin($joins, 'users', 'users.id', 'purchases.user_id');
            $this->addJoin($joins, 'lockers', 'lockers.user_id', 'users.id');
            $query->where('lockers.code', Str::upper($filters['locker_code']));
        }

        if (isset($filters['warehouse_id']) && $filters['warehouse_id']) {
             $this->addJoin($joins, 'purchases', 'work_orders.id', 'purchases.work_order_id');

            if (is_array($filters['warehouse_id']) && !empty($filters['warehouse_id'])) {
                $query = $query->whereIn('purchases.warehouse_id', $filters['warehouse_id']);
            } else {
                $query = $query->where('purchases.warehouse_id', $filters['warehouse_id']);
            }
        }

        if (isset($filters['destination_country_code']) && $filters['destination_country_code']) {
            $this->addJoin($joins, 'purchases', 'work_orders.id', 'purchases.work_order_id');
            $this->addJoin($joins, 'addresses', 'addresses.id', 'purchases.address_id');
            $this->addJoin($joins, 'countries', 'countries.id', 'addresses.country_id');

            if (is_array($filters['destination_country_code'])) {
                $query = $query->whereIn('countries.code', $filters['destination_country_code']);
            } else {
                $query = $query->where('countries.code', '=', $filters['destination_country_code']);
            }
        }

        if (isset($filters['checkpoint_code_id']) && $filters['checkpoint_code_id']) {
            $this->addJoin($joins, 'purchases', 'work_orders.id', 'purchases.work_order_id');
            $this->addJoin($joins, 'purchase_checkpoints', 'purchase_checkpoints.purchase_id', 'purchases.id');
            $query->groupBy('work_orders.id');
            $query->havingRaw('MAX(purchase_checkpoints.checkpoint_code_id) = ?', [$filters['checkpoint_code_id']]);
        }

        if (isset($filters['user_name']) && $filters['user_name']) {
            $pattern = $filters['user_name'];
            $this->addJoin($joins, 'purchases', 'work_orders.id', 'purchases.work_order_id');
            $this->addJoin($joins, 'users', 'users.id', 'purchases.user_id');
            $query->where(function ($q2) use ($pattern) {
                $q2->where('users.first_name', 'ilike', "%{$pattern}%");
                $q2->orWhere('users.last_name', 'ilike', "%{$pattern}%");
            });
        }

        if (isset($filters['work_order_id']) && $filters['work_order_id']) {
            $this->addJoin($joins, 'purchases', 'work_orders.id', 'purchases.work_order_id');

            if (is_array($filters['work_order_id']) && !empty($filters['work_order_id'])) {
                $query = $query->whereIn('purchases.work_order_id', $filters['work_order_id']);
            } else {
                $query = $query->where('purchases.work_order_id', $filters['work_order_id']);
            }
        }

        if ($filters->has('shippable')  ||  $filters->has('consolidatable')) {
            $query->where(function ($query2) use ($filters){
                $query2->where(function ($query3) use ($filters){
                    if ($filters->has('shippable')) {
                        $query3->ofShippable();
                    }
                })->orWhere(function ($query3) use ($filters){
                    if ($filters->has('consolidatable')) {
                        $query3->ofConsolidatable();
                    }
                });
            });
            logger($query->toSql());
        }

        // Perform joins
        $joins->each(function ($item, $key) use (&$query) {
            $item = json_decode($item);
            $query->join($key, $item->first, '=', $item->second, $item->join_type);
        });

        return $query;
    }

    /**
     * @param WorkOrder $workOrder
     * @param Additional $additional
     * @param $value
     * @return bool
     */
    public function attachAdditional(WorkOrder $workOrder, Additional $additional, $value)
    {
        $workOrder->additionals()->attach($additional->id, ['value' => $value]);

        return $workOrder->save();
    }

    public function markAsProcessed(WorkOrder $workOrder)
    {
        $workOrder->state = 'processed';

        return $workOrder->save();
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
