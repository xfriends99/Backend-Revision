<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

/**
 * Class Purchase
 * @package App\Models
 *
 * @property User $user
 * @property Marketplace $marketplace
 * @property Address $address
 * @property Warehouse $warehouse
 * @property Collection $preAlerts
 * @property Collection $purchaseCheckpoints
 * @property WorkOrder $workOrder
 * @property float $value
 * @property float $weight
 * @property string $carrier
 * @property string $tracking
 * @property string $invoice_url
 * @property string $type
 * @property string $state
 * @property Carbon $purchased_at
 * @property bool $is_mobile_device
 */
class Purchase extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'marketplace_id',
        'user_id',
        'address_id',
        'warehouse_id',
        'package_id',
        'work_order_id',
        'value',
        'weight',
        'carrier',
        'tracking',
        'invoice_url',
        'type',
        'state',
        'purchased_at',
        'is_mobile_device',
        'weight_unit_id'
    ];

    protected $dates = [
        'purchased_at'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function preAlerts()
    {
        return $this->belongsToMany(PackagePrealert::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function marketplace()
    {
        return $this->belongsTo(Marketplace::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function purchaseCheckpoints()
    {
        return $this->hasMany(PurchaseCheckpoint::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function weightUnit()
    {
        return $this->belongsTo(WeightUnit::class);
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
            return $query->whereIn('purchases.id', $id);
        } else {
            return !$id ? $query : $query->where('purchases.id', $id);
        }
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $id
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfUserId($query, $id)
    {
        if (is_array($id) && !empty($id)) {
            return $query->whereIn('purchases.user_id', $id);
        } else {
            return !$id ? $query : $query->where('purchases.user_id', $id);
        }
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $id
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfWorkOrderId($query, $id)
    {
        if (is_array($id) && !empty($id)) {
            return $query->whereIn('purchases.work_order_id', $id);
        } else {
            return !$id ? $query : $query->where('purchases.work_order_id', $id);
        }
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $id
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfAddressId($query, $id)
    {
        if (is_array($id) && !empty($id)) {
            return $query->whereIn('purchases.address_id', $id);
        } else {
            return !$id ? $query : $query->where('purchases.address_id', $id);
        }
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfTracking($query, $value)
    {
        if (is_array($value) && !empty($value)) {
            return $query->whereIn('purchases.tracking', $value);
        } else {
            return !$value ? $query : $query->where('purchases.tracking', $value);
        }
    }


    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $id
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfMarketplaceId($query, $id)
    {
        if (is_array($id) && !empty($id)) {
            return $query->whereIn('purchases.marketplace_id', $id);
        } else {
            return !$id ? $query : $query->where('purchases.marketplace_id', $id);
        }
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $id
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfWarehouseId($query, $id)
    {
        if (is_array($id) && !empty($id)) {
            return $query->whereIn('purchases.warehouse_id', $id);
        } else {
            return !$id ? $query : $query->where('purchases.warehouse_id', $id);
        }
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $date
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfCreatedAtBeforeThan($query, $date)
    {
        $date = Carbon::parse($date)->format('Y-m-d');
        return !$date ? $query : $query->where('purchases.created_at', '<=', $date . '  23:59:59');
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $date
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfCreatedAtAfterThan($query, $date)
    {
        $date = Carbon::parse($date)->format('Y-m-d');
        return !$date ? $query : $query->where('purchases.created_at', '>=', $date . ' 00:00:00');
    }


    /**
     * @return string|null
     */
    public function getUserLockerCode()
    {
        return $this->user ? $this->user->getLockerCode() : null;
    }

    /**
     * @return float
     */
    public function getWeight()
    {
        return $this->purchaseItems->sum(function (PurchaseItem $purchaseItem) {
            return floatval($purchaseItem->quantity * $purchaseItem->weight);
        });
    }

    /**
     * @return string|null
     */
    public function getAddressCountryCode()
    {
        return $this->address ? $this->address->getCountryCode() : null;
    }

    /**
     * @return null|string
     */
    public function getMarketplaceName()
    {
        return $this->marketplace ? $this->marketplace->name : null;
    }

    /**
     * @return null|string
     */
    public function getMarketplaceCode()
    {
        return $this->marketplace ? $this->marketplace->code : null;
    }

    /**
     * @return null|string
     */
    public function getWarehouseCountryCode()
    {
        return $this->warehouse ? $this->warehouse->getCountryCode() : null;
    }

    /**
     * @return string
     */
    public function getPurchaseItemsDescriptions()
    {
        $items = $this->purchaseItems;

        $descriptions = '';
        foreach ($items as $item) {
            $descriptions .= "[{$item->description}]";
        }

        return $descriptions;
    }

    /**
     * @return number
     */
    public function getPurchaseItemsCount()
    {
        return $this->purchaseItems->count();
    }

    /**
     * @return string|null
     */
    public function getWorkOrderPackageTracking()
    {
        return $this->workOrder ? $this->workOrder->getPackageTracking() : null;
    }

    /**
     * @return int|null
     */
    public function getWorkOrderPackageId()
    {
        return $this->workOrder ? $this->workOrder->getPackageId() : null;
    }

    /**
     * @return PurchaseCheckpoint|null
     */
    public function getLastCheckpoint()
    {
        return $this->purchaseCheckpoints ? $this->purchaseCheckpoints->sortByDesc('checkpoint_at')->first() : null;
    }

    /**
     * @return Country|null
     */
    public function getWarehouseCountry()
    {
        return $this->warehouse ? $this->warehouse->country : null;
    }

    /**
     * @return Country|null
     */
    public function getAddressCountry()
    {
        return $this->address ? $this->address->country : null;
    }

    /**
     * @return bool
     */
    public function isProcessed()
    {
        return $this->state == 'processed';
    }
}
