<?php

namespace App\GraphQL\Query;

use App\Services\Mailamericas\Tracking\PostalStructure\ZipCodesService;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ResolveInfo;
use Rebing\GraphQL\Support\SelectFields;
use Rebing\GraphQL\Support\Query;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Illuminate\Support\Facades\Cache;

class ZipCodesQuery extends Query
{
    protected $attributes = [
        'name' => 'ZipCodesQuery',
        'description' => 'A query'
    ];

    public function type()
    {
        return Type::listOf(GraphQL::type('zip_code'));
    }

    public function args()
    {
        return [
            'country_code' => [
                'name' => 'country_code',
                'type' => Type::string()
            ],
            'admin_level_1_name' => [
                'name' => 'admin_level_1_name',
                'type' => Type::string()
            ],
            'admin_level_2_name' => [
                'name' => 'admin_level_2_name',
                'type' => Type::string()
            ],
            'admin_level_3_name' => [
                'name' => 'admin_level_3_name',
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
        $admin_level_2_name = $args['admin_level_2_name'];
        $admin_level_3_name = $args['admin_level_3_name'];

        return Cache::remember("{$country_code}-{$admin_level_1_name}-{$admin_level_2_name}-{$admin_level_3_name}", 604800,
            function () use ($country_code, $admin_level_1_name, $admin_level_2_name, $admin_level_3_name) {
                /** @var ZipCodesService $zipCodesService */
                $zipCodesService = app(ZipCodesService::class);

                return $zipCodesService->search(compact('country_code', 'admin_level_1_name', 'admin_level_2_name', 'admin_level_3_name'))->get();
            });
    }
}