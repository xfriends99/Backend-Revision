<?php

namespace App\Services\WorkOrders;

use App\Events\PurchaseEventWasReceived;
use App\Events\WorkOrderWasCreated;
use App\Http\Requests\StoreWorkOrderRequest;
use App\Models\CheckpointCode;
use App\Models\Country;
use App\Models\Coupon;
use App\Models\Purchase;
use App\Models\Service;
use App\Models\ServiceType;
use App\Models\User;
use App\Models\WorkOrder;
use App\Repositories\CheckpointCodeRepository;
use App\Repositories\CouponRepository;
use App\Repositories\PurchaseRepository;
use App\Repositories\ServiceTypeRepository;
use App\Repositories\UserRepository;
use App\Services\Additionals\AdditionalEntity;
use App\Services\Additionals\CalculateAmountService;
use App\Services\Coupons\CouponService;
use App\Services\Purchases\WeightUnitConverter;
use App\Services\Services\ValidationEntity;
use App\Services\WorkOrders\CreateService as WorkOrderCreateService;
use App\Services\Services\ServiceFactory;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ConsolidationService
{
    /** @var UserRepository */
    protected $userRepository;

    /** @var CouponRepository */
    protected $couponRepository;

    /** @var WorkOrderCreateService */
    protected $workOrderCreateService;

    /** @var CalculateAmountService */
    protected $calculateAmountService;

    /** @var PurchaseRepository $purchaseRepository */
    protected $purchaseRepository;

    /** @var CheckpointCodeRepository */
    protected $checkpointCodeRepository;

    /** @var ServiceTypeRepository */
    protected $serviceTypeRepository;

    /** @var ServiceFactory */
    protected $serviceFactory;

    /** @var CouponService $couponService */
    protected $couponService;

    /**
     * ConsolidationService constructor.
     * @param UserRepository $userRepository
     * @param CouponRepository $couponRepository
     * @param CreateService $workOrderCreateService
     * @param CalculateAmountService $calculateAmountService
     * @param PurchaseRepository $purchaseRepository
     * @param CheckpointCodeRepository $checkpointCodeRepository
     * @param ServiceTypeRepository $serviceTypeRepository
     * @param ServiceFactory $serviceFactory
     * @param CouponService $couponService
     */
    public function __construct(
        UserRepository $userRepository,
        CouponRepository $couponRepository,
        WorkOrderCreateService $workOrderCreateService,
        CalculateAmountService $calculateAmountService,
        PurchaseRepository $purchaseRepository,
        CheckpointCodeRepository $checkpointCodeRepository,
        ServiceTypeRepository $serviceTypeRepository,
        ServiceFactory $serviceFactory,
        CouponService $couponService
    ) {
        $this->userRepository = $userRepository;
        $this->couponRepository = $couponRepository;
        $this->workOrderCreateService = $workOrderCreateService;
        $this->calculateAmountService = $calculateAmountService;
        $this->purchaseRepository = $purchaseRepository;
        $this->checkpointCodeRepository = $checkpointCodeRepository;
        $this->serviceTypeRepository = $serviceTypeRepository;
        $this->serviceFactory = $serviceFactory;
        $this->couponService = $couponService;
    }

    /**
     * @param StoreWorkOrderRequest $storeWorkOrderRequest
     * @return WorkOrder
     * @throws Exception
     */
    public function process(StoreWorkOrderRequest $storeWorkOrderRequest)
    {
        /** @var User $user */
        $user = $storeWorkOrderRequest->user();

        /** @var Collection $purchases */
        $purchases = $this->purchaseRepository->findMany($storeWorkOrderRequest->offsetGet('purchases'));

        list($originCountry, $destinationCountry) = $this->validateAndGetOriginDestinationCountry($purchases);

        /** @var ServiceType $serviceType */
        $serviceType = $this->serviceTypeRepository->getById($storeWorkOrderRequest->get('service_id'));

        /** @var float $weight */
        $weight = $this->getWeight($purchases);

        /** @var Service $service */
        if(!$service = $this->serviceFactory->getByServiceTypeAndOriginDestinationCountry($serviceType, $originCountry, $destinationCountry, $weight)){
            throw new Exception('El país / servicio de destino no está disponible para su dirección');
        }

        /** @var array $items */
        $items = [];

        $purchases->each(function($purchase) use(&$items){
            foreach ($purchase->purchaseItems as $purchaseItem){
                $items[] = $purchaseItem->toArray();
            }
        });

        $this->serviceFactory->validateService($service, new ValidationEntity($weight, collect($items)));

        /** @var AdditionalEntity $additionalEntity */
        $additionalEntity = $this->calculateAmountService->calculate(collect($storeWorkOrderRequest->get('additionals')), $purchases->count());

        try {
            DB::beginTransaction();

            /** @var Coupon|null $coupon */
            $coupon = $storeWorkOrderRequest->has('coupon') ? $this->couponService->getCouponUserByKeyAndCode($storeWorkOrderRequest->user(), $storeWorkOrderRequest->offsetGet('coupon.coupon_classification_key'), $storeWorkOrderRequest->offsetGet('coupon.code')) : null;

            $this->userRepository->detachCoupons($user, $this->couponRepository->filter(['purchase_id' => $storeWorkOrderRequest->offsetGet('purchases')])->get()->pluck('id'));

            if($coupon) $this->userRepository->attachCoupon($user, $coupon, Carbon::now());

            /** @var WorkOrder $workOrder */
            $workOrder = $this->workOrderCreateService->create($service, 'CONSOLIDATE', $additionalEntity->getAmount(), $coupon);

            $this->workOrderCreateService->assignAdditionals($workOrder, $additionalEntity);

            $this->workOrderCreateService->updatePurchasesWithWorkOrder($workOrder, $purchases);

            /** @var CheckpointCode $checkpointCode */
            $checkpointCode = $this->checkpointCodeRepository->getByKey('IC-2');

            /** @var Purchase $purchase */
            foreach ($purchases as $purchase) {
                event(new PurchaseEventWasReceived($checkpointCode, $purchase));
            }

            // Send event for notify Lars about work order
            event(new WorkOrderWasCreated($workOrder));

            DB::commit();

            return $workOrder;

        } catch (Exception $exception) {
            DB::rollBack();

            logger($exception->getMessage());
            logger($exception->getTraceAsString());

            throw new Exception('Error creando la consolidación');
        }
    }

    /**
     * @param Collection $purchases
     * @return array
     * @throws Exception
     */
    private function validateAndGetOriginDestinationCountry(Collection $purchases)
    {
        /** @var Purchase $comparePurchase */
        $comparePurchase = $purchases->first();

        $originCountry = $comparePurchase->getWarehouseCountry();
        $destinationCountry = $comparePurchase->getAddressCountry();

        $searchResult = $purchases->filter(function(Purchase $purchase) use($originCountry, $destinationCountry){
            /** @var Country $origin */
            $origin = $purchase->getWarehouseCountry();

            /** @var Country $destination */
            $destination = $purchase->getAddressCountry();

            return $origin->id !== $originCountry->id && $destination->id !== $destinationCountry->id;
        });

        if($searchResult->isNotEmpty()){
            throw new Exception('Las compras seleccionadas no tienen el mismo casillero y dirección de destino');
        }

        return [$originCountry, $destinationCountry];
    }

    /**
     * @param Collection $purchases
     * @return int
     */
    private function getWeight(Collection $purchases)
    {
        $weight = 0;

        $purchases->each(function (Purchase $purchase) use(&$weight){
            $weight += (new WeightUnitConverter($purchase->weightUnit, $purchase->getWeight()))->getWeightAsKg();
        });

        return $weight;
    }

}
