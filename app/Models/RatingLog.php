<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RatingLog extends Model
{
    protected $fillable = [
        'rating_id', 'customer_id', 'reply',
    ];
}
