<?php

namespace App\GraphQL\Tracking\Query;

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
            'tracking' => [
                'name' => 'tracking',
                'type' => Type::listOf(Type::string())
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
        /** @var PackageRepository $packageRepository */
        $packageRepository = app(PackageRepository::class);

        $with = $fields->getRelations();

        return $packageRepository->filter($args)->with($with)->paginate($args['limit'], ['*'], 'page', $args['page']);
    }
}