<?php

namespace App\Listeners\CasillerosEcuador;

use App\Events\EventWasReceived;
use App\Listeners\PlatformListener;
use App\Models\Package;
use App\Services\CorreosEcuador\Entities\UpdateUserInfoResponse;
use App\Services\CorreosEcuador\Requests\EventCreateRequestService;
use App\Services\Packages\Events\PackageEventEntity;
use Exception;

class CreateEvent extends PlatformListener
{
    /**
     * @param EventWasReceived $event
     * @throws Exception
     */
    public function handle(EventWasReceived $event)
    {
        if (!$this->isValidPlatform($event)) {
            return;
        }

        /** @var Package $package */
        $package = $event->package;

        /** @var PackageEventEntity $packageEventEntity */
        $packageEventEntity = $event->packageEventEntity;

        $preAlertsCreateService = new EventCreateRequestService($packageEventEntity, $package);

        /** @var UpdateUserInfoResponse $response */
        $response = $preAlertsCreateService->request();

        if ($response->hasErrors()) {
            throw new Exception('Error creando el evento');
        }
    }
}
