<?php

namespace App\Services\Services;

use App\Models\Service;
use App\Services\Services\Exception\ServiceValidationException;

class GenericValidation
{
    /** @var float */
    private $weight = 20.000;

    /**
     * @param Service $service
     * @param ValidationEntity $validationEntity
     * @throws ServiceValidationException
     */
    public function validateWeight(Service $service, ValidationEntity $validationEntity)
    {
        if ($service->max_weight) {
            if ($validationEntity->getWeight() > $service->max_weight) {
                throw new ServiceValidationException($service, "El peso máximo para {$service->getDestinationCountryName()} es de {$service->max_weight} Kg.");
            }
        }

        if ($validationEntity->getWeight() > $this->weight) {
            throw new ServiceValidationException($service, "El peso máximo permitido es de {$this->weight} Kg.");
        }
    }
}
