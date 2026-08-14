<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTimeRequest;
use App\Http\Requests\Admin\UpdateTimeRequest;
use App\Models\TourTime;
use App\Models\TourDate;
use App\Models\Package;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Inertia\Inertia;
use Inertia\Response;

class TimeController extends Controller
{
    public function index(Request $request): Response
    {
        $perPage = (int) $request->input('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50, 100]) ? $perPage : 10;

        $times = TourTime::query()
            ->with(['tourDate.package:id,package_name'])
            ->when(
                $request->input('search'),
                function (Builder $query, string $search) {
                    $query->where(function (Builder $q) use ($search) {
                        $q->where('time', 'like', "%{$search}%")
                            ->orWhere('description', 'like', "%{$search}%")
                            ->orWhereHas('tourDate.package', function (Builder $q) use ($search) {
                                $q->where('package_name', 'like', "%{$search}%");
                            });
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
                $request->input('package_id'),
                function (Builder $query, string $packageId) {
                    $query->whereHas('tourDate', function (Builder $q) use ($packageId) {
                        $q->where('package_id', $packageId);
                    });
                }
            )
            ->when(
                $request->input('sort'),
                function (Builder $query, string $sort) {
                    foreach (explode(',', $sort) as $field) {
                        $direction = str_starts_with($field, '-') ? 'desc' : 'asc';
                        $column = ltrim($field, '-');

                        if (in_array($column, ['time', 'description', 'tour_date_id', 'created_at'])) {
                            $query->orderBy($column, $direction);
                        }
                    }
                },
                fn (Builder $query) => $query->latest()
            )
            ->paginate($perPage)
            ->withQueryString();

        // Get tour dates with package info for filters
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

        // Get packages for filter
        $packages = Package::select('id', 'package_name')
            ->orderBy('package_name')
            ->get();

        return Inertia::render('admin/times/Index', [
            'times' => $times,
            'tourDates' => $tourDates,
            'packages' => $packages,
            'filters' => $request->only('sort', 'search', 'per_page', 'tour_date_id', 'package_id'),
        ]);
    }

    public function create(Request $request): Response
    {
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

        return Inertia::render('admin/times/Create', [
            'tourDates' => $tourDates,
            'selectedTourDateId' => $request->input('tour_date_id'),
        ]);
    }

    public function store(StoreTimeRequest $request): RedirectResponse
    {
        $data = $request->validated();

        TourTime::create($data);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Time slot created successfully.']);

        return redirect()->route('admin.times.index');
    }

    public function update(UpdateTimeRequest $request, TourTime $time): RedirectResponse
    {
        $data = $request->validated();

        $time->update($data);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Time slot updated successfully.']);

        return redirect()->route('admin.times.index');
    }

    public function destroy(TourTime $time): RedirectResponse
    {
        $time->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Time slot deleted successfully.']);

        return redirect()->route('admin.times.index');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            Inertia::flash('toast', ['type' => 'error', 'message' => 'No time slots selected for deletion.']);
            return redirect()->back();
        }

        $deleted = TourTime::whereIn('id', $ids)->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => "{$deleted} time slot(s) deleted successfully."]);

        return redirect()->route('admin.times.index');
    }
}