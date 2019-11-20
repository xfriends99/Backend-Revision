<?php

namespace App\GraphQL\Mutation;

use App\Models\User;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Mutation;
use Rebing\GraphQL\Support\SelectFields;
use App\Repositories\UserRepository;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Hash;

class UpdateUserPasswordMutation extends Mutation
{
    protected $attributes = [
        'name' => 'UpdateUserPasswordMutation',
        'description' => 'A mutation'
    ];

    public function type()
    {
        return GraphQL::type('user');
    }

    public function args()
    {
        return [
            'id' => [
                'name' => 'id',
                'type' => Type::nonNull(Type::string()),
                'rules' => ['required'],
            ],
            'password' => [
                'name' => 'password',
                'type' => Type::nonNull(Type::string())
            ],
            'password_confirm' => [
                'name' => 'password_confirm',
                'type' => Type::nonNull(Type::string())
            ],
        ];
    }

    public function rules(array $args = [])
    {
        return [
            'id' => ['required'],
            'password' => ['required'],
            'password_confirm' => ['required']
        ];
    }

    public function resolve($root, $args, SelectFields $fields, ResolveInfo $info)
    {
        /** @var User $user */
        $user = request()->user();

        /** @var UserRepository $userRepository */
        $userRepository = app(UserRepository::class);

        if(isset($args['id']) && $args['id'] == 'me')
        {
            $args['id'] = $user->id;
        }

        $user = $userRepository->getById($args['id']);

        $args['password'] = Hash::make($args['password']);

        $userRepository->update($user, $args);

        return $user;
    }
}