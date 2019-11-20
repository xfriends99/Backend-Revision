<?php

namespace App\Services\CostsEstimates;

use App\Models\Additional;
use App\Models\Coupon;
use App\Models\Service;
use App\Services\Additionals\AdditionalEntity;
use App\Services\Additionals\CalculateAmountService;
use App\Services\Coupons\CouponService;
use App\Services\Tariffs\CalculatorService;
use Illuminate\Support\Collection;

class CalculateService
{
    /** @var Service */
    private $service;

    /** @var Collection */
    private $data;

    /** @var Coupon|null */
    private $coupon;

    /** @var Collection|null */
    private $additional;

    /** @var Collection */
    private $entities;

    /**
     * CalculateService constructor.
     * @param Service $service
     * @param Collection $data
     * @param Coupon|null $coupon
     * @param Collection|null $additional
     */
    public function __construct(Service $service, Collection $data, Coupon $coupon = null, Collection $additional = null)
    {
        $this->entities = collect();

        $this->data = $data;
        $this->service = $service;
        $this->coupon = $coupon;
        $this->additional = $additional;
    }

    /**
     * @return Collection
     * @throws \Exception
     */
    public function getCostEntities()
    {
        $this->entities->push($this->calculateService());

        $this->entities = $this->entities->merge($this->calculateAdditional());

        if($this->coupon) $this->entities->push($this->calculateCoupon());

        return $this->entities;
    }

    /**
     * @return CostEntity
     * @throws \Exception
     */
    private function calculateService()
    {
        /** @var CalculatorService $calculatorService */
        $calculatorService = app(CalculatorService::class);

        /** @var int $items */
        $items = $this->data->has('items') ? count($this->data->get('items')) : 1;

        /** @var float $tariff */
        $tariff = $calculatorService->quote(current_platform(), $this->service->destinationCountry, $this->data->get('admin_level_1', ''), $this->data->get('admin_level_2', ''), $this->data->get('admin_level_3', ''), $this->data->get('zip_code', ''), $this->service->code, $this->data->get('weight'), $items);

        return new CostEntity("Servicio {$this->service->getServiceTypeDescription()}", $tariff, 'service');
    }

    /**
     * @return Collection
     * @throws \Exception
     */
    private function calculateAdditional()
    {
        /** @var CalculateAmountService $calculateAmountService */
        $calculateAmountService = app(CalculateAmountService::class);

        /** @var Collection $additional_response */
        $additional_response = collect();

        /** @var int $items */
        $items = $this->data->has('items') ? count($this->data->get('items')) : 1;

        /** @var AdditionalEntity $additionalEntity */
        $additionalEntity = $calculateAmountService->calculate($this->additional ? $this->additional->pluck('id') : collect([]), $items, $this->data->get('process'));

        /** @var Additional $additional */
        $additionalEntity->getAdditionals()->each(function (Additional $additional) use(&$additional_response){
            $additional_response->push( new CostEntity("Adicional {$additional->description_es}", $additional->getAmount(), 'additional'));
        });

        return $additional_response;
    }

    /**
     * @return CostEntity
     * @throws \Exception
     */
    private function calculateCoupon()
    {
        /** @var CouponService $couponService */
        $couponService = app(CouponService::class);

        /** @var float $invoice_amount */
        $invoice_amount = $this->entities->sum(function(CostEntity $costEntity){
            return $costEntity->getAmount();
        });

        $amount = $couponService->applyDiscount($this->coupon, $invoice_amount);

        return new CostEntity($this->coupon->description, 0 - ($invoice_amount - $amount), 'coupon', $this->coupon->getCouponClassificationKey());
    }
}