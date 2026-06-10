<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{

    // public function index(Request $request)
    // {
    //     $user = $request->user();
    //     $show = $request->query('show'); // active | deleted

    //     $baseQuery = \App\Models\User::query();

    //     if ($show === 'deleted') {
    //         $baseQuery->onlyTrashed();
    //     }

    //     // SUPER ADMIN
    //     if ($user->hasRole('super admin') || $user->can('view all users')) {

    //         $businessId = $request->query('business_id');

    //         if ($businessId) {
    //             $baseQuery->whereHas('businesses', fn($q) => $q->where('business_id', $businessId))
    //                 ->with(['businesses' => fn($q) => $q->where('business_id', $businessId)]);
    //         } else {
    //             $baseQuery->withCount('businesses');
    //         }

    //         $users = $baseQuery->latest()->paginate(15)->withQueryString();
    //         $allBusinesses = \App\Models\Business::orderBy('name')->get();

    //         return view('users.index', compact('users', 'allBusinesses', 'businessId', 'show'));
    //     }

    //     // NON SUPER
    //     $activeId = $user->current_business_id ?? session('active_business_id')
    //         ?? $user->businesses()->value('business_id');

    //     abort_if(!$activeId, 403, 'No business selected.');

    //     $users = $baseQuery
    //         ->whereHas('businesses', fn($q) => $q->where('business_id', $activeId))
    //         ->with(['businesses' => fn($q) => $q->where('business_id', $activeId)])
    //         ->latest()
    //         ->paginate(15);

    //     return view('users.index', compact('users', 'show'));
    // }


    public function index(Request $request)
    {
        $user = $request->user();
        $show = $request->query('show');

        $baseQuery = \App\Models\User::query();

        if ($show === 'deleted') {
            $baseQuery->onlyTrashed();
        }

        // Filters
        $search = trim($request->query('search', ''));
        $businessId = $request->query('business_id');

        if ($search) {
            $baseQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // SUPER ADMIN
        if ($user->hasRole('super admin') || $user->can('view all users')) {

            if ($businessId) {
                $baseQuery->whereHas('businesses', function ($q) use ($businessId) {
                    $q->where('business_id', $businessId);
                });
            }

            $baseQuery->withCount('businesses');

            $users = $baseQuery
                ->latest()
                ->paginate(15)
                ->withQueryString();

            $allBusinesses = \App\Models\Business::orderBy('name')->get();

            return view('users.index', compact(
                'users',
                'allBusinesses',
                'businessId',
                'search',
                'show'
            ));
        }

        // NON SUPER ADMIN
        $activeId = $user->current_business_id
            ?? session('active_business_id')
            ?? $user->businesses()->value('business_id');

        abort_if(!$activeId, 403, 'No business selected.');

        $users = $baseQuery
            ->whereHas('businesses', fn($q) => $q->where('business_id', $activeId))
            ->withCount('businesses')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('users.index', compact('users', 'search', 'show'));
    }


    public function create()
    {
        $businesses = Business::orderBy('name')->get();
        $roles = Role::orderBy('name')->get();

        return view('users.create', compact('businesses', 'roles'));
    }


    public function store(Request $request)
{
    $data = $request->validate([
        'name'     => ['required', 'string', 'max:255'],
        'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
        'google_drive_folder_id' => ['nullable', 'unique:users,google_drive_folder_id'],
        'password' => ['required', 'confirmed', 'min:8'],
        'phone' => ['required', 'string', 'max:255', 'unique:users,phone'],

        'businesses'   => ['nullable', 'array'],
        'businesses.*' => ['integer', 'exists:businesses,id'],

        // dynamic spatie roles
        'roles'   => ['nullable', 'array'],
        'roles.*' => ['string', 'exists:roles,name'],
    ]);

    DB::transaction(function () use ($data) {
        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'google_drive_folder_id' => $data['google_drive_folder_id'] ?? null,
            'password' => Hash::make($data['password']),
            'phone' => $data['phone'],
        ]);

        // businesses attach
        if (!empty($data['businesses'])) {
            $attach = [];
            foreach ($data['businesses'] as $bid) {
                $attach[$bid] = [];
            }
            $user->businesses()->attach($attach);
        }

        // spatie roles assign
        if (!empty($data['roles'])) {
            $user->assignRole($data['roles']);
        }
    });

    return redirect()->route('users.index')->with('success', 'User created successfully.');
}

    

    public function edit(User $user)
    {
        $businesses = Business::orderBy('name')->get();
        $roles = Role::orderBy('name')->get();

        $selectedBusinesses = $user->businesses()->pluck('businesses.id')->toArray();
        $selectedRoles = $user->roles()->pluck('name')->toArray();

        return view('users.edit', compact('user', 'businesses', 'roles', 'selectedBusinesses', 'selectedRoles'));
    }


    public function update(Request $request, User $user)
{
    $data = $request->validate([
        'name'     => ['required', 'string', 'max:255'],
        'email'    => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        'google_drive_folder_id' => [
            'nullable',
            Rule::unique('users', 'google_drive_folder_id')->ignore($user->id)
        ],
        'password' => ['nullable', 'confirmed', 'min:8'],
        'phone' => ['required', 'string', 'max:255', Rule::unique('users', 'phone')->ignore($user->id)],

        'businesses'   => ['nullable', 'array'],
        'businesses.*' => ['integer', 'exists:businesses,id'],

        'roles'   => ['nullable', 'array'],
        'roles.*' => ['string', 'exists:roles,name'],
    ]);

    DB::transaction(function () use ($data, $user) {
        $user->name  = $data['name'];
        $user->email = $data['email'];
        $user->google_drive_folder_id = $data['google_drive_folder_id'] ?? null;

        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->phone = $data['phone'];
        $user->save();

        // sync businesses
        $syncBusinesses = [];
        foreach (($data['businesses'] ?? []) as $bid) {
            $syncBusinesses[$bid] = [];
        }
        $user->businesses()->sync($syncBusinesses);

        // sync spatie roles
        $user->syncRoles($data['roles'] ?? []);
    });

    return redirect()->route('users.index')->with('success', 'User updated successfully.');
}
    public function destroy(User $user)
    {
        // Detach pivot (optional; cascade is safe too)
        $user->businesses()->detach();
        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }

    public function permissions(User $user){
        $permissions = $user->permissions;
        return view('users.permissions', compact('permissions', 'user'));
    }

    public function permissionRemove(User $user, $permission){
        if (is_string($permission)) {
            $permission = Permission::findByName($permission);
        }

        // remove permission
        $user->revokePermissionTo($permission);

        return back()->with([
            'success' => 'Permission removed successfully'
        ]);
    }

    public function restore($id)
    {
        $user = User::withTrashed()->findOrFail($id);
        $user->restore();

        return back()->with('success', 'User restored successfully.');
    }

    public function forceDelete($id)
    {
        $user = User::withTrashed()->findOrFail($id);
        $user->forceDelete();

        return back()->with('success', 'User permanently deleted.');
    }

}
