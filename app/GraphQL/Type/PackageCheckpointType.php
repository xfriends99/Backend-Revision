<?php

namespace App\GraphQL\Type;

use Rebing\GraphQL\Support\Type as GraphQLType;
use GraphQL\Type\Definition\Type;
use Carbon\Carbon;

class PackageCheckpointType extends GraphQLType
{
    protected $attributes = [
        'name' => 'PackageCheckpointType',
        'description' => 'A type'
    ];

    public function fields()
    {
        return [
            'date' => [
                'type' => Type::string(),
                'description' => 'Date of Package event'
            ],
            'code' => [
                'type' => Type::string(),
                'description' => 'Code of Package event'
            ],
            'category' => [
                'type' => Type::string(),
                'description' => 'Category of Package event'
            ],
            'description' => [
                'type' => Type::string(),
                'description' => 'English description of Package event'
            ],
            'description_es' => [
                'type' => Type::string(),
                'description' => 'Spanish description of Package event'
            ]
        ];
    }

    protected function resolveDateField($root, $args)
    {
        return Carbon::parse($root->date)->format('d/m/Y h:m:s');
    }

}