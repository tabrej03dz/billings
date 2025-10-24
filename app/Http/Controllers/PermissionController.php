<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use phpDocumentor\Reflection\DocBlock\Tags\Author;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index(){

//        Permission::create(['name' => 'create business']);
//        Permission::create(['name' => 'create user']);
//        Permission::create(['name' => 'create client']);
//        Permission::create(['name' => 'create invoice']);
//        Permission::create(['name' => 'show permissions']);
//        Permission::create(['name' => 'assign permissions']);
//        dd('permission created');
        if (auth()->user()->role('super admin')){
            $users = User::all();
            $permissions = Permission::all();
        }else{
            $users = User::where('business_id', auth()->user()->business_id)->get();
            $permissions = auth()->user()->getAllPermissions();
        }
        return view('permissions.index', compact('permissions', 'users'));
    }

    public function assign(Request $request)
    {
        $request->validate([
            'user' => 'required|exists:users,id',
            'permissions' => 'array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $user = User::findOrFail($request->user);

        // Add permissions without removing existing ones
        if (!empty($request->permissions)) {
            $user->givePermissionTo($request->permissions);
        }

        return back()->with('success', 'Permissions added successfully.');
    }


    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:191|unique:permissions,name',
            'guard_name' => 'nullable|string|in:web,api'
        ]);

        $data['guard_name'] = $data['guard_name'] ?? 'web';
        Permission::create($data);

        return back()->with('success', 'Permission created successfully.');
    }

    public function destroy(Permission $permission)
    {
        $permission->delete();
        return back()->with('success', 'Permission deleted successfully.');
    }


}
