<?php

namespace App\Services\Packages;

use App\Events\PackageWasDebited;
use App\Events\PurchaseEventWasReceived;
use App\Events\ShipmentWasProcessed;
use App\Http\Controllers\Api\Integrations\Requests\StoreConsolidationRequest;
use App\Http\Controllers\Api\Integrations\Requests\StoreShipmentRequest;
use App\Models\Card;
use App\Models\CheckpointCode;
use App\Models\Country;
use App\Models\Invoice;
use App\Models\Package;
use App\Models\Platform;
use App\Models\Purchase;
use App\Models\Transaction;
use App\Models\TransactionStatus;
use App\Models\User;
use App\Models\WorkOrder;
use App\Notifications\AddCardNotification;
use App\Repositories\CheckpointCodeRepository;
use App\Repositories\InvoiceRepository;
use App\Repositories\PackageRepository;
use App\Repositories\TransactionStatusRepository;
use App\Repositories\WorkOrderRepository;
use App\Services\Cards\GatewayFactory;
use App\Services\Coupons\CouponService;
use App\Services\Mailamericas\Shipping\AdmissionService;
use App\Services\Mailamericas\Shipping\LabelingService;
use App\Services\Packages\Entity\Shipment;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;

class PackageService
{
    /** @var AdmissionService */
    protected $admissionService;

    /** @var LabelingService */
    protected $labelingService;

    /** @var PackageRepository */
    protected $packageRepository;

    /** @var WorkOrderRepository */
    protected $workOrderRepository;

    /** @var  TransactionStatusRepository */
    protected $transactionStatusRepository;

    /** @var  InvoiceRepository */
    protected $invoiceRepository;

    /** @var CheckpointCodeRepository */
    protected $checkpointCodeRepository;

    /** @var CouponService */
    protected $couponService;

    public function __construct(
        AdmissionService $admissionService,
        LabelingService $labelingService,
        PackageRepository $packageRepository,
        WorkOrderRepository $workOrderRepository,
        TransactionStatusRepository $transactionStatusRepository,
        InvoiceRepository $invoiceRepository,
        CheckpointCodeRepository $checkpointCodeRepository,
        CouponService $couponService
    ) {
        $this->admissionService = $admissionService;
        $this->labelingService = $labelingService;
        $this->packageRepository = $packageRepository;
        $this->workOrderRepository = $workOrderRepository;
        $this->transactionStatusRepository = $transactionStatusRepository;
        $this->invoiceRepository = $invoiceRepository;
        $this->checkpointCodeRepository = $checkpointCodeRepository;
        $this->couponService = $couponService;
    }

    /**
     * @param StoreShipmentRequest $storeShipmentRequest
     * @return Shipment
     * @throws Exception
     */
    public function createFromShipmentRequest(StoreShipmentRequest $storeShipmentRequest)
    {
        // Prepare entity
        $shipment = Shipment::newInstanceFromStoreShipmentRequest($storeShipmentRequest);

        /** @var WorkOrder $workOrder */
        $workOrder = $storeShipmentRequest->workOrder;
        if ($workOrder->isConsolidateType()) {
            // Return fake label
            $tracking = $storeShipmentRequest->offsetGet('tracking');
            $shipment->setTracking($tracking);
            $shipment->setLabel($this->labelingService->drawConsolidationLabel($workOrder, $tracking));

            return $shipment;
        }

        if (!$shipment->isWorkOrderProcessed()) {
            // Retrieve new label from Shipping
            $this->admissionService->ship($shipment);

            /** @var Package $package */
            $package = self::createInternal($shipment);
        } else {
            // Request label from Shipping
            $this->labelingService->label($shipment);
        }

        return $shipment;
    }

