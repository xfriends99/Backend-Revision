<?php

namespace App\Listeners\CasillerosEcuador;

use App\Events\PackageGetInvoice;
use App\Events\PackageWasDebited;
use App\Listeners\PlatformListener;
use App\Models\Package;
use App\Services\CorreosEcuador\Entities\UpdateUserInfoResponse;
use App\Services\CorreosEcuador\Requests\PayOrderCreateRequestService;
use Exception;

class CreatePayOrder extends PlatformListener
{
    /**
     * The time (seconds) before the job should be processed.
     *
     * @var int
     */
    public $delay = 60;

    /**
     * @param PackageWasDebited $event
     * @throws Exception
     */
    public function handle(PackageWasDebited $event)
    {
        if (!$this->isValidPlatform($event)) {
            return;
        }

        /** @var Package $package */
        $package = $event->package;

        /** @var PayOrderCreateRequestService $payOrderCreateService */
        $payOrderCreateService = new PayOrderCreateRequestService($package);

        /** @var UpdateUserInfoResponse $response */
        $response = $payOrderCreateService->request();

        if ($response->hasErrors()) {
            throw new Exception('Error enviando order de pago');
        }

        event(new PackageGetInvoice($package));
    }
}
