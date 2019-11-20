<?php

namespace App\GraphQL\Query;

use App\Models\User;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ResolveInfo;
use Rebing\GraphQL\Support\SelectFields;
use Rebing\GraphQL\Support\Query;
use Rebing\GraphQL\Support\Facades\GraphQL;

use App\Repositories\AddressRepository;

class AddressesQuery extends Query
{
    protected $attributes = [
        'name' => 'AddressesQuery',
        'description' => 'A query'
    ];

    public function type()
    {
        return Type::listOf(GraphQL::type('address'));
    }

    public function args()
    {
        return [
            'user_id' => [
                'name' => 'user_id',
                'type' => Type::string()
            ]
        ];
    }

    public function resolve($root, $args, SelectFields $fields, ResolveInfo $info)
    {
        /** @var User $user */
        $user = request()->user();

        /** @var AddressRepository $addressRepository */
        $addressRepository = app(AddressRepository::class);

        $select = $fields->getSelect();
        $with = $fields->getRelations();

        if(isset($args['user_id']) && $args['user_id'] == 'me')
        {
            $args['user_id'] = $user->id;
        }

        return $addressRepository->filter($args)->with($with)->select($select)->get();
    }
}