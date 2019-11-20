<?php

namespace App\GraphQL\Tracking\Query;

use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ResolveInfo;
use Rebing\GraphQL\Support\SelectFields;
use Rebing\GraphQL\Support\Query;
use Rebing\GraphQL\Support\Facades\GraphQL;

use App\Repositories\WarehouseUnknownRepository;

class WarehouseUnknownQuery extends Query
{
    protected $attributes = [
        'name' => 'WarehouseUnknownQuery',
        'description' => 'A query'
    ];

    public function type()
    {
        return GraphQL::type('warehouse_unknown');
    }

    public function args()
    {
        return [
            'id' => [
                'name' => 'id',
                'type' => Type::nonNull(Type::int())
            ]
        ];
    }

    public function resolve($root, $args, SelectFields $fields, ResolveInfo $info)
    {
        /** @var WarehouseUnknownRepository $warehouseUnknownRepository */
        $warehouseUnknownRepository = app(WarehouseUnknownRepository::class);

        $with = $fields->getRelations();

        return $warehouseUnknownRepository->filter($args)->with($with)->first();
    }
}