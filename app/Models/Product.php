<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Override;
use Illuminate\Support\Str;
use App\Observers\ProductObserver;

#[ObservedBy([ProductObserver::class])]

class Product extends Model
{
    use SoftDeletes;
    //

    protected $fillable = [
        'category_id',
        'name',
        'description',
        'price',
        'sell_by_piece',
        'sell_by_weight',
        'price_per_piece',
        'price_per_kg',
        'is_available',
        'available_quantity',
    ];

    public function productVectors(): HasMany
    {
        return $this->hasMany(ProductVector::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function productImages(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }

    public function orderedProducts(): HasMany
    {
        return $this->hasMany(OrderedProduct::class);
    }

    public function nameModifier()
    {
        return Str::slug($this->name, '-', app()->getLocale());
    }

    #[Override]
    public function casts()
    {
        return [
            'is_available' => 'boolean',
            'available_quantity' => 'decimal:2',
        ];
    }


}
