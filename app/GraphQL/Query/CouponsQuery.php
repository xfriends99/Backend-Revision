<?php

namespace App\GraphQL\Query;

use App\Models\Coupon;
use App\Models\User;
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
                'type' => Type::string()
            ],
            'used' => [
                'name' => 'used',
                'type' => Type::boolean()
            ],
            'purchase_id' => [
                'name' => 'purchase_id',
                'type' => Type::listOf(Type::int())
            ],
            'code' => [
                'name' => 'code',
                'type' => Type::string()
            ],
            'coupon_classification_id' => [
                'name' => 'coupon_classification_id',
                'type' => Type::int()
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
        /** @var User $user */
        $user = request()->user();

        /** @var CouponRepository $couponRepository */
        $couponRepository = app(CouponRepository::class);

        if(isset($args['user_id']) && $args['user_id'] == 'me')
        {
            $args['user_id'] = $user->id;
        }
        
        $with = $fields->getRelations();
        $per_page = isset($args['limit']) ? $args['limit'] : null;
        $page = isset($args['page']) ? $args['page'] : 1;

        return $couponRepository->filter($args)->with($with)
						        ->paginate($per_page, ['*'], 'page', $page);
    }
}
