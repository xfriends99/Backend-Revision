<?php

namespace App\GraphQL\Type;

use App\Models\Card;
use Rebing\GraphQL\Support\Type as GraphQLType;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;

class CardType extends GraphQLType
{
    protected $attributes = [
        'name' => 'CardType',
        'description' => 'A type',
        'model' => Card::class
    ];

    public function fields()
    {
        return [
            'id' => [
                'type' => Type::nonNull(Type::int()),
                'description' => 'Id of country'
            ],
            'number' => [
                'type'  => Type::int(),
                'description' => 'Last four digits of card'
            ],
            'name' => [
                'type'  => Type::string(),
                'description' => 'Cardholder\'s Name'
            ],
            'status' => [
                'type'  => Type::string(),
                'description' => 'State of card'
            ],
            'default' => [
                'type'  => Type::boolean(),
                'description' => 'Default card'
            ],
            'created_at' => [
                'type' => Type::string(),
                'description' => 'Date of created'
            ],
            'cardBrand' => [
                'type'  => GraphQL::type('card_brand'),
                'description' => 'Card brand'
            ],
        ];
    }

    protected function resolveCreatedAtField($root, $args)
    {
        return $root->created_at->format('d/m/Y');
    }
}