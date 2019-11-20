<?php

namespace App\GraphQL\Type;

use Rebing\GraphQL\Support\Type as GraphQLType;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use App\Models\Platform;


class PlatformType extends GraphQLType
{
    protected $attributes = [
        'name' => 'PlatformType',
        'description' => 'A type',
        'model' => Platform::class
    ];

    public function fields()
    {
        return [
            'id' => [
                'type' => Type::nonNull(Type::int()),
                'description' => 'Id of platform'
            ],
            'key' => [
                'type'  => Type::string(),
                'description' => 'Key of platform'
            ],
            'name' => [
                'type'  => Type::string(),
                'description' => 'Name of platform'
            ],
            'domain' => [
                'type'  => Type::string(),
                'description' => 'Domain of platform'
            ],
        ];
    }
}