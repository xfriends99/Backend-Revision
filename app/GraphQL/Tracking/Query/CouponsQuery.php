<?php

namespace App\GraphQL\Tracking\Query;

use App\Models\Coupon;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ResolveInfo;
use Rebing\GraphQL\Support\SelectFields;
use Rebing\GraphQL\Support\Query;
use Rebing\GraphQL\Support\Facades\GraphQL;

use App\Repositories\CouponRepository;

class CouponsQuery extends Query
{
    protected $attributes = [
        'name' => 'CouponsQuery',
        'description' => 'A query'
    ];

    public function type()
    {
    	return GraphQL::paginate('coupon');
    }

    public function args()
    {
        return [
        	'id' => [
                'name' => 'id',
                'type' => Type::string()
            ],
            'user_id' => [
                'name' => 'user_id',
                'type' => Type::int()
            ],
            'coupon_classification_id' => [
                'name' => 'coupon_classification_id',
                'type' => Type::listOf(Type::string())
            ],
            'created_at_newer_than' => [
                'name' => 'created_at_newer_than',
                'type' => Type::string()
            ],
            'created_at_older_than' => [
                'name' => 'created_at_older_than',
                'type' => Type::string()
            ],
            'status' => [
                'name' => 'status',
                'type' => Type::listOf(Type::string())
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
        /** @var CouponRepository $couponRepository */
        $couponRepository = app(CouponRepository::class);

        $with = $fields->getRelations();
        $per_page = isset($args['limit']) ? $args['limit'] : null;
        $page = isset($args['page']) ? $args['page'] : 1;

        return $couponRepository->filter($args)->with($with)
						        ->paginate($per_page, ['*'], 'page', $page);
    }
}