<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class WeightUnit
 * @package App\Models
 *
 * @property int $id
 * @property string $name
 * @property string $code
 */
class WeightUnit extends Model
{
    public $timestamps = false;

    protected $fillable = ['name', 'code'];

    public function isKilogram()
    {
        return $this->code == 'KG';
    }

    public function isPound()
    {
        return $this->code == 'LB';
    }

    public function isOunce()
    {
        return $this->code == 'OZ';
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $code
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfCode($query, $code)
    {
        if (is_array($code)) {
            return $query->whereIn('weight_units.code', $code);
        } else {
            $query->where('weight_units.code', $code);
        }
    }

}
