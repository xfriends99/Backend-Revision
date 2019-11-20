<?php

namespace App\GraphQL\Tracking\Query;

use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ResolveInfo;
use Rebing\GraphQL\Support\SelectFields;
use Rebing\GraphQL\Support\Query;
use Rebing\GraphQL\Support\Facades\GraphQL;

use App\Repositories\InvoiceRepository;

class InvoiceQuery extends Query
{
    protected $attributes = [
        'name' => 'InvoiceQuery',
        'description' => 'A query'
    ];

    public function type()
    {
        return GraphQL::type('invoice');
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

    public function resolve($root, $args, SelectFields $fields, ResolveInfo $info)
    {
        /** @var InvoiceRepository $invoiceRepository */
        $invoiceRepository = app(InvoiceRepository::class);

        $with = $fields->getRelations();

        return $invoiceRepository->filter($args)->with($with)->first();
    }
}