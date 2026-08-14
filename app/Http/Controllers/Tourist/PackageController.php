<?php

namespace App\Http\Controllers\Tourist;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\TourDate;
use App\Models\PickupLocation;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Inertia\Inertia;
use Inertia\Response;

class PackageController extends Controller
{
    public function index(Request $request): Response
    {
        $perPage = (int) $request->input('per_page', 12);
        $perPage = in_array($perPage, [12, 24, 48]) ? $perPage : 12;

        $packages = Package::query()
            ->where('status', 'active')
            ->withCount(['tourDates' => function ($query) {
                $query->where('tour_date', '>=', now()->toDateString());
            }])
            ->when(
                $request->input('search'),
                function (Builder $query, string $search) {
                    $query->where(function (Builder $q) use ($search) {
                        $q->where('package_name', 'like', "%{$search}%")
                            ->orWhere('destination', 'like', "%{$search}%")
                            ->orWhere('description', 'like', "%{$search}%");
                    });
                }
            )
            ->when(
                $request->input('destination'),
                function (Builder $query, string $destination) {
                    $query->where('destination', 'like', "%{$destination}%");
                }
            )
            ->when(
                $request->input('min_price'),
                function (Builder $query, string $minPrice) {
                    $query->where('price', '>=', (float) $minPrice);
                }
            )
            ->when(
                $request->input('max_price'),
                function (Builder $query, string $maxPrice) {
                    $query->where('price', '<=', (float) $maxPrice);
                }
            )
            ->when(
                $request->input('sort'),
                function (Builder $query, string $sort) {
                    $direction = str_starts_with($sort, '-') ? 'desc' : 'asc';
                    $column = ltrim($sort, '-');
                    
                    if (in_array($column, ['package_name', 'price', 'destination'])) {
                        $query->orderBy($column, $direction);
                    }
                },
                fn (Builder $query) => $query->latest()
            )
            ->paginate($perPage)
            ->withQueryString();

        // Get unique destinations for filter
        $destinations = Package::where('status', 'active')
            ->distinct()
            ->pluck('destination')
            ->filter()
            ->values();

        return Inertia::render('tourist/packages/Index', [
            'packages' => $packages,
            'destinations' => $destinations,
            'filters' => $request->only('search', 'destination', 'min_price', 'max_price', 'sort', 'per_page'),
        ]);
    }

    public function show(Package $package): Response
    {
        // Ensure package is active
        if ($package->status !== 'active') {
            abort(404);
        }

        $package->load([
            'inclusions',
            'reminders',
            'tourDates' => function ($query) {
                $query->where('tour_date', '>=', now()->toDateString())
                    ->orderBy('tour_date')
                    ->withCount(['bookings']);
            },
            'tourDates.bookings' => function ($query) {
                $query->where('booking_status', 'confirmed');
            },
        ]);

        // Get available pickup locations
        $pickupLocations = PickupLocation::where('status', 'active')
            ->select('id', 'name', 'address')
            ->get();

        // Calculate available spots for each tour date
        $tourDates = $package->tourDates->map(function ($tourDate) {
            $confirmedBookings = $tourDate->bookings->sum('number_of_guests');
            $availableSpots = max(0, $tourDate->capacity - $confirmedBookings);
            
            return [
                'id' => $tourDate->id,
                'tour_date' => $tourDate->tour_date,
                'capacity' => $tourDate->capacity,
                'available_spots' => $availableSpots,
                'is_available' => $availableSpots > 0,
            ];
        });

        return Inertia::render('tourist/packages/Show', [
            'package' => $package,
            'tourDates' => $tourDates,
            'pickupLocations' => $pickupLocations,
        ]);
    }
}