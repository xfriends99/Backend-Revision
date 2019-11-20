<?php

namespace App\GraphQL\Type;

use App\Models\ServiceType;
use Rebing\GraphQL\Support\Type as GraphQLType;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;

class ServiceTypeType extends GraphQLType
{
    protected $attributes = [
        'name' => 'ServiceTypeType',
        'description' => 'A type',
        'model' => ServiceType::class
    ];

    public function fields()
    {
        return [
            'id' => [
                'type' => Type::nonNull(Type::int()),
                'description' => 'Id of country'
            ],
            'key' => [
                'type'  => Type::string(),
                'description' => 'Key'
            ],
            'description' => [
                'type'  => Type::string(),
                'description' => 'Description'
            ],
            'services' => [
                'type'  => Type::listOf(GraphQL::type('service')),
                'description' => 'Services'
            ],
        ];
    }
}