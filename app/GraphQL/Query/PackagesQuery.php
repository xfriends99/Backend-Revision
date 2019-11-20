<?php

namespace App\GraphQL\Query;

use App\Models\User;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ResolveInfo;
use Rebing\GraphQL\Support\SelectFields;
use Rebing\GraphQL\Support\Query;
use Rebing\GraphQL\Support\Facades\GraphQL;

use App\Repositories\PackageRepository;

class PackagesQuery extends Query
{
    protected $attributes = [
        'name' => 'PackagesQuery',
        'description' => 'A query'
    ];

    public function type()
    {
        return GraphQL::paginate('package');
    }

    public function args()
    {
        return [
            'user_id' => [
                'name' => 'user_id',
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

        /** @var PackageRepository $packageRepository */
        $packageRepository = app(PackageRepository::class);

        $with = $fields->getRelations();

        if(isset($args['user_id']) && $args['user_id'] == 'me')
        {
            $args['user_id'] = $user->id;
        }

        return $packageRepository->filter($args)->orderBy('created_at', 'desc')
            ->with($with)->paginate($args['limit'], ['*'], 'page', $args['page']);
    }
}