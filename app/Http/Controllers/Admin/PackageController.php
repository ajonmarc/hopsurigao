<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePackageRequest;
use App\Http\Requests\Admin\UpdatePackageRequest;
use App\Models\Package;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

class PackageController extends Controller
{
    public function index(Request $request): Response
    {
        $perPage = (int) $request->input('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50, 100]) ? $perPage : 10;

        $packages = Package::query()
            ->when(
                $request->input('search'),
                function (Builder $query, string $search) {
                    $query->where(function (Builder $q) use ($search) {
                        $q->where('package_name', 'like', "%{$search}%")
                            ->orWhere('destination', 'like', "%{$search}%");
                    });
                }
            )
            ->when(
                $request->input('sort'),
                function (Builder $query, string $sort) {
                    foreach (explode(',', $sort) as $field) {
                        $direction = str_starts_with($field, '-') ? 'desc' : 'asc';
                        $column = ltrim($field, '-');

                        if (in_array($column, ['package_name', 'destination', 'price', 'status'])) {
                            $query->orderBy($column, $direction);
                        }
                    }
                },
                fn (Builder $query) => $query->latest()
            )
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('admin/packages/Index', [
            'packages' => $packages,
            'filters' => $request->only('sort', 'search', 'per_page'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/packages/Create');
    }

    public function store(StorePackageRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('packages', 'public');
        }

        Package::create($data);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Package created successfully.']);

        return redirect()->route('admin.packages.index');
    }

    public function update(UpdatePackageRequest $request, Package $package): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            if ($package->image) {
                Storage::disk('public')->delete($package->image);
            }
            $data['image'] = $request->file('image')->store('packages', 'public');
        } else {
            unset($data['image']);
        }

        $package->update($data);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Package updated successfully.']);

        return redirect()->route('admin.packages.index');
    }

    public function destroy(Package $package): RedirectResponse
    {
        if ($package->image) {
            Storage::disk('public')->delete($package->image);
        }

        $package->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Package deleted successfully.']);

        return redirect()->route('admin.packages.index');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            Inertia::flash('toast', ['type' => 'error', 'message' => 'No packages selected for deletion.']);
            return redirect()->back();
        }

        $packages = Package::whereIn('id', $ids)->get();

        foreach ($packages as $package) {
            if ($package->image) {
                Storage::disk('public')->delete($package->image);
            }
        }

        $deleted = Package::whereIn('id', $ids)->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => "{$deleted} package(s) deleted successfully."]);

        return redirect()->route('admin.packages.index');
    }
}