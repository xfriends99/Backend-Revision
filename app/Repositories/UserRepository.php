<?php

namespace App\Repositories;

use App\Models\Country;
use App\Models\Coupon;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Class UserRepository
 * @package App\Repositories
 */
class UserRepository extends AbstractRepository
{
    /**
     * UserRepository constructor.
     * @param User $model
     */
    function __construct(User $model)
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
        $query = $this->model->select('users.*');

        $joins = collect();

        if (isset($filters['id']) && $filters['id']) {
            $query = $query->ofId($filters['id']);
        }

        if (isset($filters['country_id']) && $filters['country_id']) {
            $query = $query->where('users.country_id', '=', $filters['country_id']);
        }

        if (isset($filters['country_code']) && $filters['country_code']) {
            $this->addJoin($joins, 'countries', 'countries.id', 'users.country_id');

            if(is_array($filters['country_code'])){
                $query = $query->whereIn('countries.code', $filters['country_code']);
            } else {
                $query = $query->where('countries.code', '=', $filters['country_code']);
            }
        }

        if (isset($filters['verified']) && $filters['verified']) {
            $query = $query->ofEmailVerifiedAt($filters['verified']);
        }

        if (isset($filters['platform_id']) && $filters['platform_id']) {
            $query = $query->ofPlatformId($filters['platform_id']);
        }

        if (isset($filters['identification']) && $filters['identification']) {
            $query = $query->ofIdentification($filters['identification']);
        }

        if (isset($filters['referrer_id']) && $filters['referrer_id']) {
            $query = $query->ofReferrerId($filters['referrer_id']);
        }

        if (isset($filters['full_name']) && $filters['full_name']) {
            $query = $query->ofFullName($filters['full_name']);
        }

        if (isset($filters['email']) && $filters['email']) {
            $query = $query->ofEmail($filters['email']);
        }

        if (isset($filters['created_at_newer_than']) && $filters['created_at_newer_than']) {
            $query->ofCreatedAtAfterThan($filters['created_at_newer_than']);
        }

        if (isset($filters['created_at_older_than']) && $filters['created_at_older_than']) {
            $query->ofCreatedAtBeforeThan($filters['created_at_older_than']);
        }

        // Perform joins
        $joins->each(function ($item, $key) use (&$query) {
            $item = json_decode($item);
            $query->join($key, $item->first, '=', $item->second, $item->join_type);
        });

        return $query;
    }

    public function getByCountryAndEmail(Country $country, $email)
    {
        return $this->model->where('users.country_id', $country->id)->where('users.email', $email)->first();
    }

    public function getById($id)
    {
        return $this->model->ofId($id)->first();
    }

    /**
     * @param User $user
     * @param Coupon $coupon
     * @param Carbon $chargedAt
     * @return mixed
     */
    public function attachCoupon(User $user, Coupon $coupon, Carbon $chargedAt)
    {
        return $user->coupons()->attach($coupon->id, ['charged_at' => $chargedAt]);
    }

    /**
     * @param User $user
     * @param Collection $coupons_id
     * @return int
     */
    public function detachCoupons(User $user, Collection $coupons_id)
    {
        return $user->coupons()->detach($coupons_id->toArray());
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