<?php

namespace App\GraphQL\Tracking\Query;

use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ResolveInfo;
use Rebing\GraphQL\Support\SelectFields;
use Rebing\GraphQL\Support\Query;
use Rebing\GraphQL\Support\Facades\GraphQL;

use App\Repositories\WarehouseUnknownRepository;

class WarehousesUnknownsQuery extends Query
{
    protected $attributes = [
        'name' => 'WarehousesUnknownsQuery',
        'description' => 'A query'
    ];

    public function type()
    {
        return GraphQL::paginate('warehouse_unknown');
    }

    public function args()
    {
        return [
            'tracking' => [
                'name' => 'tracking',
                'type' => Type::string()
            ],
            'found' => [
                'name' => 'found',
                'type' => Type::boolean()
            ],
            'created_at_newer_than' => [
                'name' => 'created_at_newer_than',
                'type' => Type::string()
            ],
            'created_at_older_than' => [
                'name' => 'created_at_older_than',
                'type' => Type::string()
            ],
            'limit' => [
                'name' => 'limit',
                'type' => Type::int()
            ],
            'page' => [
                'name' => 'page',
                'type' => Type::int()
            ]
        ];
    }

    public function resolve($root, $args, SelectFields $fields, ResolveInfo $info)
    {
        /** @var WarehouseUnknownRepository $warehouseUnknownRepository */
        $warehouseUnknownRepository = app(WarehouseUnknownRepository::class);

        $with = $fields->getRelations();
        $per_page = isset($args['limit']) ? $args['limit'] : null;
        $page = isset($args['page']) ? $args['page'] : 1;

        return $warehouseUnknownRepository->filter($args)->with($with)->paginate($per_page, ['*'], 'page', $page);
    }
}