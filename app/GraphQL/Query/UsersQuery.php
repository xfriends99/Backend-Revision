<?php

namespace App\GraphQL\Query;

use App\Models\User;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ResolveInfo;
use Rebing\GraphQL\Support\SelectFields;
use Rebing\GraphQL\Support\Query;
use Rebing\GraphQL\Support\Facades\GraphQL;

use App\Repositories\UserRepository;

class UsersQuery extends Query
{
    protected $attributes = [
        'name' => 'UsersQuery',
        'description' => 'A query'
    ];

    public function type()
    {
        return GraphQL::type('user');
    }

    public function args()
    {
        return [
            'country_id' => [
                'name' => 'country_id',
                'type' => Type::int()
            ],
            'id' => [
                'name' => 'id',
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

        if(isset($args['id']) && $args['id'] == 'me')
        {
            $args['id'] = $user->id;
        }

        return $userRepository->filter($args)->with($with)->first();
    }
}