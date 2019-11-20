<?php

namespace App\Repositories;

use App\Models\Package;
use Illuminate\Support\Collection;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Class PackageRepository
 * @package App\Repositories
 */
class PackageRepository extends AbstractRepository
{
    /**
     * PackageRepository constructor.
     * @param Package $model
     */
    function __construct(Package $model)
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

        $query = $query->select('packages.*');

        $joins = collect();

        if (isset($filters['user_id']) && $filters['user_id']) {
            $query = $query->ofUserId($filters['user_id']);
        }

        if (isset($filters['tracking']) && $filters['tracking']) {
            $query = $query->ofTracking($filters['tracking']);
        }

        if (isset($filters['invoice_state']) && $filters['invoice_state']) {
            $this->addJoin($joins, 'invoices', 'packages.invoice_id', 'invoices.id', 'left outer');

            $query = $query->where(function ($q) use ($filters) {
                $q->whereNull('invoices.id')
                    ->orWhere('invoices.state', $filters['invoice_state']);
            });
        }

        if (isset($filters['work_order_state']) && $filters['work_order_state']) {
            $this->addJoin($joins, 'work_orders', 'work_orders.id', 'packages.work_order_id');

            $query = $query->where('work_orders.state', $filters['work_order_state']);
        }

        if (isset($filters['state']) && $filters['state']) {
            $query = $query->ofState($filters['state']);
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

    /**
     * @param User $user
     * @return Builder
     */
    public function getCountPackagesUserByState(User $user)
    {
        /** @var Builder $query */
        $query = $this->model->query();

        return $query->select(DB::raw("sum(case when (packages.state = 'created') then 1 else 0 end) as pendings_count"))
            ->addSelect(DB::raw("sum(case when (packages.state = 'processed') then 1 else 0 end) as dispatches_count"))
            ->ofUserId($user->id);
    }

    /**
     * @param $tracking
     * @return Builder|\Illuminate\Database\Eloquent\Model|null|object|Package
     */
    public function getByTrackingNumber($tracking)
    {
        return $this->filter(compact('tracking'))->first();
    }

    /**
     * @param Package $package
     * @return bool
     */
    public function markAsProcessed(Package $package)
    {
        $package->state = 'processed';

        return $package->save();
    }
}