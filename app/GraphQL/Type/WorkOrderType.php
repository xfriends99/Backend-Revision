<?php

namespace App\GraphQL\Type;

use App\Models\WorkOrder;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\Type as GraphQLType;

class WorkOrderType extends GraphQLType
{
    protected $attributes = [
        'name'        => 'WorkOrderType',
        'description' => 'A type',
        'model'       => WorkOrder::class
    ];

    public function fields()
    {
        return [
            'id'                       => [
                'type'        => Type::int(),
                'description' => 'Id'
            ],
            'value'                    => [
                'type'        => Type::float(),
                'description' => 'Value of Work order'
            ],
            'type'                     => [
                'type'        => Type::string(),
                'description' => 'Type of Work order'
            ],
            'state'                    => [
                'type'        => Type::string(),
                'description' => 'State of Work order'
            ],
            'package'                  => [
                'type'        => GraphQL::type('package'),
                'description' => 'Package of Work order'
            ],
            'purchases'                => [
                'type'        => Type::listOf(GraphQL::type('purchase')),
                'description' => 'Purchases of Package'
            ],
            'additionals'              => [
                'type'        => Type::listOf(GraphQL::type('additional')),
                'description' => 'Additionals of work order'
            ],
            'tracking'                 => [
                'type'        => Type::string(),
                'description' => 'Tracking number of package'
            ],
            'created_at'               => [
                'type'        => Type::string(),
                'description' => 'Date of created'
            ],
            'processed_purchase_count' => [
                'type'        => Type::int(),
                'description' => 'Count of processed purchase'
            ],
            'total_purchase_count'     => [
                'type'        => Type::int(),
                'description' => 'Count of processed purchase'
            ],
            'total_purchase_weight'    => [
                'type'        => Type::float(),
                'description' => 'Total weight of items'
            ]
        ];
    }

    protected function resolveProcessedPurchaseCountField($root, $args)
    {
        return $root->getProcessedPurchasesCount();
    }

    protected function resolveTotalPurchaseCountField($root, $args)
    {
        return $root->getPurchasesCount();
    }

    protected function resolveTotalPurchaseWeightField($root, $args)
    {
        return $root->getPurchasesWeightAsKg();
    }

    protected function resolveTrackingField($root, $args)
    {
        return $root->getPackageTracking();
    }
}
