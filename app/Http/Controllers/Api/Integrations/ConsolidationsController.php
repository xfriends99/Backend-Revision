<?php

namespace App\Http\Controllers\Api\Integrations;

use App\Http\Controllers\Api\Integrations\Requests\StoreConsolidationRequest;
use App\Http\Controllers\Controller;
use App\Services\Packages\PackageService;
use Exception;
use Illuminate\Support\Facades\DB;

class ConsolidationsController extends Controller
{
    /** @var PackageService */
    protected $packageService;

    public function __construct(PackageService $packageService)
    {
        $this->packageService = $packageService;
    }

    public function store(StoreConsolidationRequest $storeConsolidationRequest)
    {
        try {
            DB::beginTransaction();

            // Get Label and create underlying package
            $consolidation = $this->packageService->createFromConsolidationRequest($storeConsolidationRequest);

            DB::commit();

            $data = [
                'errors'  => false,
                'data'    => [
                    'tracking' => $consolidation->getTracking(),
                    'label'    => $consolidation->getLabel(),
                    'format'   => 'pdf',
                ],
                'message' => 'Shipment created.'
            ];

            logger('[Shipment] Response');
            logger($data);

            return response()->json($data, 201);
        } catch (Exception $exception) {
            DB::rollBack();

            logger($exception->getMessage());
            logger($exception->getTraceAsString());

            return response()->json([
                'errors'  => true,
                'message' => 'Error creating shipment.'
            ], 400);
        }
    }
}
