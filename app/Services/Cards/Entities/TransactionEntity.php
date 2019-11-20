<?php

namespace App\Services\Cards\Entities;


use App\Models\TransactionStatus;
use Carbon\Carbon;

class TransactionEntity
{
    /** @var TransactionStatus */
    protected $transactionStatus;

    /** @var int */
    protected $id;

    /** @var float */
    protected $amount;

    /** @var string|null */
    protected $details;

    /** @var Carbon $date */
    protected $date;

    /**
     * TransactionEntity constructor.
     * @param TransactionStatus $transactionStatus
     * @param $id
     * @param $amount
     * @param $details
     * @param Carbon|null $date
     */
    public function __construct(TransactionStatus $transactionStatus, $id, $amount, $details = null, $date = null)
    {
        $this->transactionStatus = $transactionStatus;
        $this->id = $id;
        $this->amount = $amount;
        $this->details = $details;
        $this->date = $date;
    }

    /**
     * @return TransactionStatus
     */
    public function getTransactionStatus()
    {
        return $this->transactionStatus;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @return string|null
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * @return Carbon|null
     */
    public function getDate()
    {
        return $this->date;
    }
}