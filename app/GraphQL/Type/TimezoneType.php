<?php

namespace App\GraphQL\Type;

use Rebing\GraphQL\Support\Type as GraphQLType;
use GraphQL\Type\Definition\Type;
use App\Models\Timezone;

class TimezoneType extends GraphQLType
{
    protected $attributes = [
        'name' => 'TimezoneType',
        'description' => 'A type',
        'model' => Timezone::class
    ];

    public function fields()
    {
        return [
            'id' => [
                'type' => Type::nonNull(Type::int()),
                'description' => 'Id of timezone'
            ],
            'name' => [
                'type'  => Type::string(),
                'description' => 'Name of timezone'
            ],
            'description' => [
                'type'  => Type::string(),
                'description' => 'Description of timezone'
            ]
        ];
    }
}