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
                'pickupLocation:id,name',
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

        return response()->json([
            'found' => true,
            'valid' => true,
            'booking' => [
                'id' => $booking->id,
                'guest_name' => $booking->user->name ?? 'Guest',
                'guest_email' => $booking->user->email ?? null,
                'package_name' => $booking->tourDate->package->package_name ?? 'N/A',
                'tour_date' => optional($booking->tourDate->tour_date)->format('F j, Y'),
                'pickup_location' => $booking->pickupLocation->name ?? 'N/A',
                'number_of_guests' => $booking->number_of_guests,
                'booking_status' => $booking->booking_status,
            ],
        ]);
    }
}