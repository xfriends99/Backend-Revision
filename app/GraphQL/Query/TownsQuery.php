<?php

namespace App\GraphQL\Query;

use App\Services\Mailamericas\Tracking\PostalStructure\TownsService;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ResolveInfo;
use Rebing\GraphQL\Support\SelectFields;
use Rebing\GraphQL\Support\Query;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Illuminate\Support\Facades\Cache;

class TownsQuery extends Query
{
    protected $attributes = [
        'name' => 'TownsQuery',
        'description' => 'A query'
    ];

    public function type()
    {
        return Type::listOf(GraphQL::type('town'));
    }

    public function args()
    {
        return [
            'admin_level_1_name' => [
                'name' => 'admin_level_1_name',
                'type' => Type::string()
            ],
            'country_code' => [
                'name' => 'country_code',
                'type' => Type::string()
            ]
        ];
    }

    /**
     * @param $root
     * @param $args
     * @param SelectFields $fields
     * @param ResolveInfo $info
     * @return array
     * @throws \Exception
     */
    public function resolve($root, $args, SelectFields $fields, ResolveInfo $info)
    {
        $country_code = $args['country_code'];
        $admin_level_1_name = $args['admin_level_1_name'];


        return Cache::remember("{$country_code}-{$admin_level_1_name}", 604800,
            function () use ($country_code, $admin_level_1_name) {
                /** @var TownsService $townsService */
                $townsService = app(TownsService::class);

                return $townsService->search(compact('country_code', 'admin_level_1_name'))->get();
            });
    }
}