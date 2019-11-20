<?php

namespace App\Repositories;

use App\Models\Coupon;
use App\Models\CouponClassification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CouponRepository extends AbstractRepository
{
    /**
     * CouponRepository constructor.
     * @param Coupon $model
     */
    function __construct(Coupon $model)
    {
        $this->model = $model;
    }

    public function filter(array $filters = [])
    {
        /** @var \Illuminate\Database\Query\Builder $query */
        $query = $this->model->select('coupons.*')->distinct();

        $joins = collect();

        if (isset($filters['id']) && $filters['id']) {
            $query = $query->ofId($filters['id']);
        }

        if (isset($filters['code']) && $filters['code']) {
            $query = $query->ofCode($filters['code']);
        }

        if (isset($filters['user_id']) && $filters['user_id']) {
            $query = $query->ofUserId($filters['user_id']);
        }

        if (isset($filters['used'])) {
            $this->addJoin($joins, 'coupon_user', 'coupon_user.coupon_id', 'coupons.id', 'left')             ;

            $query->groupBy('coupons.id');
            $query = $query->ofUsed($filters['used']);
        }

        if (isset($filters['coupon_classification_id']) && $filters['coupon_classification_id']) {
            $query = $query->ofClassificationId($filters['coupon_classification_id']);
        }

        if (isset($filters['created_at_newer_than']) && $filters['created_at_newer_than']) {
            $query->ofCreatedAtAfterThan($filters['created_at_newer_than']);
        }

        if (isset($filters['created_at_older_than']) && $filters['created_at_older_than']) {
            $query->ofCreatedAtBeforeThan($filters['created_at_older_than']);
        }

        if (isset($filters['status']) && $filters['status']){
            $query->ofStatus($filters['status']);
        }

        if (isset($filters['purchase_id']) && is_array($filters['purchase_id'])){
            $this->addJoin($joins, 'work_orders', 'work_orders.coupon_id', 'coupons.id');
            $this->addJoin($joins, 'purchases', 'purchases.work_order_id', 'work_orders.id');
            $query->whereIn('purchases.id', $filters['purchase_id']);
        }

        $joins->each(function ($item, $key) use (&$query) {
            $item = json_decode($item);
            $query->join($key, $item->first, '=', $item->second, $item->join_type);
        });

        return $query;
    }

    public function getByCode($code)
    {
        return $this->filter(compact('code'))->first();
    }

    public function getByCodeAndClassification(CouponClassification $couponClassification, $code)
    {
        return $this->filter(['coupon_classification_id' => $couponClassification->id, 'code' => $code])->first();
    }

    /**
     * @param Coupon $coupon
     * @param User $user
     * @param Carbon $chargedAt
     * @return mixed
     */
    public function attachUser(Coupon $coupon, User $user, Carbon $chargedAt)
    {
        return $coupon->users()->attach($user->id, ['charged_at' => $chargedAt]);
    }

    /**
     * @param User $user
     * @return Builder
     */
    public function getCountUserCouponsByUse(User $user)
    {
        /** @var Builder $query */
        $query = $this->model->query();

        return $query->select(DB::raw("sum(case when (coupon_user.coupon_id IS NULL) then 1 else 0 end) as coupons_free_count"))
            ->addSelect(DB::raw("sum(case when (coupon_user.coupon_id = coupons.id) then 1 else 0 end) as coupons_use_count"))
            ->leftJoin('coupon_user', 'coupon_user.coupon_id', '=', 'coupons.id')->ofUserId($user->id);
    }

    private function addJoin(Collection &$joins, $table, $first, $second, $join_type = 'inner')
    {
        if (!$joins->has($table)) {
            $joins->put($table, json_encode(compact('first', 'second', 'join_type')));
        }
    }
}

