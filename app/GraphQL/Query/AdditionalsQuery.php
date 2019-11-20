<?php

namespace App\GraphQL\Query;

use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ResolveInfo;
use Rebing\GraphQL\Support\SelectFields;
use Rebing\GraphQL\Support\Query;
use Rebing\GraphQL\Support\Facades\GraphQL;

use App\Repositories\AdditionalRepository;

class AdditionalsQuery extends Query
{
    protected $attributes = [
        'name' => 'AdditionalsQuery',
        'description' => 'A query'
    ];

    public function type()
    {
        return Type::listOf(GraphQL::type('additional'));
    }

    public function args()
    {
        return [
            'required' => [
                'name' => 'required',
                'type' => Type::boolean()
            ],
            'active' => [
                'name' => 'active',
                'type' => Type::boolean()
            ],
            'country_code' => [
                'name' => 'country_code',
                'type' => Type::string()
            ]
        ];
    }

    public function resolve($root, $args, SelectFields $fields, ResolveInfo $info)
    {
        /** @var AdditionalRepository $countryRepository */
        $additionalRepository = app(AdditionalRepository::class);

        $select = $fields->getSelect();
        $with = $fields->getRelations();

        $args['platform_key'] = current_platform()->key;
        $args['enabled'] = true;

        return $additionalRepository->filter($args)->with($with)->select($select)->get();
    }
}