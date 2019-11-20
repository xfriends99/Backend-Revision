<?php

namespace App\Services\Purchases;

use App\Models\WeightUnit;

class WeightUnitConverter
{
    const OZ_KG = 0.0283495231;
    const LB_KG = 0.453592;

    /** @var WeightUnit */
    private $weightUnit;

    /** @var float */
    private $weight;

    /**
     * WeightUnitConverter constructor.
     * @param WeightUnit $weightUnit
     * @param $weight
     */
    public function __construct(WeightUnit $weightUnit, $weight)
    {
        $this->weightUnit = $weightUnit;
        $this->weight = $weight;
    }

    /**
     * @return float
     */
    public function getWeightAsKg()
    {
        if ($this->weightUnit->isOunce()) {
            return $this->weight * self::OZ_KG;
        } elseif ($this->weightUnit->isPound()) {
            return $this->weight * self::LB_KG;
        }

        return $this->weight;
    }
}
