<?php

namespace App\GraphQL\Type;

use Rebing\GraphQL\Support\Type as GraphQLType;
use GraphQL\Type\Definition\Type;
use App\Models\Additional;

class AdditionalType extends GraphQLType
{
    protected $attributes = [
        'name' => 'AdditionalType',
        'description' => 'A type',
        'model' => Additional::class
    ];

    public function fields()
    {
        return [
            'id' => [
                'type' => Type::nonNull(Type::int()),
                'description' => 'Id of country'
            ],
            'description_en' => [
                'type'  => Type::string(),
                'description' => 'Description en of Additional'
            ],
            'description_es' => [
                'type'  => Type::string(),
                'description' => 'Description es of Additional'
            ],
            'value' => [
                'type'  => Type::float(),
                'description' => 'Value of Additional'
            ],
            'key' => [
                'type'  => Type::string(),
                'description' => 'Key of Additional'
            ],
            'active' => [
                'type'  => Type::boolean(),
                'description' => 'Is active Additional'
            ],
            'required' => [
                'type'  => Type::boolean(),
                'description' => 'Is required Additional'
            ]
        ];
    }
}