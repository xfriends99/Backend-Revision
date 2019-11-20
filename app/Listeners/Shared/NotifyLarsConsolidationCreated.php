<?php

namespace App\Listeners\Shared;

use App\Events\PurchaseEventWasReceived;
use App\Listeners\PlatformListener;
use App\Models\Purchase;
use App\Models\WorkOrder;
use App\Services\Warehouses\Lars\CreatePayOrderService;

class NotifyLarsConsolidationCreated extends PlatformListener
{
    /**
     * The time (seconds) before the job should be processed.
     *
     * @var int
     */
    public $delay = 60;

    /**
     * @param PurchaseEventWasReceived $event
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle(PurchaseEventWasReceived $event)
    {
        /** @var Purchase $purchase */
        $purchase = $event->purchase;
        $purchase->fresh(['workOrder']);

        /** @var WorkOrder $workOrder */
        $workOrder = $purchase->workOrder;

        // Alert LARS only if all purchases were processed
        $total = $workOrder->getPurchasesCount();
        $processed = $workOrder->getProcessedPurchasesCount();
        if ($processed == $total) {
            /** @var CreatePayOrderService $createPayOrderService */
            $createPayOrderService = app(CreatePayOrderService::class);

            // Notify LARS
            $createPayOrderService->consolidate($workOrder);
        }
    }
}
