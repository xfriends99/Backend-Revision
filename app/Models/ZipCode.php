<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ZipCode
 * @package App\Models
 */
class ZipCode extends Model
{
    public $timestamps = false;

    protected $fillable = ['code', 'township_id'];

    protected $hidden = ['id'];

    public function township()
    {
        return $this->belongsTo(Township::class);
    }

}