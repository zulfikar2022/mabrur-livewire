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

    public function calculatePrice()
    {
        $this->price = $this->unit_price * $this->quantity;
        $this->save();
    }

    public function calculateShippingCharge($destination, $isHomeDelivery = true)
    {
        $productsPerPieceWeight = $this->product->weight_per_piece;
        $totalWeight = $productsPerPieceWeight * $this->quantity;
        // Calculate shipping charge based on total weight and destination
    }
}
