<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PickupLocation extends Model
{
    protected $fillable = [
        'name',
        'address',
        'description',
        'status',
    ];

    public function pickupSchedules(): HasMany
    {
        return $this->hasMany(PickupSchedule::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}