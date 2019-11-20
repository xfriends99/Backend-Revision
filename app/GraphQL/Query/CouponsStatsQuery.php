<?php

namespace App\GraphQL\Query;

use App\Models\User;
use App\Repositories\CouponRepository;
use GraphQL\Type\Definition\ResolveInfo;
use Rebing\GraphQL\Support\SelectFields;
use Rebing\GraphQL\Support\Query;
use Rebing\GraphQL\Support\Facades\GraphQL;

class CouponsStatsQuery extends Query
{
    protected $attributes = [
        'name' => 'CouponsStatsQuery',
        'description' => 'A query'
    ];

    public function type()
    {
        return GraphQL::type('coupon_stats');
    }

    public function args()
    {
        return [

        ];
    }

    public function resolve($root, $args, SelectFields $fields, ResolveInfo $info)
    {
        /** @var User $user */
        $user = request()->user();

        /** @var CouponRepository $couponRepository */
        $couponRepository = app(CouponRepository::class);
        
        return $couponRepository->getCountUserCouponsByUse($user)->first();
    }

}