<?php

namespace App\Listeners\Shared;

use App\Events\PackageWasDebited;
use App\Listeners\PlatformListener;
use App\Models\Package;
use App\Models\WorkOrder;
use App\Services\Warehouses\Lars\MarkPackagePaidService;

class NotifyLarsMarkPackagePaid extends PlatformListener
{

    /**
     * @param PackageWasDebited $event
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle(PackageWasDebited $event)
    {
        /** @var MarkPackagePaidService $markPackagePaidService */
        $markPackagePaidService = app(MarkPackagePaidService::class);

        /** @var Package $package */
        $package = $event->package;

        /** @var WorkOrder $workOrder */
        $workOrder = $package->workOrder;

        $markPackagePaidService->markAsPaid($workOrder);
    }
}