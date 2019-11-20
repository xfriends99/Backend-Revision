<?php

namespace App\GraphQL\Type;

use Rebing\GraphQL\Support\Type as GraphQLType;
use GraphQL\Type\Definition\Type;
use App\Models\PurchaseItem;

class PurchaseItemType extends GraphQLType
{
    protected $attributes = [
        'name' => 'PurchaseItemType',
        'description' => 'A type',
        'model' => PurchaseItem::class
    ];

    public function fields()
    {
        return [
            'id' => [
                'type' => Type::nonNull(Type::int()),
                'description' => 'Id of purchase item'
            ],
            'description' => [
                'type'  => Type::string(),
                'description' => 'Description of purchase item'
            ],
            'link' => [
                'type'  => Type::string(),
                'description' => 'Link of purchase item'
            ],
            'quantity' => [
                'type'  => Type::int(),
                'description' => 'Quantity of purchase item'
            ],
            'amount' => [
                'type'  => Type::float(),
                'description' => 'Amount of purchase item'
            ],
            'length' => [
                'type'  => Type::float(),
                'description' => 'Length of purchase item'
            ],
            'width' => [
                'type'  => Type::float(),
                'description' => 'Width of purchase item'
            ],
            'height' => [
                'type'  => Type::float(),
                'description' => 'Height of purchase item'
            ],
            'weight' => [
                'type'  => Type::float(),
                'description' => 'Weight of purchase item'
            ]
        ];
    }
}