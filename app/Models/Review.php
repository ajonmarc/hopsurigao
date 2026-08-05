<?php

namespace App\Models;

use Database\Factories\ReviewFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int $tour_package_id
 * @property int $booking_id
 * @property int $rating
 * @property string|null $comment
 * @property array|null $images
 * @property array|null $ratings_breakdown
 * @property bool $is_verified
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read User $user
 * @property-read TourPackage $tourPackage
 * @property-read Booking $booking
 */
#[Fillable([
    'user_id',
    'tour_package_id',
    'booking_id',
    'rating',
    'comment',
    'images',
    'ratings_breakdown',
    'is_verified'
])]
class Review extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'images' => 'array',
            'ratings_breakdown' => 'array',
            'is_verified' => 'boolean',
            'rating' => 'integer',
        ];
    }

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tourPackage(): BelongsTo
    {
        return $this->belongsTo(TourPackage::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    // Accessors
    public function getImageUrlsAttribute(): array
    {
        return array_map(function ($image) {
            return asset('storage/' . $image);
        }, $this->images ?? []);
    }

    // Scopes
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeHighRating($query, int $minRating = 4)
    {
        return $query->where('rating', '>=', $minRating);
    }

    public function scopeForTour($query, int $tourId)
    {
        return $query->where('tour_package_id', $tourId);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}