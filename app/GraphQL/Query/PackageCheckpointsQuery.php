<?php

namespace App\GraphQL\Query;

use App\Services\Mailamericas\Tracking\Checkpoints\EventsService;
use Carbon\Carbon;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ResolveInfo;
use Rebing\GraphQL\Support\SelectFields;
use Rebing\GraphQL\Support\Query;
use Rebing\GraphQL\Support\Facades\GraphQL;

class PackageCheckpointsQuery extends Query
{
    protected $attributes = [
        'name' => 'PackageCheckpointsQuery',
        'description' => 'A query'
    ];

    public function type()
    {
        return Type::listOf(GraphQL::type('package_checkpoint'));
    }

    public function args()
    {
        return [
            'tracking' => [
                'name' => 'tracking',
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
        $tracking = $args['tracking'];

        /** @var EventsService $eventsService */
        $eventsService = app(EventsService::class);
        $events = $eventsService->search(compact('tracking'))->getEvents();

        return collect($events)->sortByDesc(function ($e) {
            return Carbon::parse($e->date);
        })->toArray();
    }
}