    /**
     * @param StoreConsolidationRequest $storeConsolidationRequest
     * @return Shipment
     * @throws Exception
     */
    public function createFromConsolidationRequest(StoreConsolidationRequest $storeConsolidationRequest)
    {
        // Prepare entity
        $shipment = Shipment::newInstanceFromStoreConsolidationRequest($storeConsolidationRequest);

        if (!$shipment->isWorkOrderProcessed()) {
            // Retrieve new label from Shipping
            $this->admissionService->ship($shipment);

            /** @var Package $package */
            $package = self::createInternal($shipment);
        } else {
            // Request label from Shipping
            $this->labelingService->label($shipment);
        }

        return $shipment;
    }

    /**
     * @param Shipment $shipment
     * @return Package
     * @throws Exception
     */
    protected function createInternal(Shipment &$shipment)
    {
        $package = null;

        try {
            /** @var WorkOrder $workOrder */
            $workOrder = $shipment->getWorkOrder();

            /** @var Purchase $firstPurchase */
            $firstPurchase = $workOrder->purchases->first();

            /** @var User $user */
            $user = $firstPurchase->user;

            // Save Package and associate with corresponding purchases
            DB::beginTransaction();

            /** @var Package $package */
            $package = $this->packageRepository->create([
                'user_id'       => $user ? $user->id : null,
                'work_order_id' => $workOrder->id,
                'tracking'      => $shipment->getTracking(),
                'value'         => $workOrder->getDeclaredValue(),
                'weight'        => $shipment->getWeight(),
                'height'        => $shipment->getHeight(),
                'width'         => $shipment->getWidth(),
                'length'        => $shipment->getLength()
            ]);

            // Update entity too
            $shipment->setPackage($package);

            // Mark WorkOrder as processed
            $this->workOrderRepository->markAsProcessed($workOrder);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            logger($e->getMessage());

            throw $e;
        }

        /** @var CheckpointCode $checkpointCode */
        $checkpointCode = $this->checkpointCodeRepository->getByKey('IC-4');

        /** @var Purchase $purchase */
        foreach ($package->getWorkOrderPurchases() as $purchase) {
            // Fire event for each purchase in current work order
            event(new PurchaseEventWasReceived($checkpointCode, $purchase));
        }

        // Fire shipment processed event
        event(new ShipmentWasProcessed($package));

        return $package;
    }

    /**
     * @param Package $package
     * @throws Exception
     */
    public function debitPackage(Package $package)
    {
        try {
            if (!$package->wasInvoiced()) {
                if (!$invoice = $package->invoice) {
                    $invoice = $this->generateInvoice($package);
                }

                if (!$invoice) {
                    throw new Exception('Could not generate invoice for package');
                }

                /** @var Card $card */
                $card = $package->getUserDefaultCard();

                if (!$card) {
                    if (!$package->invoiceExceededAttemptsWithoutCard()) {
                        $this->sendAddCardNotification($package);
                    }
                } else {
                    // Get count of pending transactions
                    // If the invoice has pending transactions, we must wait a DLocal response and not debit again
                    $pendingTransactions = $invoice->transactions->filter(function ($transaction) use ($card) {
                        /** @var Transaction $transaction */
                        return $transaction->isPending();
                    })->count();

                    if ($pendingTransactions >= 1) {
                        throw new Exception('Invoice with pending transactions');
                    }

                    // Get count of transactions with the same card
                    $cardTransactions = $invoice->transactions->filter(function ($transaction) use ($card) {
                        return $transaction->card_id == $card->id;
                    })->count();

                    if ($cardTransactions < 5) {
                        GatewayFactory::debit($card, $invoice);
                    }
                }
            }

            if ($package->wasInvoiced()) {
                event(new PackageWasDebited($package));
            }
        } catch (Exception $exception) {
            logger("[Debit Package Invoice] Exception in package {$package->tracking}");
            logger($exception->getMessage());
            logger($exception->getTraceAsString());
        }
    }

