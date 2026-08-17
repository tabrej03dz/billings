<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\UserPlan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\Permission\PermissionRegistrar;

class PlanController extends Controller
{
    public function index(Request $request)
    {
        $query = Plan::with(['permissions', 'planFeatures'])->where('status', '1')
            ->orderBy('sort_order', 'asc')
            ->latest();

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('subtitle', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        return response()->json([
            'status' => true,
            'message' => 'Plans fetched successfully.',
            'data' => $query->paginate($request->per_page ?? 10),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validatePlan($request);

        $slug = !empty($validated['slug'])
            ? Str::slug($validated['slug'])
            : Str::slug($validated['name']);

        $slug = $this->makeUniqueSlug($slug);

        $plan = DB::transaction(function () use ($request, $validated, $slug) {
            $plan = Plan::create([
                'name' => $validated['name'],
                'subtitle' => $validated['subtitle'] ?? null,
                'slug' => $slug,
                'price' => $validated['price'],
                'duration_days' => $validated['duration_days'],
                'description' => $validated['description'] ?? null,
                'status' => $request->boolean('status'),
                'is_recommended' => $request->boolean('is_recommended'),
                'sort_order' => $validated['sort_order'] ?? 0,
            ]);

            $plan->permissions()->sync($validated['permission_ids'] ?? []);
            $this->syncPlanFeatures($plan, $request);

            return $plan->load(['permissions', 'planFeatures']);
        });

        return response()->json([
            'status' => true,
            'message' => 'Plan created successfully.',
            'data' => $plan,
        ], 201);
    }

    public function show($id)
    {
        $plan = Plan::with(['permissions', 'planFeatures'])->findOrFail($id);

        return response()->json([
            'status' => true,
            'message' => 'Plan fetched successfully.',
            'data' => $plan,
        ]);
    }

    public function update(Request $request, $id)
    {
        $plan = Plan::with('planFeatures')->findOrFail($id);

        $validated = $this->validatePlan($request, $plan->id);

        $slug = !empty($validated['slug'])
            ? Str::slug($validated['slug'])
            : Str::slug($validated['name']);

        $slug = $this->makeUniqueSlug($slug, $plan->id);

        DB::transaction(function () use ($request, $validated, $plan, $slug) {
            $plan->update([
                'name' => $validated['name'],
                'subtitle' => $validated['subtitle'] ?? null,
                'slug' => $slug,
                'price' => $validated['price'],
                'duration_days' => $validated['duration_days'],
                'description' => $validated['description'] ?? null,
                'status' => $request->boolean('status'),
                'is_recommended' => $request->boolean('is_recommended'),
                'sort_order' => $validated['sort_order'] ?? 0,
            ]);

            $plan->permissions()->sync($validated['permission_ids'] ?? []);
            $this->syncPlanFeatures($plan, $request);
        });

        return response()->json([
            'status' => true,
            'message' => 'Plan updated successfully.',
            'data' => $plan->fresh(['permissions', 'planFeatures']),
        ]);
    }

    public function destroy($id)
    {
        $plan = Plan::findOrFail($id);

        DB::transaction(function () use ($plan) {
            $plan->permissions()->detach();
            $plan->planFeatures()->delete();
            $plan->delete();
        });

        return response()->json([
            'status' => true,
            'message' => 'Plan deleted successfully.',
        ]);
    }

    public function toggleStatus($id)
    {
        $plan = Plan::findOrFail($id);
        $plan->status = !$plan->status;
        $plan->save();

        return response()->json([
            'status' => true,
            'message' => 'Plan status updated successfully.',
            'data' => $plan,
        ]);
    }

    public function choose()
    {
        $plans = Plan::where('status', 1)
            ->with(['permissions', 'planFeatures' => function ($query) {
                $query->where('is_active', 1)->orderBy('sort_order', 'asc');
            }])
            ->orderByDesc('is_recommended')
            ->orderBy('sort_order', 'asc')
            ->orderBy('price', 'asc')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Active plans fetched successfully.',
            'data' => $plans,
        ]);
    }

    // public function choosenSave(Request $request)
    // {
    //     $request->validate([
    //         'plan_id' => ['required', 'exists:plans,id'],
    //         'business_id' => ['nullable', 'exists:businesses,id'],
    //     ]);

    //     $user = Auth::user();

    //     $plan = Plan::with('permissions')->findOrFail($request->plan_id);

    //     $businessId = $request->business_id
    //         ?? $user->current_business_id
    //         ?? session('active_business_id')
    //         ?? $user->businesses()->pluck('businesses.id')->first();

    //     if (!$businessId) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Business not found. Please select business first.',
    //         ], 422);
    //     }

    //     $userPlan = DB::transaction(function () use ($businessId, $user, $plan) {
    //         UserPlan::where('business_id', $businessId)
    //             ->where('status', 1)
    //             ->update(['status' => 0]);

    //         $userPlan = UserPlan::create([
    //             'business_id' => $businessId,
    //             'user_id' => $user->id,
    //             'plan_id' => $plan->id,
    //             'start_date' => Carbon::today(),
    //             'expiry_date' => Carbon::today()->addDays((int) $plan->duration_days),
    //             'status' => 1,
    //         ]);

    //         $permissions = $plan->permissions->pluck('name')->toArray();

    //         $user->syncPermissions($permissions);

    //         app(PermissionRegistrar::class)->forgetCachedPermissions();

    //         return $userPlan;
    //     });

    //     return response()->json([
    //         'status' => true,
    //         'message' => 'Plan selected successfully and permissions assigned.',
    //         'data' => $userPlan->load('plan'),
    //     ]);
    // }

    public function choosenSave(Request $request)
    {
        $request->validate([
            'plan_id' => ['required', 'exists:plans,id'],
            'business_id' => ['nullable', 'exists:businesses,id'],
            'trial' => ['nullable', 'boolean'],
        ]);

        $user = Auth::user();
        $plan = Plan::with('permissions')->findOrFail($request->plan_id);

        $isTrial = (bool) $request->input('trial', false);

        $businessId = $request->business_id
            ?? $user->current_business_id
            ?? session('active_business_id')
            ?? $user->businesses()->pluck('businesses.id')->first();

        if (!$businessId) {
            return response()->json([
                'status' => false,
                'message' => 'Business not found. Please select business first.',
            ], 422);
        }

        $userPlan = DB::transaction(function () use ($businessId, $user, $plan, $isTrial) {
            UserPlan::where('business_id', $businessId)
                ->where('status', 1)
                ->update(['status' => 0]);

            $startDate = Carbon::today();

            $expiryDate = $isTrial
                ? Carbon::today()->addMonth()
                : Carbon::today()->addDays((int) $plan->duration_days);

            $userPlan = UserPlan::create([
                'business_id' => $businessId,
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'start_date' => $startDate,
                'expiry_date' => $expiryDate,
                'status' => 1,
                // agar table me column hai to:
                // 'is_trial' => $isTrial,
            ]);

            $permissions = $plan->permissions->pluck('name')->toArray();

            $user->syncPermissions($permissions);

            app(PermissionRegistrar::class)->forgetCachedPermissions();

            return $userPlan;
        });

        return response()->json([
            'status' => true,
            'message' => $isTrial
                ? 'Trial plan selected successfully for 1 month.'
                : 'Plan selected successfully and permissions assigned.',
            'data' => $userPlan->load('plan'),
        ]);
    }


    private function validatePlan(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('plans', 'slug')->ignore($ignoreId),
            ],
            'price' => ['required', 'numeric', 'min:0'],
            'duration_days' => ['required', 'integer', 'min:1'],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', 'boolean'],
            'is_recommended' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],

