<?php

namespace App\Services\WorkOrders;

use App\Models\Coupon;
use App\Models\Service;
use App\Models\WorkOrder;
use App\Repositories\PurchaseRepository;
use App\Repositories\WorkOrderRepository;
use App\Services\Additionals\AdditionalEntity;
use Illuminate\Support\Collection;

class CreateService
{
    /** @var WorkOrderRepository $workOrderRepository */
    private $workOrderRepository;

    /** @var PurchaseRepository $purchaseRepository */
    private $purchaseRepository;

    public function __construct(WorkOrderRepository $workOrderRepository,
                                PurchaseRepository $purchaseRepository)
    {
        $this->workOrderRepository = $workOrderRepository;
        $this->purchaseRepository = $purchaseRepository;
    }

    /**
     * @param Service $service
     * @param string $type
     * @param float $value
     * @param Coupon|null $coupon
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function create(Service $service, $type, $value, Coupon $coupon = null)
    {
        return $this->workOrderRepository->create([
            'service_id' => $service->id,
            'coupon_id' => $coupon ? $coupon->id : null,
            'type' => $type,
            'value' => $value,
            'state' => 'created'
        ]);
    }

    public function assignAdditionals(WorkOrder $workOrder, AdditionalEntity $additionalEntity)
    {
        /** @var Collection $additionals */
        $additionals = $additionalEntity->getAdditionals();

        $additionals->each(function ($additional) use($workOrder){
           $this->workOrderRepository->attachAdditional($workOrder, $additional, $additional->value);
        });
    }

    public function updatePurchasesWithWorkOrder(WorkOrder $workOrder, Collection $purchases)
    {
        $purchases->each(function ($purchase) use($workOrder) {
           $this->purchaseRepository->update($purchase, ['work_order_id' => $workOrder->id]);
        });
    }
}