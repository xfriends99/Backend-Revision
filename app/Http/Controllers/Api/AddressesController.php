<?php

namespace App\Http\Controllers\Api;

use App\Events\AddressWasUpdated;
use App\Http\Controllers\Api\Transformers\AddressTransformer;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAddressRequest;
use App\Models\Address;
use App\Models\Country;
use App\Models\State;
use App\Models\User;
use App\Repositories\AddressRepository;
use App\Repositories\StateRepository;
use App\Traits\JsonApiResponse;
use Exception;
use Illuminate\Http\Request;

class AddressesController extends Controller
{
    use JsonApiResponse;

    /** @var  AddressRepository */
    protected $addressRepository;

    /** @var  StateRepository */
    protected $stateRepository;

    /** @var  StateRepository */
    protected $purchaseRepository;

    public function __construct(AddressRepository $addressRepository, StateRepository $stateRepository)
    {
        $this->addressRepository = $addressRepository;
        $this->stateRepository = $stateRepository;
    }

    /**
     * @param StoreAddressRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function store(StoreAddressRequest $request)
    {
        /** @var User $user */
        $user = $request->user();

        /** @var Country $country */
        $country = $user->country;

        /** @var State $state */
        $state = $this->stateRepository->getById($request->get('state'));

        $data = array_merge(
            $request->all(),
            [
                'user_id'    => $user->id,
                'country_id' => $country->id,
                'state'      => $state->name
            ]
        );

        try {
            $address = $this->addressRepository->create($data);

            $fractal = fractal($address, new AddressTransformer());
            $fractal->addMeta(['error' => false]);

            return response()->json($fractal->toArray(), 201, [], JSON_PRETTY_PRINT);
        } catch (Exception $exception) {
            logger($exception->getMessage());
            logger($exception->getTraceAsString());

            return self::errorResponse('Error creada la dirección', 500);
        }
    }

    /**
     * @param StoreAddressRequest $request
     * @param $address_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(StoreAddressRequest $request, $address_id)
    {
        /** @var User $user */
        $user = $request->user();

        /** @var Address $address */
        if (!$address = $this->addressRepository->getById($address_id)) {
            return self::errorResponse('Dirección no encontrada', 404);
        }

        /** @var State $state */
        $state = $this->stateRepository->getById($request->get('state'));

        $data = array_merge(
            $request->only('number', 'reference', 'floor', 'apartment', 'address1', 'address2', 'city', 'state', 'township', 'postal_code'),
            [
                'state' => $state->name
            ]
        );

        try {
            $this->addressRepository->update($address, $data);

            $address->syncChanges();

            event(new AddressWasUpdated($address));

            $fractal = fractal($address, new AddressTransformer());
            $fractal->addMeta(['error' => false]);

            return response()->json($fractal->toArray(), 201, [], JSON_PRETTY_PRINT);
        } catch (Exception $exception) {
            logger($exception->getMessage());
            logger($exception->getTraceAsString());

            return self::errorResponse('Error actualizando la dirección', 500);
        }
    }

    /**
     * @param Request $request
     * @param $address_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $address_id)
    {
        /** @var Address $address */
        if (!$address = $this->addressRepository->getById($address_id)) {
            return self::errorResponse('Dirección no encontrada', 404);
        }

        try {
            if (!$address->hasPurchases()) {
                $this->addressRepository->delete($address);

                return self::success(['message' => 'Dirección eliminada correctamente']);
            }

            return self::errorResponse('La dirección esta siendo utilizada', 500);
        } catch (Exception $exception) {
            logger($exception->getMessage());
            logger($exception->getTraceAsString());

            return self::errorResponse('Error eliminando la dirección', 500);
        }
    }
}
