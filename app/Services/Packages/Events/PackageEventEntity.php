<?php

namespace App\Services\Packages\Events;

use Carbon\Carbon;

class PackageEventEntity
{
    /** @var string */
    private $date;

    /** @var string */
    private $category;

    /** @var string */
    private $code;

    /** @var string */
    private $description;

    /** @var string */
    private $description_alt;

    /** @var string */
    private $received_by;

    /** @var string */
    private $office;

    /** @var string */
    private $city;

    /** @var array */
    private $notify_events_list = [
        'WH-1',
        'WH-2',
        'WH-3',
        'TD-1',
        'TD-2',
        'TD-3',
        'TD-4',
        'AR-1',
        'AR-2',
        'PD-1'
    ];

    /**
     * @param array $attributes
     * @return $this
     */
    public function initialize(array $attributes = [])
    {
        foreach ($attributes as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }

        return $this;
    }

    /**
     * @param $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * @param $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }

    /**
     * @param $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @param $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @param $description_alt
     */
    public function setDescriptionAlt($description_alt)
    {
        $this->description_alt = $description_alt;
    }

    /**
     * @param v $received_by
     */
    public function setReceivedBy(v$received_by)
    {
        $this->received_by = $received_by;
    }

    /**
     * @param $office
     */
    public function setOffice($office)
    {
        $this->office = $office;
    }

    /**
     * @param $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * @return string
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getDescriptionAlt()
    {
        return $this->description_alt;
    }

    /**
     * @return string
     */
    public function getReceivedBy()
    {
        return $this->received_by;
    }

    /**
     * @return string
     */
    public function getOffice()
    {
        return $this->office;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @return bool
     */
    public function isDelivered()
    {
        return $this->code === 'PF-1';
    }

    /**
     * @return null|string
     */
    public function getParseDate()
    {
        return $this->date ? Carbon::parse($this->date)->format('Y-m-d\Th:i:s') : null;
    }

    /**
     * @return bool
     */
    public function isEventToNotify()
    {
        $code = $this->getCode();

        if(in_array($code, $this->notify_events_list)){
            return true;
        }

        return false;
    }
}