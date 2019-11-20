<?php

namespace App\GraphQL\Query;

use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ResolveInfo;
use Rebing\GraphQL\Support\SelectFields;
use Rebing\GraphQL\Support\Query;
use Rebing\GraphQL\Support\Facades\GraphQL;

use App\Repositories\ServiceTypeRepository;

class ServiceTypesQuery extends Query
{
    protected $attributes = [
        'name' => 'ServiceTypesQuery',
        'description' => 'A query'
    ];

    public function type()
    {
        return Type::listOf(GraphQL::type('service_type'));
    }

    public function args()
    {
        return [
            'country_code' => [
                'name' => 'country_code',
                'type' => Type::string()
            ],
            'country_id' => [
                'name' => 'country_id',
                'type' => Type::int()
            ]
        ];
    }

    public function resolve($root, $args, SelectFields $fields, ResolveInfo $info)
    {
        /** @var ServiceTypeRepository $serviceTypeRepository */
        $serviceTypeRepository = app(ServiceTypeRepository::class);

        $select = $fields->getSelect();
        $with = $fields->getRelations();

        return $serviceTypeRepository->filter($args)->with($with)->select($select)->get();
    }
}