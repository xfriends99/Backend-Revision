<?php

namespace App\GraphQL\Type;

use Rebing\GraphQL\Support\Type as GraphQLType;
use GraphQL\Type\Definition\Type;
use App\Models\Locker;

class LockerType extends GraphQLType
{
    protected $attributes = [
        'name' => 'LockerType',
        'description' => 'A type',
        'model' => Locker::class
    ];

    public function fields()
    {
        return [
            'id' => [
                'type' => Type::nonNull(Type::int()),
                'description' => 'Id of locker'
            ],
            'code' => [
                'type'  => Type::string(),
                'description' => 'Code of user'
            ]
        ];
    }
}