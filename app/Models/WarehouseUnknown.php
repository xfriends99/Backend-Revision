<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

/**
 * Class WarehouseUnknown
 * @package App\Models
 *
 * @property string $tracking
 * @property boolean $found
 * @property string $details
 * @property string $created_at
 */
class WarehouseUnknown extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tracking',
        'found',
        'details',
        'created_at'
    ];

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $date
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfCreatedAtBeforeThan($query, $date)
    {
        $date = Carbon::parse($date)->format('Y-m-d');
        return !$date ? $query : $query->where('warehouse_unknowns.created_at', '<=', $date . '  23:59:59');
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $date
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfCreatedAtAfterThan($query, $date)
    {
        $date = Carbon::parse($date)->format('Y-m-d');
        return !$date ? $query : $query->where('warehouse_unknowns.created_at', '>=', $date . ' 00:00:00');
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $id
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfId($query, $id)
    {
        if (is_array($id) && !empty($id)) {
            return $query->whereIn('warehouse_unknowns.id', $id);
        } else {
            return !$id ? $query : $query->where('warehouse_unknowns.id', $id);
        }
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $tracking
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfTracking($query, $tracking)
    {
        return is_null($tracking) ? $query : $query->where('warehouse_unknowns.tracking', $tracking);
    }
}
