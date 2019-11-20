<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Package
 * @package App\Models
 *
 * @property User $user
 * @property WorkOrder $workOrder
 * @property Invoice $invoice
 * @property string $tracking
 * @property float $value
 * @property float $weight
 * @property float $width
 * @property float $height
 * @property float $length
 * @property string $id_prealert
 */
class Package extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'tracking',
        'state',
        'value',
        'weight',
        'width',
        'height',
        'length',
        'user_id',
        'invoice_id',
        'work_order_id',
        'id_prealert'
    ];

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
    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $id
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfUserId($query, $id)
    {
        if (is_array($id) && !empty($id)) {
            return $query->whereIn('packages.user_id', $id);
        } else {
            return !$id ? $query : $query->where('packages.user_id', $id);
        }
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $tracking
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfTracking($query, $tracking)
    {
        if (is_array($tracking) && !empty($tracking)) {
            return $query->whereIn('packages.tracking', $tracking);
        } else {
            return !$tracking ? $query : $query->where('packages.tracking', $tracking);
        }
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $state
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfState($query, $state)
    {
        if (is_array($state) && !empty($state)) {
            return $query->whereIn('packages.state', $state);
        } else {
            return !$state ? $query : $query->where('packages.state', $state);
        }
    }

    /**
     * @return string|null
     */
    public function getUserLockerCode()
    {
        return $this->user ? $this->user->getLockerCode() : null;
    }

    /**
     * @return Card|null
     */
    public function getUserDefaultCard()
    {
        return $this->user ? $this->user->getDefaultCard() : null;
    }

    /**
     * @return mixed|null
     */
    public function getUserExternalId()
    {
        return $this->user ? $this->user->external_id : null;
    }

    /**
     * @return \Illuminate\Support\Collection|null
     */
    public function getWorkOrderPurchases()
    {
        return $this->workOrder ? $this->workOrder->purchases : null;
    }

    /**
     * @return int
     */
    public function getWorkOrderPurchasesCount()
    {
        return $this->workOrder ? $this->workOrder->getPurchasesCount() : 0;
    }

    public function getWorkOrderAdditionals()
    {
        return $this->workOrder ? $this->workOrder->additionals : null;
    }

    /**
     * @return float
     */
    public function getWorkOrderAdditionalsTotalValue()
    {
        return $this->workOrder ? $this->workOrder->getAdditionalsTotalValue() : 0.0;
    }

    /**
     * @return bool
     */
    public function hasCoupon()
    {
        if ($this->workOrder) {
            return $this->workOrder->coupon ? true : false;
        }
        return false;        
    }

    /**
     * @return Platform|null
     */
    public function getUserPlatform()
    {
        return $this->user ? $this->user->platform : null;
    }

    /**
     * @return bool
     */
    public function wasInvoiced()
    {
        if (!$invoice = $this->invoice) {
            return false;
        }

        return $invoice->isApproved();
    }

    /**
     * @return bool
     */
    public function invoiceExceededAttemptsWithoutCard()
    {
        return $this->invoice ? $this->invoice->exceededAttemptsWithoutCard() : false;
    }
}
