<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\User;
use Illuminate\Http\Request;

class RecycleController extends Controller
{
     public function index(Request $request)
    {
        $type = $request->get('type', 'users');

        $users = collect();
        $businesses = collect();

        if ($type === 'users') {
            $users = User::onlyTrashed()
                ->latest('deleted_at')
                ->paginate(20);
        }

        if ($type === 'businesses') {
            $businesses = Business::onlyTrashed()
                ->latest('deleted_at')
                ->paginate(20);
        }

        return view('recycle.index', compact('type', 'users', 'businesses'));
    }

    /**
     * Restore single user
     */
    public function restoreUser($id)
    {
        $user = User::onlyTrashed()->findOrFail($id);
        $user->restore();

        return back()->with('success', 'User successfully restore ho gaya.');
    }

    /**
     * Permanently delete single user
     */
    public function forceDeleteUser($id)
    {
        $user = User::onlyTrashed()->findOrFail($id);
        $user->forceDelete();

        return back()->with('success', 'User permanently delete ho gaya.');
    }

    /**
     * Restore single business
     */
    public function restoreBusiness($id)
    {
        $business = Business::onlyTrashed()->findOrFail($id);
        $business->restore();

        return back()->with('success', 'Business successfully restore ho gaya.');
    }

    /**
     * Permanently delete single business
     */
    public function forceDeleteBusiness($id)
    {
        $business = Business::onlyTrashed()->findOrFail($id);
        $business->forceDelete();

        return back()->with('success', 'Business permanently delete ho gaya.');
    }

    /**
     * Bulk restore
     */
    public function bulkRestore(Request $request)
    {
        $request->validate([
            'type' => 'required|in:users,businesses',
            'ids' => 'required|array',
            'ids.*' => 'integer',
        ]);

        if ($request->type === 'users') {
            User::onlyTrashed()
                ->whereIn('id', $request->ids)
                ->restore();
        }

        if ($request->type === 'businesses') {
            Business::onlyTrashed()
                ->whereIn('id', $request->ids)
                ->restore();
        }

        return back()->with('success', 'Selected records restore ho gaye.');
    }

    /**
     * Bulk permanent delete
     */
    public function bulkForceDelete(Request $request)
    {
        $request->validate([
            'type' => 'required|in:users,businesses',
            'ids' => 'required|array',
            'ids.*' => 'integer',
        ]);

        if ($request->type === 'users') {
            User::onlyTrashed()
                ->whereIn('id', $request->ids)
                ->forceDelete();
        }

        if ($request->type === 'businesses') {
            Business::onlyTrashed()
                ->whereIn('id', $request->ids)
                ->forceDelete();
        }

        return back()->with('success', 'Selected records permanently delete ho gaye.');
    }

    /**
     * Empty recycle bin by type
     */
    public function empty(Request $request)
    {
        $request->validate([
            'type' => 'required|in:users,businesses',
        ]);

        if ($request->type === 'users') {
            User::onlyTrashed()->forceDelete();
        }

        if ($request->type === 'businesses') {
            Business::onlyTrashed()->forceDelete();
        }

        return back()->with('success', 'Recycle bin empty ho gaya.');
    }
}
