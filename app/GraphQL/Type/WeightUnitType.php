<?php

namespace App\GraphQL\Type;

use Rebing\GraphQL\Support\Type as GraphQLType;
use GraphQL\Type\Definition\Type;
use App\Models\WeightUnit;

class WeightUnitType extends GraphQLType
{
    protected $attributes = [
        'name' => 'WeightUnitType',
        'description' => 'A type',
        'model' => WeightUnit::class
    ];

    public function fields()
    {
        return [
            'id' => [
                'type' => Type::nonNull(Type::int()),
                'description' => 'Id of invoice'
            ],
            'name' => [
                'type' => Type::string(),
                'description' => 'Name of weight unit'
            ],
            'code' => [
                'type' => Type::string(),
                'description' => 'Code of weight unit'
            ]
        ];
    }
}
