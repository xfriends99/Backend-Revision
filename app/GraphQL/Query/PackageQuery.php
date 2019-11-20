<?php

namespace App\GraphQL\Query;

use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ResolveInfo;
use Rebing\GraphQL\Support\SelectFields;
use Rebing\GraphQL\Support\Query;
use Rebing\GraphQL\Support\Facades\GraphQL;

use App\Repositories\PackageRepository;

class PackageQuery extends Query
{
    protected $attributes = [
        'name' => 'PackageQuery',
        'description' => 'A query'
    ];

    public function type()
    {
        return GraphQL::type('package');
    }

    public function args()
    {
        return [
            'id' => [
                'name' => 'id',
                'type' => Type::int()
            ]
        ];
    }

    public function resolve($root, $args, SelectFields $fields, ResolveInfo $info)
    {
        /** @var PackageRepository $packageRepository */
        $packageRepository = app(PackageRepository::class);

        /** @var $with */
        $with = $fields->getRelations();

        /** @var int $id */
        $id = $args['id'];

        return $packageRepository->getById($id)->load($with);
    }
}