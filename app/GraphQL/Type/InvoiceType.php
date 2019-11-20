<?php

namespace App\GraphQL\Type;

use Carbon\Carbon;
use Rebing\GraphQL\Support\Type as GraphQLType;
use GraphQL\Type\Definition\Type;
use App\Models\Invoice;
use Rebing\GraphQL\Support\Facades\GraphQL;

class InvoiceType extends GraphQLType
{
    protected $attributes = [
        'name' => 'InvoiceType',
        'description' => 'A type',
        'model' => Invoice::class
    ];

    public function fields()
    {
        return [
            'id' => [
                'type' => Type::nonNull(Type::int()),
                'description' => 'Id of invoice'
            ],
            'transactions' => [
                'type'  => Type::listOf(GraphQL::type('transaction')),
                'description' => 'Transactions of Invoice'
            ],
            'state' => [
                'type'  => Type::string(),
                'description' => 'State of invoice'
            ],
            'total_amount' => [
                'type'  => Type::float(),
                'description' => 'Total_amount of invoice'
            ],
            'shipping_cost' => [
                'type'  => Type::float(),
                'description' => 'shipping cost of invoice'
            ],
            'additional' => [
                'type'  => Type::float(),
                'description' => 'Additional of invoice'
            ],
            'subtotal' => [
                'type'  => Type::float(),
                'description' => 'Sub total of invoice'
            ],
            'iva' => [
                'type'  => Type::float(),
                'description' => 'IVA of invoice'
            ],
            'external_invoice_link' => [
                'type'  => Type::string(),
                'description' => 'External link of invoice'
            ],
            'charged_at' => [
                'type' => Type::string(),
                'description' => 'Date of created'
            ],
            'created_at' => [
                'type' => Type::string(),
                'description' => 'Date of created'
            ]
        ];
    }

    protected function resolveChargedAtField($root, $args)
    {
        return Carbon::parse($root->charged_at)->format('d/m/Y');
    }

    protected function resolveCreatedAtField($root, $args)
    {
        return $root->created_at->format('d/m/Y');
    }
}