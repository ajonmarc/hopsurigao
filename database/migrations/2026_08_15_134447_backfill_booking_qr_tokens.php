<?php

use App\Models\Booking;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // Backfill any existing bookings that don't have a qr_token yet
        // (created before this feature was added). New bookings get one
        // automatically via the Booking model's creating() hook.
        Booking::whereNull('qr_token')
            ->orWhere('qr_token', '')
            ->cursor()
            ->each(function (Booking $booking) {
                $booking->update(['qr_token' => (string) Str::uuid()]);
            });
    }

    public function down(): void
    {
        // No-op — don't null out tokens on rollback, since that would
        // break already-issued/printed/downloaded QR codes.
    }
};