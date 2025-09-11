<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'vehicle_id',
        'reservation_id',
        'admin_id',
        'rating',
        'feedback',
        'status',
        'adminreply',
    ];
    public function reservation() {
        return $this->belongsTo(Reservation::class);
    }
    // 查询作用域
    public function scopeApproved($query) {
        return $query->where('status', 'approved');
    }

    public function scopePending($query) {
        return $query->where('status', 'pending');
    }

    public function scopeRejected($query) {
        return $query->where('status', 'rejected');
    }

    // 关联
    public function customer() {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function vehicle() {
        return $this->belongsTo(Vehicle::class);
    }
    
}
