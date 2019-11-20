<?php

namespace App\Services\Warehouses\Entities;

class UnknownPackage
{
    /** @var string */
    private $DescripcionCodigoRetencion;

    /** @var string */
    private $Service;

    /** @var string */
    private $AgentCode;

    /** @var string */
    private $Tracking;

    /** @var string */
    private $Taxes;

    /** @var string */
    private $DeclaredValue;

    /** @var string */
    private $RealWeight;

    /** @var string */
    private $InsuranceFFw;

    /** @var string */
    private $InsuranceAgent;

    /** @var string */
    private $Total;

    /** @var string */
    private $CancellationDate;

    /** @var string */
    private $SenderAddress;

    /** @var string */
    private $SenderCity;

    /** @var string */
    private $SenderCountry;

    /** @var string */
    private $SenderState;

    /** @var string */
    private $SenderZip;

    /** @var string */
    private $DestName;

    /** @var string */
    private $DestAddress;

    /** @var string */
    private $DestCity;

    /** @var string */
    private $DestCountry;

    /** @var string */
    private $DestState;

    /** @var string */
    private $DestZip;

    /** @var string */
    private $DestPhone;

    /** @var string */
    private $AgentName;

    /** @var string */
    private $AgentAddress;

    /** @var string */
    private $AgentPhone;

    /** @var string */
    private $Description;

    /** @var string */
    private $strMeasure;

    /** @var string */
    private $Id;

    /** @var string */
    private $WaybillNumber;

    /** @var string */
    private $ReceiptDate;

    /** @var string */
    private $Pieces;

    /** @var string */
    private $WeightLB;

    /** @var string */
    private $SenderName;

    /** @var string */
    private $SenderPhone;

    public function initialize(array $attributes = [])
    {
        foreach ($attributes as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    /**
     * @return string
     */
    public function getTracking()
    {
        return $this->Tracking;
    }

    /**
     * @return array
     */
    public function getProperties()
    {
        return get_object_vars($this);
    }
}