    /**
     * @param Package $package
     * @return Invoice
     * @throws Exception
     */
    public function generateInvoice(Package $package)
    {
        /** @var User $user */
        $user  = $package->user;

        /** @var Card $card */
        $card = $user->getDefaultCard();

        /** @var TransactionStatus $invoiceStatus $invoiceStatus */
        $invoiceStatus = $this->transactionStatusRepository->getByKey('pending');

        // Get shipping cost from Tracking
        $tariff = $this->getPackageTariff($package);

        // Set Shipping cost
        $shipping_cost = floatval($tariff['data']['tariff_value_usd']);

        // Calculate package additional
        $additional = $package->getWorkOrderAdditionalsTotalValue();

        // Calculate insurance: 1% from declared value
        $insurance = $package->value * 0.01;

        // Subtotal = Package shipping cost + additional
        $subtotal = $shipping_cost + $additional + $insurance;

        // Get IVA percentage per country
        $iva_percentage = $this->calculateIvaPercentage($package);

        // Calculate IVA
        $iva = $subtotal * $iva_percentage;

        // Total amount = Subtotal + IVA
        $total_amount = $subtotal + $iva;

        if ($package->hasCoupon()) {
            /** @var Coupon $coupon */
            $coupon = $package->workOrder->coupon;
            // Total amount with discount
            $total_amount = $this->couponService->applyDiscount($coupon_id, $total_amount);
        }

        // Set tariff code to Correos del Ecuador
        $tariff_code = $tariff['data']['tariff_code'];

        /** @var Invoice $invoice */
        $invoice = $this->invoiceRepository->create([
            'shipping_cost' => $shipping_cost,
            'additional'    => $additional,
            'subtotal'      => $subtotal,
            'insurance'     => $insurance,
            'iva'           => $iva,
            'total_amount'  => GatewayFactory::getAmount($card->getFirstPaymentGateway(), $total_amount),
            'state'         => $invoiceStatus->key,
            'tariff_code'   => $tariff_code
        ]);

        $this->invoiceRepository->update($invoice, [
            'number' => $invoice->id
        ]);

        $this->packageRepository->update($package, [
            'invoice_id' => $invoice->id
        ]);

        return $invoice;
    }

    /**
     * @param Package $package
     * @return float|int
     */
    private function calculateIvaPercentage(Package $package)
    {
        /** @var Purchase $purchase */
        $purchase = $package->getWorkOrderPurchases()->first();

        $country_code = $purchase->getAddressCountryCode();

        switch ($country_code) {
            case 'EC':
                return 0.12;
            default:
                return 0;
        }
    }

    /**
     * @param Package $package
     * @return array
     * @throws Exception
     */
    public function getPackageTariff(Package $package)
    {
        $url = env('TRACKING_API_URL') . '/v1/packages/' . $package->tracking . '/tariff' . '?access_token=' . env('TRACKING_API_ACCESS_TOKEN');

        $client = new Client();
        $response = $client->get($url);

        $response = json_decode($response->getBody()->getContents(), true);

        if ($response['error']) {
            throw new Exception($response['message']);
        }

        return $response;
    }

    /**
     * @param Package $package
     */
    public function sendAddCardNotification(Package $package)
    {
        logger("[Debit Package Invoice] Card not found for package {$package->tracking}");

        /** @var User $user */
        $user = $package->user;

        /** @var Invoice $invoice */
        $invoice = $package->invoice;

        $this->invoiceRepository->update($invoice, [
            'attempts_without_card' => $invoice->attempts_without_card + 1
        ]);

        $user->notify(new AddCardNotification($package));
    }

    /**
     * @param Country $country
     * @param Platform $platform
     * @return string
     */
    static public function getServiceCode(Country $country, Platform $platform)
    {
        if ($platform->isMailamericas()) {
            switch ($country->code) {
                case 'AR':
                    return 'USEMSAR';
                case 'MX':
                    return 'US0015MX';
                case 'CO':
                    return 'US0015CO';
                case 'CL':
                    return 'CN0030CL';
                case 'PE':
                    return 'USHYBPE';
                case 'EC':
                    return 'USCAS11EX';
            }
        } else {
            return 'USCAS11EX';
        }

        return 'registered';
    }
}
