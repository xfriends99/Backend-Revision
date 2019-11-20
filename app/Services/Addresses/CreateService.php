<?php

namespace App\Services\Addresses;

use App\Models\Address;
use App\Models\Country;
use App\Models\State;
use App\Models\User;
use App\Repositories\AddressRepository;

class CreateService
{
    /** @var AddressRepository */
    protected $addressRepository;

    /**
     * CreateService constructor.
     * @param AddressRepository $addressRepository
     */
    public function __construct(
        AddressRepository $addressRepository
    ) {
        $this->addressRepository = $addressRepository;
    }

    /**
     * @param User $user
     * @param Country $country
     * @param State $state
     * @param $address1
     * @param $town
     * @param $township
     * @param $postal_code
     * @param null $floor
     * @param null $apartment
     * @param null $address2
     * @param null $number
     * @param null $reference
     * @return \Illuminate\Database\Eloquent\Model|Address
     */
    public function create(User $user, Country $country, State $state, $address1, $town, $township, $postal_code, $floor = null, $apartment = null, $address2 = null, $number = null, $reference = null)
    {
        return $this->addressRepository->create([
            'user_id'     => $user->id,
            'country_id'  => $country->id,
            'state'       => $state->name,
            'address1'    => $address1,
            'city'        => $town,
            'postal_code' => $postal_code,
            'township'    => $township,
            'floor'       => $floor,
            'apartment'   => $apartment,
            'address2'    => $address2,
            'number'      => $number,
            'reference'   => $reference
        ]);
    }
}