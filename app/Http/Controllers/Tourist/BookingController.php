<?php

namespace App\Http\Controllers\Tourist;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tourist\StoreBookingRequest;
use App\Models\Booking;
use App\Models\TourDate;
use App\Models\PickupLocation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class BookingController extends Controller
{
    public function index(Request $request): Response
    {
        $perPage = (int) $request->input('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50]) ? $perPage : 10;

        $bookings = Auth::user()
            ->bookings()
            ->with([
                'tourDate.package:id,package_name,image,description,price', // Added 'price' here
                'pickupLocation:id,name,address',
                'tourDate:id,tour_date,package_id,capacity'
            ])
            ->when(
                $request->input('status'),
                function ($query, string $status) {
                    $query->where('booking_status', $status);
                }
            )
            ->when(
                $request->input('search'),
                function ($query, string $search) {
                    $query->whereHas('tourDate.package', function ($q) use ($search) {
                        $q->where('package_name', 'like', "%{$search}%");
                    });
                }
            )
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('tourist/bookings/Index', [
            'bookings' => $bookings,
            'filters' => $request->only('status', 'search', 'per_page'),
        ]);
    }

    /**
     * @return Response|RedirectResponse
     */
    public function create(Request $request): mixed
    {
        // Get parameters from URL
        $tourDateId = $request->input('tour_date_id');
        $pickupLocationId = $request->input('pickup_location_id');
        $guests = (int) $request->input('guests', 1);

        // If tour_date_id is provided, validate and get the tour date
        if (!$tourDateId) {
            Inertia::flash('toast', ['type' => 'error', 'message' => 'Please select a tour date first.']);
            return redirect()->route('tourist.packages.index');
        }

        $tourDate = TourDate::with('package')->findOrFail($tourDateId);

        // Check if user already has a booking for this tour date
        $existingBooking = Booking::where('user_id', Auth::id())
            ->where('tour_date_id', $tourDateId)
            ->whereIn('booking_status', ['pending', 'confirmed'])
            ->first();

        if ($existingBooking) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'You already have a pending/confirmed booking for this tour date. Please check your bookings.'
            ]);
            return redirect()->route('tourist.bookings.index');
        }

        // Check availability
        $confirmedBookings = $tourDate->bookings()
            ->where('booking_status', 'confirmed')
            ->sum('number_of_guests');
        $availableSpots = $tourDate->capacity - $confirmedBookings;

        if ($availableSpots <= 0) {
            Inertia::flash('toast', ['type' => 'error', 'message' => 'This tour date is fully booked.']);
            return redirect()->route('tourist.packages.show', $tourDate->package_id);
        }

        $pickupLocations = PickupLocation::where('status', 'active')
            ->select('id', 'name', 'address')
            ->get();

        return Inertia::render('tourist/bookings/Create', [
            'tourDate' => [
                'id' => $tourDate->id,
                'tour_date' => $tourDate->tour_date,
                'package' => $tourDate->package,
                'available_spots' => $availableSpots,
                'capacity' => $tourDate->capacity,
            ],
            'pickupLocations' => $pickupLocations,
            'selectedPickupLocationId' => $pickupLocationId ? (int) $pickupLocationId : null,
            'guests' => max(1, min($guests, $availableSpots)),
        ]);
    }

    public function store(StoreBookingRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['user_id'] = Auth::id();
        $data['booking_status'] = 'pending';

        // Check if user already has a booking for this tour date
        $existingBooking = Booking::where('user_id', Auth::id())
            ->where('tour_date_id', $data['tour_date_id'])
            ->whereIn('booking_status', ['pending', 'confirmed'])
            ->first();

        if ($existingBooking) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'You already have a pending/confirmed booking for this tour date.'
            ]);
            return redirect()->back();
        }

        // Check availability again
        $tourDate = TourDate::findOrFail($data['tour_date_id']);
        $confirmedBookings = $tourDate->bookings()
            ->where('booking_status', 'confirmed')
            ->sum('number_of_guests');
        $availableSpots = $tourDate->capacity - $confirmedBookings;

        if ($data['number_of_guests'] > $availableSpots) {
            Inertia::flash('toast', ['type' => 'error', 'message' => 'Not enough spots available.']);
            return redirect()->back();
        }

        Booking::create($data);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Booking created successfully!']);

        return redirect()->route('tourist.bookings.index');
    }

    public function show(Booking $booking): Response
    {
        if ($booking->user_id !== Auth::id()) {
            abort(403);
        }

        $booking->load([
            'tourDate.package:id,package_name,image,description,price',
            'tourDate:id,tour_date,package_id,capacity',
            'pickupLocation:id,name,address',
        ]);

        return Inertia::render('tourist/bookings/Show', [
            'booking' => $booking,
        ]);
    }

    public function cancel(Booking $booking): RedirectResponse
    {
        if ($booking->user_id !== Auth::id()) {
            abort(403);
        }

        if (!in_array($booking->booking_status, ['pending', 'confirmed'])) {
            Inertia::flash('toast', ['type' => 'error', 'message' => 'This booking cannot be cancelled.']);
            return redirect()->back();
        }

        $booking->update(['booking_status' => 'cancelled']);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Booking cancelled successfully.']);

        return redirect()->route('tourist.bookings.index');
    }

    public function destroy(Booking $booking): RedirectResponse
    {
        if ($booking->user_id !== Auth::id()) {
            abort(403);
        }

        if (!in_array($booking->booking_status, ['pending', 'cancelled'])) {
            Inertia::flash('toast', ['type' => 'error', 'message' => 'This booking cannot be deleted.']);
            return redirect()->back();
        }

        $booking->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Booking deleted successfully.']);

        return redirect()->route('tourist.bookings.index');
    }
}
