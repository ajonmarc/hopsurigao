<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $perPage = (int) $request->input('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50, 100]) ? $perPage : 10;

        $users = User::query()
            ->with('role') // Make sure role is loaded
            ->when(
                $request->input('search'),
                function (Builder $query, string $search) {
                    $query->where(function (Builder $q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
                }
            )
            ->when(
                $request->input('sort'),
                function (Builder $query, string $sort) {
                    foreach (explode(',', $sort) as $field) {
                        $direction = str_starts_with($field, '-') ? 'desc' : 'asc';
                        $column = ltrim($field, '-');

                        if (in_array($column, ['name', 'email'])) {
                            $query->orderBy($column, $direction);
                        }
                    }
                },
                fn(Builder $query) => $query->latest()
            )
            ->paginate($perPage)
            ->withQueryString();

        // Add roles to the index page as well (for the edit modal)
        $roles = Role::select('id', 'name')->get();

        return Inertia::render('admin/users/Index', [
            'users' => $users,
            'roles' => $roles, // Pass roles to the index page
            'filters' => $request->only('sort', 'search', 'per_page'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/users/Create', [
            'roles' => Role::select('id', 'name')->get(),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        User::create([
            ...$request->validated(),
            'password' => Hash::make($request->validated('password')),
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'User created successfully.']);

        return redirect()->route('admin.users.index');
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        if ($user->is_protected) {
            Inertia::flash('toast', ['type' => 'error', 'message' => 'This account is protected and cannot be modified.']);
            return redirect()->back();
        }

        $data = $request->validated();

        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'User updated successfully.']);

        return redirect()->route('admin.users.index');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->is_protected) {
            Inertia::flash('toast', ['type' => 'error', 'message' => 'This account is protected and cannot be deleted.']);
            return redirect()->back();
        }

        $user->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'User deleted successfully.']);

        return redirect()->route('admin.users.index');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            Inertia::flash('toast', ['type' => 'error', 'message' => 'No users selected for deletion.']);
            return redirect()->back();
        }

        $validIds = User::whereIn('id', $ids)
            ->where('is_protected', false)
            ->pluck('id')
            ->toArray();

        if (empty($validIds)) {
            Inertia::flash('toast', ['type' => 'error', 'message' => 'No valid users found for deletion.']);
            return redirect()->back();
        }

        $deleted = User::whereIn('id', $validIds)->delete();

        $skipped = count($ids) - count($validIds);
        $message = $deleted . ' user(s) deleted successfully.';
        if ($skipped > 0) {
            $message .= " {$skipped} protected account(s) were skipped.";
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => $message]);

        return redirect()->route('admin.users.index');
    }
}
