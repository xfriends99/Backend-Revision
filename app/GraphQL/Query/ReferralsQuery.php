<?php

namespace App\GraphQL\Query;

use App\Models\User;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ResolveInfo;
use Rebing\GraphQL\Support\SelectFields;
use Rebing\GraphQL\Support\Query;
use Rebing\GraphQL\Support\Facades\GraphQL;

use App\Repositories\UserRepository;

class ReferralsQuery extends Query
{
    protected $attributes = [
        'name' => 'ReferralsQuery',
        'description' => 'A query'
    ];

    public function type()
    {
        return Type::listOf(GraphQL::type('user'));
    }

    public function args()
    {
        return [
            'referrer_id' => [
                'name' => 'referrer_id',
                'type' => Type::string()
            ]
        ];
    }

    public function resolve($root, $args, SelectFields $fields, ResolveInfo $info)
    {
        /** @var User $user */
        $user = request()->user();

        /** @var UserRepository $userRepository */
        $userRepository = app(UserRepository::class);

        $with = $fields->getRelations();

        if(isset($args['referrer_id']) && $args['referrer_id'] == 'me')
        {
            $args['referrer_id'] = $user->id;
        }

        return $userRepository->filter($args)->with($with)->get();
    }
}