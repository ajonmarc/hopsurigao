<?php
// database/migrations/[timestamp]_create_tour_packages_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tour_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('operator_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->text('description');
            $table->string('cover_image');
            $table->json('images')->nullable();
            $table->decimal('price', 10, 2);
            $table->integer('duration_hours');
            $table->integer('max_capacity')->default(20);
            $table->json('inclusions')->nullable();
            $table->json('exclusions')->nullable();
            $table->json('meeting_point')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->integer('view_count')->default(0);
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['operator_id', 'is_active']);
            $table->index('is_featured');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tour_packages');
    }
};