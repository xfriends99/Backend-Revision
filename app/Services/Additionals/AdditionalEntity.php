<?php

namespace App\Services\Additionals;

use App\Models\Additional;
use Illuminate\Support\Collection;

class AdditionalEntity
{
    /** @var Collection */
    private $additionals;

    /** @var float */
    private $amount;

    public function __construct()
    {
        $this->additionals = collect();
    }

    /**
     * @param Additional $additional
     */
    public function pushAdditional(Additional $additional)
    {
        $this->additionals->push($additional);
    }

    /**
     * @return Collection
     */
    public function getAdditionals()
    {
        return $this->additionals;
    }

    /**
     * @param $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

}