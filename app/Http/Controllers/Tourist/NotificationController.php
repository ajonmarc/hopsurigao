<?php

namespace App\Http\Controllers\Tourist;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Returns the authenticated tourist's own bookings that either:
     *  - were newly created since `since`, or
     *  - had their status/details change since `since`
     *    (e.g. pending -> confirmed after admin confirms payment).
     *
     * Each item is tagged with `is_new` so the frontend only bumps the
     * unread badge / plays a sound for genuinely new bookings, while
     * silently refreshing the status of ones it already has.
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

        $query = Auth::user()
            ->bookings()
            ->with([
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
                'package_name' => $booking->tourDate->package->package_name ?? 'N/A',
                'number_of_guests' => $booking->number_of_guests,
                'booking_status' => $booking->booking_status,
                'created_at' => $booking->created_at->toIso8601String(),
                'is_new' => $sinceCarbon ? $booking->created_at->gt($sinceCarbon) : true,
            ];
        });

        return response()->json([
            'bookings' => $bookings,
            'server_time' => now()->toIso8601String(),
        ]);
    }
}