<?php

namespace App\GraphQL\Type;

use Rebing\GraphQL\Support\Type as GraphQLType;
use GraphQL\Type\Definition\Type;
use App\Models\Transaction;
use Rebing\GraphQL\Support\Facades\GraphQL;

class TransactionType extends GraphQLType
{
    protected $attributes = [
        'name' => 'TransactionType',
        'description' => 'A type',
        'model' => Transaction::class
    ];

    public function fields()
    {
        return [
            'id' => [
                'type' => Type::nonNull(Type::int()),
                'description' => 'Id of invoice'
            ],
            'key' => [
                'type'  => Type::boolean(),
                'description' => 'Is approved the transaction'
            ],
            'card' => [
                'type'  => GraphQL::type('card'),
                'description' => 'Card of transaction'
            ],
            'paymentMethod' => [
                'type'  => GraphQL::type('payment_method'),
                'description' => 'Payment method of transaction'
            ],
            'transactionStatus' => [
                'type'  => GraphQL::type('transaction_status'),
                'description' => 'Transaction status'
            ],
            'amount' => [
                'type'  => Type::int(),
                'description' => 'Amount of transaction'
            ],
            'external_id' => [
                'type'  => Type::string(),
                'description' => 'externalId of transaction'
            ],
            'details' => [
                'type'  => Type::string(),
                'description' => 'Details of transaction'
            ],
            'created_at' => [
                'type' => Type::string(),
                'description' => 'Date of created'
            ]
        ];
    }

    protected function resolveCreatedAtField($root, $args)
    {
        return $root->created_at->format('d/m/Y h:i:s');
    }

}