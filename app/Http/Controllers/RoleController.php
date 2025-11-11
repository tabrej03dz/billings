<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
//    public function index()
//    {
//        $users = User::select('id','name')->orderBy('name')->get();
//        $roles = Role::orderBy('name')->get();
//
//        return view('roles.index', compact('users','roles'));
//    }
//
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:191|unique:roles,name',
            'guard_name' => 'nullable|in:web,api'
        ]);
        $data['guard_name'] = $data['guard_name'] ?? 'web';

        Role::create($data);

        return back()->with('success','Role created successfully.');
    }
//
//    public function assign(Request $request)
//    {
//        $validated = $request->validate([
//            'user' => 'required|exists:users,id',
//            'roles' => 'required|array|min:1',
//            'roles.*' => 'string|exists:roles,name',
//        ]);
//
//        $user = User::findOrFail($validated['user']);
//        // attach without removing existing other roles; use syncRoles if you want replace
//        $user->syncRoles(array_unique(array_merge($user->getRoleNames()->toArray(), $validated['roles'])));
//
//        return back()->with('success','Roles assigned successfully.');
//    }
//
    public function destroy(Role $role)
    {
        // Optional: detach related permissions
        $role->permissions()->detach();
        $role->delete();

        return back()->with('success','Role deleted successfully.');
    }


    public function index()
    {
        $users = \App\Models\User::select('id','name')->orderBy('name')->get();
        $roles = Role::with('permissions:id,name')->orderBy('name')->get();
        $permissions = Permission::select('id','name')->orderBy('name')->get();

        // Build a simple map: role_id => [permission names]
        $rolePerms = $roles->mapWithKeys(fn($r) => [
            $r->id => $r->permissions->pluck('name')->values()
        ]);

        return view('roles.index', [
            'users' => $users,
            'roles' => $roles,
            'permissions' => $permissions,
            'rolePerms' => $rolePerms,
        ]);
    }

    public function syncPermissions(Request $request, Role $role)
    {
        // Validate incoming checkbox list (permission names)
        $data = $request->validate([
            'permissions'   => ['array'],
            'permissions.*' => ['string'],
        ]);

        // Sync by names (Spatie supports names or ids)
        $role->syncPermissions($data['permissions'] ?? []);

        return back()->with('success', "Permissions updated for role: {$role->name}");
    }
}
