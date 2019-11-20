<?php

namespace App\Services\Cards\Entities\DLocal;

use App\Services\Cards\Entities\ProcessConfirmationEntity as BaseProcessConfirmationEntity;

class ProcessConfirmationEntity extends BaseProcessConfirmationEntity
{
    /** @var string */
    protected $id;

    /** @var string */
    protected $status;

    /** @var array */
    protected $details;

    /** @var string|null */
    protected $date;

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return array
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * @return null|string
     */
    public function getDate()
    {
        return $this->date;
    }

}