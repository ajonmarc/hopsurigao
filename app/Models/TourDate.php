<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TourDate extends Model
{
    protected $fillable = [
        'package_id',
        'tour_date',
        'capacity',
    ];

    protected function casts(): array
    {
        return [
            'tour_date' => 'date',
        ];
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function times(): HasMany
    {
        return $this->hasMany(TourTime::class);
    }


    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}