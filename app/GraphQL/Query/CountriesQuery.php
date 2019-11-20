<?php

namespace App\GraphQL\Query;

use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ResolveInfo;
use Rebing\GraphQL\Support\SelectFields;
use Rebing\GraphQL\Support\Query;
use Rebing\GraphQL\Support\Facades\GraphQL;

use App\Repositories\CountryRepository;

class CountriesQuery extends Query
{
    protected $attributes = [
        'name' => 'CountriesQuery',
        'description' => 'A query'
    ];

    public function type()
    {
        return Type::listOf(GraphQL::type('country'));
    }

    public function args()
    {
        return [
            'code' => [
                'name' => 'code',
                'type' => Type::string()
            ]
        ];
    }

    public function resolve($root, $args, SelectFields $fields, ResolveInfo $info)
    {
        /** @var CountryRepository $countryRepository */
        $countryRepository = app(CountryRepository::class);

        $select = $fields->getSelect();
        $with = $fields->getRelations();

        return $countryRepository->filter($args)->with($with)->select($select)->get();
    }
}