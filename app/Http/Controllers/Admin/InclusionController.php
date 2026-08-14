<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreInclusionRequest;
use App\Http\Requests\Admin\UpdateInclusionRequest;
use App\Models\Inclusion;
use App\Models\Package;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Inertia\Inertia;
use Inertia\Response;

class InclusionController extends Controller
{
    public function index(Request $request): Response
    {
        $perPage = (int) $request->input('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50, 100]) ? $perPage : 10;

        $inclusions = Inclusion::query()
            ->with('package:id,package_name')
            ->when(
                $request->input('search'),
                function (Builder $query, string $search) {
                    $query->where(function (Builder $q) use ($search) {
                        $q->where('description', 'like', "%{$search}%")
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
                $request->input('sort'),
                function (Builder $query, string $sort) {
                    foreach (explode(',', $sort) as $field) {
                        $direction = str_starts_with($field, '-') ? 'desc' : 'asc';
                        $column = ltrim($field, '-');

                        if (in_array($column, ['description', 'package_id', 'created_at'])) {
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

        return Inertia::render('admin/inclusions/Index', [
            'inclusions' => $inclusions,
            'packages' => $packages,
            'filters' => $request->only('sort', 'search', 'per_page', 'package_id'),
        ]);
    }

    public function create(Request $request): Response
    {
        $packages = Package::select('id', 'package_name')
            ->orderBy('package_name')
            ->get();

        return Inertia::render('admin/inclusions/Create', [
            'packages' => $packages,
            'selectedPackageId' => $request->input('package_id'),
        ]);
    }

    public function store(StoreInclusionRequest $request): RedirectResponse
    {
        $data = $request->validated();

        Inclusion::create($data);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Inclusion created successfully.']);

        return redirect()->route('admin.inclusions.index');
    }

    public function update(UpdateInclusionRequest $request, Inclusion $inclusion): RedirectResponse
    {
        $data = $request->validated();

        $inclusion->update($data);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Inclusion updated successfully.']);

        return redirect()->route('admin.inclusions.index');
    }

    public function destroy(Inclusion $inclusion): RedirectResponse
    {
        $inclusion->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Inclusion deleted successfully.']);

        return redirect()->route('admin.inclusions.index');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            Inertia::flash('toast', ['type' => 'error', 'message' => 'No inclusions selected for deletion.']);
            return redirect()->back();
        }

        $deleted = Inclusion::whereIn('id', $ids)->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => "{$deleted} inclusion(s) deleted successfully."]);

        return redirect()->route('admin.inclusions.index');
    }
}