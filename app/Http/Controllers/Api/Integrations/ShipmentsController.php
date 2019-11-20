<?php

namespace App\Http\Controllers\Api\Integrations;

use App\Http\Controllers\Api\Integrations\Requests\StoreShipmentRequest;
use App\Http\Controllers\Controller;
use App\Services\Packages\PackageService;
use Exception;
use Illuminate\Support\Facades\DB;

class ShipmentsController extends Controller
{
    /** @var PackageService */
    protected $packageService;

    public function __construct(PackageService $packageService)
    {
        $this->packageService = $packageService;
    }

    public function store(StoreShipmentRequest $storeShipmentRequest)
    {
        try {
            DB::beginTransaction();

            // Get Label and create underlying package
            $shipment = $this->packageService->createFromShipmentRequest($storeShipmentRequest);

            DB::commit();

            $data = [
                'errors'  => false,
                'data'    => [
                    'tracking' => $shipment->getTracking(),
                    'label'    => $shipment->getLabel(),
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
                'message' => $exception->getMessage()
            ], 400);
        }
    }
}
