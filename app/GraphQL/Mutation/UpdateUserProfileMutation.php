<?php

namespace App\GraphQL\Mutation;

use App\Models\User;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Mutation;
use Rebing\GraphQL\Support\SelectFields;
use App\Repositories\UserRepository;
use Rebing\GraphQL\Support\Facades\GraphQL;

class UpdateUserProfileMutation extends Mutation
{
    protected $attributes = [
        'name' => 'UpdateUserProfileMutation',
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
            'language' => [
                'name' => 'language',
                'type' => Type::nonNull(Type::string())
            ],
            'timezone_id' => [
                'name' => 'timezone_id',
                'type' => Type::nonNull(Type::int())
            ],
        ];
    }

    public function rules(array $args = [])
    {
        return [
            'id' => ['required'],
            'language' => ['required'],
            'timezone_id' => ['required', 'exists:timezones,id']
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

        $userRepository->update($user, $args);

        return $user;
    }
}