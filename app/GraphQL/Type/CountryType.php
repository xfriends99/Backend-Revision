<?php

namespace App\GraphQL\Type;

use Rebing\GraphQL\Support\Type as GraphQLType;
use GraphQL\Type\Definition\Type;
use App\Models\Country;

class CountryType extends GraphQLType
{
    protected $attributes = [
        'name' => 'CountryType',
        'description' => 'A type',
        'model' => Country::class
    ];

    public function fields()
    {
        return [
            'id' => [
                'type' => Type::nonNull(Type::int()),
                'description' => 'Id of country'
            ],
            'name' => [
                'type'  => Type::string(),
                'description' => 'Name of country'
            ],
            'code' => [
                'type'  => Type::string(),
                'description' => 'Code of country'
            ],
        ];
    }
}