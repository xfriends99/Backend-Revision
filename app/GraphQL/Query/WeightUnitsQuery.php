<?php

namespace App\GraphQL\Query;

use App\Repositories\WeightUnitRepository;
use GraphQL\Type\Definition\ResolveInfo;
use Rebing\GraphQL\Support\SelectFields;
use Rebing\GraphQL\Support\Query;
use Rebing\GraphQL\Support\Facades\GraphQL;
use GraphQL\Type\Definition\Type;

class WeightUnitsQuery extends Query
{
    protected $attributes = [
        'name' => 'WeightUnitsQuery',
        'description' => 'A query'
    ];

    public function type()
    {
        return Type::listOf(GraphQL::type('weight_unit'));
    }

    public function args()
    {
        return [
        ];
    }

    public function resolve($root, $args, SelectFields $fields, ResolveInfo $info)
    {
        /** @var WeightUnitRepository $weightUnitRepository */
        $weightUnitRepository = app(WeightUnitRepository::class);

        return $weightUnitRepository->filter()->get();
    }
}
