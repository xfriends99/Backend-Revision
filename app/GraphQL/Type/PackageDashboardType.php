<?php

namespace App\GraphQL\Type;

use Rebing\GraphQL\Support\Type as GraphQLType;
use GraphQL\Type\Definition\Type;

class PackageDashboardType extends GraphQLType
{
    protected $attributes = [
        'name' => 'PackageDashboardType',
        'description' => 'A type'
    ];

    public function fields()
    {
        return [
            'pendings_count' => [
                'type' => Type::int(),
                'description' => 'Packages pending to send'
            ],
            'dispatches_count' => [
                'type' => Type::int(),
                'description' => 'Packages dispatches to destination country'
            ]
        ];
    }
}