<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackagePrealert extends Model
{
    protected $fillable = ['package_id', 'http_request_id'];

    public function package()
    {
        return $this->belongsToMany(Package::class);
    }

    public function httpRequest()
    {
        return $this->belongsToMany(HttpRequest::class);
    }
}
