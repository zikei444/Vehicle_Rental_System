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
        'admin_id',
        'rating',
        'feedback',
        'status', // include new status column
    ];

    /**
     * Scope for approved ratings
     */

}
