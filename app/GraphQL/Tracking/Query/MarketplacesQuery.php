<?php

namespace App\GraphQL\Tracking\Query;

use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ResolveInfo;
use Rebing\GraphQL\Support\SelectFields;
use Rebing\GraphQL\Support\Query;
use Rebing\GraphQL\Support\Facades\GraphQL;

use App\Repositories\MarketplaceRepository;

class MarketplacesQuery extends Query
{
    protected $attributes = [
        'name' => 'MarketplacesQuery',
        'description' => 'A query'
    ];

    public function type()
    {
        return Type::listOf(GraphQL::type('marketplace'));
    }

    public function args()
    {
        return [

        ];
    }

    public function resolve($root, $args, SelectFields $fields, ResolveInfo $info)
    {
        /** @var MarketplaceRepository $marketplaceRepository */
        $marketplaceRepository = app(MarketplaceRepository::class);

        $select = $fields->getSelect();
        $with = $fields->getRelations();

        $args['informed_by_user'] = false;

        return $marketplaceRepository->filter($args)->with($with)->select($select)->get();
    }
}