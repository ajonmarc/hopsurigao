<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreBookingRequest;
use App\Http\Requests\Admin\UpdateBookingRequest;
use App\Models\Booking;
use App\Models\TourDate;
use App\Models\PickupLocation;
use App\Models\Package;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Inertia\Inertia;
use Inertia\Response;

class BookingController extends Controller
{
    public function index(Request $request): Response
    {
        $perPage = (int) $request->input('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50, 100]) ? $perPage : 10;

        $bookings = Booking::query()
            ->with([
                'user:id,name,email',
                'tourDate.package:id,package_name,price,description,image,destination,status',
                'pickupLocation:id,name,address',
                // NEW: needed so the admin table/columns can read payment_status
                'payments',
            ])
            ->when(
                $request->input('search'),
                function (Builder $query, string $search) {
                    $query->where(function (Builder $q) use ($search) {
                        $q->where('phone_number', 'like', "%{$search}%")
                            ->orWhere('nationality', 'like', "%{$search}%")
                            ->orWhereHas('user', function (Builder $q) use ($search) {
                                $q->where('name', 'like', "%{$search}%")
                                    ->orWhere('email', 'like', "%{$search}%");
                            })
                            ->orWhereHas('tourDate.package', function (Builder $q) use ($search) {
                                $q->where('package_name', 'like', "%{$search}%");
                            });
                    });
                }
            )
            ->when(
                $request->input('booking_status'),
                function (Builder $query, string $status) {
                    $query->where('booking_status', $status);
                }
            )
            ->when(
                $request->input('package_id'),
                function (Builder $query, string $packageId) {
                    $query->whereHas('tourDate', function (Builder $q) use ($packageId) {
                        $q->where('package_id', $packageId);
                    });
                }
            )
            ->when(
                $request->input('tour_date_id'),
                function (Builder $query, string $tourDateId) {
                    $query->where('tour_date_id', $tourDateId);
                }
            )
            ->when(
                $request->input('from_date'),
                function (Builder $query, string $fromDate) {
                    $query->whereHas('tourDate', function (Builder $q) use ($fromDate) {
                        $q->where('tour_date', '>=', $fromDate);
                    });
                }
            )
            ->when(
                $request->input('to_date'),
                function (Builder $query, string $toDate) {
                    $query->whereHas('tourDate', function (Builder $q) use ($toDate) {
                        $q->where('tour_date', '<=', $toDate);
                    });
                }
            )
            ->when(
                $request->input('sort'),
                function (Builder $query, string $sort) {
                    foreach (explode(',', $sort) as $field) {
                        $direction = str_starts_with($field, '-') ? 'desc' : 'asc';
                        $column = ltrim($field, '-');

                        if (in_array($column, ['booking_status', 'number_of_guests', 'created_at'])) {
                            $query->orderBy($column, $direction);
                        }
                    }
                },
                fn(Builder $query) => $query->latest()
            )
            ->paginate($perPage)
            ->withQueryString();

        // Get filter options
        $packages = Package::select('id', 'package_name')
            ->orderBy('package_name')
            ->get();

        $tourDates = TourDate::with('package:id,package_name')
            ->select('id', 'package_id', 'tour_date')
            ->orderBy('tour_date')
            ->get()
            ->map(function ($tourDate) {
                return [
                    'id' => $tourDate->id,
                    'label' => $tourDate->package->package_name . ' - ' . $tourDate->tour_date->format('M d, Y'),
                ];
            });

        $pickupLocations = PickupLocation::select('id', 'name')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $users = User::select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        return Inertia::render('admin/bookings/Index', [
            'bookings' => $bookings,
            'packages' => $packages,
            'tourDates' => $tourDates,
            'pickupLocations' => $pickupLocations,
            'users' => $users,
            'filters' => $request->only(
                'sort',
                'search',
                'per_page',
                'booking_status',
                'package_id',
                'tour_date_id',
                'from_date',
                'to_date'
            ),
        ]);
    }

    public function create(Request $request): Response
    {
        $tourDates = TourDate::with('package:id,package_name,price,description,image,destination,status')
            ->select('id', 'package_id', 'tour_date')
            ->orderBy('tour_date')
            ->get()
            ->map(function ($tourDate) {
                return [
                    'id' => $tourDate->id,
                    'label' => $tourDate->package->package_name . ' - ' . $tourDate->tour_date->format('M d, Y'),
                ];
            });

        $pickupLocations = PickupLocation::select('id', 'name', 'address')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $users = User::select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        return Inertia::render('admin/bookings/Create', [
            'tourDates' => $tourDates,
            'pickupLocations' => $pickupLocations,
            'users' => $users,
            'selectedTourDateId' => $request->input('tour_date_id'),
        ]);
    }

    public function store(StoreBookingRequest $request): RedirectResponse
    {
        $data = $request->validated();

        Booking::create($data);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Booking created successfully.']);

        return redirect()->route('admin.bookings.index');
    }

    public function edit(Booking $booking): Response
    {
        $booking->load([
            'user:id,name,email',
            'tourDate.package:id,package_name,price,description,image,destination,status',
            'pickupLocation:id,name,address',
            'payments', // NEW
        ]);

        $tourDates = TourDate::with('package:id,package_name')
            ->select('id', 'package_id', 'tour_date')
            ->orderBy('tour_date')
            ->get()
            ->map(function ($tourDate) {
                return [
                    'id' => $tourDate->id,
                    'label' => $tourDate->package->package_name . ' - ' . $tourDate->tour_date->format('M d, Y'),
                ];
            });

        $pickupLocations = PickupLocation::select('id', 'name', 'address')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $users = User::select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        return Inertia::render('admin/bookings/Edit', [
            'booking' => $booking,
            'tourDates' => $tourDates,
            'pickupLocations' => $pickupLocations,
            'users' => $users,
        ]);
    }

    public function update(UpdateBookingRequest $request, Booking $booking): RedirectResponse
    {
        $data = $request->validated();

        $booking->update($data);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Booking updated successfully.']);

        return redirect()->route('admin.bookings.index');
    }

    public function destroy(Booking $booking): RedirectResponse
    {
        $booking->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Booking deleted successfully.']);

        return redirect()->route('admin.bookings.index');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            Inertia::flash('toast', ['type' => 'error', 'message' => 'No bookings selected for deletion.']);
            return redirect()->back();
        }

        $deleted = Booking::whereIn('id', $ids)->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => "{$deleted} booking(s) deleted successfully."]);

        return redirect()->route('admin.bookings.index');
    }

    /**
     * NEW: Admin confirms a booking's payment.
     * Marks the latest payment record as 'paid', stamps paid_at,
     * and bumps the booking to 'confirmed' if it was still 'pending'.
     */
    public function confirmPayment(Booking $booking): RedirectResponse
    {
        $payment = $booking->payments()->latest()->first();

        if (!$payment) {
            Inertia::flash('toast', ['type' => 'error', 'message' => 'No payment record found for this booking.']);
            return redirect()->back();
        }

        if ($payment->payment_status === 'paid') {
            Inertia::flash('toast', ['type' => 'info', 'message' => 'This payment is already confirmed.']);
            return redirect()->back();
        }

        $payment->update([
            'payment_status' => 'paid',
            'paid_at' => now(),
        ]);

        if ($booking->booking_status === 'pending') {
            $booking->update(['booking_status' => 'confirmed']);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Payment confirmed and booking marked as confirmed.']);

        return redirect()->back();
    }


    public function updateStatus(Request $request, Booking $booking): RedirectResponse
    {
        $data = $request->validate([
            'booking_status' => ['required', 'in:pending,confirmed,cancelled,completed'],
        ]);

        $booking->update($data);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Booking status updated.']);

        return redirect()->back();
    }
}
