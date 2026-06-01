<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Division extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
    ];

    public function districts()
    {
        return $this->hasMany(District::class);
    }

    public function orderAddresses()
    {
        return $this->hasMany(OrderAddress::class);
    }
}
