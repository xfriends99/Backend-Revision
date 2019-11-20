<?php

namespace App\GraphQL\Type;

use Rebing\GraphQL\Support\Type as GraphQLType;
use GraphQL\Type\Definition\Type;
use App\Models\Address;
use Rebing\GraphQL\Support\Facades\GraphQL;

class AddressType extends GraphQLType
{
    protected $attributes = [
        'name' => 'AddressType',
        'description' => 'A type',
        'model' => Address::class
    ];

    public function fields()
    {
        return [
            'id' => [
                'type' => Type::nonNull(Type::int()),
                'description' => 'Id of user address'
            ],
            'address1' => [
                'type'  => Type::string(),
                'description' => 'Address 1'
            ],
            'address2' => [
                'type'  => Type::string(),
                'description' => 'Address 2'
            ],
            'city' => [
                'type'  => Type::string(),
                'description' => 'City'
            ],
            'state' => [
                'type'  => Type::string(),
                'description' => 'State'
            ],
            'number' => [
                'type'  => Type::string(),
                'description' => 'Number'
            ],
            'reference' => [
                'type'  => Type::string(),
                'description' => 'Reference'
            ],
            'apartment' => [
                'type'  => Type::string(),
                'description' => 'Apartment'
            ],
            'floor' => [
                'type'  => Type::string(),
                'description' => 'Floor'
            ],
            'township' => [
                'type'  => Type::string(),
                'description' => 'Township'
            ],
            'postal_code' => [
                'type'  => Type::string(),
                'description' => 'Postal Code'
            ],
            'user' => [
                'type'  => GraphQL::type('user'),
                'description' => 'User'
            ],
            'country' => [
                'type'  => GraphQL::type('country'),
                'description' => 'Country of address'
            ]
        ];
    }
}