            'permission_ids' => ['nullable', 'array'],
            'permission_ids.*' => ['exists:permissions,id'],

            'feature_titles' => ['nullable', 'array'],
            'feature_titles.*' => ['nullable', 'string', 'max:255'],
            'feature_descriptions' => ['nullable', 'array'],
            'feature_descriptions.*' => ['nullable', 'string'],
            'feature_icons' => ['nullable', 'array'],
            'feature_icons.*' => ['nullable', 'string', 'max:255'],
            'feature_sort_orders' => ['nullable', 'array'],
            'feature_sort_orders.*' => ['nullable', 'integer', 'min:0'],
            'feature_is_active' => ['nullable', 'array'],
        ]);
    }

    private function syncPlanFeatures(Plan $plan, Request $request): void
    {
        $plan->planFeatures()->delete();

        $titles = $request->input('feature_titles', []);
        $descriptions = $request->input('feature_descriptions', []);
        $icons = $request->input('feature_icons', []);
        $sortOrders = $request->input('feature_sort_orders', []);
        $activeItems = $request->input('feature_is_active', []);

        foreach ($titles as $index => $title) {
            $title = trim((string) $title);

            if ($title === '') {
                continue;
            }

            $plan->planFeatures()->create([
                'title' => $title,
                'description' => $descriptions[$index] ?? null,
                'icon' => $icons[$index] ?? null,
                'sort_order' => $sortOrders[$index] ?? $index,
                'is_active' => isset($activeItems[$index]),
            ]);
        }
    }

    private function makeUniqueSlug(string $slug, ?int $ignoreId = null): string
    {
        $slug = $slug ?: 'plan';
        $originalSlug = $slug;
        $count = 1;

        while (
            Plan::where('slug', $slug)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $originalSlug . '-' . $count++;
        }

        return $slug;
    }








    public function myPlans(Request $request)
    {
        $userPlans = UserPlan::with(['plan', 'business'])
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(15);

        return response()->json([
            'success' => true,
            'message' => 'User plans fetched successfully.',
            'data' => $userPlans,
        ]);
    }
}
