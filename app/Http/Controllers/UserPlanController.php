<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\User;
use App\Models\UserPlan;
use Carbon\Carbon;
use Illuminate\Http\Request;

class UserPlanController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q'));

        $userPlans = UserPlan::with(['user', 'plan'])
            ->when($q !== '', function ($query) use ($q) {
                $query->whereHas('user', function ($subQuery) use ($q) {
                    $subQuery->where('name', 'like', "%{$q}%")
                            ->orWhere('email', 'like', "%{$q}%");
                })->orWhereHas('plan', function ($subQuery) use ($q) {
                    $subQuery->where('name', 'like', "%{$q}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('user-plans.index', compact('userPlans', 'q'));
    }

    public function create()
    {
        $users = User::orderBy('name')->get();
        $plans = Plan::where('status', 1)->orderBy('name')->get();

        return view('user-plans.create', compact('users', 'plans'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id'     => ['required', 'exists:users,id'],
            'plan_id'     => ['required', 'exists:plans,id'],
            'start_date'  => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status'      => ['nullable', 'boolean'],
        ]);

        $plan = Plan::findOrFail($validated['plan_id']);

        $startDate = !empty($validated['start_date'])
            ? Carbon::parse($validated['start_date'])
            : now();

        $expiryDate = !empty($validated['expiry_date'])
            ? Carbon::parse($validated['expiry_date'])
            : (clone $startDate)->addDays((int) $plan->duration_days);

        UserPlan::create([
            'user_id'     => $validated['user_id'],
            'plan_id'     => $validated['plan_id'],
            'start_date'  => $startDate->toDateString(),
            'expiry_date' => $expiryDate->toDateString(),
            'status'      => $request->boolean('status', true),
        ]);

        return redirect()
            ->route('user-plans.index')
            ->with('success', 'User plan created successfully.');
    }

    public function show($id)
    {
        $userPlan = UserPlan::with(['user', 'plan'])->findOrFail($id);

        return view('user-plans.show', compact('userPlan'));
    }

    public function edit($id)
    {
        $userPlan = UserPlan::findOrFail($id);
        $users = User::orderBy('name')->get();
        $plans = Plan::where('status', 1)->orderBy('name')->get();

        return view('user-plans.edit', compact('userPlan', 'users', 'plans'));
    }

    public function update(Request $request, $id)
    {
        $userPlan = UserPlan::findOrFail($id);

        $validated = $request->validate([
            'user_id'     => ['required', 'exists:users,id'],
            'plan_id'     => ['required', 'exists:plans,id'],
            'start_date'  => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status'      => ['nullable', 'boolean'],
        ]);

        $plan = Plan::findOrFail($validated['plan_id']);

        $startDate = !empty($validated['start_date'])
            ? Carbon::parse($validated['start_date'])
            : now();

        $expiryDate = !empty($validated['expiry_date'])
            ? Carbon::parse($validated['expiry_date'])
            : (clone $startDate)->addDays((int) $plan->duration_days);

        $userPlan->update([
            'user_id'     => $validated['user_id'],
            'plan_id'     => $validated['plan_id'],
            'start_date'  => $startDate->toDateString(),
            'expiry_date' => $expiryDate->toDateString(),
            'status'      => $request->boolean('status', true),
        ]);

        return redirect()
            ->route('user-plans.index')
            ->with('success', 'User plan updated successfully.');
    }

    public function destroy($id)
    {
        $userPlan = UserPlan::findOrFail($id);
        $userPlan->delete();

        return redirect()
            ->route('user-plans.index')
            ->with('success', 'User plan deleted successfully.');
    }
}
