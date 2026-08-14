<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTourDateRequest;
use App\Http\Requests\Admin\UpdateTourDateRequest;
use App\Models\TourDate;
use App\Models\Package;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Inertia\Inertia;
use Inertia\Response;

class TourDateController extends Controller
{
    public function index(Request $request): Response
    {
        $perPage = (int) $request->input('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50, 100]) ? $perPage : 10;

        $tourDates = TourDate::query()
            ->with('package:id,package_name')
            ->when(
                $request->input('search'),
                function (Builder $query, string $search) {
                    $query->where(function (Builder $q) use ($search) {
                        $q->where('tour_date', 'like', "%{$search}%")
                            ->orWhereHas('package', function (Builder $q) use ($search) {
                                $q->where('package_name', 'like', "%{$search}%");
                            });
                    });
                }
            )
            ->when(
                $request->input('package_id'),
                function (Builder $query, string $packageId) {
                    $query->where('package_id', $packageId);
                }
            )
            ->when(
                $request->input('from_date'),
                function (Builder $query, string $fromDate) {
                    $query->where('tour_date', '>=', $fromDate);
                }
            )
            ->when(
                $request->input('to_date'),
                function (Builder $query, string $toDate) {
                    $query->where('tour_date', '<=', $toDate);
                }
            )
            ->when(
                $request->input('sort'),
                function (Builder $query, string $sort) {
                    foreach (explode(',', $sort) as $field) {
                        $direction = str_starts_with($field, '-') ? 'desc' : 'asc';
                        $column = ltrim($field, '-');

                        if (in_array($column, ['tour_date', 'capacity', 'package_id', 'created_at'])) {
                            $query->orderBy($column, $direction);
                        }
                    }
                },
                fn (Builder $query) => $query->latest()
            )
            ->paginate($perPage)
            ->withQueryString();

        // Get packages for the filter dropdown
        $packages = Package::select('id', 'package_name')
            ->orderBy('package_name')
            ->get();

        return Inertia::render('admin/tour-dates/Index', [
            'tourDates' => $tourDates,
            'packages' => $packages,
            'filters' => $request->only('sort', 'search', 'per_page', 'package_id', 'from_date', 'to_date'),
        ]);
    }

    public function create(Request $request): Response
    {
        $packages = Package::select('id', 'package_name')
            ->orderBy('package_name')
            ->get();

        return Inertia::render('admin/tour-dates/Create', [
            'packages' => $packages,
            'selectedPackageId' => $request->input('package_id'),
        ]);
    }

    public function store(StoreTourDateRequest $request): RedirectResponse
    {
        $data = $request->validated();

        TourDate::create($data);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Tour date created successfully.']);

        return redirect()->route('admin.tour-dates.index');
    }

    public function update(UpdateTourDateRequest $request, TourDate $tourDate): RedirectResponse
    {
        $data = $request->validated();

        $tourDate->update($data);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Tour date updated successfully.']);

        return redirect()->route('admin.tour-dates.index');
    }

    public function destroy(TourDate $tourDate): RedirectResponse
    {
        $tourDate->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Tour date deleted successfully.']);

        return redirect()->route('admin.tour-dates.index');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            Inertia::flash('toast', ['type' => 'error', 'message' => 'No tour dates selected for deletion.']);
            return redirect()->back();
        }

        $deleted = TourDate::whereIn('id', $ids)->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => "{$deleted} tour date(s) deleted successfully."]);

        return redirect()->route('admin.tour-dates.index');
    }
}