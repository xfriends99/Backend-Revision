<?php

namespace App\Services\Services\Validations;

use App\Models\Service;
use App\Services\Services\Exception\ServiceValidationException;
use App\Services\Services\ValidationEntity;
use Exception;

abstract class AbstractValidationService
{
    /** @var Service $service */
    protected $service;

    /** @var ValidationEntity $validationEntity */
    protected $validationEntity;

    /**
     * AbstractValidationService constructor.
     * @param Service $service
     * @param ValidationEntity $validationEntity
     */
    public function __construct(Service $service, ValidationEntity $validationEntity)
    {
        $this->service = $service;
        $this->validationEntity = $validationEntity;
    }

    /**
     * @return void
     * @throws ServiceValidationException|Exception
     */
    abstract public function validate();
}
