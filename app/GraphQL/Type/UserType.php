<?php

namespace App\GraphQL\Type;

use Carbon\Carbon;
use Rebing\GraphQL\Support\Type as GraphQLType;
use GraphQL\Type\Definition\Type;
use App\Models\User;
use Rebing\GraphQL\Support\Facades\GraphQL;

class UserType extends GraphQLType
{
    protected $attributes = [
        'name' => 'UserType',
        'description' => 'A type',
        'model' => User::class
    ];

    public function fields()
    {
        return [
            'id' => [
                'type' => Type::nonNull(Type::int()),
                'description' => 'Id of user'
            ],
            'first_name' => [
                'type'  => Type::string(),
                'description' => 'First name of user'
            ],
            'last_name' => [
                'type'  => Type::string(),
                'description' => 'Last name of user'
            ],
            'email' => [
                'type'  => Type::string(),
                'description' => 'Email of user'
            ],
            'email_verified_at' => [
                'type' => Type::string(),
                'description' => 'Email Verified of user'
            ],
            'identification' => [
                'type'  => Type::string(),
                'description' => 'Identification user'
            ],
            'identification_type' => [
                'type'  => Type::string(),
                'description' => 'Identification type of user'
            ],
            'phone' => [
                'type'  => Type::string(),
                'description' => 'Phone of user'
            ],
            'language' => [
                'type'  => Type::string(),
                'description' => 'Language of user'
            ],
            'has_cards' => [
                'type'  => Type::nonNull(Type::boolean()),
                'description' => 'Has a card user'
            ],
            'born_at' => [
                'type' => Type::string(),
                'description' => 'Date of born user'
            ],
            'country' => [
                'type'  => GraphQL::type('country'),
                'description' => 'Country of user'
            ],
            'timezone' => [
                'type'  => GraphQL::type('timezone'),
                'description' => 'Timezone of user'
            ],
            'locker' => [
                'type'  => GraphQL::type('locker'),
                'description' => 'Locker of user'
            ],
            'created_at' => [
                'type'  =>  Type::string(),
                'description' => 'Created at of user'
            ],
            'packages' => [
                'type'  =>  Type::listOf(GraphQL::type('package')),
                'description' => 'package of user'
            ],
            'packages_count' => [
                'type' => Type::int(),
                'description' => 'The total packages for user',
            ],
            'purchases' => [
                'type'  =>  Type::listOf(GraphQL::type('purchase')),
                'description' => 'purchase of user'
            ],
            'purchases_count' => [
                'type' => Type::int(),
                'description' => 'The total purchases for user',
            ],
            'platform_id' => [
                'type' => Type::int(),
                'description' => 'platform of user'
            ],
            'platform' => [
                'type' => GraphQL::type('platform'),
                'description' => 'platform name of user'
            ],
            'full_name' => [
                'type' => Type::string(),
                'description' => 'fullname of user'
            ],
            'link' => [
                'type' => Type::string(),
                'description' => 'link to register by referred'
            ],
            'status' => [
                'type' => Type::string(),
                'description' => 'status of user'
            ],
            'coupon_status' => [
                'type' => Type::string(),
                'description' => 'coupon status of user'
            ]                
        ];
    }

    protected function resolveBornAtField($root, $args)
    {
        return Carbon::parse($root->born_at)->format('d/m/Y');
    }

    protected function resolveCreatedAtField($root, $args)
    {
        return Carbon::parse($root->created_at)->format('d/m/Y');
    }

    protected function resolveEmailVerifiedAtField($root, $args)
    {
        return Carbon::parse($root->email_verified_at)->format('d/m/Y');
    }

    protected function resolveIdentificationTypeField($root, $args)
    {
        return $root->getIdentificationTypeKey();
    }

    protected function resolvePurchasesCountField($root, $args)
    {
        return $root->getPurchasesCount();
    }

    protected function resolvePackagesCountField($root, $args)
    {
        return $root->getPackagesCount();
    }

    protected function resolveFullNameField($root, $args)
    {
        return $root->getFullNameAttribute();
    }

    protected function resolveStatusField($root, $args)
    {
        return $root->getStatus();
    }

    protected function resolveCouponStatusField($root, $args)
    {
        return $root->getCouponStatus();
    }

    protected function resolveLinkField($root, $args)
    {
        return $root->getReferralLinkAttribute();
    }

}