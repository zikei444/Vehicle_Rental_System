<?php

// STUDENT NAME: LIEW ZI KEI 
// STUDENT ID: 23WMR14570

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Customer;
use App\Models\Vehicle;
use App\Models\Rating;

class Reservation extends Model
{
    protected $fillable = [
        'customer_id', 'vehicle_id', 'pickup_date', 'return_date',
        'days', 'total_cost', 'payment_method', 'status'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function rating()
    {
        return $this->hasOne(Rating::class);
    }

    // Scope for completed rentals
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
    public function hasRated(): bool
    {

     $customerId = $this->customer_id;
    // vehicle for a reservation hasrated
    return Rating::where('reservation_id', $this->id)
                 ->where('customer_id', $this->customer_id)// 没登录先用 1
                 ->exists();
    }
}

?>