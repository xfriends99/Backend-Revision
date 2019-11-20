<?php

namespace App\Models;

use App\Services\Purchases\WeightUnitConverter;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class WorkOrder
 * @package App\Models
 *
 * @property Collection $additionals
 * @property Collection $purchases
 * @property Package $package
 * @property Coupon $coupon
 * @property float $value
 * @property string $type
 * @property string $state
 * @property int $coupon_id
 */
class WorkOrder extends Model
{
    protected $fillable = ['value', 'type', 'state', 'service_id', 'coupon_id'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function additionals()
    {
        return $this->belongsToMany(Additional::class)->withPivot('value');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function package()
    {
        return $this->hasOne(Package::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function coupon()
    {
        return $this->hasOne(Coupon::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfShippable($query)
    {
        return $query->where('work_orders.type', 'SHIP');
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfConsolidatable($query)
    {
        return $query->where('work_orders.type', 'CONSOLIDATE');
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
            return $query->whereIn('work_orders.id', $id);
        } else {
            return !$id ? $query : $query->where('work_orders.id', $id);
        }
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $coupon_id
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfCouponId($query, $coupon_id)
    {
        if (is_array($coupon_id) && !empty($coupon_id)) {
            return $query->whereIn('work_orders.coupon_id', $coupon_id);
        } else {
            return !$coupon_id ? $query : $query->where('work_orders.coupon_id', $coupon_id);
        }
    }

    /**
     * @param string $date
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfCreatedAtBeforeThan($query, $date)
    {
        $date = Carbon::parse($date)->format('Y-m-d');

        return !$date ? $query : $query->where('work_orders.created_at', '<=', $date . '  23:59:59');
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $date
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfCreatedAtAfterThan($query, $date)
    {
        $date = Carbon::parse($date)->format('Y-m-d');

        return !$date ? $query : $query->where('work_orders.created_at', '>=', $date . ' 00:00:00');
    }

    /**
     * @return int
     */
    public function getPurchasesCount()
    {
        return $this->purchases->count();
    }

    /**
     * @return int
     */
    public function getProcessedPurchasesCount()
    {
        return $this->purchases->filter(function (Purchase $purchase) {
            return $purchase->isProcessed();
        })->count();
    }

    /**
     * @return int
     */
    public function getDeclaredValue()
    {
        return $this->purchases->sum('value');
    }

    /**
     * @return float
     */
    public function getAdditionalsTotalValue()
    {
        return $this->additionals->sum('pivot.value');
    }

    /**
     * @return bool
     */
    public function isConsolidateType()
    {
        return ($this->type == 'CONSOLIDATE');
    }

    /**
     * @return bool
     */
    public function isShipType()
    {
        return ($this->type == 'SHIP');
    }

    /**
     * @return null|string
     */
    public function getPackageTracking()
    {
        return $this->package ? $this->package->tracking : null;
    }

    /**
     * @return mixed|null
     */
    public function getPackageId()
    {
        return $this->package ? $this->package->id : null;
    }

    /**
     * @return User|null
     */
    public function getPackageUser()
    {
        return $this->package ? $this->package->user : null;
    }

    /**
     * @return string
     */
    public function getPurchasesDescription()
    {
        $purchases = $this->purchases;

        $descriptions = '';
        foreach ($purchases as $item) {
            $descriptions .= "({$item->getPurchaseItemsDescriptions()})";
        }

        return $descriptions;
    }

    /**
     * @return string
     */
    public function getPurchasesTracking()
    {
        $purchases = $this->purchases;

        $descriptions = collect();
        foreach ($purchases as $item) {
            $descriptions->push($item->tracking);
        }

        return $descriptions->implode(',');
    }

    /**
     * @return string
     */
    public function getAdditionalsDescriptions()
    {
        $additionals = $this->additionals;

        $descriptions = collect();
        foreach ($additionals as $item) {
            $descriptions->push($item->description_en);
        }

        return $descriptions->implode(',');
    }

    /**
     * @return float
     */
    public function getPurchasesWeightAsKg()
    {
        return $this->purchases->map(function (Purchase $purchase) {
            return (new WeightUnitConverter($purchase->weightUnit, $purchase->getWeight()))->getWeightAsKg();
        })->sum();
    }
}
