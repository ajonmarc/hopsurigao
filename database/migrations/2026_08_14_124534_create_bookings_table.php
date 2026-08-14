<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();

            // Lead traveler / user who made the booking
            $table->foreignId('user_id')
                  ->constrained()
                  ->cascadeOnDelete();

            // Specific package schedule/date
            $table->foreignId('tour_date_id')
                  ->constrained('tour_dates')
                  ->restrictOnDelete();

            // Selected pickup location
            $table->foreignId('pickup_location_id')
                  ->constrained('pickup_locations')
                  ->restrictOnDelete();

            // Total number of travelers, including lead traveler
            $table->unsignedInteger('number_of_guests');

            // Booking-specific contact information
            $table->string('phone_number');

            $table->string('nationality');

            // Optional request
            $table->text('special_request')->nullable();

            $table->enum('booking_status', [
                'pending',
                'confirmed',
                'cancelled',
                'completed',
            ])->default('pending');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};