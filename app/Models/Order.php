<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'order_state_id',
        'total_price',
        'total_shipping_charge'
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orderState()
    {
        return $this->belongsTo(OrderState::class);
    }

    public function orderAddress()
    {
        return $this->hasOne(OrderAddress::class);
    }

    public function orderedProducts()
    {
        return $this->hasMany(OrderedProduct::class);
    }
}
