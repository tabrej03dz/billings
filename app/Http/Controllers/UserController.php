<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // List all users
    public function index()
    {
        $users = User::with('business')->latest()->get();
        return view('users.index', compact('users'));
    }

    // Show form to create a new user
    public function create()
    {
        $businesses = Business::all();
        return view('users.form', [
            'businesses' => $businesses
        ]);
    }

    // Store new user
    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'required|email|unique:users,email',
            'password'    => 'required|string|min:6|confirmed',
            'business_id' => 'nullable|exists:businesses,id',
            'role' => 'nullable',
        ]);

        $user = User::create([
            'name'        => $request->name,
            'email'       => $request->email,
            'password'    => Hash::make($request->password),
            'business_id' => $request->business_id ?? auth()->user()->business_id,
        ]);

// Assign role if provided, fallback to 'user'
        if ($request->filled('role')) {
            $user->assignRole($request->role);
        } else {
            $user->assignRole('user');
        }

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }



    // Show form to edit user
    public function edit(User $user)
    {
        $businesses = Business::all();
        return view('users.form', compact('user', 'businesses'));
    }

    // Update user details
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'required|email|unique:users,email,' . $user->id,
            'password'    => 'nullable|string|min:6|confirmed',
            'business_id' => 'nullable|exists:businesses,id',
            'role'        => 'nullable',
        ]);

        $user->update([
            'name'        => $request->name,
            'email'       => $request->email,
            'business_id' => $request->business_id ?? auth()->user()->business_id,
            'password'    => $request->filled('password') ? Hash::make($request->password) : $user->password,
        ]);

        // Sync or assign role
        if ($request->filled('role')) {
            $user->syncRoles([$request->role]);
        } else {
            $user->syncRoles(['user']);
        }

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }


    // Delete a user
    public function delete(User $user)
    {
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }
}
