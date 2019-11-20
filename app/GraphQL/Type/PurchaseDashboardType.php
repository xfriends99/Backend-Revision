<?php

namespace App\GraphQL\Type;

use Rebing\GraphQL\Support\Type as GraphQLType;
use GraphQL\Type\Definition\Type;

class PurchaseDashboardType extends GraphQLType
{
    protected $attributes = [
        'name' => 'PurchaseDashboardType',
        'description' => 'A type'
    ];

    public function fields()
    {
        return [
            'prealerts_count' => [
                'type' => Type::int(),
                'description' => 'Purchases prealerts'
            ],
            'lockers_count' => [
                'type' => Type::int(),
                'description' => 'Total of purchases in warehouse'
            ]
        ];
    }
}