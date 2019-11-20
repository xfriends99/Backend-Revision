<?php

namespace App\GraphQL\Query;

use App\Repositories\TimezoneRepository;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ResolveInfo;
use Rebing\GraphQL\Support\SelectFields;
use Rebing\GraphQL\Support\Query;
use Rebing\GraphQL\Support\Facades\GraphQL;

use App\Repositories\CountryRepository;

class TimezonesQuery extends Query
{
    protected $attributes = [
        'name' => 'TimezonesQuery',
        'description' => 'A query'
    ];

    public function type()
    {
        return Type::listOf(GraphQL::type('timezone'));
    }

    public function args()
    {
        return [

        ];
    }

    public function resolve($root, $args, SelectFields $fields, ResolveInfo $info)
    {
        /** @var TimezoneRepository $timezoneRepository */
        $timezoneRepository = app(TimezoneRepository::class);

        $select = $fields->getSelect();
        $with = $fields->getRelations();

        return $timezoneRepository->filter($args)->with($with)->select($select)->get();
    }
}