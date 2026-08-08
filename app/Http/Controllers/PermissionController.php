<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use phpDocumentor\Reflection\DocBlock\Tags\Author;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rule;

class PermissionController extends Controller
{
    public function index()
    {
        if (auth()->user()->hasRole('super admin') || auth()->user()->hasRole('admin')) {
            $users = User::orderBy('name')->get();
            $permissions = Permission::orderBy('name')->get();
            $roles = Role::orderBy('name')->get();
        } else {
            $users = User::where('business_id', auth()->user()->business_id)
                ->orderBy('name')
                ->get();

            $permissions = Permission::where('guard_name', 'web')
                ->orderBy('name')
                ->get();

            $roles = Role::where('guard_name', 'web')
                ->orderBy('name')
                ->get();
        }

        return view('permissions.index', compact('permissions', 'users', 'roles'));
    }

    public function assign(Request $request)
    {
        $request->validate([
            'user_id'       => ['nullable', 'exists:users,id'],
            'role_id'       => ['nullable', 'exists:roles,id'],
            'guard_name'    => ['nullable', Rule::in(['web', 'api'])],
            'permissions'   => ['required', 'array', 'min:1'],
            'permissions.*' => ['required', 'string', 'exists:permissions,name'],
        ]);

        if (!$request->filled('user_id') && !$request->filled('role_id')) {
            return back()
                ->withErrors(['target' => 'Please select at least one User or Role.'])
                ->withInput();
        }

        $guard = $request->guard_name ?? 'web';

        $permissions = Permission::whereIn('name', $request->permissions)
            ->where('guard_name', $guard)
            ->pluck('name')
            ->toArray();

        if (empty($permissions)) {
            return back()
                ->withErrors(['permissions' => 'Selected permissions do not match the selected guard.'])
                ->withInput();
        }

        $messages = [];

        if ($request->filled('user_id')) {
            $user = User::findOrFail($request->user_id);
            $user->givePermissionTo($permissions);
            $messages[] = 'User permissions assigned successfully';
        }

        if ($request->filled('role_id')) {
            $role = Role::where('id', $request->role_id)
                ->where('guard_name', $guard)
                ->firstOrFail();

            $role->givePermissionTo($permissions);
            $messages[] = 'Role permissions assigned successfully';
        }

        return back()->with('success', implode(' & ', $messages) . '.');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:191|unique:permissions,name',
            'guard_name' => 'nullable|string|in:web,api',
        ]);

        $data['guard_name'] = $data['guard_name'] ?? 'web';

        Permission::create($data);

        return back()->with('success', 'Permission created successfully.');
    }

    public function storeRole(Request $request)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:191|unique:roles,name',
            'guard_name' => 'nullable|string|in:web,api',
        ]);

        $data['guard_name'] = $data['guard_name'] ?? 'web';

        Role::create($data);

        return back()->with('success', 'Role created successfully.');
    }

    public function destroy(Permission $permission)
    {
        $permission->delete();

        return back()->with('success', 'Permission deleted successfully.');
    }

    public function destroyRole(Role $role)
    {
        $role->delete();

        return back()->with('success', 'Role deleted successfully.');
    }


}
