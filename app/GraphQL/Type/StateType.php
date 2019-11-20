<?php

namespace App\GraphQL\Type;

use Rebing\GraphQL\Support\Type as GraphQLType;
use GraphQL\Type\Definition\Type;
use App\Models\State;
use Rebing\GraphQL\Support\Facades\GraphQL;

class StateType extends GraphQLType
{
    protected $attributes = [
        'name' => 'StateType',
        'description' => 'A type',
        'model' => State::class
    ];

    public function fields()
    {
        return [
            'id' => [
                'type' => Type::nonNull(Type::int()),
                'description' => 'Id of state'
            ],
            'name' => [
                'type'  => Type::string(),
                'description' => 'Name of state'
            ],
            'country' => [
                'type'  => GraphQL::type('country'),
                'description' => 'Country of State'
            ]
        ];
    }
}