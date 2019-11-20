<?php

namespace App\Services\CostsEstimates;


class CostEntity
{
    /**
     * @var string
     */
    private $title;

    /**
     * @var float
     */
    private $amount;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $classification;


    /**
     * CostCouponEntity constructor.
     * @param string $title
     * @param float $amount
     * @param string $type
     * @param string|null $classification
     */
    public function __construct(string $title, float $amount, string $type, string $classification = null)
    {
        $this->title = $title;
        $this->amount = $amount;
        $this->type = $type;
        $this->classification = $classification;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return float
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     */
    public function setAmount(float $amount): void
    {
        $this->amount = $amount;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return null|string
     */
    public function getClassification()
    {
        return $this->classification;
    }

    /**
     * @param string $classification
     */
    public function setClassification(string $classification): void
    {
        $this->classification = $classification;
    }
}