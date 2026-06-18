<?php

namespace App\Models;

use App\Observers\CategoryObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Override;

#[ObservedBy([CategoryObserver::class])]
class Category extends Model
{
    use SoftDeletes;
    protected $fillable = [
      'name', 'deleted_at', 'is_available'
    ];


    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function categoryImage()
    {
        return $this->hasOne(CategoryImage::class);
    }



    #[Override]
    public function casts()
    {
        return [
            'is_available' => 'boolean',
        ];
    }
}
