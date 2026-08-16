<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePickupScheduleRequest;
use App\Http\Requests\Admin\UpdatePickupScheduleRequest;
use App\Models\PickupSchedule;
use App\Models\TourDate;
use App\Models\PickupLocation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PickupScheduleController extends Controller
{
    public function index(Request $request): Response
    {
        $perPage = (int) $request->input('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50, 100]) ? $perPage : 10;

        $pickupSchedules = PickupSchedule::query()
            ->with([
                'tourDate.package:id,package_name',
                'pickupLocation:id,name,address',
            ])
            ->when(
                $request->input('search'),
                function (Builder $query, string $search) {
                    $query->where(function (Builder $q) use ($search) {
                        $q->whereHas('pickupLocation', function (Builder $q2) use ($search) {
                            $q2->where('name', 'like', "%{$search}%");
                        })->orWhereHas('tourDate.package', function (Builder $q2) use ($search) {
                            $q2->where('package_name', 'like', "%{$search}%");
                        });
                    });
                }
            )
            ->when(
                $request->input('tour_date_id'),
                fn (Builder $query, string $tourDateId) => $query->where('tour_date_id', $tourDateId)
            )
            ->when(
                $request->input('pickup_location_id'),
                fn (Builder $query, string $locationId) => $query->where('pickup_location_id', $locationId)
            )
            ->when(
                $request->input('sort'),
                function (Builder $query, string $sort) {
                    foreach (explode(',', $sort) as $field) {
                        $direction = str_starts_with($field, '-') ? 'desc' : 'asc';
                        $column = ltrim($field, '-');

                        if (in_array($column, ['pickup_time', 'created_at'])) {
                            $query->orderBy($column, $direction);
                        }
                    }
                },
                fn (Builder $query) => $query->latest()
            )
            ->paginate($perPage)
            ->withQueryString();

        $tourDates = TourDate::with('package:id,package_name')
            ->select('id', 'package_id', 'tour_date')
            ->orderBy('tour_date')
            ->get()
            ->map(fn (TourDate $tourDate) => [
                'id' => $tourDate->id,
                'label' => $tourDate->package->package_name . ' - ' . $tourDate->tour_date->format('M d, Y'),
            ]);

        $pickupLocations = PickupLocation::select('id', 'name', 'address')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return Inertia::render('admin/pickup-schedule/Index', [
            'pickupSchedules' => $pickupSchedules,
            'tourDates' => $tourDates,
            'pickupLocations' => $pickupLocations,
            'filters' => $request->only('sort', 'search', 'per_page', 'tour_date_id', 'pickup_location_id'),
        ]);
    }

    public function create(): Response
    {
        $tourDates = TourDate::with('package:id,package_name')
            ->select('id', 'package_id', 'tour_date')
            ->orderBy('tour_date')
            ->get()
            ->map(fn (TourDate $tourDate) => [
                'id' => $tourDate->id,
                'label' => $tourDate->package->package_name . ' - ' . $tourDate->tour_date->format('M d, Y'),
            ]);

        $pickupLocations = PickupLocation::select('id', 'name', 'address')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return Inertia::render('admin/pickup-schedule/Create', [
            'tourDates' => $tourDates,
            'pickupLocations' => $pickupLocations,
        ]);
    }

    public function edit(PickupSchedule $pickupSchedule): RedirectResponse
    {
        // Not used by the UI — editing happens via the modal in Index.vue.
        // Kept only so the full Route::resource() route doesn't error if visited.
        return redirect()->route('admin.pickup-schedules.index');
    }

    public function store(StorePickupScheduleRequest $request): RedirectResponse
    {
        PickupSchedule::create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Pickup schedule created successfully.']);

        return redirect()->route('admin.pickup-schedules.index');
    }

    public function show(PickupSchedule $pickupSchedule): RedirectResponse
    {
        // Not used by the UI (details are shown inline via the table/edit dialog),
        // redirect back to the index rather than rendering a dedicated page.
        return redirect()->route('admin.pickup-schedules.index');
    }

    public function update(UpdatePickupScheduleRequest $request, PickupSchedule $pickupSchedule): RedirectResponse
    {
        $pickupSchedule->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Pickup schedule updated successfully.']);

        return redirect()->route('admin.pickup-schedules.index');
    }

    public function destroy(PickupSchedule $pickupSchedule): RedirectResponse
    {
        $pickupSchedule->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Pickup schedule deleted successfully.']);

        return redirect()->route('admin.pickup-schedules.index');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            Inertia::flash('toast', ['type' => 'error', 'message' => 'No pickup schedules selected for deletion.']);
            return redirect()->back();
        }

        $deleted = PickupSchedule::whereIn('id', $ids)->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => "{$deleted} pickup schedule(s) deleted successfully."]);

        return redirect()->route('admin.pickup-schedules.index');
    }
}