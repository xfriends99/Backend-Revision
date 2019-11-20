<?php

namespace App\GraphQL\Type;

use Rebing\GraphQL\Support\Type as GraphQLType;
use GraphQL\Type\Definition\Type;
use App\Models\PaymentMethod;
use Rebing\GraphQL\Support\Facades\GraphQL;

class PaymentMethodType extends GraphQLType
{
    protected $attributes = [
        'name' => 'PaymentMethodType',
        'description' => 'A type',
        'model' => PaymentMethod::class
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