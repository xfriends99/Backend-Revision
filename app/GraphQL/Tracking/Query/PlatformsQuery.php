<?php

namespace App\GraphQL\Tracking\Query;


use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ResolveInfo;
use Rebing\GraphQL\Support\SelectFields;
use Rebing\GraphQL\Support\Query;
use Rebing\GraphQL\Support\Facades\GraphQL;

use App\Repositories\PlatformRepository;

class PlatformsQuery extends Query
{

    protected $attributes = [
        'name' => 'PlatformsQuery',
        'description' => 'A query'
    ];

    public function type()
    {
        return Type::listOf(GraphQL::type('platform'));
    }

    public function args()
    {
        return [

        ];
    }

    public function resolve($root, $args, SelectFields $fields, ResolveInfo $info)
    {

        /** @var PlatformRepository $platformRepository */
        $platformRepository = app(PlatformRepository::class);

        /** @var $select */
        $select = $fields->getSelect();

        /** @var $with */
        $with = $fields->getRelations();

        return $platformRepository->filter($args)->with($with)->select($select)->get();

    }

}