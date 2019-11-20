<?php

namespace App\GraphQL\Type;

use App\Models\CardBrand;
use Rebing\GraphQL\Support\Type as GraphQLType;
use GraphQL\Type\Definition\Type;

class CardBrandType extends GraphQLType
{
    protected $attributes = [
        'name' => 'CardBrandType',
        'description' => 'A type',
        'model' => CardBrand::class
    ];

    public function fields()
    {
        return [
            'id' => [
                'type' => Type::nonNull(Type::int()),
                'description' => 'Id of country'
            ],
            'brand' => [
                'type'  => Type::string(),
                'description' => 'Brand name'
            ]
        ];
    }
}