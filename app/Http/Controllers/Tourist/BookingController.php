<?php

namespace App\Http\Controllers\Tourist;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tourist\StoreBookingRequest;
use App\Http\Requests\Tourist\UpdateBookingRequest;
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
    // Bookings can only be edited by the tourist while still pending —
    // once confirmed/paid or further along, changes should go through
    // the operator instead of being silently altered by the guest.
    private const EDITABLE_STATUSES = ['pending'];

    public function index(Request $request): Response
    {
        $perPage = (int) $request->input('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50]) ? $perPage : 10;

        $bookings = Auth::user()
            ->bookings()
            ->with([
                'tourDate.package:id,package_name,image,description,price',
                'pickupLocation:id,name,address',
                'tourDate:id,tour_date,package_id,capacity',
                'payments'
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
        $tourDateId = $request->input('tour_date_id');
        $pickupLocationId = $request->input('pickup_location_id');
        $guests = (int) $request->input('guests', 1);

        if (!$tourDateId) {
            Inertia::flash('toast', ['type' => 'error', 'message' => 'Please select a tour date first.']);
            return redirect()->route('tourist.packages.index');
        }

        $tourDate = TourDate::with('package')->findOrFail($tourDateId);

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

        $tourDate = TourDate::findOrFail($data['tour_date_id']);
        $confirmedBookings = $tourDate->bookings()
            ->where('booking_status', 'confirmed')
            ->sum('number_of_guests');
        $availableSpots = $tourDate->capacity - $confirmedBookings;

        if ($data['number_of_guests'] > $availableSpots) {
            Inertia::flash('toast', ['type' => 'error', 'message' => 'Not enough spots available.']);
            return redirect()->back();
        }

        $booking = Booking::create($data);

        return redirect()->route('tourist.payments.create', ['booking_id' => $booking->id]);
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
            'payments',
        ]);

        return Inertia::render('tourist/bookings/Show', [
            'booking' => $booking,
        ]);
    }

    /**
     * Shows the edit form for a tourist's own booking. Only pending
     * bookings are editable — confirmed/completed/cancelled bookings
     * redirect back with an explanatory message.
     */
    public function edit(Booking $booking): Response|RedirectResponse
    {
        if ($booking->user_id !== Auth::id()) {
            abort(403);
        }

        if (!in_array($booking->booking_status, self::EDITABLE_STATUSES, true)) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'This booking can no longer be edited. Please contact support for changes.',
            ]);
            return redirect()->route('tourist.bookings.index');
        }

        $booking->load([
            'tourDate.package:id,package_name,image,description,price',
            'tourDate:id,tour_date,package_id,capacity',
            'pickupLocation:id,name,address',
        ]);

        // Spots available for THIS booking to grow into, i.e. capacity
        // minus everyone else's confirmed guests (excluding this
        // booking's own current count, since it's not displacing itself).
        $confirmedGuestsExcludingThis = $booking->tourDate->bookings()
            ->where('booking_status', 'confirmed')
            ->where('id', '!=', $booking->id)
            ->sum('number_of_guests');
        $availableSpots = $booking->tourDate->capacity - $confirmedGuestsExcludingThis;

        $pickupLocations = PickupLocation::where('status', 'active')
            ->select('id', 'name', 'address')
            ->get();

        return Inertia::render('tourist/bookings/Edit', [
            'booking' => $booking,
            'pickupLocations' => $pickupLocations,
            'availableSpots' => $availableSpots,
        ]);
    }

    public function update(UpdateBookingRequest $request, Booking $booking): RedirectResponse
    {
        if ($booking->user_id !== Auth::id()) {
            abort(403);
        }

        if (!in_array($booking->booking_status, self::EDITABLE_STATUSES, true)) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'This booking can no longer be edited.',
            ]);
            return redirect()->route('tourist.bookings.index');
        }

        $booking->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Booking updated successfully.']);

        return redirect()->route('tourist.bookings.index');
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