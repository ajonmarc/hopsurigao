<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePickupLocationRequest;
use App\Http\Requests\Admin\UpdatePickupLocationRequest;
use App\Models\PickupLocation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Inertia\Inertia;
use Inertia\Response;

class PickupLocationController extends Controller
{
    public function index(Request $request): Response
    {
        $perPage = (int) $request->input('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50, 100]) ? $perPage : 10;

        $pickupLocations = PickupLocation::query()
            ->when(
                $request->input('search'),
                function (Builder $query, string $search) {
                    $query->where(function (Builder $q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('address', 'like', "%{$search}%")
                            ->orWhere('description', 'like', "%{$search}%");
                    });
                }
            )
            ->when(
                $request->input('status'),
                function (Builder $query, string $status) {
                    $query->where('status', $status);
                }
            )
            ->when(
                $request->input('sort'),
                function (Builder $query, string $sort) {
                    foreach (explode(',', $sort) as $field) {
                        $direction = str_starts_with($field, '-') ? 'desc' : 'asc';
                        $column = ltrim($field, '-');

                        if (in_array($column, ['name', 'status', 'created_at'])) {
                            $query->orderBy($column, $direction);
                        }
                    }
                },
                fn (Builder $query) => $query->latest()
            )
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('admin/pickup-locations/Index', [
            'pickupLocations' => $pickupLocations,
            'filters' => $request->only('sort', 'search', 'per_page', 'status'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/pickup-locations/Create');
    }

    public function store(StorePickupLocationRequest $request): RedirectResponse
    {
        $data = $request->validated();

        PickupLocation::create($data);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Pickup location created successfully.']);

        return redirect()->route('admin.pickup-locations.index');
    }

    public function update(UpdatePickupLocationRequest $request, PickupLocation $pickupLocation): RedirectResponse
    {
        $data = $request->validated();

        $pickupLocation->update($data);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Pickup location updated successfully.']);

        return redirect()->route('admin.pickup-locations.index');
    }

    public function destroy(PickupLocation $pickupLocation): RedirectResponse
    {
        $pickupLocation->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Pickup location deleted successfully.']);

        return redirect()->route('admin.pickup-locations.index');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            Inertia::flash('toast', ['type' => 'error', 'message' => 'No pickup locations selected for deletion.']);
            return redirect()->back();
        }

        $deleted = PickupLocation::whereIn('id', $ids)->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => "{$deleted} pickup location(s) deleted successfully."]);

        return redirect()->route('admin.pickup-locations.index');
    }
}