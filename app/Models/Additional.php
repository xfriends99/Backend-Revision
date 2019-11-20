<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Additional
 * @package App\Models
 *
 * @property string $description_en
 * @property string $description_es
 * @property float $value
 * @property string $key
 * @property boolean $active
 */
class Additional extends Model
{
    /** @var float */
    private $amount;

    protected $fillable = [
        'description_en', 
        'description_es', 
        'value', 
        'key', 
        'required', 
        'active',
        'external_key'
    ];

    public function additionalOptions()
    {
        return $this->hasMany(AdditionalOption::class);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param boolean $active
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfActive($query, $active)
    {
        return is_null($active) ? $query : $query->where('additionals.active', $active);
    }

    public function scopeOfKey($query, $key)
    {
        return is_null($key) ? $query : $query->where('additionals.key', $key);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param boolean $required
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfRequired($query, $required)
    {
        return is_null($required) ? $query : $query->where('additionals.required', $required);
    }

    /**
     * @param $amount
     */
    public function setAmount($amount): void
    {
        $this->amount = $amount;
    }

    /**
     * @return float
     */
    public function getAmount(): float
    {
        return $this->amount;
    }
}
