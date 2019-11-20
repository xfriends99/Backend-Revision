<?php

namespace App\Services\Purchases;

use App\Models\CheckpointCode;
use App\Models\Purchase;
use App\Repositories\PurchaseCheckpointRepository;
use Carbon\Carbon;

class CreatePurchaseCheckpoint
{

    /** @var PurchaseCheckpointRepository  */
    private $purchaseCheckpointRepository;

    /**
     * CreatePurchaseCheckpoint constructor.
     * @param PurchaseCheckpointRepository $purchaseCheckpointRepository
     */
    public function __construct(PurchaseCheckpointRepository $purchaseCheckpointRepository)
    {
        $this->purchaseCheckpointRepository = $purchaseCheckpointRepository;
    }

    /**
     * @param CheckpointCode $checkpointCode
     * @param Purchase $purchase
     * @param Carbon $checkpointAt
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function create(CheckpointCode $checkpointCode, Purchase $purchase, Carbon $checkpointAt)
    {
        return $this->purchaseCheckpointRepository->create([
           'checkpoint_code_id' => $checkpointCode->id,
           'purchase_id' => $purchase->id,
           'checkpoint_at' => $checkpointAt->toDateTimeString()
        ]);
    }

}