<?php
namespace App\Services\Services\Exception;

use App\Models\Service;
use Exception;

class ServiceValidationException extends Exception
{
    public function __construct(Service $service, $message) {
//        parent::__construct("Error procesando servicio {$service->getServiceTypeDescription()}. {$message}");
        parent::__construct($message);
    }
}
