<?php

namespace App\Services\Additionals\Calculate;

use App\Models\Additional;

class BubbleWrapCalculateService implements Calculate
{
    /** @var Additional  */
    private $additional;

    /** @var int */
    private $purchase_count;

    /**
     * BubbleWrapCalculateService constructor.
     * @param Additional $additional
     */
    public function __construct(Additional $additional)
    {
        $this->additional = $additional;
    }

    /**
     * @return float|int
     */
    public function getAmount()
    {
        return $this->additional->value;
    }

    /**
     * @param int $purchase_count
     * @return mixed
     */
    public function setPurchaseCount($purchase_count)
    {
        $this->purchase_count = $purchase_count;
    }
}