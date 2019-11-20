<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Town
 * @package App\Models
 *
 * @propertt string $name
 * @property State $state
 */
class Town extends Model
{
    public $timestamps = false;

    protected $fillable = ['name', 'state_id'];

    protected $hidden = ['id', 'state_id'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function townships()
    {
        return $this->hasMany(Township::class);
    }
}