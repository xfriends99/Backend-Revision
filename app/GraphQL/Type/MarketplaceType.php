<?php

namespace App\GraphQL\Type;

use Rebing\GraphQL\Support\Type as GraphQLType;
use GraphQL\Type\Definition\Type;
use App\Models\Marketplace;

class MarketplaceType extends GraphQLType
{
    protected $attributes = [
        'name' => 'MarketplaceType',
        'description' => 'A type',
        'model' => Marketplace::class
    ];

    public function fields()
    {
        return [
            'id' => [
                'type' => Type::nonNull(Type::int()),
                'description' => 'Id of marketplace'
            ],
            'name' => [
                'type'  => Type::string(),
                'description' => 'Name of marketplace'
            ],
            'code' => [
                'type'  => Type::string(),
                'description' => 'Code of marketplace'
            ]
        ];
    }
}