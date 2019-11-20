<?php

namespace App\GraphQL\Type;

use Rebing\GraphQL\Support\Type as GraphQLType;
use GraphQL\Type\Definition\Type;
use App\Models\Package;
use Rebing\GraphQL\Support\Facades\GraphQL;

class PackageType extends GraphQLType
{
    protected $attributes = [
        'name' => 'PackageType',
        'description' => 'A type',
        'model' => Package::class
    ];

    public function fields()
    {
        return [
            'id' => [
                'type' => Type::nonNull(Type::int()),
                'description' => 'Id of package'
            ],
            'user' => [
                'type'  => GraphQL::type('user'),
                'description' => 'User of Package'
            ],
            'workOrder' => [
                'type'  => GraphQL::type('work_order'),
                'description' => 'Work Order of Package'
            ],
            'invoice' => [
                'type'  => GraphQL::type('invoice'),
                'description' => 'Invoice of Package'
            ],
            'tracking' => [
                'type'  => Type::string(),
                'description' => 'Tracking number'
            ],
            'value' => [
                'type'  => Type::float(),
                'description' => 'Value of package'
            ],
            'length' => [
                'type'  => Type::float(),
                'description' => 'Length of package'
            ],
            'width' => [
                'type'  => Type::float(),
                'description' => 'Width of package'
            ],
            'height' => [
                'type'  => Type::float(),
                'description' => 'Height of package'
            ],
            'weight' => [
                'type'  => Type::float(),
                'description' => 'Weight of package'
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