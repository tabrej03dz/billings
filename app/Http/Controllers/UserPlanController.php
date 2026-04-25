<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Plan;
use App\Models\User;
use App\Models\UserPlan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserPlanController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q'));
        $businessId = $request->get('business_id');

        $userPlans = UserPlan::with(['user', 'plan', 'business'])
            ->when($businessId, function ($query) use ($businessId) {
                $query->where('business_id', $businessId);
            })
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($query) use ($q) {
                    $query->whereHas('user', function ($subQuery) use ($q) {
                        $subQuery->where('name', 'like', "%{$q}%")
                            ->orWhere('email', 'like', "%{$q}%");
                    })
                    ->orWhereHas('plan', function ($subQuery) use ($q) {
                        $subQuery->where('name', 'like', "%{$q}%");
                    })
                    ->orWhereHas('business', function ($subQuery) use ($q) {
                        $subQuery->where('name', 'like', "%{$q}%")
                            ->orWhere('business_name', 'like', "%{$q}%");
                    });
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $businesses = Business::orderBy('name')->get();

        return view('user-plans.index', compact('userPlans', 'q', 'businesses', 'businessId'));
    }

    public function create(Request $request)
    {
        $businesses = Business::orderBy('name')->get();
        $users = User::orderBy('name')->get();
        $plans = Plan::where('status', 1)->orderBy('name')->get();

        $selectedBusinessId = $request->get('business_id');

        return view('user-plans.create', compact(
            'businesses',
            'users',
            'plans',
            'selectedBusinessId'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'business_id' => ['required', 'exists:businesses,id'],
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
            : (clone $startDate)->addDays((int) ($plan->duration_days ?? 0));

        UserPlan::create([
            'business_id' => $validated['business_id'],
            'user_id'     => $validated['user_id'],
            'plan_id'     => $validated['plan_id'],
            'start_date'  => $startDate->toDateString(),
            'expiry_date' => $expiryDate->toDateString(),
            'status'      => $request->boolean('status', true),
        ]);

        return redirect()
            ->route('user-plans.index', ['business_id' => $validated['business_id']])
            ->with('success', 'Business wise user plan created successfully.');
    }

    public function show($id)
    {
        $userPlan = UserPlan::with(['user', 'plan', 'business'])->findOrFail($id);

        return view('user-plans.show', compact('userPlan'));
    }

    public function edit($id)
    {
        $userPlan = UserPlan::with(['user', 'plan', 'business'])->findOrFail($id);

        $businesses = Business::orderBy('name')->get();
        $users = User::orderBy('name')->get();
        $plans = Plan::where('status', 1)->orderBy('name')->get();

        return view('user-plans.edit', compact(
            'userPlan',
            'businesses',
            'users',
            'plans'
        ));
    }

    public function update(Request $request, $id)
    {
        $userPlan = UserPlan::findOrFail($id);

        $validated = $request->validate([
            'business_id' => ['required', 'exists:businesses,id'],
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
            : (clone $startDate)->addDays((int) ($plan->duration_days ?? 0));

        $userPlan->update([
            'business_id' => $validated['business_id'],
            'user_id'     => $validated['user_id'],
            'plan_id'     => $validated['plan_id'],
            'start_date'  => $startDate->toDateString(),
            'expiry_date' => $expiryDate->toDateString(),
            'status'      => $request->boolean('status', true),
        ]);

        return redirect()
            ->route('user-plans.index', ['business_id' => $validated['business_id']])
            ->with('success', 'Business wise user plan updated successfully.');
    }

    public function destroy($id)
    {
        $userPlan = UserPlan::findOrFail($id);
        $businessId = $userPlan->business_id;

        $userPlan->delete();

        return redirect()
            ->route('user-plans.index', ['business_id' => $businessId])
            ->with('success', 'User plan deleted successfully.');
    }

    public function index1(Request $request)
    {
        $q = trim((string) $request->get('q'));

        $businessId = $request->user()->current_business_id
            ?? session('active_business_id')
            ?? $request->get('business_id')
            ?? $request->user()->businesses()->pluck('businesses.id')->first();

        abort_if(!$businessId, 404, 'Business not found.');

        $userPlans = UserPlan::with(['user', 'plan', 'business'])
            ->where('business_id', $businessId)
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($query) use ($q) {
                    $query->whereHas('user', function ($subQuery) use ($q) {
                        $subQuery->where('name', 'like', "%{$q}%")
                            ->orWhere('email', 'like', "%{$q}%");
                    })
                    ->orWhereHas('plan', function ($subQuery) use ($q) {
                        $subQuery->where('name', 'like', "%{$q}%");
                    });
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $business = Business::findOrFail($businessId);

        return view('user-plans.index1', compact(
            'userPlans',
            'q',
            'business',
            'businessId'
        ));
    }
}