<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        return view('recycle.index', compact(
            'type',
            'users',
            'businesses'
        ));
    }

    public function restoreUser($id)
    {
        $user = User::onlyTrashed()->findOrFail($id);
        $user->restore();

        return back()->with(
            'success',
            'User successfully restore ho gaya.'
        );
    }

    public function forceDeleteUser($id)
    {
        $user = User::onlyTrashed()->findOrFail($id);

        DB::transaction(function () use ($user) {
            $this->forceDeleteUserWithEmptyBusinesses($user);
        });

        return back()->with(
            'success',
            'User permanently delete ho gaya. Empty businesses bhi permanently delete ho gaye.'
        );
    }

    public function restoreBusiness($id)
    {
        $business = Business::onlyTrashed()->findOrFail($id);
        $business->restore();

        return back()->with(
            'success',
            'Business successfully restore ho gaya.'
        );
    }

    public function forceDeleteBusiness($id)
    {
        $business = Business::onlyTrashed()->findOrFail($id);

        DB::transaction(function () use ($business) {
            $this->forceDeleteBusinessWithUsers($business);
        });

        return back()->with(
            'success',
            'Business aur uske sabhi users permanently delete ho gaye.'
        );
    }

    public function bulkRestore(Request $request)
    {
        $data = $request->validate([
            'type'  => ['required', 'in:users,businesses'],
            'ids'   => ['required', 'array'],
            'ids.*' => ['integer'],
        ]);

        if ($data['type'] === 'users') {
            User::onlyTrashed()
                ->whereIn('id', $data['ids'])
                ->restore();
        }

        if ($data['type'] === 'businesses') {
            Business::onlyTrashed()
                ->whereIn('id', $data['ids'])
                ->restore();
        }

        return back()->with(
            'success',
            'Selected records restore ho gaye.'
        );
    }

    public function bulkForceDelete(Request $request)
    {
        $data = $request->validate([
            'type'  => ['required', 'in:users,businesses'],
            'ids'   => ['required', 'array'],
            'ids.*' => ['integer'],
        ]);

        DB::transaction(function () use ($data) {
            if ($data['type'] === 'users') {
                $users = User::onlyTrashed()
                    ->whereIn('id', $data['ids'])
                    ->get();

                foreach ($users as $user) {
                    $this->forceDeleteUserWithEmptyBusinesses($user);
                }
            }

            if ($data['type'] === 'businesses') {
                $businesses = Business::onlyTrashed()
                    ->whereIn('id', $data['ids'])
                    ->get();

                foreach ($businesses as $business) {
                    if ($business->exists) {
                        $this->forceDeleteBusinessWithUsers($business);
                    }
                }
            }
        });

        return back()->with(
            'success',
            'Selected records aur unse jude records permanently delete ho gaye.'
        );
    }

    public function empty(Request $request)
    {
        $data = $request->validate([
            'type' => ['required', 'in:users,businesses'],
        ]);

        DB::transaction(function () use ($data) {
            if ($data['type'] === 'users') {
                $users = User::onlyTrashed()->get();

                foreach ($users as $user) {
                    if ($user->exists) {
                        $this->forceDeleteUserWithEmptyBusinesses($user);
                    }
                }
            }

            if ($data['type'] === 'businesses') {
                $businesses = Business::onlyTrashed()->get();

                foreach ($businesses as $business) {
                    if ($business->exists) {
                        $this->forceDeleteBusinessWithUsers($business);
                    }
                }
            }
        });

        return back()->with(
            'success',
            'Recycle bin empty ho gaya.'
        );
    }

    private function forceDeleteUserWithEmptyBusinesses(User $user): void
    {
        $businessIds = DB::table('business_user')
            ->where('user_id', $user->id)
            ->pluck('business_id')
            ->unique()
            ->values();

        DB::table('business_user')
            ->where('user_id', $user->id)
            ->delete();

        $user->forceDelete();

        foreach ($businessIds as $businessId) {
            $otherUserExists = DB::table('business_user')
                ->where('business_id', $businessId)
                ->exists();

            if (!$otherUserExists) {
                $business = Business::withTrashed()->find($businessId);

                if ($business) {
                    $business->forceDelete();
                }
            }
        }
    }

    private function forceDeleteBusinessWithUsers(Business $business): void
    {
        $userIds = DB::table('business_user')
            ->where('business_id', $business->id)
            ->pluck('user_id')
            ->unique()
            ->values();

        DB::table('business_user')
            ->where('business_id', $business->id)
            ->delete();

        foreach ($userIds as $userId) {
            $user = User::withTrashed()->find($userId);

            if (!$user) {
                continue;
            }

            DB::table('business_user')
                ->where('user_id', $user->id)
                ->delete();

            $user->forceDelete();
        }

        $business->forceDelete();
    }
}