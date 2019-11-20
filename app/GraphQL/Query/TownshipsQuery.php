<?php

namespace App\GraphQL\Query;

use App\Services\Mailamericas\Tracking\PostalStructure\TownshipsService;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ResolveInfo;
use Rebing\GraphQL\Support\SelectFields;
use Rebing\GraphQL\Support\Query;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Illuminate\Support\Facades\Cache;

class TownshipsQuery extends Query
{
    protected $attributes = [
        'name' => 'TownshipsQuery',
        'description' => 'A query'
    ];

    public function type()
    {
        return Type::listOf(GraphQL::type('township'));
    }

    public function args()
    {
        return [
            'admin_level_1_name' => [
                'name' => 'admin_level_1_name',
                'type' => Type::string()
            ],
            'admin_level_2_name' => [
                'name' => 'admin_level_2_name',
                'type' => Type::string()
            ],
            'country_code' => [
                'name' => 'country_code',
                'type' => Type::string()
            ],
            'territorial_code' => [
                'name' => 'territorial_code',
                'type' => Type::int()
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
        $admin_level_1_name = isset($args['admin_level_1_name']) ? $args['admin_level_1_name'] : '';
        $admin_level_2_name = isset($args['admin_level_2_name']) ? $args['admin_level_2_name'] : '';
        $territorial_code = isset($args['territorial_code']) ? $args['territorial_code'] : '';
        $cache_key = isset($args['admin_level_1_name']) ? "{$admin_level_1_name}-{$admin_level_2_name}" : "{$territorial_code}";


        return Cache::remember("{$country_code}-{$cache_key}", 604800,
            function () use ($country_code, $admin_level_1_name, $admin_level_2_name, $territorial_code) {
                /** @var TownshipsService $townshipsService */
                $townshipsService = app(TownshipsService::class);

                return $townshipsService->search(compact('country_code', 'admin_level_1_name', 'admin_level_2_name',
                    'territorial_code'))->get();
            });
    }
}