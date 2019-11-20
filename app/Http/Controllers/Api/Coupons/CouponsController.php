<?php

namespace App\Http\Controllers\Api\Coupons;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\CouponRepository;
use App\Services\Coupons\ClubLaNacionCreationCouponService;
use App\Services\Coupons\CostEntityCreationService;
use App\Services\Coupons\CouponValidation;
use App\Http\Controllers\Api\Transformers\CostEntityTransformer;
use App\Traits\JsonApiResponse;
use Exception;

class CouponsController extends Controller
{
    use JsonApiResponse;

    /**
     * @var CouponRepository
     */
    protected $couponRepository;

    /**
     * @var ClubLaNacionCreationCouponService
     */
    protected $clubLaNacionCreationCouponService;

    /**
     * @var CostEntityCreationService
     */
    protected $costEntityCreationService;

    /**
     * @var CouponValidation
     */
    protected $couponValidation;

    /**
     * CouponsController constructor.
     * @param CouponRepository $couponRepository
     * @param CouponValidation $couponValidation
     * @param ClubLaNacionCreationCouponService $clubLaNacionCreationCouponService
     * @param CostEntityCreationService $costEntityCreationService
     */
    public function __construct(
        CouponRepository $couponRepository,
        CouponValidation $couponValidation,
        ClubLaNacionCreationCouponService $clubLaNacionCreationCouponService,
        CostEntityCreationService $costEntityCreationService
    ){
        $this->couponRepository = $couponRepository;
        $this->couponValidation = $couponValidation;
        $this->clubLaNacionCreationCouponService = $clubLaNacionCreationCouponService;
        $this->costEntityCreationService = $costEntityCreationService;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function show(Request $request, $code)
    {
        $this->validate($request, [
            'amount' => 'required|numeric'
        ]);

        try{
            if (!$coupon = $this->couponRepository->getByCode($code)){
                $coupon = $this->clubLaNacionCreationCouponService->generateCoupon($request->user(), $code,20,0,true);
            }

            if (!$this->couponValidation->validate($coupon, $request->user())) {
                return self::badRequest("El cupón con el código {$code} no es valido.");
            }

            $fractal = fractal($this->costEntityCreationService->make($coupon ,$request->offsetGet('amount')),new CostEntityTransformer());
            $fractal->addMeta(['error' => false]);
            return response()->json($fractal->toArray(), 200, [], JSON_PRETTY_PRINT);
        } catch (Exception $e) {
            logger($e->getMessage());
            logger($e->getTraceAsString());

            return self::errorResponse($e->getMessage(), 404);
        }
    }
}
