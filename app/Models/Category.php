<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Override;

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



    #[Override]
    public function casts()
    {
        return [
            'is_available' => 'boolean',
        ];
    }
}
