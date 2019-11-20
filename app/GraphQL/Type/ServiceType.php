<?php

namespace App\GraphQL\Type;

use App\Models\Service;
use Rebing\GraphQL\Support\Type as GraphQLType;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;

class ServiceType extends GraphQLType
{
    protected $attributes = [
        'name' => 'ServiceType',
        'description' => 'A type',
        'model' => Service::class
    ];

    public function fields()
    {
        return [
            'id' => [
                'type' => Type::nonNull(Type::int()),
                'description' => 'Id of country'
            ],
            'name' => [
                'type'  => Type::string(),
                'description' => 'Name'
            ],
            'code' => [
                'type'  => Type::string(),
                'description' => 'Code'
            ],
            'description' => [
                'type'  => Type::string(),
                'description' => 'Description'
            ],
            'enabled' => [
                'type'  => Type::boolean(),
                'description' => 'Enabled'
            ],
            'created_at' => [
                'type' => Type::string(),
                'description' => 'Date of created'
            ],
            'serviceType' => [
                'type'  => GraphQL::type('service_type'),
                'description' => 'Service type'
            ],
            'originCountry' => [
                'type'  => GraphQL::type('country'),
                'description' => 'Origin country'
            ],
            'destinationCountry' => [
                'type'  => GraphQL::type('country'),
                'description' => 'Destination country'
            ]
        ];
    }

    protected function resolveCreatedAtField($root, $args)
    {
        return $root->created_at->format('d/m/Y');
    }
}