<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Site
 * @package App\Models
 *
 * @property Country $country
 * @property Platform $platform
 * @property boolean $default
 * @property string $locale
 */
class Site extends Model
{
    use SoftDeletes;

    protected $fillable = ['platform_id', 'country_id', 'default', 'locale'];

    protected $hidden = ['id'];

    protected $casts = [
        'default' => 'boolean'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function platform()
    {
        return $this->belongsTo(Platform::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $id
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function scopeOfPlatformId($query, $id)
    {
        return $query->where('sites.platform_id', $id);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $id
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function scopeOfCountryId($query, $id)
    {
        return $query->where('sites.country_id', $id);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function scopeOfDefault($query)
    {
        return $query->where('sites.default', true);
    }

    /**
     * @return string|null
     */
    public function getCountryName()
    {
        return $this->country ? $this->country->name : null;
    }

    /**
     * @return string|null
     */
    public function getCountryCode()
    {
        return $this->country ? $this->country->code : null;
    }

    /**
     * @return bool
     */
    public function isCountryArgentina()
    {
        return $this->country ? $this->country->isArgentina() : false;
    }

    /**
     * @return bool
     */
    public function isCountryBrazil()
    {
        return $this->country ? $this->country->isBrazil() : false;
    }

    /**
     * @return bool
     */
    public function isCountryChile()
    {
        return $this->country ? $this->country->isChile() : false;
    }

    /**
     * @return bool
     */
    public function isCountryColombia()
    {
        return $this->country ? $this->country->isColombia() : false;
    }

    /**
     * @return bool
     */
    public function isCountryEcuador()
    {
        return $this->country ? $this->country->isEcuador() : false;
    }

    /**
     * @return bool
     */
    public function isCountryMexico()
    {
        return $this->country ? $this->country->isMexico() : false;
    }

    /**
     * @return bool
     */
    public function isCountryPeru()
    {
        return $this->country ? $this->country->isPeru() : false;
    }
}
