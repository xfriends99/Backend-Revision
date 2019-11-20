<?php

namespace App\GraphQL\Tracking\Query;

use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ResolveInfo;
use Rebing\GraphQL\Support\SelectFields;
use Rebing\GraphQL\Support\Query;
use Rebing\GraphQL\Support\Facades\GraphQL;

use App\Repositories\CheckpointCodeRepository;

class CheckpointCodesQuery extends Query
{


    protected $attributes = [
        'name' => 'CheckpointCodesQuery',
        'description' => 'A query'
    ];

    public function type()
    {
        return Type::listOf(GraphQL::type('checkpoint_code'));
    }

    public function args()
    {
        return [

        ];
    }

    public function resolve($root, $args, SelectFields $fields, ResolveInfo $info)
    {

        /** @var CheckpointCodeRepository $checkpointCodeRepository */
        $checkpointCodeRepository = app(CheckpointCodeRepository::class);

        /** @var $select */
        $select = $fields->getSelect();

        /** @var $with */
        $with = $fields->getRelations();

        return $checkpointCodeRepository->filter($args)->with($with)->select($select)->get();

    }

}