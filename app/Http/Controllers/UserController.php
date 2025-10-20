<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    // List all users
    public function index(Request $request)
    {
        $user = $request->user();

        // SUPER ADMIN → optional filter
        if ($user->hasRole('super admin') || $user->can('view all users')) {
            $businessId = $request->query('business_id'); // may be null for "All"
            $query = \App\Models\User::query();

            if ($businessId) {
                $query->whereHas('businesses', fn($q) => $q->where('business_id', $businessId))
                    ->with(['businesses' => fn($q) => $q->where('business_id', $businessId)]);
            } else {
                // All businesses; keep a light relation for display
                $query->withCount('businesses');
            }

            $users = $query->latest()->paginate(15)->withQueryString();

            // super admin filter dropdown needs list of businesses
            $allBusinesses = \App\Models\Business::orderBy('name')->get();

            return view('users.index', compact('users', 'allBusinesses', 'businessId'));
        }

        // NON-SUPER: restrict to active business
        $activeId = $user->current_business_id ?? session('active_business_id')
            ?? $user->businesses()->value('business_id');

        abort_if(!$activeId, 403, 'No business selected.');

        $users = \App\Models\User::whereHas('businesses', fn($q) => $q->where('business_id', $activeId))
            ->with(['businesses' => fn($q) => $q->where('business_id', $activeId)])
            ->latest()
            ->paginate(15);

        return view('users.index', compact('users'));
    }



    public function create()
    {
        $businesses = Business::orderBy('name')->get(); // assign businesses on create
        $roles = ['owner' => 'Owner', 'admin' => 'Admin', 'staff' => 'Staff'];
        return view('users.create', compact('businesses', 'roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required','string','max:255'],
            'email'    => ['required','email','max:255','unique:users,email'],
            'password' => ['required','confirmed','min:8'],
            // businesses[]: array of business_ids that were checked
            'businesses'          => ['array'],
            'businesses.*'        => ['integer','exists:businesses,id'],
            // roles[business_id] => role string
            'roles'               => ['array'],
            'roles.*'             => ['in:owner,admin,staff'],
        ]);

        DB::transaction(function () use ($request, $data) {
            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            // Attach to selected businesses with roles
            $attach = [];
            foreach ((array)($data['businesses'] ?? []) as $bid) {
                $role = $data['roles'][$bid] ?? 'staff';
                $attach[$bid] = ['role' => $role];
            }
            if ($attach) {
                $user->businesses()->attach($attach);
            }
        });

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        $businesses = Business::orderBy('name')->get();
        $roles = ['owner' => 'Owner', 'admin' => 'Admin', 'staff' => 'Staff'];

        // existing roles per business (pivot)
        $pivotRoles = $user->businesses()
            ->pluck('business_user.role', 'business_id')
            ->toArray();

        return view('users.edit', compact('user','businesses','roles','pivotRoles'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'     => ['required','string','max:255'],
            'email'    => ['required','email','max:255', Rule::unique('users','email')->ignore($user->id)],
            'password' => ['nullable','confirmed','min:8'],
            'businesses'   => ['array'],
            'businesses.*' => ['integer','exists:businesses,id'],
            'roles'        => ['array'],
            'roles.*'      => ['in:owner,admin,staff'],
        ]);

        DB::transaction(function () use ($request, $user, $data) {
            // Update main fields
            $user->name  = $data['name'];
            $user->email = $data['email'];
            if (!empty($data['password'])) {
                $user->password = Hash::make($data['password']);
            }
            $user->save();

            // Sync businesses + roles
            $selected = (array)($data['businesses'] ?? []);
            $sync = [];
            foreach ($selected as $bid) {
                $role = $data['roles'][$bid] ?? 'staff';
                $sync[$bid] = ['role' => $role];
            }
            // Remove unselected; keep selected with roles
            $user->businesses()->sync($sync);
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
}
