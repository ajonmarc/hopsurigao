<?php
// database/migrations/[timestamp]_add_hopsurigao_fields_to_users_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add new columns
            $table->string('phone')->nullable()->after('email');
            $table->enum('role', ['admin', 'operator', 'user'])->default('user')->after('phone');
            $table->string('profile_image')->nullable()->after('role');
            $table->text('bio')->nullable()->after('profile_image');
            $table->text('address')->nullable()->after('bio');
            $table->boolean('is_active')->default(true)->after('address');
            
            // Add two-factor authentication columns if not exists
            if (!Schema::hasColumn('users', 'two_factor_secret')) {
                $table->text('two_factor_secret')->nullable()->after('remember_token');
            }
            if (!Schema::hasColumn('users', 'two_factor_recovery_codes')) {
                $table->text('two_factor_recovery_codes')->nullable()->after('two_factor_secret');
            }
            if (!Schema::hasColumn('users', 'two_factor_confirmed_at')) {
                $table->timestamp('two_factor_confirmed_at')->nullable()->after('two_factor_recovery_codes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'role',
                'profile_image',
                'bio',
                'address',
                'is_active',
                'two_factor_secret',
                'two_factor_recovery_codes',
                'two_factor_confirmed_at'
            ]);
        });
    }
};