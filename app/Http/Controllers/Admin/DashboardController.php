<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\User;
use App\Models\Package;
use App\Models\TourDate;
use App\Models\PickupLocation;
use App\Models\Reminder;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $stats = [
            'total_users' => User::count(),
            'total_packages' => Package::count(),
            'total_bookings' => Booking::count(),
            'pending_bookings' => Booking::where('booking_status', 'pending')->count(),
            'confirmed_bookings' => Booking::where('booking_status', 'confirmed')->count(),
            'cancelled_bookings' => Booking::where('booking_status', 'cancelled')->count(),
            'completed_bookings' => Booking::where('booking_status', 'completed')->count(),
            'total_tour_dates' => TourDate::count(),
            'total_pickup_locations' => PickupLocation::count(),
            'total_reminders' => Reminder::count(),
            'recent_bookings' => Booking::with([
                    'user:id,name,email',
                    'tourDate.package:id,package_name',
                ])
                ->latest()
                ->limit(10)
                ->get()
                ->map(function ($booking) {
                    return [
                        'id' => $booking->id,
                        'user' => $booking->user,
                        'tour_date' => [
                            'package' => $booking->tourDate->package,
                            'tour_date' => $booking->tourDate->tour_date,
                        ],
                        'booking_status' => $booking->booking_status,
                        'number_of_guests' => $booking->number_of_guests,
                        'created_at' => $booking->created_at,
                    ];
                }),
        ];

        return Inertia::render('admin/Dashboard', [
            'stats' => $stats,
        ]);
    }
}