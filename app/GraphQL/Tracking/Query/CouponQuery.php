<?php

namespace App\GraphQL\Tracking\Query;

use App\Models\Coupon;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ResolveInfo;
use Rebing\GraphQL\Support\SelectFields;
use Rebing\GraphQL\Support\Query;
use Rebing\GraphQL\Support\Facades\GraphQL;

use App\Repositories\CouponRepository;

class CouponQuery extends Query
{
    protected $attributes = [
        'name' => 'CouponQuery',
        'description' => 'A query'
    ];

    public function type()
    {
    	return GraphQL::type('coupon');
    }

    public function args()
    {
        return [
        	'id' => [
                'name' => 'id',
                'type' => Type::int()
            ]
        ];
    }

    public function resolve($root, $args, SelectFields $fields, ResolveInfo $info)
    {
        /** @var CouponRepository $couponRepository */
        $couponRepository = app(CouponRepository::class);

        $with = $fields->getRelations();

        return $couponRepository->filter($args)->with($with)->first();
    }
}