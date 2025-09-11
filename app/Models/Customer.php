<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Notifications\Notifiable; 
class Customer extends Model
{
    use Notifiable;//observer pattern
    protected $table = 'customers';

    protected $fillable = [
        'user_id',
        'phoneNo',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }
}
