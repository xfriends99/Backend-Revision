<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Transformers\CouponTransformer;
use App\Models\Coupon;
use App\Http\Requests\StoreCouponRequest;
use App\Services\Coupons\CouponService;
use App\Repositories\CouponRepository;
use App\Repositories\WorkOrderRepository;
use App\Services\Coupons\Entities\CouponEntity;
use App\Traits\JsonApiResponse;
use App\Http\Controllers\Controller;
use Exception;

class CouponsController extends Controller
{
    use JsonApiResponse;

    /** @var  CouponRepository */
    protected $couponRepository;

    /** @var  CouponService */
    protected $couponService;

    /** @var  WorkOrderRepository */
    protected $workOrderRepository;

    /**
     * CouponsController constructor.
     * @param CouponRepository $couponRepository
     * @param CouponService $couponService
     * @param WorkOrderRepository $workOrderRepository
     */
    public function __construct(
    	CouponRepository $couponRepository,
    	CouponService $couponService,
    	WorkOrderRepository $workOrderRepository
        
    ) {
        $this->couponRepository = $couponRepository;
        $this->couponService = $couponService;
        $this->workOrderRepository = $workOrderRepository;
    }

    /**
     * @param StoreCouponRequest $storeCouponRequest
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function store(StoreCouponRequest $storeCouponRequest)
    {
        /** @var CouponEntity $couponEntity */
        $couponEntity = $this->couponService->parseCoupon($storeCouponRequest, null);

        try {
            /** @var Coupon $coupon */
            $coupon = $this->couponService->createCoupon($couponEntity);

            $fractal = fractal($coupon, new CouponTransformer());
            $fractal->addMeta(['error' => false]);

            return response()->json($fractal->toArray(), 201, [], JSON_PRETTY_PRINT);

        } catch (Exception $exception) {
            logger($exception->getMessage());
            logger($exception->getTraceAsString());

            return self::errorResponse('Error creating coupon.', 500);
        }
    }

    /**
     * @param StoreCouponRequest $request
     * @param $coupon_id
     * @return \Illuminate\Http\JsonResponse
     * @throws Exception
     */
    public function update(StoreCouponRequest $request, $coupon_id)
    {        
        /** @var Coupon $coupon */
        if (!$coupon = $this->couponRepository->getById($coupon_id)) {
            return self::errorResponse('Coupons not found.', 404);
        }

        /** @var CouponEntity $couponEntity */
        $couponEntity = $this->couponService->parseCoupon($request, $coupon);

        try {
            /** @var Coupon $coupon */
            $coupon = $this->couponService->updateCoupon($coupon, $couponEntity);

            return self::success(['message' => 'Coupons update successfully.']);
        } catch (Exception $exception) {
            logger($exception->getMessage());
            logger($exception->getTraceAsString());

            return self::errorResponse('Error updating coupon', 500);
        }
    }

    /**
     * @param $coupon_id
     * @return \Illuminate\Http\JsonResponse
     * @throws Exception
     */
    public function destroy($coupon_id)
    {
        /** @var Coupon $coupon */
        if (!$coupon = $this->couponRepository->getById($coupon_id)) {
            return self::errorResponse('Coupons not found.', 404);
        }
        
        try {
            if (!$this->workOrderRepository->filter(compact('coupon_id'))->first()) {
                $this->couponRepository->delete($coupon);

                return self::success(['message' => 'Coupons deleted successfully.']);
            }

            return self::errorResponse('The coupon is being used', 500);
        } catch (Exception $exception) {
            logger($exception->getMessage());
            logger($exception->getTraceAsString());

            return self::errorResponse('Error deleting coupon.', 500);
        }
    }

}
