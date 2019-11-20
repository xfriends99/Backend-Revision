<?php

namespace App\GraphQL\Type;

use Rebing\GraphQL\Support\Type as GraphQLType;
use GraphQL\Type\Definition\Type;
use App\Models\PurchaseCheckpoint;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Carbon\Carbon;

class PurchaseCheckpointType extends GraphQLType
{
    protected $attributes = [
        'name' => 'PurchaseCheckpointType',
        'description' => 'A type',
        'model' => PurchaseCheckpoint::class
    ];

    public function fields()
    {
        return [
            'id' => [
                'type' => Type::nonNull(Type::int()),
                'description' => 'Id of purchase'
            ],
            'checkpointCode' => [
                'type'  => GraphQL::type('checkpoint_code'),
                'description' => 'Checkpoint code of purchase checkpoint'
            ],
            'purchase' => [
                'type'  => GraphQL::type('purchase'),
                'description' => 'Purchase of purchase checkpoint'
            ],
            'checkpoint_at' => [
                'type' => Type::string(),
                'description' => 'Checkpoint at of purchase checkpoint'
            ],
            'created_at' => [
                'type' => Type::string(),
                'description' => 'Date of created'
            ]
        ];
    }

    protected function resolveCheckpointAtField($root, $args)
    {
        $checkpointAt = Carbon::parse($root->checkpoint_at);
        return $checkpointAt->copy()->format('d/m/Y h:m:s');
    }

    protected function resolveCreatedAtField($root, $args)
    {
        return $root->created_at->format('d/m/Y');
    }

}