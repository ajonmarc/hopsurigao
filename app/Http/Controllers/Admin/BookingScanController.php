<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BookingScanController extends Controller
{
    /**
     * Renders the operator-facing QR scanner page.
     */
    public function index(): Response
    {
        return Inertia::render('admin/bookings/Scan');
    }

    /**
     * Looks up a booking by its scanned qr_token. Returns booking +
     * guest details so the operator can visually confirm identity
     * before checking the guest in.
     */
    public function verify(Request $request): JsonResponse
    {
        $data = $request->validate([
            'qr_token' => ['required', 'string'],
        ]);

        $booking = Booking::query()
            ->where('qr_token', $data['qr_token'])
            ->with([
                'user:id,name,email',
                'tourDate:id,tour_date,package_id',
                'tourDate.package:id,package_name',
                // Load the booking's direct pickup schedule
                'pickupSchedule:id,pickup_time,pickup_location_id',
                'pickupSchedule.pickupLocation:id,name',
            ])
            ->first();

        if (!$booking) {
            return response()->json([
                'found' => false,
                'message' => 'No booking found for this QR code.',
            ], 404);
        }

        if ($booking->booking_status === 'cancelled') {
            return response()->json([
                'found' => true,
                'valid' => false,
                'message' => 'This booking was cancelled.',
                'booking' => $booking,
            ]);
        }

        // Get the pickup schedule from the booking's direct relation
        $pickupSchedule = $booking->pickupSchedule;
        
        // Get the pickup time as a plain string without timezone conversion
        $pickupTime = null;
        if ($pickupSchedule) {
            // Access the raw attribute directly to avoid the cast
            $pickupTime = $pickupSchedule->getRawOriginal('pickup_time');
            
            // If that doesn't work, try getting it as a string
            if (!$pickupTime) {
                $pickupTime = $pickupSchedule->pickup_time;
                if ($pickupTime instanceof \DateTimeInterface) {
                    $pickupTime = $pickupTime->format('H:i');
                }
            }
        }

        return response()->json([
            'found' => true,
            'valid' => true,
            'booking' => [
                'id' => $booking->id,
                'guest_name' => $booking->user->name ?? 'Guest',
                'guest_email' => $booking->user->email ?? null,
                'package_name' => $booking->tourDate->package->package_name ?? 'N/A',
                'tour_date' => optional($booking->tourDate->tour_date)->format('F j, Y'),
                'pickup_location' => $pickupSchedule?->pickupLocation?->name ?? 'N/A',
                // Use the raw time string without timezone conversion
                'pickup_time' => $pickupTime,
                'number_of_guests' => $booking->number_of_guests,
                'booking_status' => $booking->booking_status,
            ],
        ]);
    }
}