<?php
// database/migrations/[timestamp]_create_schedules_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_package_id')->constrained()->cascadeOnDelete();
            $table->foreignId('boat_id')->nullable()->constrained()->nullOnDelete();
            $table->date('date');
            $table->time('departure_time');
            $table->time('return_time')->nullable();
            $table->integer('available_slots');
            $table->integer('booked_slots')->default(0);
            $table->enum('status', ['available', 'full', 'cancelled'])->default('available');
            $table->json('special_notes')->nullable();
            $table->timestamps();
            
            // Composite unique index to prevent duplicate schedules
            $table->unique(['tour_package_id', 'date', 'departure_time']);
            $table->index(['date', 'status']);
            $table->index('available_slots');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};