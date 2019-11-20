<?php

namespace App\GraphQL\Type;

use Rebing\GraphQL\Support\Type as GraphQLType;
use GraphQL\Type\Definition\Type;
use App\Models\CheckpointCode;

class CheckpointCodeType extends GraphQLType
{
    protected $attributes = [
        'name' => 'CheckpointCodeType',
        'description' => 'A type',
        'model' => CheckpointCode::class
    ];

    public function fields()
    {
        return [
            'id' => [
                'type' => Type::nonNull(Type::int()),
                'description' => 'Id of purchase'
            ],
            'description_en' => [
                'type' => Type::string(),
                'description' => 'Description english of checkpoint code'
            ],
            'description_es' => [
                'type' => Type::string(),
                'description' => 'Description spanish of checkpoint code'
            ],
            'key' => [
                'type' => Type::string(),
                'description' => 'Key of checkpoint code'
            ],
            'created_at' => [
                'type' => Type::string(),
                'description' => 'Date of created'
            ]
        ];
    }

    protected function resolveCreatedAtField($root, $args)
    {
        return $root->created_at->format('d/m/Y');
    }

}