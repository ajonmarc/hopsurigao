<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Returns bookings that are either:
     *  - newly created since `since`, or
     *  - existing bookings whose status/details changed since `since`
     *    (so the admin's cached notification list can stay in sync,
     *    e.g. pending -> completed).
     *
     * Each item is tagged with `is_new` so the frontend only bumps the
     * unread badge / plays a sound for genuinely new bookings, while
     * silently refreshing the status of ones it already has.
     *
     * If `since` is omitted, returns the most recent bookings so the
     * dropdown isn't empty on first load.
     */
    public function bookings(Request $request): JsonResponse
    {
        $since = $request->input('since');
        $sinceCarbon = null;

        if ($since) {
            try {
                $sinceCarbon = Carbon::parse($since);
            } catch (\Exception $e) {
                $sinceCarbon = null;
            }
        }

        $query = Booking::query()
            ->with([
                'user:id,name,email',
                'tourDate.package:id,package_name',
            ])
            ->latest();

        if ($sinceCarbon) {
            $query->where(function ($q) use ($sinceCarbon) {
                $q->where('created_at', '>', $sinceCarbon)
                    ->orWhere('updated_at', '>', $sinceCarbon);
            });
        }

        $bookings = $query->limit(30)->get()->map(function (Booking $booking) use ($sinceCarbon) {
            return [
                'id' => $booking->id,
                'guest_name' => $booking->user->name ?? 'Guest',
                'package_name' => $booking->tourDate->package->package_name ?? 'N/A',
                'number_of_guests' => $booking->number_of_guests,
                'booking_status' => $booking->booking_status,
                'created_at' => $booking->created_at->toIso8601String(),
                // True only if this booking was created after `since` —
                // i.e. genuinely new, not just updated.
                'is_new' => $sinceCarbon ? $booking->created_at->gt($sinceCarbon) : true,
            ];
        });

        return response()->json([
            'bookings' => $bookings,
            'server_time' => now()->toIso8601String(),
        ]);
    }
}