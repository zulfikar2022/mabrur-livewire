<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Override;

class ProductVector extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'product_id',
        'category_id',
        'content',
        'embedding',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function categories()
    {
        return $this->belongsTo(Category::class);
    }

    #[Override]
    public function casts()
    {
        return [
            'embedding' => 'array',
        ];
    }

}
