<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Transformers\WorkOrderTransformer;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWorkOrderRequest;
use App\Models\WorkOrder;
use App\Services\WorkOrders\ConsolidationService;
use App\Traits\JsonApiResponse;
use Exception;


class WorkOrdersController extends Controller
{
    use JsonApiResponse;

    /** @var ConsolidationService  */
    private $consolidationService;

    /**
     * WorkOrdersController constructor.
     * @param ConsolidationService $consolidationService
     */
    public function __construct(ConsolidationService $consolidationService)
    {
        $this->consolidationService = $consolidationService;
    }

    /**
     * @param StoreWorkOrderRequest $storeWorkOrderRequest
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function store(StoreWorkOrderRequest $storeWorkOrderRequest)
    {
        try {
            /** @var WorkOrder $workOrder */
            $workOrder = $this->consolidationService->process($storeWorkOrderRequest);

            $fractal = fractal($workOrder, new WorkOrderTransformer());
            $fractal->addMeta(['error' => false]);

            return response()->json($fractal->toArray(), 201, [], JSON_PRETTY_PRINT);
        } catch (Exception $exception) {

            return self::errorResponse($exception->getMessage(), 500);
        }
    }
}