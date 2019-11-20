<?php

namespace App\Services\Services\Validations;

use App\Models\Service;
use App\Services\Services\Exception\ServiceValidationException;
use App\Services\Services\ValidationEntity;

class ArValidationService extends AbstractValidationService
{
    /**
     * MxValidationService constructor.
     * @param Service $service
     * @param ValidationEntity $validationEntity
     */
    public function __construct(Service $service, ValidationEntity $validationEntity)
    {
        parent::__construct($service, $validationEntity);
    }

    /**
     * @throws ServiceValidationException
     */
    public function validate()
    {
        $items = $this->validationEntity->getItems();

        if (isset($items->first()['description'])) {
            $counted = [];
            $items->each(function ($item) use (&$counted) {
                if (!isset($counted[$item['description']])) {
                    $counted[$item['description']] = 0;
                }
                $counted[$item['description']] += $item['quantity'];
            });

            foreach ($counted as $key => $val) {
                if ($val > 3) {
                    throw new ServiceValidationException($this->service, "El producto {$key} no puede superar las 3 unidades.");
                }
            }
        } else {
            if ($items->count() > 3) {
                throw new ServiceValidationException($this->service, "El número máximo de artículos permitidos es 3.");
            }
        }
    }
}
