<?php

namespace App\GraphQL\Type;

use App\Models\CouponClassification;
use Rebing\GraphQL\Support\Type as GraphQLType;
use GraphQL\Type\Definition\Type;

class CouponClassificationType extends GraphQLType
{
    protected $attributes = [
        'name' => 'CouponClassificationType',
        'description' => 'A type',
        'model' => CouponClassification::class
    ];

    public function fields()
    {
        return [
            'id' => [
                'type' => Type::nonNull(Type::int()),
                'description' => 'Id of coupon classification'
            ],
            'name' => [
                'type'  => Type::string(),
                'description' => 'Coupon classification name'
            ],
            'key' => [
                'type'  => Type::string(),
                'description' => 'Coupon classification key'
            ]
        ];
    }
}
