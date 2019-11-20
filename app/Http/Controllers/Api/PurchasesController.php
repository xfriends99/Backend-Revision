<?php

namespace App\Http\Controllers\Api;

use App\Events\PurchaseEventWasReceived;
use App\Http\Controllers\Api\Transformers\PurchaseTransformer;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePurchaseRequest;
use App\Models\CheckpointCode;
use App\Models\Purchase;
use App\Repositories\CheckpointCodeRepository;
use App\Services\Purchases\PurchaseService;
use App\Traits\JsonApiResponse;
use Illuminate\Http\Request;
use Exception;

class PurchasesController extends Controller
{
    use JsonApiResponse;

    /** @var PurchaseService */
    protected $purchaseService;

    /** @var CheckpointCodeRepository */
    protected $checkpointCodeRepository;

    public function __construct(PurchaseService $purchaseService, CheckpointCodeRepository $checkpointCodeRepository)
    {
        $this->purchaseService = $purchaseService;
        $this->checkpointCodeRepository = $checkpointCodeRepository;
    }

    /**
     * @param StorePurchaseRequest $storePurchaseRequest
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function store(StorePurchaseRequest $storePurchaseRequest)
    {
        try{
            /** @var Purchase $purchase */
            $purchase = $this->purchaseService->createFromRequest($storePurchaseRequest);

            // Send event for create Purchase informed event
            /** @var CheckpointCode $checkpointCode */
            $checkpointCode = $this->checkpointCodeRepository->getByKey('IC-1');

            event(new PurchaseEventWasReceived($checkpointCode, $purchase));

            $fractal = fractal($purchase, new PurchaseTransformer());
            $fractal->addMeta(['error' => false]);

            return response()->json($fractal->toArray(), 201, [], JSON_PRETTY_PRINT);
        } catch(Exception $e){
            logger($e->getMessage());
            logger($e->getTraceAsString());
            return self::errorResponse($e->getMessage(), 500);
        }

    }

    /**
     * @param Request $request
     * @param $id\
     */
    public function update(Request $request, $id)
    {
        //
    }
}
