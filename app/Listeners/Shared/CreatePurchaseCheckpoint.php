<?php

namespace App\Listeners\Shared;

use App\Events\PurchaseEventWasReceived;
use App\Listeners\PlatformListener;
use App\Repositories\PurchaseRepository;
use App\Services\Purchases\CreatePurchaseCheckpoint as CreateCheckpoint;
use Carbon\Carbon;

class CreatePurchaseCheckpoint extends PlatformListener
{
    /** @var PurchaseRepository */
    private $purchaseRepository;

    /** @var CreateCheckpoint */
    private $createCheckpoint;

    /**
     * Create the event listener.
     *
     * @param PurchaseRepository $purchaseRepository
     * @param CreateCheckpoint $createCheckpoint
     * @return void
     */
    public function __construct(
        PurchaseRepository $purchaseRepository,
        CreateCheckpoint $createCheckpoint
    ) {
        $this->purchaseRepository = $purchaseRepository;
        $this->createCheckpoint = $createCheckpoint;
    }

    /**
     * Handle the event.
     *
     * @param PurchaseEventWasReceived $event
     * @return void
     */
    public function handle(PurchaseEventWasReceived $event)
    {
        $this->createCheckpoint->create($event->checkpointCode, $event->purchase, Carbon::now());

        if ($event->status) {
            $this->purchaseRepository->update($event->purchase, ['state' => $event->status]);
        }
    }
}
