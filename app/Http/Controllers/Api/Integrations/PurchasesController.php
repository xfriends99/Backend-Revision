<?php

namespace App\Http\Controllers\Api\Integrations;

use App\Events\PurchaseEventWasReceived;
use App\Http\Controllers\Api\Integrations\Requests\ApiRequest;
use App\Http\Controllers\Api\Integrations\Transformers\PurchaseTransformer;
use App\Models\CheckpointCode;
use App\Models\User;
use App\Repositories\CheckpointCodeRepository;
use App\Repositories\PurchaseRepository;
use App\Repositories\WarehouseUnknownRepository;
use App\Services\Cloud\MultiAnalyticsFactory;
use Str;

class PurchasesController
{
    /** @var PurchaseRepository */
    private $purchaseRepository;

    /** @var CheckpointCodeRepository */
    private $checkpointCodeRepository;

    /** @var WarehouseUnknownRepository */
    protected $warehouseUnknownRepository;

    /**
     * PurchasesController constructor.
     * @param PurchaseRepository $purchaseRepository
     * @param CheckpointCodeRepository $checkpointCodeRepository
     * @param WarehouseUnknownRepository $warehouseUnknownRepository
     */
    public function __construct(
        PurchaseRepository $purchaseRepository,
        CheckpointCodeRepository $checkpointCodeRepository,
        WarehouseUnknownRepository $warehouseUnknownRepository
    ) {
        $this->purchaseRepository = $purchaseRepository;
        $this->checkpointCodeRepository = $checkpointCodeRepository;
        $this->warehouseUnknownRepository = $warehouseUnknownRepository;
    }

    public function index(ApiRequest $apiRequest)
    {
        $query = $this->purchaseRepository->filter($apiRequest->only(['tracking']));
        $paginator = $query->paginate(10);

        if (!$paginator->total()) {
            return response()->json(['errors' => 'No purchases found.'], 404);
        }

        $purchases = fractal($paginator, new PurchaseTransformer)
            ->collection($query->get())
            ->toArray();

        return response()->json($purchases);
    }

    public function show(ApiRequest $apiRequest, $tracking)
    {
        $tracking = Str::upper($tracking);
        $purchase = $this->purchaseRepository->getByTracking($tracking);

        if (!$purchase) {
            logger("Purchase [$tracking] not found.");

            // Save into warehouse unknowns table
            $this->warehouseUnknownRepository->firstOrCreate(compact('tracking'));

            return response()->json(['errors' => 'Not found.'], 404);
        }

        $purchases = fractal()->item($purchase, new PurchaseTransformer);

        // Send event for create received in warehouse event
        /** @var CheckpointCode $checkpointCode */
        $checkpointCode = $this->checkpointCodeRepository->getByKey('AW-1');

        event(new PurchaseEventWasReceived($checkpointCode, $purchase, 'processed'));

        // Track event
        MultiAnalyticsFactory::trackUser($purchase->user, 'Recibido');

        return response()->json($purchases);
    }
}
