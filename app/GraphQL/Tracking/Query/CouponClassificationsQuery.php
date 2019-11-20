<?php

namespace App\GraphQL\Tracking\Query;


use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ResolveInfo;
use Rebing\GraphQL\Support\SelectFields;
use Rebing\GraphQL\Support\Query;
use Rebing\GraphQL\Support\Facades\GraphQL;
use App\Repositories\CouponClassificationRepository;

class CouponClassificationsQuery extends Query
{

    protected $attributes = [
        'name' => 'CouponClassificationsQuery',
        'description' => 'A query'
    ];

    public function type()
    {
        return Type::listOf(GraphQL::type('coupon_classification'));
    }

    public function args()
    {
        return [

        ];
    }

    public function resolve($root, $args, SelectFields $fields, ResolveInfo $info)
    {
        /** @var CouponClassificationRepository $couponClassificationRepository */
        $couponClassificationRepository = app(CouponClassificationRepository::class);

        $select = $fields->getSelect();
        $with = $fields->getRelations();

        return $couponClassificationRepository->filter($args)->with($with)->select($select)->get();
    }

}