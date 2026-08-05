<?php
// database/migrations/[timestamp]_create_boats_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('boats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('operator_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('registration_number')->unique();
            $table->enum('type', ['pump_boat', 'speedboat', 'yacht', 'banca'])->default('pump_boat');
            $table->integer('capacity');
            $table->json('amenities')->nullable();
            $table->string('image')->nullable();
            $table->enum('status', ['available', 'maintenance', 'booked'])->default('available');
            $table->timestamps();
            
            $table->index(['operator_id', 'status']);
            $table->index('registration_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boats');
    }
};