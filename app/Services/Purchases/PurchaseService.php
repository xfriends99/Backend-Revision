<?php

namespace App\Services\Purchases;

use App\Http\Requests\StorePurchaseRequest;
use App\Models\Address;
use App\Models\Country;
use App\Models\Coupon;
use App\Models\Marketplace;
use App\Models\Purchase;
use App\Models\Service;
use App\Models\ServiceType;
use App\Models\State;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WeightUnit;
use App\Models\WorkOrder;
use App\Notifications\PurchaseCreatedNotification;
use App\Repositories\AddressRepository;
use App\Repositories\CouponClassificationRepository;
use App\Repositories\MarketplaceRepository;
use App\Repositories\PurchaseRepository;
use App\Repositories\ServiceTypeRepository;
use App\Repositories\StateRepository;
use App\Repositories\UserRepository;
use App\Repositories\WarehouseRepository;
use App\Repositories\WeightUnitRepository;
use App\Services\Additionals\AdditionalEntity;
use App\Services\Additionals\CalculateAmountService;
use App\Services\Cloud\AmazonS3Manager;
use App\Services\Coupons\CouponService;
use App\Services\Services\Exception\ServiceValidationException;
use App\Services\Services\ValidationEntity;
use App\Services\WorkOrders\CreateService as WorkOrderCreateService;
use App\Services\Services\ServiceFactory;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PurchaseService
{
    /** @var UserRepository */
    protected $userRepository;

    /** @var PurchaseRepository */
    protected $purchaseRepository;

    /** @var AddressRepository */
    protected $addressRepository;

    /** @var MarketplaceRepository */
    protected $marketplaceRepository;

    /** @var StateRepository */
    protected $stateRepository;

    /** @var WarehouseRepository */
    protected $warehouseRepository;

    /** @var AmazonS3Manager */
    protected $amazonS3Manager;

    /** @var WorkOrderCreateService $workOrderCreateService */
    protected $workOrderCreateService;

    /** @var CalculateAmountService $calculateAmountService */
    protected $calculateAmountService;

    /** @var ServiceTypeRepository $serviceTypeRepository */
    protected $serviceTypeRepository;

    /** @var WeightUnitRepository $weightUnitRepository */
    protected $weightUnitRepository;

    /** @var ServiceFactory $serviceFactory */
    protected $serviceFactory;

    /** @var CouponClassificationRepository  */
    protected $couponClassificationRepository;

    /** @var CouponService $couponService */
    protected $couponService;

    /**
     * PurchaseService constructor.
     * @param UserRepository $userRepository
     * @param PurchaseRepository $purchaseRepository
     * @param AddressRepository $addressRepository
     * @param MarketplaceRepository $marketplaceRepository
     * @param StateRepository $stateRepository
     * @param WarehouseRepository $warehouseRepository
     * @param WeightUnitRepository $weightUnitRepository
     * @param AmazonS3Manager $amazonS3Manager
     * @param WorkOrderCreateService $workOrderCreateService
     * @param CalculateAmountService $calculateAmountService
     * @param ServiceTypeRepository $serviceTypeRepository
     * @param ServiceFactory $serviceFactory
     * @param CouponClassificationRepository $couponClassificationRepository
     * @param CouponService $couponService
     */
    public function __construct(
        UserRepository $userRepository,
        PurchaseRepository $purchaseRepository,
        AddressRepository $addressRepository,
        MarketplaceRepository $marketplaceRepository,
        StateRepository $stateRepository,
        WarehouseRepository $warehouseRepository,
        WeightUnitRepository $weightUnitRepository,
        AmazonS3Manager $amazonS3Manager,
        WorkOrderCreateService $workOrderCreateService,
        CalculateAmountService $calculateAmountService,
        ServiceTypeRepository $serviceTypeRepository,
        ServiceFactory $serviceFactory,
        CouponClassificationRepository $couponClassificationRepository,
        CouponService $couponService
    ) {
        $this->userRepository = $userRepository;
        $this->purchaseRepository = $purchaseRepository;
        $this->addressRepository = $addressRepository;
        $this->marketplaceRepository = $marketplaceRepository;
        $this->stateRepository = $stateRepository;
        $this->warehouseRepository = $warehouseRepository;
        $this->weightUnitRepository = $weightUnitRepository;
        $this->amazonS3Manager = $amazonS3Manager;
        $this->workOrderCreateService = $workOrderCreateService;
        $this->calculateAmountService = $calculateAmountService;
        $this->serviceTypeRepository = $serviceTypeRepository;
        $this->serviceFactory = $serviceFactory;
        $this->couponClassificationRepository = $couponClassificationRepository;
        $this->couponService = $couponService;
    }

    /**
     * @param StorePurchaseRequest $storePurchaseRequest
     * @return Purchase|bool
     * @throws Exception|ServiceValidationException
     */
    public function createFromRequest(StorePurchaseRequest $storePurchaseRequest)
    {
        /** @var User $user */
        $user = $storePurchaseRequest->user();

        /** @var ServiceType $serviceType */
        $serviceType = $this->serviceTypeRepository->getById($storePurchaseRequest->get('service_id'));

        /** @var Warehouse $warehouse */
        $warehouse = $this->warehouseRepository->getById($storePurchaseRequest->get('warehouse_id'));

        /** @var WeightUnit $weightUnit */
        $weightUnit = $this->weightUnitRepository->getById($storePurchaseRequest->get('weight_unit_id'));

        /** @var Collection $items */
        $items = collect($storePurchaseRequest->offsetGet('items'));

        /** @var float $weight_total */
        $weight_total = $items->sum(function($item) { return $item['weight'] * $item['quantity']; } );

        /** @var float $weight */
        $weight = (new WeightUnitConverter($weightUnit, $weight_total))->getWeightAsKg();

        /** @var Service $service */
        if(!$service = $this->serviceFactory->getByServiceTypeAndOriginDestinationCountry($serviceType, $warehouse->country, $this->getCountryByAddress($storePurchaseRequest->offsetGet('address')), $weight)){
            throw new Exception('El país / servicio de destino no está disponible para su dirección');
        }

        $this->serviceFactory->validateService($service, new ValidationEntity($weight, collect($storePurchaseRequest->has('items') ? $storePurchaseRequest->offsetGet('items') : [])));

        /** @var Address $address */
        $address = $this->firstOrCreateAddress($user, $storePurchaseRequest->offsetGet('address'));

        /** @var Marketplace $marketplace */
        $marketplace = $this->getMarketplace($storePurchaseRequest->get('marketplace'));

        /** @var string $invoice_url */
        $invoice_url = $this->upload($user, $storePurchaseRequest->file('file'), $storePurchaseRequest->get('tracking'));

        try {
            DB::beginTransaction();

            /** @var Coupon|null $coupon */
            $coupon = $storePurchaseRequest->has('coupon') ? $this->couponService->getCouponUserByKeyAndCode($storePurchaseRequest->user(), $storePurchaseRequest->offsetGet('coupon.coupon_classification_key'), $storePurchaseRequest->offsetGet('coupon.code')) : null;

            if($coupon) $this->userRepository->attachCoupon($user, $coupon, Carbon::now());

            /** @var WorkOrder $workOrder */
            $workOrder = $this->getWorkOrder($storePurchaseRequest, $service, $coupon);

            $data = array_merge([
                'invoice_url'    => $invoice_url,
                'address_id'     => $address->id,
                'marketplace_id' => $marketplace->id,
                'user_id'        => $user->id,
                'work_order_id'  => $workOrder->id,
                'purchased_at'   => Carbon::now(),
                'state'          => 'created'
            ], $storePurchaseRequest->all());

            /** @var Purchase $purchase */
            $purchase = $this->purchaseRepository->create($data);

            $this->addItems($purchase, $storePurchaseRequest);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            logger($e->getFile());
            logger($e->getLine());
            logger($e->getMessage());
            logger($e->getTraceAsString());

            throw new Exception($e->getMessage());
        }

        $user->notify(new PurchaseCreatedNotification($user, $purchase));

        return $purchase;
    }

    /**
     * @param User $user
     * @param UploadedFile $uploadedFile
     * @param $tracking
     * @return null|string
     */
    private function upload(User $user, UploadedFile $uploadedFile, $tracking)
    {
        $username = collect(preg_split("/@/", $user->email))->first();
        $filename = "{$tracking}_{$username}.{$uploadedFile->extension()}";

        $file_path = storage_path() . '/tmp/' . time() . "{$filename}";
        file_put_contents($file_path, file_get_contents($uploadedFile));

        return $this->amazonS3Manager->upload('purchases/' . $filename, env('AWS_BUCKET'), $file_path);
    }

    private function addItems(Purchase $purchase, StorePurchaseRequest $storePurchaseRequest)
    {
        foreach ($storePurchaseRequest->offsetGet('items') as $item) {
            $this->purchaseRepository->addItem($purchase, [
                'quantity'    => $item['quantity'],
                'amount'      => !empty($item['value']) ? $item['value'] : 0,
                'description' => $item['description'],
                'link'        => !empty($item['link']) ? $item['link'] : null,
                'weight'      => !empty($item['weight']) ? $item['weight'] : 0.001,
                'length'      => !empty($item['length']) ? $item['length'] : null,
                'width'       => !empty($item['width']) ? $item['width'] : null,
                'height'      => !empty($item['height']) ? $item['height'] : null
            ]);
        }
    }

    /**
     * @param User $user
     * @param array $address
     * @return \Illuminate\Database\Eloquent\Model
     */
    private function firstOrCreateAddress(User $user, array $address)
    {
        if ($address['type'] == 'exists') {
            return $this->addressRepository->getById($address['address_id']);
        } else {
            /** @var State $state */
            $state = $this->stateRepository->getById($address['state']);

            /** @var Country $country */
            $country = $state->country;

            return $this->addressRepository->create([
                'address1'    => $address['address'],
                'city'        => $address['city'],
                'state'       => $state->name,
                'postal_code' => $address['postal_code'],
                'township'    => $address['township'],
                'floor'       => isset($address['floor']) ? $address['floor'] : null,
                'apartment'   => isset($address['apartment']) ? $address['apartment'] : null,
                'country_id'  => $country->id,
                'user_id'     => $user->id
            ]);
        }
    }

    private function getCountryByAddress($address)
    {
        $country = null;

        if ($address['type'] == 'exists') {
            /** @var Address $address */
            $address = $this->addressRepository->getById($address['address_id']);

            $country = $address->country;
        } else {
            /** @var State $state */
            $state = $this->stateRepository->getById($address['state']);

            /** @var Country $country */
            $country = $state->country;
        }

        return $country;
    }

    /**
     * @param $name
     * @return Marketplace
     */
    private function getMarketplace($name)
    {
        if ($marketplace = $this->marketplaceRepository->getByName($name)) {
            return $marketplace;
        }

        /** @var Marketplace $marketplace */
        $marketplace = $this->marketplaceRepository->create([
            'code'             => Str::slug($name),
            'name'             => $name,
            'informed_by_user' => true
        ]);

        return $marketplace;
    }

    /**
     * @param StorePurchaseRequest $storePurchaseRequest
     * @param Service $service
     * @param Coupon|null $coupon
     * @return \Illuminate\Database\Eloquent\Model|WorkOrder
     */
    private function getWorkOrder(StorePurchaseRequest $storePurchaseRequest, Service $service, Coupon $coupon = null)
    {
        $value = 0;

        if ($storePurchaseRequest->get('additionals')) {
            /** @var AdditionalEntity $additionalEntity */
            $additionalEntity = $this->calculateAmountService->calculate(collect($storePurchaseRequest->get('additionals')), 1, 'SHIP');

            $value = $additionalEntity->getAmount();
        }

        /** @var WorkOrder $workOrder */
        $workOrder = $this->workOrderCreateService->create($service, 'SHIP', $value, $coupon);

        if ($storePurchaseRequest->get('additionals')) {
            $this->workOrderCreateService->assignAdditionals($workOrder, $additionalEntity);
        }

        return $workOrder;
    }

}
