<?php

namespace App\GraphQL\Type;

use App\Models\Coupon;
use Rebing\GraphQL\Support\Type as GraphQLType;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;

class CouponType extends GraphQLType
{
    protected $attributes = [
        'name' => 'CouponType',
        'description' => 'A type',
        'model' => Coupon::class
    ];

    public function fields()
    {    	
        return [
            'id' => [
                'type' => Type::nonNull(Type::int()),
                'description' => 'Id of coupon'
            ],
            'code' => [
                'type'  => Type::string(),
                'description' => 'Coupon code'
            ],
            'description' => [
                'type'  => Type::string(),
                'description' => 'Coupon description'
            ],
            'max_uses' => [
                'type'  => Type::int(),
                'description' => 'Coupon max uses'
            ],
            'user_uses' => [
                'type'  => Type::string(),
                'description' => 'Coupon user uses'
            ],
            'amount' => [
                'type'  => Type::float(),
                'description' => 'Coupon amount'
            ],
            'percent' => [
                'type'  => Type::float(),
                'description' => 'Coupon percent'
            ],
            'max_amount' => [
                'type'  => Type::float(),
                'description' => 'Coupon max amount'
            ],
            'active' => [
                'type'  => Type::boolean(),
                'description' => 'Coupon active'
            ],
            'created_at' => [
                'type' => Type::string(),
                'description' => 'Date of created'
            ],
            'valid_from' => [
                'type' => Type::string(),
                'description' => 'Date of valid from'
            ],
            'valid_to' => [
                'type' => Type::string(),
                'description' => 'Date of valid to'
            ],
            'couponClassification' => [
                'type'  => GraphQL::type('coupon_classification'),
                'description' => 'Coupon classification'
            ],
            'date_of_use' => [
                'type'  => Type::string(),
                'description' => 'Date of use'
            ],
            'uses' => [
                'type'  => Type::string(),
                'description' => 'max uses'
            ],
            'user' => [
                'type'  => GraphQL::type('user'),
                'description' => 'Coupon owner'
            ],
            'status' => [
                'type'  => Type::string(),
                'description' => 'Coupon available'
            ],
            'promo' => [
                'type'  => Type::string(),
                'description' => 'Coupon promo'
            ]
        ];
    }

    protected function resolveCreatedAtField($root, $args)
    {
        return $root->created_at->format('d/m/Y');
    }

    protected function resolveValidFromField($root, $args)
    {
        return $root->valid_from ? $root->valid_from->format('d/m/Y') : '-';
    }

    protected function resolveValidToField($root, $args)
    {
        return $root->valid_to ? $root->valid_to->format('d/m/Y') : '-';
    }

    protected function resolveDateOfUseField($root, $args)
    {
        return $root->getLastDateOfUse();
    }

    protected function resolveUsesField($root, $args)
    {
        return $root->getTotalUses();
    }

    protected function resolveUserUsesField($root, $args)
    {
        $uses = $root->getTotalUses() . '/' . ($root->max_uses ?? 'X');
        return $uses;
    }

    protected function resolvePromoField($root, $args)
    {
        return $root->getPromoAttribute();
    }

    protected function resolveStatusField($root, $args)
    {
        if ($root->users()->count() < $root->max_uses) {
            return 'Disponible';
        }
        return 'Utilizado';
    }

}