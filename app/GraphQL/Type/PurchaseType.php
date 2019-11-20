<?php

namespace App\GraphQL\Type;

use App\Models\CheckpointCode;
use App\Models\Purchase;
use App\Models\PurchaseCheckpoint;
use App\Services\Purchases\WeightUnitConverter;
use Carbon\Carbon;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\Type as GraphQLType;

class PurchaseType extends GraphQLType
{
    protected $attributes = [
        'name'        => 'PurchaseType',
        'description' => 'A type',
        'model'       => Purchase::class
    ];

    public function fields()
    {
        return [
            'id'                       => [
                'type'        => Type::nonNull(Type::int()),
                'description' => 'Id of purchase'
            ],
            'user'                     => [
                'type'        => GraphQL::type('user'),
                'description' => 'User of Purchase'
            ],
            'marketplace'              => [
                'type'        => GraphQL::type('marketplace'),
                'description' => 'Marketplace of Purchase'
            ],
            'address'                  => [
                'type'        => GraphQL::type('address'),
                'description' => 'User Adress of Purchase'
            ],
            'warehouse'                => [
                'type'        => GraphQL::type('warehouse'),
                'description' => 'Warehouse of Purchase'
            ],
            'workOrder'                => [
                'type'        => GraphQL::type('work_order'),
                'description' => 'Work Order of Purchase'
            ],
            'purchaseItems'            => [
                'type'        => Type::listOf(GraphQL::type('purchase_item')),
                'description' => 'Items of Purchase'
            ],
            'purchaseCheckpoints'      => [
                'type'        => Type::listOf(GraphQL::type('purchase_checkpoint')),
                'description' => 'checkpoints of Purchase'
            ],
            'weightUnit'      => [
                'type'        => GraphQL::type('weight_unit'),
                'description' => 'Weight unit of Purchase'
            ],
            'description'              => [
                'type'        => Type::string(),
                'description' => 'Description of purchase'
            ],
            'tracking'                 => [
                'type'        => Type::string(),
                'description' => 'Tracking number of purchase'
            ],
            'weight'                   => [
                'type'        => Type::float(),
                'description' => 'Weight of purchase'
            ],
            'carrier'                  => [
                'type'        => Type::string(),
                'description' => 'Carrier of purchase'
            ],
            'value'                    => [
                'type'        => Type::float(),
                'description' => 'Value of purchase'
            ],
            'invoice_url'              => [
                'type'        => Type::string(),
                'description' => 'Invoice url of purchase'
            ],
            'type'                     => [
                'type'        => Type::string(),
                'description' => 'Type of purchase'
            ],
            'state'                    => [
                'type'        => Type::string(),
                'description' => 'Status of purchase'
            ],
            'purchased_at'             => [
                'type'        => Type::string(),
                'description' => 'Date of purchased'
            ],
            'created_at'               => [
                'type'        => Type::string(),
                'description' => 'Date of created'
            ],
            'last_event_en'            => [
                'type'        => Type::string(),
                'description' => 'Last event en of purchase'
            ],
            'last_event'               => [
                'type'        => Type::string(),
                'description' => 'Last event of purchase'
            ],
            'package_tracking'         => [
                'type'        => Type::string(),
                'description' => 'Tracking number of package'
            ],
            'package_id'               => [
                'type'        => Type::int(),
                'description' => 'Id of package'
            ],
            'get_purchase_items_count' => [
                'type'        => Type::int(),
                'description' => 'count for items'
            ],
            'get_convert_weight' => [
                'type'        => Type::int(),
                'description' => 'convert weight'
            ]
        ];
    }

    protected function resolvePurchasedAtField($root, $args)
    {
        return Carbon::parse($root->purchased_at)->format('d/m/Y');
    }

    protected function resolveCreatedAtField($root, $args)
    {
        return $root->created_at->format('d/m/Y');
    }

    protected function resolvePackageTrackingField($root, $args)
    {
        return $root->getWorkOrderPackageTracking();
    }

    protected function resolvePackageIdField($root, $args)
    {
        return $root->getWorkOrderPackageId();
    }

    protected function resolveLastEventField($root, $args)
    {
        /** @var PurchaseCheckpoint $purchaseCheckpoint */
        if ($purchaseCheckpoint = $root->getLastCheckpoint()) {
            /** @var CheckpointCode $checkpointCode */
            $checkpointCode = $purchaseCheckpoint->checkpointCode;

            return $checkpointCode->description_es;
        }

        return null;
    }

    protected function resolveLastEventEnField($root, $args)
    {
        /** @var PurchaseCheckpoint $purchaseCheckpoint */
        if ($purchaseCheckpoint = $root->getLastCheckpoint()) {
            /** @var CheckpointCode $checkpointCode */
            $checkpointCode = $purchaseCheckpoint->checkpointCode;

            return $checkpointCode->description_en;
        }

        return null;
    }

    protected function resolveDescriptionField($root, $args)
    {
        return $root->getPurchaseItemsDescriptions();
    }

    protected function resolveWeightField($root, $args)
    {
        return $root->getWeight();
    }

    protected function resolveGetPurchaseItemsCountField($root, $args)
    {
        return $root->getPurchaseItemsCount();
    }

    protected function resolveGetConvertWeightField($root, $args)
    {
        $weight = new WeightUnitConverter($root->weightUnit, $root->getWeight());
        

        return $weight->getWeightAsKg();
    }


}
