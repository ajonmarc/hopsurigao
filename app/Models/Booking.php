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
        'pickup_location_id',
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
            // Every booking gets a unique, unguessable token used to
            // generate its QR code. Generated here (not in the
            // controller) so it's set no matter which controller or
            // seeder creates the booking.
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

    public function pickupLocation(): BelongsTo
    {
        return $this->belongsTo(PickupLocation::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}