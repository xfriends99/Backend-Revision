<?php

namespace App\GraphQL\Tracking\Query;

use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ResolveInfo;
use Rebing\GraphQL\Support\SelectFields;
use Rebing\GraphQL\Support\Query;
use Rebing\GraphQL\Support\Facades\GraphQL;

use App\Repositories\PurchaseRepository;

class PurchaseQuery extends Query
{
    protected $attributes = [
        'name' => 'PurchaseQuery',
        'description' => 'A query'
    ];

    public function type()
    {
        return GraphQL::type('purchase');
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
        /** @var PurchaseRepository $purchaseRepository */
        $purchaseRepository = app(PurchaseRepository::class);

        /** @var $with */
        $with = $fields->getRelations();

        return $purchaseRepository->filter($args)->with($with)->first();
    }
}