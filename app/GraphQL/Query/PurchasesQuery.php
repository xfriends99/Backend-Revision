<?php

namespace App\GraphQL\Query;

use App\Models\User;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ResolveInfo;
use Rebing\GraphQL\Support\SelectFields;
use Rebing\GraphQL\Support\Query;
use Rebing\GraphQL\Support\Facades\GraphQL;

use App\Repositories\PurchaseRepository;

class PurchasesQuery extends Query
{
    protected $attributes = [
        'name' => 'PurchasesQuery',
        'description' => 'A query'
    ];

    public function type()
    {
        return GraphQL::paginate('purchase');
    }

    public function args()
    {
        return [
            'address_id' => [
                'name' => 'address_id',
                'type' => Type::int()
            ],
            'user_id' => [
                'name' => 'user_id',
                'type' => Type::string()
            ],
            'limit' => [
                'name' => 'limit',
                'type' => Type::int()
            ],
            'page' => [
                'name' => 'page',
                'type' => Type::int()
            ],
            'consolidate' => [
                'name' => 'consolidate',
                'type' => Type::boolean()
            ]
        ];
    }

    public function resolve($root, $args, SelectFields $fields, ResolveInfo $info)
    {
        /** @var User $user */
        $user = request()->user();

        /** @var PurchaseRepository $purchaseRepository */
        $purchaseRepository = app(PurchaseRepository::class);

        $with = $fields->getRelations();

        if(isset($args['user_id']) && $args['user_id'] == 'me')
        {
            $args['user_id'] = $user->id;
        }

        return $purchaseRepository->filter($args)->with($with)->paginate($args['limit'], ['*'], 'page', $args['page']);
    }
}