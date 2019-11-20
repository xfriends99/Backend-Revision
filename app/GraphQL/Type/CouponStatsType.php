<?php

namespace App\GraphQL\Type;

use Rebing\GraphQL\Support\Type as GraphQLType;
use GraphQL\Type\Definition\Type;

class CouponStatsType extends GraphQLType
{
    protected $attributes = [
        'name' => 'CouponStatsType',
        'description' => 'A type'
    ];

    public function fields()
    {
        return [
            'coupons_free_count' => [
                'type' => Type::int(),
                'description' => 'Actives coupons'
            ],
            'coupons_use_count' => [
                'type' => Type::int(),
                'description' => 'Used coupons'
            ]
        ];
    }
}