<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class AdditionalOption
 * @package App\Models
 *
 * @property int $warehouse_id
 * @property int $country_id
 * @property int $platform_id
 * @property int $additional_id
 */
class AdditionalOption extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['warehouse_id', 'country_id', 'platform_id', 'additional_id', 'enabled'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'enabled' => 'boolean'
    ];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function platform()
    {
        return $this->belongsTo(Platform::class);
    }

    public function additional()
    {
        return $this->belongsTo(Additional::class);
    }
}
