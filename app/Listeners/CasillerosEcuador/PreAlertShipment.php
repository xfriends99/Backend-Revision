<?php

namespace App\Listeners\CasillerosEcuador;

use App\Events\ShipmentWasProcessed;
use App\Listeners\PlatformListener;
use App\Models\Package;
use App\Services\CorreosEcuador\Entities\UpdateUserInfoResponse;
use App\Services\CorreosEcuador\Requests\PreAlertsCreateRequestService;
use Exception;

class PreAlertShipment extends PlatformListener
{
    /**
     * @param ShipmentWasProcessed $event
     * @throws Exception
     */
    public function handle(ShipmentWasProcessed $event)
    {
        if (!$this->isValidPlatform($event)) {
            return;
        }

        /** @var Package $package */
        $package = $event->package;

        $preAlertsCreateService = new PreAlertsCreateRequestService($package);

        /** @var UpdateUserInfoResponse $response */
        $response = $preAlertsCreateService->request();

        if ($response->hasErrors()) {
            throw new Exception('Error prealertando paquete');
        }
    }
}
