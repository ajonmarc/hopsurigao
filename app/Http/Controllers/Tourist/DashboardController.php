<?php

namespace App\Http\Controllers\Tourist;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $user = Auth::user();
        
        $stats = [
            'total_bookings' => $user->bookings()->count(),
            'upcoming_bookings' => $user->bookings()
                ->whereHas('tourDate', function ($query) {
                    $query->where('tour_date', '>=', now()->toDateString());
                })
                ->whereIn('booking_status', ['pending', 'confirmed'])
                ->count(),
            'completed_bookings' => $user->bookings()
                ->where('booking_status', 'completed')
                ->count(),
            'cancelled_bookings' => $user->bookings()
                ->where('booking_status', 'cancelled')
                ->count(),
            'recent_bookings' => $user->bookings()
                ->with([
                    'tourDate.package:id,package_name,image,description',
                    'tourDate:id,tour_date,package_id',
                    'pickupLocation:id,name'
                ])
                ->latest()
                ->limit(5)
                ->get()
                ->map(function ($booking) {
                    return [
                        'id' => $booking->id,
                        'package_name' => $booking->tourDate->package->package_name,
                        'package_image' => $booking->tourDate->package->image,
                        'tour_date' => $booking->tourDate->tour_date,
                        'booking_status' => $booking->booking_status,
                        'number_of_guests' => $booking->number_of_guests,
                        'pickup_location' => $booking->pickupLocation?->name,
                        'created_at' => $booking->created_at,
                    ];
                }),
        ];

        return Inertia::render('tourist/Dashboard', [
            'stats' => $stats,
            'user' => $user->only('name', 'email'),
        ]);
    }
}