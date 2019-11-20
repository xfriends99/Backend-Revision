<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Transformers\CostEntityTransformer;
use App\Http\Requests\CostsEstimateRequest;
use App\Models\Country;
use App\Models\Coupon;
use App\Models\Service;
use App\Models\ServiceType;
use App\Repositories\AdditionalRepository;
use App\Repositories\CountryRepository;
use App\Repositories\CouponRepository;
use App\Repositories\ServiceTypeRepository;
use App\Services\CostsEstimates\CalculateService;
use App\Services\Services\ServiceFactory;
use App\Services\Services\ValidationEntity;
use App\Traits\JsonApiResponse;
use Exception;
use Illuminate\Support\Collection;

class CostsEstimatesController
{
    use JsonApiResponse;

    /** @var ServiceTypeRepository  */
    private $serviceTypeRepository;

    /** @var AdditionalRepository  */
    private $additionalRepository;

    /** @var CountryRepository  */
    private $countryRepository;

    /** @var CouponRepository  */
    private $couponRepository;

    /** @var ServiceFactory  */
    private $serviceFactory;

    /**
     * CostsEstimatesController constructor.
     * @param ServiceTypeRepository $serviceTypeRepository
     * @param AdditionalRepository $additionalRepository
     * @param CountryRepository $countryRepository
     * @param CouponRepository $couponRepository
     * @param ServiceFactory $serviceFactory
     */
    public function __construct(
        ServiceTypeRepository $serviceTypeRepository,
        AdditionalRepository $additionalRepository,
        CountryRepository $countryRepository,
        CouponRepository $couponRepository,
        ServiceFactory $serviceFactory
    ) {
        $this->serviceTypeRepository = $serviceTypeRepository;
        $this->additionalRepository = $additionalRepository;
        $this->countryRepository = $countryRepository;
        $this->couponRepository = $couponRepository;
        $this->serviceFactory = $serviceFactory;
    }

    /**
     * @param CostsEstimateRequest $costsEstimateRequest
     * @return \Illuminate\Http\JsonResponse
     * @throws Exception
     */
    public function index(CostsEstimateRequest $costsEstimateRequest)
    {
        /** @var ServiceType $serviceType */
        $serviceType = $this->serviceTypeRepository->getById($costsEstimateRequest->offsetGet('service_type_id'));

        /** @var Country $originCountry */
        $originCountry = $this->countryRepository->getByCode($costsEstimateRequest->offsetGet('data.origin_country_code'));

        /** @var Country $destinationCountry */
        $destinationCountry = $this->countryRepository->getByCode($costsEstimateRequest->offsetGet('data.destination_country_code'));

        /** @var Service $service */
        if(!$service = $this->serviceFactory->getByServiceTypeAndOriginDestinationCountry($serviceType, $originCountry, $destinationCountry, $costsEstimateRequest->offsetGet('data.weight'))){
            throw new Exception('El país / servicio de destino no está disponible para su dirección');
        }

        $this->serviceFactory->validateService($service, new ValidationEntity($costsEstimateRequest->offsetGet('data.weight'), collect($costsEstimateRequest->get('data.items', []))));

        /** @var Collection|null $additional */
        $additional = $costsEstimateRequest->has('additional') ? $this->additionalRepository->findMany($costsEstimateRequest->offsetGet('additional')) : null;

        /** @var Coupon|null $coupon */
        $coupon = $costsEstimateRequest->has('coupon') ? $this->couponRepository->getByCode($costsEstimateRequest->get('coupon')) : null;

        try {
            $fractal = fractal((new CalculateService($service, collect($costsEstimateRequest->offsetGet('data')), $coupon, $additional))->getCostEntities(), new CostEntityTransformer());
            $fractal->addMeta(['error' => false]);

            return response()->json($fractal->toArray(), 200, [], JSON_PRETTY_PRINT);
        } catch (Exception $e){
            logger($e->getMessage());
            logger($e->getTraceAsString());

            return self::internalServerError();
        }
    }
}