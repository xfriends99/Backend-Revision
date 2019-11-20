<?php

namespace App\GraphQL\Tracking\Query;

use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ResolveInfo;
use Rebing\GraphQL\Support\SelectFields;
use Rebing\GraphQL\Support\Query;
use Rebing\GraphQL\Support\Facades\GraphQL;

use App\Repositories\WorkOrderRepository;

class WorkOrderQuery extends Query
{
    protected $attributes = [
        'name' => 'WorkOrdersQuery',
        'description' => 'A query'
    ];

    public function type()
    {
        return GraphQL::type('work_order');
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
        /** @var WorkOrderRepository $workOrderRepository */
        $workOrderRepository = app(WorkOrderRepository::class);

        /** @var $with */
        $with = $fields->getRelations();

        return $workOrderRepository->filter($args)->first();
    }
}