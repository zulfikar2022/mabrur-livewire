<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Override;

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

    #[Override]
    public function casts()
    {
        return [
            'is_available' => 'boolean',
        ];
    }


}
