<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Booking extends Model
{
    protected $fillable = [
        'user_id',
        'tour_date_id',
        'pickup_schedule_id',
        'number_of_guests',
        'phone_number',
        'nationality',
        'special_request',
        'booking_status',
        'qr_token',
    ];

    protected static function booted(): void
    {
        static::creating(function (Booking $booking) {
            // Every booking gets a unique, unguessable token
            // used to generate its QR code.
            if (empty($booking->qr_token)) {
                $booking->qr_token = (string) Str::uuid();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tourDate(): BelongsTo
    {
        return $this->belongsTo(TourDate::class);
    }

    public function pickupSchedule(): BelongsTo
    {
        return $this->belongsTo(PickupSchedule::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}