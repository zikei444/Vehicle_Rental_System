<?php
// STUDENT NAME: Kek Xin Ying
// STUDENT ID: 23WMR14547

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RatingLog extends Model
{
    protected $fillable = [
        'rating_id', 'customer_id', 'reply',
    ];
}
