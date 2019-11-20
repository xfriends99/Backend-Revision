<?php

namespace App\GraphQL\Query;

use App\Models\User;
use App\Repositories\PurchaseRepository;
use GraphQL\Type\Definition\ResolveInfo;
use Rebing\GraphQL\Support\SelectFields;
use Rebing\GraphQL\Support\Query;
use Rebing\GraphQL\Support\Facades\GraphQL;

class PurchasesDashboardQuery extends Query
{
    protected $attributes = [
        'name' => 'PurchasesDashboardQuery',
        'description' => 'A query'
    ];

    public function type()
    {
        return GraphQL::type('purchase_dashboard');
    }

    public function args()
    {
        return [

        ];
    }

    public function resolve($root, $args, SelectFields $fields, ResolveInfo $info)
    {
        /** @var User $user */
        $user = request()->user();

        /** @var PurchaseRepository $purchaseRepository */
        $purchaseRepository = app(PurchaseRepository::class);

        return $purchaseRepository->getCountPurchasesUserByState($user)->first();
    }
}