<?php

namespace App\GraphQL\Type;

use Rebing\GraphQL\Support\Type as GraphQLType;
use GraphQL\Type\Definition\Type;

class ZipCodeType extends GraphQLType
{
    protected $attributes = [
        'name' => 'ZipCodeType',
        'description' => 'A type'
    ];

    public function fields()
    {
        return [
            'admin_level_3_name' => [
                'type'  => Type::string(),
                'description' => 'Name of Admin level 3'
            ],
            'territorial_code' => [
                'type'  => Type::string(),
                'description' => 'Territorial code of Admin level 3'
            ],
            'code' => [
                'type'  => Type::string(),
                'description' => 'Code of Zip code'
            ]
        ];
    }
}