<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $auth = $request->user();

        // SUPER ADMIN → optional filter
        if ($auth->hasRole('super admin') || $auth->can('view all users')) {

            $businessId = $request->query('business_id'); // may be null for "All"
            $perPage = (int)($request->query('per_page', 15));

            $query = User::query();

            if ($businessId) {
                $query->whereHas('businesses', fn($q) => $q->where('business_id', $businessId))
                    ->with(['businesses' => fn($q) => $q->where('business_id', $businessId)]);
            } else {
                // All businesses; keep a light relation for display
                $query->withCount('businesses');
            }

            $users = $query->latest()
                ->paginate($perPage)
                ->withQueryString();

            // Optional: dropdown list for UI
            $allBusinesses = Business::orderBy('name')->get(['id','name','slug']);

            return response()->json([
                'status' => true,
                'message' => 'Users fetched successfully.',
                'data' => [
                    'users' => $users,
                    'filters' => [
                        'business_id' => $businessId,
                        'businesses' => $allBusinesses,
                    ]
                ],
            ]);
        }

        // NON-SUPER: restrict to active business
        $activeId = $auth->current_business_id
            ?? session('active_business_id')
            ?? $auth->businesses()->value('business_id');

        if (!$activeId) {
            return response()->json([
                'status' => false,
                'message' => 'No business selected.',
            ], 403);
        }

        $perPage = (int)($request->query('per_page', 15));

        $users = User::whereHas('businesses', fn($q) => $q->where('business_id', $activeId))
            ->with(['businesses' => fn($q) => $q->where('business_id', $activeId)])
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return response()->json([
            'status' => true,
            'message' => 'Users fetched successfully.',
            'data' => [
                'active_business_id' => $activeId,
                'users' => $users,
            ],
        ]);
    }


    // POST /api/users
    // public function store(Request $request)
    // {
    //     $data = $request->validate([
    //         'name'     => ['required','string','max:255'],
    //         'email'    => ['required','email','max:255','unique:users,email'],
    //         'password' => ['required','confirmed','min:8'],

    //         // businesses[]: array of business_ids that were checked
    //         'businesses'          => ['nullable','array'],
    //         'businesses.*'        => ['integer','exists:businesses,id'],

    //         // roles[business_id] => role string
    //         'roles'               => ['nullable','array'],
    //         'roles.*'             => ['in:owner,admin,staff'],
    //     ]);

    //     $user = DB::transaction(function () use ($data) {
    //         $user = User::create([
    //             'name'     => $data['name'],
    //             'email'    => $data['email'],
    //             'password' => Hash::make($data['password']),
    //         ]);

    //         // Attach to selected businesses with roles
    //         $attach = [];
    //         foreach ((array)($data['businesses'] ?? []) as $bid) {
    //             $role = $data['roles'][$bid] ?? 'staff';
    //             $attach[$bid] = ['role' => $role];
    //         }

    //         if (!empty($attach)) {
    //             $user->businesses()->attach($attach);
    //         }

    //         return $user;
    //     });

    //     $user->load(['businesses:id,name,slug']);

    //     return response()->json([
    //         'status' => true,
    //         'message' => 'User created successfully.',
    //         'data' => $user,
    //     ], 201);
    // }




    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', 'min:8'],

            // businesses[]: selected business IDs
            'businesses'   => ['nullable', 'array'],
            'businesses.*' => ['integer', 'exists:businesses,id'],

            // Business pivot roles
            'roles'   => ['nullable', 'array'],
            'roles.*' => ['in:owner,admin,staff'],

            // Optional Spatie role
            'spatie_role' => ['nullable', 'string'],
        ]);

        $user = DB::transaction(function () use ($data) {

            /*
            |--------------------------------------------------------------------------
            | Create User
            |--------------------------------------------------------------------------
            */
            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            /*
            |--------------------------------------------------------------------------
            | Attach Businesses
            |--------------------------------------------------------------------------
            */
            $attach = [];

            foreach ((array) ($data['businesses'] ?? []) as $businessId) {

                $businessRole = $data['roles'][$businessId] ?? 'staff';

                $attach[$businessId] = [
                    'role' => $businessRole,
                ];
            }

            if (!empty($attach)) {
                $user->businesses()->attach($attach);
            }

            /*
            |--------------------------------------------------------------------------
            | Spatie Role
            |--------------------------------------------------------------------------
            |
            | Request me spatie_role aaya to wahi assign hoga.
            | Agar nahi aaya to default "user" role assign hoga.
            |
            */

            $roleName = $data['spatie_role'] ?? 'user';

            // Role nahi hai to create bhi kar dega
            Role::firstOrCreate([
                'name'       => $roleName,
                'guard_name' => 'web',
            ]);

            $user->assignRole($roleName);

            return $user;
        });

        $user->load([
            'businesses:id,name,slug',
            'roles',
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'User created successfully.',
            'data'    => $user,
        ], 201);
    }

    // PUT/PATCH /api/users/{user}
    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'     => ['required','string','max:255'],
            'email'    => ['required','email','max:255', Rule::unique('users','email')->ignore($user->id)],
            'password' => ['nullable','confirmed','min:8'],

            'businesses'   => ['nullable','array'],
            'businesses.*' => ['integer','exists:businesses,id'],

            'roles'        => ['nullable','array'],
            'roles.*'      => ['in:owner,admin,staff'],
        ]);

        $user = DB::transaction(function () use ($user, $data) {
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

            $user->businesses()->sync($sync);

            return $user;
        });

        $user->load(['businesses:id,name,slug']);

        return response()->json([
            'status' => true,
            'message' => 'User updated successfully.',
            'data' => $user,
        ]);
    }

    // DELETE /api/users/{user}
    public function destroy(Request $request, User $user)
    {
        DB::transaction(function () use ($user) {
            $user->businesses()->detach();
            $user->delete();
        });

        return response()->json([
            'status' => true,
            'message' => 'User deleted successfully.',
        ]);
    }
}
