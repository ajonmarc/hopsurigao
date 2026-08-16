<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PickupSchedule extends Model
{
    protected $fillable = [
        'tour_date_id',
        'pickup_location_id',
        'pickup_time',
    ];

    protected $casts = [
        'pickup_time' => 'datetime:H:i',
    ];

    public function tourDate(): BelongsTo
    {
        return $this->belongsTo(TourDate::class);
    }

    public function pickupLocation(): BelongsTo
    {
        return $this->belongsTo(PickupLocation::class);
    }
}