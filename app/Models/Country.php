<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Country
 * @package App\Models
 *
 * @property string $name
 * @property string $code
 * @property boolean $tenant
 */
class Country extends Model
{
    protected $fillable = ['name', 'code', 'tenant'];

    public $timestamps = false;

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $code
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfCode($query, $code)
    {
        if (is_array($code)) {
            return $query->whereIn('countries.code', $code);
        } else {
            $query->where('countries.code', $code);
        }
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfTenant($query)
    {
        return $query->where('countries.tenant', true);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfNotTenant($query)
    {
        return $query->where('countries.tenant', false);
    }

    /**
     * @return bool
     */
    public function isArgentina()
    {
        return $this->code == 'AR';
    }

    /**
     * @return bool
     */
    public function isBrazil()
    {
        return $this->code == 'BR';
    }

    /**
     * @return bool
     */
    public function isChile()
    {
        return $this->code == 'CL';
    }

    /**
     * @return bool
     */
    public function isColombia()
    {
        return $this->code == 'CO';
    }

    /**
     * @return bool
     */
    public function isEcuador()
    {
        return $this->code == 'EC';
    }

    /**
     * @return bool
     */
    public function isMexico()
    {
        return $this->code == 'MX';
    }

    /**
     * @return bool
     */
    public function isPeru()
    {
        return $this->code == 'PE';
    }
}
