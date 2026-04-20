<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\UserPlan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;

class PlanController extends Controller
{
    

    public function index(Request $request)
    {
        $query = Plan::with('permissions')->latest();

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $plans = $query->paginate(10)->withQueryString();

        return view('plans.index', compact('plans'));
    }

    /**
     * Create form
     */
    public function create()
    {
        $permissions = Permission::orderBy('name')->get();

        return view('plans.create', compact('permissions'));
    }

    /**
     * Store new plan
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'            => ['required', 'string', 'max:255'],
            'slug'            => ['nullable', 'string', 'max:255', 'unique:plans,slug'],
            'price'           => ['required', 'numeric', 'min:0'],
            'duration_days'   => ['required', 'integer', 'min:1'],
            'description'     => ['nullable', 'string'],
            'status'          => ['nullable', 'boolean'],
            'permission_ids'  => ['nullable', 'array'],
            'permission_ids.*'=> ['exists:permissions,id'],
        ]);

        $slug = !empty($validated['slug'])
            ? Str::slug($validated['slug'])
            : Str::slug($validated['name']);

        // slug unique banana
        $originalSlug = $slug;
        $count = 1;
        while (Plan::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count++;
        }

        $plan = Plan::create([
            'name'          => $validated['name'],
            'slug'          => $slug,
            'price'         => $validated['price'],
            'duration_days' => $validated['duration_days'],
            'description'   => $validated['description'] ?? null,
            'status'        => $request->boolean('status'),
        ]);

        $plan->permissions()->sync($validated['permission_ids'] ?? []);

        return redirect()
            ->route('plans.index')
            ->with('success', 'Plan created successfully.');
    }

    /**
     * Edit form
     */
    public function edit($id)
    {
        $plan = Plan::with('permissions')->findOrFail($id);
        $permissions = Permission::orderBy('name')->get();
        $selectedPermissions = $plan->permissions->pluck('id')->toArray();

        return view('plans.edit', compact('plan', 'permissions', 'selectedPermissions'));
    }

    /**
     * Update plan
     */
    public function update(Request $request, $id)
    {
        $plan = Plan::findOrFail($id);

        $validated = $request->validate([
            'name'            => ['required', 'string', 'max:255'],
            'slug'            => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('plans', 'slug')->ignore($plan->id),
            ],
            'price'           => ['required', 'numeric', 'min:0'],
            'duration_days'   => ['required', 'integer', 'min:1'],
            'description'     => ['nullable', 'string'],
            'status'          => ['nullable', 'boolean'],
            'permission_ids'  => ['nullable', 'array'],
            'permission_ids.*'=> ['exists:permissions,id'],
        ]);

        $slug = !empty($validated['slug'])
            ? Str::slug($validated['slug'])
            : Str::slug($validated['name']);

        // update ke time unique slug check
        $originalSlug = $slug;
        $count = 1;
        while (
            Plan::where('slug', $slug)
                ->where('id', '!=', $plan->id)
                ->exists()
        ) {
            $slug = $originalSlug . '-' . $count++;
        }

        $plan->update([
            'name'          => $validated['name'],
            'slug'          => $slug,
            'price'         => $validated['price'],
            'duration_days' => $validated['duration_days'],
            'description'   => $validated['description'] ?? null,
            'status'        => $request->boolean('status'),
        ]);

        $plan->permissions()->sync($validated['permission_ids'] ?? []);

        return redirect()
            ->route('plans.index')
            ->with('success', 'Plan updated successfully.');
    }

    /**
     * Delete plan
     */
    public function destroy($id)
    {
        $plan = Plan::findOrFail($id);
        $plan->permissions()->detach();
        $plan->delete();

        return redirect()
            ->route('plans.index')
            ->with('success', 'Plan deleted successfully.');
    }


    public function show($id)
    {
        $plan = \App\Models\Plan::with('permissions')->findOrFail($id);
        return view('plans.show', compact('plan'));
    }

    /**
     * Change status quickly
     */
    public function toggleStatus($id)
    {
        $plan = Plan::findOrFail($id);
        $plan->status = !$plan->status;
        $plan->save();

        return redirect()
            ->route('plans.index')
            ->with('success', 'Plan status updated successfully.');
    }







public function choose()
    {
        $plans = Plan::where('status', 1)->orderBy('price')->get();

        return view('choose-plan', compact('plans'));
    }

    public function choosenSave(Request $request)
    {
        $request->validate([
            'plan_id' => ['required', 'exists:plans,id'],
        ]);

        $user = Auth::user();
        $plan = Plan::findOrFail($request->plan_id);

        // old active plans disable
        UserPlan::where('user_id', $user->id)->where('status', 1)->update([
            'status' => 0,
        ]);

        UserPlan::create([
            'user_id'    => $user->id,
            'plan_id'    => $plan->id,
            'start_date' => Carbon::today(),
            'expiry_date'=> Carbon::today()->addDays($plan->duration_days),
            'status'     => 1,
        ]);

        // Agar aap plan ke basis par role/permission dena chahte ho
        // Example:
        // if ($plan->slug === 'basic') {
        //     $user->syncPermissions(['show invoices']);
        // } elseif ($plan->slug === 'premium') {
        //     $user->syncPermissions(['show invoices', 'show quotations', 'show proformas']);
        // }

        return redirect()->route('bill-templates.choose')->with('success', 'Plan selected successfully.');
    }


}
