<?php

namespace App\GraphQL\Type;

use Rebing\GraphQL\Support\Type as GraphQLType;
use GraphQL\Type\Definition\Type;

class TownType extends GraphQLType
{
    protected $attributes = [
        'name' => 'TownType',
        'description' => 'A type'
    ];

    public function fields()
    {
        return [
            'id' => [
                'type' => Type::nonNull(Type::int()),
                'description' => 'Id of town'
            ],
            'name' => [
                'type'  => Type::string(),
                'description' => 'Name of town'
            ],
            'admin_level_1_name' => [
                'type'  => Type::string(),
                'description' => 'Admin level 1 name of town'
            ]
        ];
    }
}