<?php

namespace App\Models;

use Database\Factories\ScheduleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $tour_package_id
 * @property int|null $boat_id
 * @property Carbon $date
 * @property string $departure_time
 * @property string|null $return_time
 * @property int $available_slots
 * @property int $booked_slots
 * @property string $status
 * @property array|null $special_notes
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read TourPackage $tourPackage
 * @property-read Boat|null $boat
 * @property-read \Illuminate\Database\Eloquent\Collection|Booking[] $bookings
 */
#[Fillable([
    'tour_package_id',
    'boat_id',
    'date',
    'departure_time',
    'return_time',
    'available_slots',
    'booked_slots',
    'status',
    'special_notes'
])]
class Schedule extends Model
{
    use HasFactory;

    public const STATUS_AVAILABLE = 'available';
    public const STATUS_FULL = 'full';
    public const STATUS_CANCELLED = 'cancelled';

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'departure_time' => 'datetime:H:i',
            'return_time' => 'datetime:H:i',
            'special_notes' => 'array',
            'available_slots' => 'integer',
            'booked_slots' => 'integer',
        ];
    }

    // Relationships
    public function tourPackage(): BelongsTo
    {
        return $this->belongsTo(TourPackage::class);
    }

    public function boat(): BelongsTo
    {
        return $this->belongsTo(Boat::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    // Accessors
    public function getIsAvailableAttribute(): bool
    {
        return $this->status === self::STATUS_AVAILABLE && $this->available_slots > 0;
    }

    public function getFormattedDateTimeAttribute(): string
    {
        return $this->date->format('M d, Y') . ' at ' . $this->departure_time->format('g:i A');
    }

    public function getOccupancyRateAttribute(): float
    {
        $total = $this->available_slots + $this->booked_slots;
        return $total > 0 ? round(($this->booked_slots / $total) * 100, 2) : 0;
    }

    public function getStatusLabelAttribute(): string
    {
        return [
            self::STATUS_AVAILABLE => 'Available',
            self::STATUS_FULL => 'Fully Booked',
            self::STATUS_CANCELLED => 'Cancelled',
        ][$this->status] ?? $this->status;
    }

    // Scopes
    public function scopeAvailable($query)
    {
        return $query->where('status', self::STATUS_AVAILABLE)
            ->where('available_slots', '>', 0)
            ->where('date', '>=', now()->toDateString());
    }

    public function scopeForTour($query, int $tourId)
    {
        return $query->where('tour_package_id', $tourId);
    }

    public function scopeForDate($query, string $date)
    {
        return $query->where('date', $date);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('date', '>=', now()->toDateString())
            ->orderBy('date', 'asc');
    }

    // Methods
    public function reserveSlots(int $count): bool
    {
        if ($this->available_slots < $count) {
            return false;
        }

        $this->available_slots -= $count;
        $this->booked_slots += $count;

        if ($this->available_slots <= 0) {
            $this->status = self::STATUS_FULL;
        }

        return $this->save();
    }

    public function releaseSlots(int $count): bool
    {
        $this->available_slots += $count;
        $this->booked_slots -= $count;

        if ($this->status === self::STATUS_FULL && $this->available_slots > 0) {
            $this->status = self::STATUS_AVAILABLE;
        }

        return $this->save();
    }
}