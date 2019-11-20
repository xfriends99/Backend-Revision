<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Service
 *
 * @property Country $originCountry
 * @property Country $destinationCountry
 * @property ServiceType $serviceType
 * @property int $id
 * @property int $origin_country_id
 * @property int $destination_country_id
 * @property string $code
 * @property string $name
 * @property string $description
 * @property boolean $enabled
 * @property float $max_weight
 * @property boolean $ddp
 */
class Service extends Model
{
    protected $fillable = [
        'origin_country_id',
        'destination_country_id',
        'service_type_id',
        'code',
        'name',
        'description',
        'enabled',
        'max_weight',
        'ddp'
    ];

    public function originCountry()
    {
        return $this->belongsTo(Country::class);
    }

    public function destinationCountry()
    {
        return $this->belongsTo(Country::class);
    }

    public function serviceType()
    {
        return $this->belongsTo(ServiceType::class);
    }

    public function scopeOfCode($query, $code)
    {
        return !$code ? $query : $query->where('services.code', $code);
    }

    public function scopeOfServiceTypeId($query, $id)
    {
        if (is_array($id) && !empty($id)) {
            return $query->whereIn('services.service_type_id', $id);
        } else {
            return !$id ? $query : $query->where('services.service_type_id', $id);
        }
    }

    public function scopeOfOriginCountryId($query, $id)
    {
        if (is_array($id) && !empty($id)) {
            return $query->whereIn('origin_country.id', $id);
        } else {
            return !$id ? $query : $query->where('origin_country.id', $id);
        }
    }

    public function scopeOfDestinationCountryId($query, $id)
    {
        if (is_array($id) && !empty($id)) {
            return $query->whereIn('destination_country.id', $id);
        } else {
            return !$id ? $query : $query->where('destination_country.id', $id);
        }
    }

    /**
     * @return null|string
     */
    public function getServiceTypeDescription()
    {
        return $this->serviceType ? $this->serviceType->description : null;
    }

    /**
     * @return null|string
     */
    public function getOriginCountryCode()
    {
        return $this->originCountry ? $this->originCountry->code : null;
    }

    /**
     * @return null|string
     */
    public function getOriginCountryName()
    {
        return $this->originCountry ? $this->originCountry->name : null;
    }

    /**
     * @return null|string
     */
    public function getDestinationCountryCode()
    {
        return $this->destinationCountry ? $this->destinationCountry->code : null;
    }

    /**
     * @return null|string
     */
    public function getDestinationCountryName()
    {
        return $this->destinationCountry ? $this->destinationCountry->name : null;
    }

    /**
     * @return bool
     */
    public function isServiceTypeCourier()
    {
        return $this->serviceType ? $this->serviceType->isCourier() : false;
    }

    /**
     * @return bool
     */
    public function isServiceTypePostal()
    {
        return $this->serviceType ? $this->serviceType->isPostal() : false;
    }
}
