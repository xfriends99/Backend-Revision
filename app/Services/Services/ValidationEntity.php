<?php

namespace App\Services\Services;

use Illuminate\Support\Collection;

class ValidationEntity
{

    /** @var float */
    private $weight;

    /** @var array */
    private $items;

    /**
     * ValidationEntity constructor.
     * @param float $weight
     * @param Collection $items
     */
    public function __construct(float $weight, Collection $items)
    {
        $this->weight = $weight;
        $this->items = $items;
    }

    /**
     * @return float
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @return Collection
     */
    public function getItems()
    {
        return $this->items;
    }
}
