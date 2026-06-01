<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderedProduct extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'order_id',
        'product_id',
        'unit_price',
        'quantity',
        'price',
        'shipping_charge'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
