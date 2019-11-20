<?php

namespace App\GraphQL\Query;

use App\Repositories\CardBrandRepository;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ResolveInfo;
use Rebing\GraphQL\Support\SelectFields;
use Rebing\GraphQL\Support\Query;

class CardBrandsQuery extends Query
{
    protected $attributes = [
        'name' => 'CardBrandsQuery',
        'description' => 'A query'
    ];

    public function type()
    {
        return Type::listOf(Type::string());
    }

    public function args()
    {
        return [

        ];
    }

    public function resolve($root, $args, SelectFields $fields, ResolveInfo $info)
    {
        /** @var CardBrandRepository $cardBrandRepository */
        $cardBrandRepository = app(CardBrandRepository::class);
        
        $select = $fields->getSelect();
        $with = $fields->getRelations();

        return $cardBrandRepository->filter($args)->with($with)->select($select)->get();
    }
}