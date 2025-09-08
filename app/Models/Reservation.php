<?php
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
    public function getHasRatedAttribute() {
        return $this->is_rated;  // 这样 Blade 里就能用 $reservation->hasRated
    }
    // Scope for completed rentals
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}

?>