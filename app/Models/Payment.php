<?php

namespace App\Models;

use Database\Factories\PaymentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $booking_id
 * @property string $payment_reference
 * @property string|null $transaction_id
 * @property float $amount
 * @property string $currency
 * @property string $method
 * @property string $status
 * @property array|null $payment_details
 * @property Carbon|null $paid_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Booking $booking
 */
#[Fillable([
    'booking_id',
    'payment_reference',
    'transaction_id',
    'amount',
    'currency',
    'method',
    'status',
    'payment_details',
    'paid_at'
])]
class Payment extends Model
{
    use HasFactory;

    // Constants
    public const METHOD_GCASH = 'gcash';
    public const METHOD_MAYA = 'maya';
    public const METHOD_CREDIT_CARD = 'credit_card';
    public const METHOD_BANK_TRANSFER = 'bank_transfer';
    public const METHOD_CASH = 'cash';

    public const STATUS_PENDING = 'pending';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_REFUNDED = 'refunded';

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'payment_details' => 'array',
            'paid_at' => 'datetime',
        ];
    }

    // Relationships
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    // Accessors
    public function getMethodLabelAttribute(): string
    {
        return [
            self::METHOD_GCASH => 'GCash',
            self::METHOD_MAYA => 'Maya',
            self::METHOD_CREDIT_CARD => 'Credit Card',
            self::METHOD_BANK_TRANSFER => 'Bank Transfer',
            self::METHOD_CASH => 'Cash',
        ][$this->method] ?? $this->method;
    }

    public function getStatusLabelAttribute(): string
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_FAILED => 'Failed',
            self::STATUS_REFUNDED => 'Refunded',
        ][$this->status] ?? $this->status;
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeForMethod($query, string $method)
    {
        return $query->where('method', $method);
    }

    // Methods
    public function complete(): bool
    {
        return $this->update([
            'status' => self::STATUS_COMPLETED,
            'paid_at' => now(),
        ]);
    }

    public function fail(): bool
    {
        return $this->update([
            'status' => self::STATUS_FAILED,
        ]);
    }

    public function refund(): bool
    {
        return $this->update([
            'status' => self::STATUS_REFUNDED,
        ]);
    }
}