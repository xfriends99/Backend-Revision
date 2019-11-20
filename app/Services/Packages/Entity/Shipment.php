<?php

namespace App\Services\Packages\Entity;

use App\Http\Controllers\Api\Integrations\Requests\StoreConsolidationRequest;
use App\Http\Controllers\Api\Integrations\Requests\StoreShipmentRequest;
use App\Models\Package;
use App\Models\WorkOrder;

class Shipment
{
    /** @var WorkOrder */
    protected $workOrder;

    /** @var Package */
    protected $package;

    /** @var string */
    protected $label;

    /** @var string */
    protected $tracking;

    /** @var float */
    protected $weight;

    /** @var float */
    protected $height;

    /** @var float */
    protected $width;

    /** @var float */
    protected $length;

    /**
     * @param StoreShipmentRequest $storeShipmentRequest
     * @return Shipment
     */
    public static function newInstanceFromStoreShipmentRequest(StoreShipmentRequest $storeShipmentRequest)
    {
        $shipment = new Shipment();

        /** @var WorkOrder $workOrder */
        if ($workOrder = $storeShipmentRequest->workOrder) {
            $shipment->setWorkOrder($workOrder);
        }

        /** @var Package $package */
        if ($package = $workOrder->package) {
            $shipment->setPackage($package);
            $shipment->setTracking($package->tracking);
        }

        $shipment->setWeight($storeShipmentRequest->offsetGet('weight'));
        $shipment->setHeight($storeShipmentRequest->offsetGet('height'));
        $shipment->setWidth($storeShipmentRequest->offsetGet('width'));
        $shipment->setLength($storeShipmentRequest->offsetGet('length'));

        return $shipment;
    }

    /**
     * @param StoreConsolidationRequest $storeConsolidationRequest
     * @return Shipment
     */
    public static function newInstanceFromStoreConsolidationRequest(StoreConsolidationRequest $storeConsolidationRequest)
    {
        $shipment = new Shipment();

        /** @var WorkOrder $workOrder */
        if ($workOrder = $storeConsolidationRequest->workOrder) {
            $shipment->setWorkOrder($workOrder);
        }

        /** @var Package $package */
        if ($package = $workOrder->package) {
            $shipment->setPackage($package);
            $shipment->setTracking($package->tracking);
        }

        $shipment->setWeight($storeConsolidationRequest->offsetGet('weight'));
        $shipment->setHeight($storeConsolidationRequest->offsetGet('height'));
        $shipment->setWidth($storeConsolidationRequest->offsetGet('width'));
        $shipment->setLength($storeConsolidationRequest->offsetGet('length'));

        return $shipment;
    }

    /**
     * @return WorkOrder
     */
    public function getWorkOrder()
    {
        return $this->workOrder;
    }

    /**
     * @param WorkOrder $workOrder
     */
    public function setWorkOrder(WorkOrder $workOrder)
    {
        $this->workOrder = $workOrder;
    }

    /**
     * @return Package
     */
    public function getPackage()
    {
        return $this->package;
    }

    /**
     * @param Package $package
     */
    public function setPackage(Package $package)
    {
        $this->package = $package;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $value
     */
    public function setLabel($value)
    {
        $this->label = $value;
    }

    /**
     * @return string
     */
    public function getTracking()
    {
        return $this->tracking;
    }

    /**
     * @param string $value
     */
    public function setTracking($value)
    {
        $this->tracking = $value;
    }

    /**
     * @return float
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @param $value
     */
    public function setWeight($value)
    {
        $this->weight = $value;
    }

    /**
     * @return float
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param $value
     */
    public function setHeight($value)
    {
        $this->height = $value;
    }

    /**
     * @return float
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @param $value
     */
    public function setWidth($value)
    {
        $this->width = $value;
    }

    /**
     * @return float
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * @param $value
     */
    public function setLength($value)
    {
        $this->length = $value;
    }

    /**
     * @return bool
     */
    public function isWorkOrderProcessed()
    {
        return ($this->workOrder && !empty($this->workOrder->package));
    }
}
