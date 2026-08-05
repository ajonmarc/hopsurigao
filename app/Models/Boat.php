<?php

namespace App\Models;

use Database\Factories\BoatFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $operator_id
 * @property string $name
 * @property string $registration_number
 * @property string $type
 * @property int $capacity
 * @property array|null $amenities
 * @property string|null $image
 * @property string $status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read User $operator
 * @property-read \Illuminate\Database\Eloquent\Collection|Schedule[] $schedules
 */
#[Fillable([
    'operator_id',
    'name',
    'registration_number',
    'type',
    'capacity',
    'amenities',
    'image',
    'status'
])]
class Boat extends Model
{
    use HasFactory;

    // Constants
    public const TYPES = ['pump_boat', 'speedboat', 'yacht', 'banca'];
    public const STATUS_AVAILABLE = 'available';
    public const STATUS_MAINTENANCE = 'maintenance';
    public const STATUS_BOOKED = 'booked';

    protected function casts(): array
    {
        return [
            'amenities' => 'array',
            'capacity' => 'integer',
        ];
    }

    // Relationships
    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    // Accessors
    public function getStatusLabelAttribute(): string
    {
        return [
            self::STATUS_AVAILABLE => 'Available',
            self::STATUS_MAINTENANCE => 'Under Maintenance',
            self::STATUS_BOOKED => 'Booked',
        ][$this->status] ?? $this->status;
    }

    public function getTypeLabelAttribute(): string
    {
        return [
            'pump_boat' => 'Pump Boat',
            'speedboat' => 'Speedboat',
            'yacht' => 'Yacht',
            'banca' => 'Banca',
        ][$this->type] ?? $this->type;
    }

    public function getImageUrlAttribute(): string
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }

    // Scopes
    public function scopeAvailable($query)
    {
        return $query->where('status', self::STATUS_AVAILABLE);
    }

    public function scopeForOperator($query, int $operatorId)
    {
        return $query->where('operator_id', $operatorId);
    }
}