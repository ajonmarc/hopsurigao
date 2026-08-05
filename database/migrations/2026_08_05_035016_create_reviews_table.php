<?php
// database/migrations/[timestamp]_create_reviews_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tour_package_id')->constrained()->cascadeOnDelete();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->integer('rating')->unsigned()->between(1, 5);
            $table->text('comment')->nullable();
            $table->json('images')->nullable();
            $table->json('ratings_breakdown')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->timestamps();
            
            // Ensure one review per booking
            $table->unique(['user_id', 'booking_id']);
            $table->index(['tour_package_id', 'rating']);
            $table->index('is_verified');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};