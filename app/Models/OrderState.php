<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderState extends Model
{
    use SoftDeletes;
    public const PENDING = 'pending';
    public const APPROVED = 'approved';
    public const CANCELLED = 'cancelled';
    public const SHIPPED = 'shipped';
    public const DELIVERED = 'delivered';
    public const DELIVER_FAILED = 'deliver_failed';
    public const RETURNED = 'returned';
    protected $fillable = [
        'name',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
