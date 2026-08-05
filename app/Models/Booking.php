<?php

namespace App\Models;

use Database\Factories\BookingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property int $user_id
 * @property int $schedule_id
 * @property int $tour_package_id
 * @property string $booking_reference
 * @property int $number_of_guests
 * @property float $total_price
 * @property string $status
 * @property string $payment_status
 * @property array|null $guest_details
 * @property array|null $special_requests
 * @property Carbon $booking_date
 * @property Carbon|null $confirmed_at
 * @property Carbon|null $cancelled_at
 * @property string|null $cancellation_reason
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read User $user
 * @property-read Schedule $schedule
 * @property-read TourPackage $tourPackage
 * @property-read Payment|null $payment
 * @property-read Review|null $review
 */
#[Fillable([
    'user_id',
    'schedule_id',
    'tour_package_id',
    'booking_reference',
    'number_of_guests',
    'total_price',
    'status',
    'payment_status',
    'guest_details',
    'special_requests',
    'booking_date',
    'confirmed_at',
    'cancelled_at',
    'cancellation_reason'
])]
class Booking extends Model
{
    use HasFactory;

    // Status Constants
    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_REFUNDED = 'refunded';

    public const PAYMENT_PENDING = 'pending';
    public const PAYMENT_PAID = 'paid';
    public const PAYMENT_FAILED = 'failed';
    public const PAYMENT_REFUNDED = 'refunded';

    protected function casts(): array
    {
        return [
            'guest_details' => 'array',
            'special_requests' => 'array',
            'total_price' => 'decimal:2',
            'booking_date' => 'date',
            'confirmed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    // Boot method to generate booking reference
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($booking) {
            $booking->booking_reference = self::generateBookingReference();
            $booking->booking_date = $booking->booking_date ?? now()->toDateString();
        });
    }

    // Generate unique booking reference
    public static function generateBookingReference(): string
    {
        do {
            $reference = 'HOP' . strtoupper(Str::random(8));
        } while (self::where('booking_reference', $reference)->exists());

        return $reference;
    }

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    public function tourPackage(): BelongsTo
    {
        return $this->belongsTo(TourPackage::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    // Accessors
    public function getCanBeCancelledAttribute(): bool
    {
        $schedule = $this->schedule;
        $departureDateTime = $schedule->date . ' ' . $schedule->departure_time;

        return $this->status === self::STATUS_PENDING ||
            ($this->status === self::STATUS_CONFIRMED &&
                now()->diffInHours($departureDateTime) > 24);
    }

    public function getCancellationFeeAttribute(): float
    {
        $schedule = $this->schedule;
        $departureDateTime = $schedule->date . ' ' . $schedule->departure_time;
        $hoursBeforeDeparture = now()->diffInHours($departureDateTime);

        if ($hoursBeforeDeparture >= 48) {
            return 0;
        } elseif ($hoursBeforeDeparture >= 24) {
            return $this->total_price * 0.10;
        } elseif ($hoursBeforeDeparture >= 12) {
            return $this->total_price * 0.25;
        } else {
            return $this->total_price * 0.50;
        }
    }

    public function getStatusLabelAttribute(): string
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_CONFIRMED => 'Confirmed',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_REFUNDED => 'Refunded',
        ][$this->status] ?? $this->status;
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        return [
            self::PAYMENT_PENDING => 'Pending',
            self::PAYMENT_PAID => 'Paid',
            self::PAYMENT_FAILED => 'Failed',
            self::PAYMENT_REFUNDED => 'Refunded',
        ][$this->payment_status] ?? $this->payment_status;
    }

    public function getInvoiceNumberAttribute(): string
    {
        return 'INV-' . str_pad($this->id, 6, '0', STR_PAD_LEFT);
    }

    // Scopes
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForOperator($query, int $operatorId)
    {
        return $query->whereHas('tourPackage', function ($q) use ($operatorId) {
            $q->where('operator_id', $operatorId);
        });
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', self::STATUS_CONFIRMED);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeUpcoming($query)
    {
        return $query->whereHas('schedule', function ($q) {
            $q->where('date', '>=', now()->toDateString());
        });
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', self::PAYMENT_PAID);
    }

    // Methods
    public function confirm(): bool
    {
        return $this->update([
            'status' => self::STATUS_CONFIRMED,
            'confirmed_at' => now(),
        ]);
    }

    public function complete(): bool
    {
        return $this->update([
            'status' => self::STATUS_COMPLETED,
        ]);
    }

    public function cancel(string $reason = null): bool
    {
        // Release slots back to schedule
        $this->schedule->releaseSlots($this->number_of_guests);

        return $this->update([
            'status' => self::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
        ]);
    }

    public function markPaymentPaid(): bool
    {
        return $this->update([
            'payment_status' => self::PAYMENT_PAID,
        ]);
    }

    public function markPaymentFailed(): bool
    {
        return $this->update([
            'payment_status' => self::PAYMENT_FAILED,
        ]);
    }
}