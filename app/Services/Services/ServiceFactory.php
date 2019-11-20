<?php

namespace App\Services\Services;

use App\Models\Country;
use App\Models\Service;
use App\Models\ServiceType;
use App\Repositories\ServiceRepository;
use App\Services\Services\Exception\ServiceValidationException;
use App\Services\Services\Validations\ArValidationService;
use Illuminate\Support\Collection;

class ServiceFactory
{
    /** @var ServiceRepository */
    private $serviceRepository;

    /** @var GenericValidation */
    private $genericValidation;

    /**
     * SearchService constructor.
     * @param ServiceRepository $serviceRepository
     * @param GenericValidation $genericValidation
     */
    public function __construct(ServiceRepository $serviceRepository, GenericValidation $genericValidation)
    {
        $this->serviceRepository = $serviceRepository;
        $this->genericValidation = $genericValidation;
    }

    /**
     * @param ServiceType $serviceType
     * @param Country $originCountry
     * @param Country $destinationCountry
     * @param float $weight
     * @return Service|null
     */
    public function getByServiceTypeAndOriginDestinationCountry(ServiceType $serviceType, Country $originCountry, Country $destinationCountry, float $weight)
    {
        /** @var Collection $services */
        $services = $this->serviceRepository->filter([
            'service_type_id'        => $serviceType->id,
            'origin_country_id'      => $originCountry->id,
            'destination_country_id' => $destinationCountry->id
        ])->get();

        if ($services->count() > 1) {
            return $services->filter(function ($service) use ($weight) {
                return $service->max_weight >= $weight;
            })->first() ?: $services->filter(function ($service) use ($weight) {
                return $service->max_weight == null;
            })->first();
        } else {
            if ($services->count() == 1) {
                return $services->first();
            }
        }

        return null;
    }

    /**
     * @param Service $service
     * @param ValidationEntity $validationEntity
     * @throws ServiceValidationException
     */
    public function validateService(Service $service, ValidationEntity $validationEntity)
    {
        $validationService = null;

        if ($service->getDestinationCountryCode() == 'AR') {
            $validationService = new ArValidationService($service, $validationEntity);
        }

        if ($validationService) {
            $validationService->validate();
        }

        $this->genericValidation->validateWeight($service, $validationEntity);
    }
}
