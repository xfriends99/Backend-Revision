<?php

namespace App\GraphQL\Query;

use App\Models\User;
use App\Repositories\PackageRepository;
use GraphQL\Type\Definition\ResolveInfo;
use Rebing\GraphQL\Support\SelectFields;
use Rebing\GraphQL\Support\Query;
use Rebing\GraphQL\Support\Facades\GraphQL;

class PackagesDashboardQuery extends Query
{
    protected $attributes = [
        'name' => 'PackagesDashboardQuery',
        'description' => 'A query'
    ];

    public function type()
    {
        return GraphQL::type('package_dashboard');
    }

    public function args()
    {
        return [

        ];
    }

    public function resolve($root, $args, SelectFields $fields, ResolveInfo $info)
    {
        /** @var User $user */
        $user = request()->user();

        /** @var PackageRepository $packageRepository */
        $packageRepository = app(PackageRepository::class);

        return $packageRepository->getCountPackagesUserByState($user)->first();
    }
}