<?php

namespace App\Models;

use App\Observers\ProductImageObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

// ProductImageObserver
#[ObservedBy([ProductImageObserver::class])]
class ProductImage extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'product_id',
        'image_link',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
