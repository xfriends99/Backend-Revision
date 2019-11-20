<?php

namespace App\GraphQL\Query;

use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ResolveInfo;
use Rebing\GraphQL\Support\SelectFields;
use Rebing\GraphQL\Support\Query;
use Rebing\GraphQL\Support\Facades\GraphQL;

use App\Repositories\StateRepository;

class StatesQuery extends Query
{
    protected $attributes = [
        'name' => 'StatesQuery',
        'description' => 'A query'
    ];

    public function type()
    {
        return Type::listOf(GraphQL::type('state'));
    }

    public function args()
    {
        return [
            'country_id' => [
                'name' => 'country_id',
                'type' => Type::int()
            ],
            'country_code' => [
                'name' => 'country_code',
                'type' => Type::string()
            ]
        ];
    }

    public function resolve($root, $args, SelectFields $fields, ResolveInfo $info)
    {
        /** @var StateRepository $stateRepository */
        $stateRepository = app(StateRepository::class);

        $select = $fields->getSelect();
        $with = $fields->getRelations();

        return $stateRepository->filter($args)->with($with)->select($select)->get();
    }
}