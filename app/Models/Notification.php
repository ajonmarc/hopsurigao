<?php

namespace App\Models;

use Database\Factories\NotificationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $type
 * @property string $title
 * @property string $message
 * @property array|null $data
 * @property string|null $icon
 * @property string|null $action_url
 * @property bool $is_read
 * @property Carbon|null $read_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read User $user
 */
#[Fillable([
    'user_id',
    'type',
    'title',
    'message',
    'data',
    'icon',
    'action_url',
    'is_read',
    'read_at'
])]
class Notification extends Model
{
    use HasFactory;

    // Constants
    public const TYPE_BOOKING_CONFIRMATION = 'booking_confirmation';
    public const TYPE_PAYMENT = 'payment';
    public const TYPE_REMINDER = 'reminder';
    public const TYPE_PROMOTION = 'promotion';
    public const TYPE_CANCELLATION = 'cancellation';

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'is_read' => 'boolean',
            'read_at' => 'datetime',
        ];
    }

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Accessors
    public function getTypeLabelAttribute(): string
    {
        return [
            self::TYPE_BOOKING_CONFIRMATION => 'Booking Confirmation',
            self::TYPE_PAYMENT => 'Payment',
            self::TYPE_REMINDER => 'Reminder',
            self::TYPE_PROMOTION => 'Promotion',
            self::TYPE_CANCELLATION => 'Cancellation',
        ][$this->type] ?? $this->type;
    }

    // Scopes
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    // Methods
    public function markAsRead(): bool
    {
        return $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    public function markAsUnread(): bool
    {
        return $this->update([
            'is_read' => false,
            'read_at' => null,
        ]);
    }
}