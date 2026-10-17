<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionController extends Controller
{
    /**
     * Permissions & Roles page
     */
    public function index()
    {
        $authUser = auth()->user();

        /*
        |--------------------------------------------------------------------------
        | Super Admin / Admin
        |--------------------------------------------------------------------------
        |
        | Sabhi users dikhेंगे.
        | businesses eager load ki gayi hain taaki dropdown me
        | business name + pivot role show kar sakein.
        |
        */
        if (
            $authUser->hasRole('super admin') ||
            $authUser->hasRole('admin')
        ) {

            $users = User::query()
                ->with([
                    'businesses' => function ($query) {
                        $query->select('businesses.id', 'businesses.name');
                    }
                ])
                ->orderBy('name')
                ->get();

            $permissions = Permission::query()
                ->orderBy('name')
                ->get();

            $roles = Role::query()
                ->orderBy('name')
                ->get();

        } else {

            /*
            |--------------------------------------------------------------------------
            | Normal User
            |--------------------------------------------------------------------------
            |
            | Login user jin businesses se business_user pivot table ke through
            | connected hai, unhi businesses ke users dikhेंगे.
            |
            */

            $businessIds = $authUser->businesses()
                ->pluck('businesses.id')
                ->toArray();

            /*
             * Purane data ke liye fallback:
             * agar business_user pivot entry nahi hai lekin users.business_id hai.
             */
            if (
                empty($businessIds) &&
                !empty($authUser->business_id)
            ) {
                $businessIds = [$authUser->business_id];
            }

            $users = User::query()
                ->with([
                    'businesses' => function ($query) {
                        $query->select('businesses.id', 'businesses.name');
                    }
                ])
                ->where(function (Builder $query) use ($businessIds) {

                    if (!empty($businessIds)) {
                        $query->whereHas('businesses', function (Builder $businessQuery) use ($businessIds) {
                            $businessQuery->whereIn(
                                'businesses.id',
                                $businessIds
                            );
                        });

                        /*
                         * Legacy users.business_id fallback
                         */
                        $query->orWhereIn(
                            'business_id',
                            $businessIds
                        );
                    } else {
                        /*
                         * Agar login user ka koi business nahi mila
                         * to sirf wahi login user dikhao.
                         */
                        $query->where('id', auth()->id());
                    }
                })
                ->orderBy('name')
                ->get();

            $permissions = Permission::query()
                ->where('guard_name', 'web')
                ->orderBy('name')
                ->get();

            $roles = Role::query()
                ->where('guard_name', 'web')
                ->orderBy('name')
                ->get();
        }

        return view(
            'permissions.index',
            compact(
                'permissions',
                'users',
                'roles'
            )
        );
    }

    /**
     * Assign permissions to user and/or role.
     */
    public function assign(Request $request)
    {
        $request->validate([
            'user_id' => [
                'nullable',
                'exists:users,id',
            ],

            'role_id' => [
                'nullable',
                'exists:roles,id',
            ],

            'guard_name' => [
                'nullable',
                Rule::in(['web', 'api']),
            ],

            'permissions' => [
                'required',
                'array',
                'min:1',
            ],

            'permissions.*' => [
                'required',
                'string',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | At least one target
        |--------------------------------------------------------------------------
        */
        if (
            !$request->filled('user_id') &&
            !$request->filled('role_id')
        ) {
            return back()
                ->withErrors([
                    'target' => 'Please select at least one User or Role.',
                ])
                ->withInput();
        }

        $guard = $request->input('guard_name', 'web');

        /*
        |--------------------------------------------------------------------------
        | Get only valid permissions for selected guard
        |--------------------------------------------------------------------------
        */
        $permissions = Permission::query()
            ->where('guard_name', $guard)
            ->whereIn(
                'name',
                $request->input('permissions', [])
            )
            ->get();

        if ($permissions->isEmpty()) {
            return back()
                ->withErrors([
                    'permissions' => 'Selected permissions do not match the selected guard.',
                ])
                ->withInput();
        }

        $messages = [];

        /*
        |--------------------------------------------------------------------------
        | Assign permission directly to user
        |--------------------------------------------------------------------------
        */
        if ($request->filled('user_id')) {

            $user = $this->getAccessibleUser(
                (int) $request->user_id
            );

            /*
             * Spatie ko permission objects de rahe hain.
             * Isse guard related issues kam rahenge.
             */
            $user->givePermissionTo($permissions);

            $messages[] = 'User permissions assigned successfully';
        }

        /*
        |--------------------------------------------------------------------------
        | Assign permission to role
        |--------------------------------------------------------------------------
        */
        if ($request->filled('role_id')) {

            $role = Role::query()
                ->where('id', $request->role_id)
                ->where('guard_name', $guard)
                ->first();

            if (!$role) {
                return back()
                    ->withErrors([
                        'role_id' => 'Selected role does not belong to the selected guard.',
                    ])
                    ->withInput();
            }

            $role->givePermissionTo($permissions);

            $messages[] = 'Role permissions assigned successfully';
        }

        return back()->with(
            'success',
            implode(' & ', $messages) . '.'
        );
    }

    /**
     * Create Permission
     */
    public function store(Request $request)
    {
        $guard = $request->input('guard_name', 'web');

        $request->merge([
            'guard_name' => $guard,
        ]);

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:191',

                /*
                 * Spatie me same permission name alag guard me ho sakta hai.
                 */
                Rule::unique('permissions', 'name')
                    ->where(function ($query) use ($guard) {
                        return $query->where(
                            'guard_name',
                            $guard
                        );
                    }),
            ],

            'guard_name' => [
                'required',
                Rule::in(['web', 'api']),
            ],
        ]);

        Permission::create([
            'name' => trim($data['name']),
            'guard_name' => $data['guard_name'],
        ]);

        app()[\Spatie\Permission\PermissionRegistrar::class]
            ->forgetCachedPermissions();

        return back()->with(
            'success',
            'Permission created successfully.'
        );
    }

    /**
     * Create Role
     */
    public function storeRole(Request $request)
    {
        $guard = $request->input('guard_name', 'web');

        $request->merge([
            'guard_name' => $guard,
        ]);

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:191',

                Rule::unique('roles', 'name')
                    ->where(function ($query) use ($guard) {
                        return $query->where(
                            'guard_name',
                            $guard
                        );
                    }),
            ],

            'guard_name' => [
                'required',
                Rule::in(['web', 'api']),
            ],
        ]);

        Role::create([
            'name' => trim($data['name']),
            'guard_name' => $data['guard_name'],
        ]);

        app()[\Spatie\Permission\PermissionRegistrar::class]
            ->forgetCachedPermissions();

        return back()->with(
            'success',
            'Role created successfully.'
        );
    }

    /**
     * Delete Permission
     */
    public function destroy(Permission $permission)
    {
        $permission->delete();

        app()[\Spatie\Permission\PermissionRegistrar::class]
            ->forgetCachedPermissions();

        return back()->with(
            'success',
            'Permission deleted successfully.'
        );
    }

    /**
     * Delete Role
     */
    public function destroyRole(Role $role)
    {
        $role->delete();

        app()[\Spatie\Permission\PermissionRegistrar::class]
            ->forgetCachedPermissions();

        return back()->with(
            'success',
            'Role deleted successfully.'
        );
    }

    /**
     * Check whether selected user is accessible
     * by current logged-in user.
     */
    private function getAccessibleUser(int $userId): User
    {
        $authUser = auth()->user();

        /*
         * Admin / Super Admin can manage everyone.
         */
        if (
            $authUser->hasRole('super admin') ||
            $authUser->hasRole('admin')
        ) {
            return User::findOrFail($userId);
        }

        /*
         * Login user ke businesses.
         */
        $businessIds = $authUser->businesses()
            ->pluck('businesses.id')
            ->toArray();

        /*
         * Legacy fallback
         */
        if (
            empty($businessIds) &&
            !empty($authUser->business_id)
        ) {
            $businessIds = [$authUser->business_id];
        }

        /*
         * Koi business nahi mila.
         */
        if (empty($businessIds)) {
            return User::query()
                ->where('id', auth()->id())
                ->where('id', $userId)
                ->firstOrFail();
        }

        return User::query()
            ->where('id', $userId)
            ->where(function (Builder $query) use ($businessIds) {

                $query->whereHas(
                    'businesses',
                    function (Builder $businessQuery) use ($businessIds) {
                        $businessQuery->whereIn(
                            'businesses.id',
                            $businessIds
                        );
                    }
                );

                /*
                 * Legacy users.business_id fallback
                 */
                $query->orWhereIn(
                    'business_id',
                    $businessIds
                );
            })
            ->firstOrFail();
    }
}