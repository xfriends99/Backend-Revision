<?php

namespace App\GraphQL\Type;

use Rebing\GraphQL\Support\Type as GraphQLType;
use GraphQL\Type\Definition\Type;
use App\Models\WarehouseUnknown;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Carbon\Carbon;

class WarehouseUnknownType extends GraphQLType
{
    protected $attributes = [
        'name' => 'WarehouseUnknownType',
        'description' => 'A type',
        'model' => WarehouseUnknown::class
    ];

    public function fields()
    {
        return [
            'id' => [
                'type' => Type::nonNull(Type::int()),
                'description' => 'Id of warehouseUnknown'
            ],
            'tracking' => [
                'type' => Type::string(),
                'description' => 'Tracking of warehouseUnknown'
            ],
            'found' => [
                'type' => Type::boolean(),
                'description' => 'Found of warehouseUnknown',
            ],
            'details' => [
                'type' => Type::string(),
                'description' => 'Details of warehouseUnknown'
            ],
            'created_at' => [
                'type' => Type::string()
            ]
        ];
    }

    protected function resolveCreatedAtField($root, $args)
    {
        return Carbon::parse($root->created_at)->format('d/m/Y');
    }
}