<?php

namespace App\Models;

use Database\Factories\TourPackageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $operator_id
 * @property string $name
 * @property string $description
 * @property string $cover_image
 * @property array|null $images
 * @property float $price
 * @property int $duration_hours
 * @property int $max_capacity
 * @property array|null $inclusions
 * @property array|null $exclusions
 * @property array|null $meeting_point
 * @property bool $is_active
 * @property bool $is_featured
 * @property int $view_count
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read User $operator
 * @property-read \Illuminate\Database\Eloquent\Collection|Schedule[] $schedules
 * @property-read \Illuminate\Database\Eloquent\Collection|Booking[] $bookings
 * @property-read \Illuminate\Database\Eloquent\Collection|Review[] $reviews
 */
#[Fillable([
    'operator_id',
    'name',
    'description',
    'cover_image',
    'images',
    'price',
    'duration_hours',
    'max_capacity',
    'inclusions',
    'exclusions',
    'meeting_point',
    'is_active',
    'is_featured',
    'view_count'
])]
class TourPackage extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'images' => 'array',
            'inclusions' => 'array',
            'exclusions' => 'array',
            'meeting_point' => 'array',
            'price' => 'decimal:2',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'view_count' => 'integer',
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

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    // Accessors
    public function getAverageRatingAttribute(): float
    {
        return round($this->reviews()->avg('rating') ?? 0, 1);
    }

    public function getReviewCountAttribute(): int
    {
        return $this->reviews()->count();
    }

    public function getTotalBookingsAttribute(): int
    {
        return $this->bookings()->count();
    }

    public function getTotalRevenueAttribute(): float
    {
        return $this->bookings()
            ->where('status', 'completed')
            ->sum('total_price') ?? 0;
    }

    public function getAvailableSchedulesAttribute()
    {
        return $this->schedules()
            ->where('date', '>=', now()->toDateString())
            ->where('status', 'available')
            ->get();
    }

    public function getCoverImageUrlAttribute(): string
    {
        return asset('storage/' . $this->cover_image);
    }

    public function getImageUrlsAttribute(): array
    {
        return array_map(function ($image) {
            return asset('storage/' . $image);
        }, $this->images ?? []);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeForOperator($query, int $operatorId)
    {
        return $query->where('operator_id', $operatorId);
    }

    public function scopePriceRange($query, float $min, float $max)
    {
        return $query->whereBetween('price', [$min, $max]);
    }

    // Methods
    public function incrementViews(): void
    {
        $this->increment('view_count');
    }
}