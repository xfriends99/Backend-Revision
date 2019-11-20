<?php

namespace App\GraphQL\Type;

use Rebing\GraphQL\Support\Type as GraphQLType;
use GraphQL\Type\Definition\Type;

class TownshipType extends GraphQLType
{
    protected $attributes = [
        'name' => 'TownshipType',
        'description' => 'A type'
    ];

    public function fields()
    {
        return [
            'id' => [
                'type' => Type::nonNull(Type::int()),
                'description' => 'Id of township'
            ],
            'name' => [
                'type'  => Type::string(),
                'description' => 'Name of township'
            ],
            'admin_level_2_name' => [
                'type'  => Type::string(),
                'description' => 'Admin level 2 of township'
            ],
            'admin_level_1_name' => [
                'type'  => Type::string(),
                'description' => 'Admin level 1 of township'
            ],
            'territorial_code' => [
                'type'  => Type::string(),
                'description' => 'Territorial code of township'
            ]
        ];
    }
}