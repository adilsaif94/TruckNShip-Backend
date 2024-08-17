<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
     // Specify the fillable fields to allow mass assignment
     protected $fillable = [
        'weight',
        'size',
        'pickup_location',
        'shipment_date',
        'shipment_time',
        'additional_info',
        'status', // Uncomment if you want to include the status field
        'user_id', // Assuming you have a user_id field to link the shipment to a user
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}

