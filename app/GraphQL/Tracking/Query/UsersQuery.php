<?php

namespace App\GraphQL\Tracking\Query;

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
        return GraphQL::paginate('user');
    }

    public function args()
    {
        return [
            'country_code' => [
                'name' => 'country_code',
                'type' => Type::listOf(Type::string())
            ],
            'platform_id' => [
                'name' => 'platform_id',
                'type' => Type::listOf(Type::string())
            ],
            'full_name' => [
                'name' => 'full_name',
                'type' => Type::string()
            ],
            'email' => [
                'name' => 'email',
                'type' => Type::string()
            ],
            'identification' => [
                'name' => 'identification',
                'type' => Type::string()
            ],
            'verified' => [
                'name' => 'verified',
                'type' => Type::boolean()
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
        /** @var UserRepository $userRepository */
        $userRepository = app(UserRepository::class);

        //$select = $fields->getSelect();
        $with = $fields->getRelations();
        $per_page = isset($args['limit']) ? $args['limit'] : null;
        $page = isset($args['page']) ? $args['page'] : 1;

        return $userRepository->filter($args)->with($with)->paginate($per_page, ['*'], 'page', $page);
    }
}