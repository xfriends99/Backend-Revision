<?php

namespace App\Http\Controllers\Api\Transformers;

use App\Models\Address;
use League\Fractal\TransformerAbstract;

class AddressTransformer extends TransformerAbstract
{
    /**
     * @param Address $address
     * @return array
     */
    public function transform(Address $address)
    {
        return [
            'id' => $address->id,
            'address1' => $address->address1,
            'address2' => $address->address2,
            'city' => $address->city,
            'state' => $address->state,
            'township' => $address->township,
            'apartment' => $address->apartment,
            'floor' => $address->floor,
            'number' => $address->number,
            'reference' => $address->reference,
            'postal_code' => $address->postal_code,
            'country' => [
                'code' => $address->getCountryCode(),
                'name' => $address->getCountryName()
            ],
            'created_at' => $address->created_at->diffForHumans()
        ];
    }
}