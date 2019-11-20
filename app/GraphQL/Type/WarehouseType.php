<?php

namespace App\GraphQL\Type;

use Rebing\GraphQL\Support\Type as GraphQLType;
use GraphQL\Type\Definition\Type;
use App\Models\Warehouse;
use Rebing\GraphQL\Support\Facades\GraphQL;

class WarehouseType extends GraphQLType
{
    protected $attributes = [
        'name' => 'Warehouse',
        'description' => 'A type',
        'model' => Warehouse::class
    ];

    public function fields()
    {
        return [
            'id' => [
                'type' => Type::nonNull(Type::int()),
                'description' => 'Id of warehouse'
            ],
            'name' => [
                'type'  => Type::string(),
                'description' => 'Name of warehouse'
            ],
            'code' => [
                'type'  => Type::string(),
                'description' => 'code of warehouse'
            ],
            'address1' => [
                'type'  => Type::string(),
                'description' => 'Address1 of warehouse'
            ],
            'address2' => [
                'type'  => Type::string(),
                'description' => 'Address2 of warehouse'
            ],
            'state' => [
                'type'  => Type::string(),
                'description' => 'State of warehouse'
            ],
            'city' => [
                'type'  => Type::string(),
                'description' => 'City of warehouse'
            ],
            'township' => [
                'type'  => Type::string(),
                'description' => 'Township of warehouse'
            ],
            'postal_code' => [
                'type'  => Type::string(),
                'description' => 'Postal code of warehouse'
            ],
            'country' => [
                'type'  => GraphQL::type('country'),
                'description' => 'Country of warehouse'
            ],
            'tracking' => [
                'type' => Type::string(),
                'description' => 'Tracking of warehouse'
            ]
        ];
    }
}