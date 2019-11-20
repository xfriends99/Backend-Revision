<?php

namespace App\GraphQL\Type;

use Rebing\GraphQL\Support\Type as GraphQLType;
use GraphQL\Type\Definition\Type;
use App\Models\TransactionStatus;

class TransactionStatusType extends GraphQLType
{
    protected $attributes = [
        'name' => 'TransactionStatusType',
        'description' => 'A type',
        'model' => TransactionStatus::class
    ];

    public function fields()
    {
        return [
            'id' => [
                'type' => Type::nonNull(Type::int()),
                'description' => 'Id of invoice'
            ],
            'key' => [
                'type'  => Type::string(),
                'description' => 'Key of payment method'
            ],
            'name' => [
                'type'  => Type::string(),
                'description' => 'Name of payment method'
            ]
        ];
    }

}