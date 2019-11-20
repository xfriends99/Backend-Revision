<?php

namespace App\Services\Additionals\Calculate;

use App\Models\Additional;
use Illuminate\Support\Collection;

interface Calculate
{
    public function __construct(Additional $additional);

    /**
     * @return float
     */
    public function getAmount();

    /**
     * @param int $purchase_count
     * @return mixed
     */
    public function setPurchaseCount($purchase_count);
}