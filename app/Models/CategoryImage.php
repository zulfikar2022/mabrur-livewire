<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CategoryImage extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id',
        'image_link',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
