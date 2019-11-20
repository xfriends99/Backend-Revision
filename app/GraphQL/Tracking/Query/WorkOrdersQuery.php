<?php

namespace App\GraphQL\Tracking\Query;

use App\Repositories\WorkOrderRepository;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\Query;
use Rebing\GraphQL\Support\SelectFields;

class WorkOrdersQuery extends Query
{
    protected $attributes = [
        'name'        => 'WorkOrdersQuery',
        'description' => 'A query'
    ];

    public function type()
    {
        return GraphQL::paginate('work_order');
    }

    public function args()
    {
        return [
            'tracking'                 => ['name' => 'tracking', 'type' => Type::string()],
            'platform_id'              => ['name' => 'platform_id', 'type' => Type::listOf(Type::string())],
            'work_order_id'            => ['name' => 'work_order_id', 'type' => Type::string()],
            'created_at_newer_than'    => ['name' => 'created_at_newer_than', 'type' => Type::string()],
            'created_at_older_than'    => ['name' => 'created_at_older_than', 'type' => Type::string()],
            'marketplace_id'           => ['name' => 'marketplace_id', 'type' => Type::listOf(Type::string())],
            'warehouse_id'             => ['name' => 'warehouse_id', 'type' => Type::listOf(Type::string())],
            'destination_country_code' => ['name' => 'destination_country_code', 'type' => Type::listOf(Type::string())],
            'locker_code'              => ['name' => 'locker_code', 'type' => Type::string()],
            'user_name'                => ['name' => 'user_name', 'type' => Type::string()],
            'checkpoint_code_id'       => ['name' => 'checkpoint_code_id', 'type' => Type::string()],
            'consolidatable'           => ['name' => 'consolidatable', 'type' => Type::boolean()],
            'shippable'                => ['name' => 'shippable', 'type' => Type::boolean()],
            'orphaned'                 => ['name' => 'orphaned', 'type' => Type::boolean()],
            'limit'                    => ['name' => 'limit', 'type' => Type::int()],
            'page'                     => ['name' => 'page', 'type' => Type::int()]
        ];
    }

    public function resolve($root, $args, SelectFields $fields, ResolveInfo $info)
    {
        /** @var WorkOrderRepository $workOrderRepository */
        $workOrderRepository = app(WorkOrderRepository::class);

        //$with = $fields->getRelations();
        $per_page = isset($args['limit']) ? $args['limit'] : null;
        $page = isset($args['page']) ? $args['page'] : 1;

        return $workOrderRepository
            ->filter($args)
            ->orderBy('work_orders.id', 'desc')
            //->with($with)
            ->paginate($per_page, ['*'], 'page', $page);
    }
}
