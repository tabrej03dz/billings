<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\UserPlan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PlanController extends Controller
{
    public function index(Request $request)
    {
        $query = Plan::with(['permissions', 'planFeatures'])
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

        $plans = $query->paginate(10)->withQueryString();

        return view('plans.index', compact('plans'));
    }

    public function create()
    {
        $permissions = Permission::orderBy('name')->get();

        return view('plans.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                    => ['required', 'string', 'max:255'],
            'subtitle'                => ['nullable', 'string', 'max:255'],
            'slug'                    => ['nullable', 'string', 'max:255', 'unique:plans,slug'],
            'price'                   => ['required', 'numeric', 'min:0'],
            'duration_days'           => ['required', 'integer', 'min:1'],
            'description'             => ['nullable', 'string'],
            'status'                  => ['nullable', 'boolean'],
            'is_recommended'          => ['nullable', 'boolean'],
            'sort_order'              => ['nullable', 'integer', 'min:0'],

            'permission_ids'          => ['nullable', 'array'],
            'permission_ids.*'        => ['exists:permissions,id'],

            'feature_titles'          => ['nullable', 'array'],
            'feature_titles.*'        => ['nullable', 'string', 'max:255'],
            'feature_descriptions'    => ['nullable', 'array'],
            'feature_descriptions.*'  => ['nullable', 'string'],
            'feature_icons'           => ['nullable', 'array'],
            'feature_icons.*'         => ['nullable', 'string', 'max:255'],
            'feature_sort_orders'     => ['nullable', 'array'],
            'feature_sort_orders.*'   => ['nullable', 'integer', 'min:0'],
            'feature_is_active'       => ['nullable', 'array'],
        ]);

        $slug = !empty($validated['slug'])
            ? Str::slug($validated['slug'])
            : Str::slug($validated['name']);

        $slug = $this->makeUniqueSlug($slug);

        DB::transaction(function () use ($request, $validated, $slug) {
            $plan = Plan::create([
                'name'              => $validated['name'],
                'subtitle'          => $validated['subtitle'] ?? null,
                'slug'              => $slug,
                'price'             => $validated['price'],
                'duration_days'     => $validated['duration_days'],
                'description'       => $validated['description'] ?? null,
                'status'            => $request->boolean('status'),
                'is_recommended'    => $request->boolean('is_recommended'),
                'sort_order'        => $validated['sort_order'] ?? 0,
            ]);

            $plan->permissions()->sync($validated['permission_ids'] ?? []);

            $this->syncPlanFeatures($plan, $request);
        });

        return redirect()
            ->route('plans.index')
            ->with('success', 'Plan created successfully.');
    }

    public function show($id)
    {
        $plan = Plan::with(['permissions', 'planFeatures'])->findOrFail($id);

        return view('plans.show', compact('plan'));
    }

    public function edit($id)
    {
        $plan = Plan::with(['permissions', 'planFeatures'])->findOrFail($id);
        $permissions = Permission::orderBy('name')->get();
        $selectedPermissions = $plan->permissions->pluck('id')->toArray();

        return view('plans.edit', compact('plan', 'permissions', 'selectedPermissions'));
    }

    public function update(Request $request, $id)
    {
        $plan = Plan::with('planFeatures')->findOrFail($id);

        $validated = $request->validate([
            'name'                    => ['required', 'string', 'max:255'],
            'subtitle'                => ['nullable', 'string', 'max:255'],
            'slug'                    => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('plans', 'slug')->ignore($plan->id),
            ],
            'price'                   => ['required', 'numeric', 'min:0'],
            'duration_days'           => ['required', 'integer', 'min:1'],
            'description'             => ['nullable', 'string'],
            'status'                  => ['nullable', 'boolean'],
            'is_recommended'          => ['nullable', 'boolean'],
            'sort_order'              => ['nullable', 'integer', 'min:0'],

            'permission_ids'          => ['nullable', 'array'],
            'permission_ids.*'        => ['exists:permissions,id'],

            'feature_titles'          => ['nullable', 'array'],
            'feature_titles.*'        => ['nullable', 'string', 'max:255'],
            'feature_descriptions'    => ['nullable', 'array'],
            'feature_descriptions.*'  => ['nullable', 'string'],
            'feature_icons'           => ['nullable', 'array'],
            'feature_icons.*'         => ['nullable', 'string', 'max:255'],
            'feature_sort_orders'     => ['nullable', 'array'],
            'feature_sort_orders.*'   => ['nullable', 'integer', 'min:0'],
            'feature_is_active'       => ['nullable', 'array'],
        ]);

        $slug = !empty($validated['slug'])
            ? Str::slug($validated['slug'])
            : Str::slug($validated['name']);

        $slug = $this->makeUniqueSlug($slug, $plan->id);

        DB::transaction(function () use ($request, $validated, $plan, $slug) {
            $plan->update([
                'name'              => $validated['name'],
                'subtitle'          => $validated['subtitle'] ?? null,
                'slug'              => $slug,
                'price'             => $validated['price'],
                'duration_days'     => $validated['duration_days'],
                'description'       => $validated['description'] ?? null,
                'status'            => $request->boolean('status'),
                'is_recommended'    => $request->boolean('is_recommended'),
                'sort_order'        => $validated['sort_order'] ?? 0,
            ]);

            $plan->permissions()->sync($validated['permission_ids'] ?? []);

            $this->syncPlanFeatures($plan, $request);
        });

        return redirect()
            ->route('plans.index')
            ->with('success', 'Plan updated successfully.');
    }

    public function destroy($id)
    {
        $plan = Plan::findOrFail($id);

        $plan->permissions()->detach();
        $plan->planFeatures()->delete();
        $plan->delete();

        return redirect()
            ->route('plans.index')
            ->with('success', 'Plan deleted successfully.');
    }

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
        $plans = Plan::where('status', 1)
            ->with(['permissions', 'planFeatures' => function ($query) {
                $query->where('is_active', 1)->orderBy('sort_order', 'asc');
            }])
            ->orderByDesc('is_recommended')
            ->orderBy('sort_order', 'asc')
            ->orderBy('price', 'asc')
            ->get();

        return view('choose-plan', compact('plans'));
    }

    // public function choosenSave(Request $request)
    // {
    //     $request->validate([
    //         'plan_id'     => ['required', 'exists:plans,id'],
    //         'business_id' => ['nullable', 'exists:businesses,id'],
    //     ]);

    //     $user = Auth::user();

    //     $plan = Plan::with('permissions')->findOrFail($request->plan_id);

    //     $businessId = $request->business_id
    //         ?? $user->current_business_id
    //         ?? session('active_business_id')
    //         ?? $user->businesses()->pluck('businesses.id')->first();

    //     if (!$businessId) {
    //         return back()->with('error', 'Business not found. Please select business first.');
    //     }

    //     DB::transaction(function () use ($businessId, $user, $plan) {
    //         UserPlan::where('business_id', $businessId)
    //             ->where('status', 1)
    //             ->update([
    //                 'status' => 0,
    //             ]);

    //         UserPlan::create([
    //             'business_id' => $businessId,
    //             'user_id'     => $user->id,
    //             'plan_id'     => $plan->id,
    //             'start_date'  => Carbon::today(),
    //             'expiry_date' => Carbon::today()->addDays((int) $plan->duration_days),
    //             'status'      => 1,
    //         ]);

    //         $permissions = $plan->permissions->pluck('name')->toArray();

    //         $user->syncPermissions($permissions);

    //         app(PermissionRegistrar::class)->forgetCachedPermissions();
    //     });

    //     return redirect()
    //         ->route('bill-templates.choose')
    //         ->with('success', 'Plan selected successfully and permissions assigned.');
    // }


    // public function choosenSave(Request $request)
    // {
    //     $request->validate([
    //         'plan_id' => [
    //             'required',
    //             'exists:plans,id',
    //         ],

    //         'business_id' => [
    //             'nullable',
    //             'exists:businesses,id',
    //         ],
    //     ]);

    //     $user = Auth::user();

    //     if (!$user) {
    //         return redirect()
    //             ->route('login')
    //             ->with('error', 'Please login first.');
    //     }

    //     $plan = Plan::with([
    //         'permissions' => function ($query) {
    //             $query->where('guard_name', 'web');
    //         },
    //     ])->findOrFail($request->plan_id);

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Resolve active business
    //     |--------------------------------------------------------------------------
    //     */

    //     $businessId = $request->input('business_id')
    //         ?? $user->current_business_id
    //         ?? session('active_business_id')
    //         ?? $user->businesses()
    //             ->pluck('businesses.id')
    //             ->first();

    //     if (!$businessId) {
    //         return redirect()
    //             ->route('business-profile.index')
    //             ->with(
    //                 'error',
    //                 'Business not found. Please complete your business profile first.'
    //             );
    //     }

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Ensure business belongs to logged-in user
    //     |--------------------------------------------------------------------------
    //     */

    //     $business = $user->businesses()
    //         ->where('businesses.id', $businessId)
    //         ->first();

    //     if (!$business) {
    //         return redirect()
    //             ->route('business-profile.index')
    //             ->with(
    //                 'error',
    //                 'Selected business is not available. Please complete your business profile.'
    //             );
    //     }

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Check business profile status before transaction
    //     |--------------------------------------------------------------------------
    //     |
    //     | Plan pehle save hoga. Is flag ke according transaction ke baad
    //     | redirect decide hoga.
    //     |
    //     */

    //     $temporaryBusinessName =
    //         trim((string) $user->name) . "'s Business";

    //     $businessProfileIncomplete =
    //         blank($business->name) ||
    //         blank($business->business_type_id) ||
    //         trim((string) $business->name) === $temporaryBusinessName;

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Save plan and permissions
    //     |--------------------------------------------------------------------------
    //     */

    //     DB::transaction(function () use (
    //         $business,
    //         $user,
    //         $plan
    //     ) {
    //         UserPlan::where('business_id', $business->id)
    //             ->where('status', 1)
    //             ->update([
    //                 'status' => 0,
    //             ]);

    //         UserPlan::create([
    //             'business_id' => $business->id,
    //             'user_id' => $user->id,
    //             'plan_id' => $plan->id,
    //             'start_date' => Carbon::today(),
    //             'expiry_date' => Carbon::today()->addDays(
    //                 (int) ($plan->duration_days ?? 30)
    //             ),
    //             'status' => 1,
    //         ]);

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Update active business
    //         |--------------------------------------------------------------------------
    //         */

    //         $user->update([
    //             'current_business_id' => $business->id,
    //         ]);

    //         session([
    //             'active_business_id' => $business->id,
    //         ]);

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Assign plan permissions
    //         |--------------------------------------------------------------------------
    //         */

    //         app(PermissionRegistrar::class)
    //             ->forgetCachedPermissions();

    //         $permissions = $plan->permissions
    //             ->pluck('name')
    //             ->filter()
    //             ->unique()
    //             ->values()
    //             ->toArray();

    //         $user->syncPermissions($permissions);

    //         app(PermissionRegistrar::class)
    //             ->forgetCachedPermissions();
    //     });

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Redirect after plan save
    //     |--------------------------------------------------------------------------
    //     */

    //     if ($businessProfileIncomplete) {
    //         return redirect()
    //             ->route('business-profile.index')
    //             ->with(
    //                 'success',
    //                 'Plan selected successfully. Please complete your business profile.'
    //             );
    //     }

    //     return redirect()
    //         ->route('bill-templates.choose')
    //         ->with(
    //             'success',
    //             'Plan selected successfully and permissions assigned.'
    //         );
    // }


    public function choosenSave(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([
            'plan_id' => [
                'required',
                'integer',
                'exists:plans,id',
            ],

            'business_id' => [
                'nullable',
                'integer',
                'exists:businesses,id',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Logged-in user
        |--------------------------------------------------------------------------
        */

        $user = Auth::user();

        if (!$user) {
            return redirect()
                ->route('login')
                ->with(
                    'error',
                    'Please login first.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Get selected plan with web permissions
        |--------------------------------------------------------------------------
        */

        $plan = Plan::query()
            ->with([
                'permissions' => function ($query) {
                    $query->where(
                        'guard_name',
                        'web'
                    );
                },
            ])
            ->findOrFail(
                (int) $validated['plan_id']
            );

        /*
        |--------------------------------------------------------------------------
        | Resolve user's business
        |--------------------------------------------------------------------------
        |
        | Priority:
        |
        | 1. Request business_id
        | 2. User current_business_id
        | 3. Session active_business_id
        | 4. User ke attached businesses me se first business
        |
        | Business nahi milne par $business null rahega.
        |
        */

        $requestedBusinessId =
            $validated['business_id'] ?? null;

        $businessId =
            $requestedBusinessId
            ?? $user->current_business_id
            ?? session('active_business_id')
            ?? $user->businesses()
                ->value('businesses.id');

        $business = null;

        if ($businessId) {
            /*
            |--------------------------------------------------------------------------
            | Ensure business belongs to logged-in user
            |--------------------------------------------------------------------------
            */

            $business = $user->businesses()
                ->where(
                    'businesses.id',
                    $businessId
                )
                ->first();

            if (!$business) {
                return redirect()
                    ->route('plan.choose')
                    ->with(
                        'error',
                        'Selected business is not associated with your account.'
                    );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Check business profile status
        |--------------------------------------------------------------------------
        |
        | Business nahi hai to profile incomplete maana jayega.
        |
        | Aapke purane records me dummy business ho sakta hai:
        | "User Name's Business"
        |
        */

        $temporaryBusinessName =
            trim((string) $user->name)
            . "'s Business";

        $businessTypeId = $business
            ? (
                $business->business_type_id
                ?? $business->type
                ?? null
            )
            : null;

        $businessProfileIncomplete =
            !$business ||
            blank($business->name) ||
            blank($businessTypeId) ||
            trim((string) $business->name)
                === $temporaryBusinessName;

        $createdUserPlan = null;

        try {
            DB::transaction(function () use (
                $business,
                $user,
                $plan,
                &$createdUserPlan
            ) {
                /*
                |--------------------------------------------------------------------------
                | Deactivate existing active plan
                |--------------------------------------------------------------------------
                |
                | Business available hai:
                |     us business ka existing active plan deactivate hoga.
                |
                | Business available nahi hai:
                |     user ka business-less pending active plan deactivate hoga.
                |
                */

                if ($business) {
                    UserPlan::query()
                        ->where(
                            'business_id',
                            $business->id
                        )
                        ->where('status', 1)
                        ->update([
                            'status' => 0,
                        ]);
                } else {
                    UserPlan::query()
                        ->where(
                            'user_id',
                            $user->id
                        )
                        ->whereNull('business_id')
                        ->where('status', 1)
                        ->update([
                            'status' => 0,
                        ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Create selected UserPlan
                |--------------------------------------------------------------------------
                |
                | Business nahi hai to business_id null save hoga.
                |
                */

                $createdUserPlan =
                    UserPlan::query()->create([
                        'business_id' =>
                            $business?->id,

                        'user_id' =>
                            $user->id,

                        'plan_id' =>
                            $plan->id,

                        'start_date' =>
                            Carbon::today(),

                        'expiry_date' =>
                            Carbon::today()->addDays(
                                (int) (
                                    $plan->duration_days
                                    ?? 30
                                )
                            ),

                        'status' => 1,
                    ]);

                /*
                |--------------------------------------------------------------------------
                | Set current business only when business exists
                |--------------------------------------------------------------------------
                |
                | Business nahi hone par koi dummy business create nahi hoga aur
                | current_business_id me fake value set nahi hogi.
                |
                */

                if ($business) {
                    $user->current_business_id =
                        $business->id;

                    $user->save();
                }

                /*
                |--------------------------------------------------------------------------
                | Assign selected plan permissions
                |--------------------------------------------------------------------------
                */

                app(PermissionRegistrar::class)
                    ->forgetCachedPermissions();

                $permissions = $plan->permissions
                    ->pluck('name')
                    ->filter()
                    ->unique()
                    ->values()
                    ->toArray();

                /*
                | Plan me jitni direct permissions hain, wahi user ko assign hongi.
                */

                $user->syncPermissions(
                    $permissions
                );

                app(PermissionRegistrar::class)
                    ->forgetCachedPermissions();
            });
        } catch (\Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->withErrors([
                    'plan' =>
                        app()->environment('local')
                            ? $exception->getMessage()
                            : 'Plan select nahi ho paya. Dobara try kijiye.',
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Session handling
        |--------------------------------------------------------------------------
        */

        if ($business) {
            session([
                'active_business_id' =>
                    $business->id,

                'active_business_name' =>
                    $business->name,
            ]);

            session()->forget([
                'pending_registration_plan_id',
                'pending_user_plan_id',
            ]);
        } else {
            /*
            | Business profile save hone ke baad isi pending UserPlan me
            | business_id attach kiya ja sakta hai.
            */

            session()->forget([
                'active_business_id',
                'active_business_name',
            ]);

            session([
                'pending_registration_plan_id' =>
                    $plan->id,

                'pending_user_plan_id' =>
                    $createdUserPlan?->id,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Business missing
        |--------------------------------------------------------------------------
        |
        | UserPlan create ho chuka hai aur permissions assign ho chuki hain.
        | Ab user ko business profile complete karne bhejenge.
        |
        */

        if (!$business) {
            return redirect()
                ->route('business-profile.index')
                ->with(
                    'success',
                    'Plan selected successfully and all plan permissions have been assigned. Please complete your business profile.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Business profile incomplete
        |--------------------------------------------------------------------------
        */

        if ($businessProfileIncomplete) {
            return redirect()
                ->route('business-profile.index')
                ->with(
                    'success',
                    'Plan selected successfully and permissions assigned. Please complete your business profile.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Business profile complete
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route('bill-templates.choose')
            ->with(
                'success',
                'Plan selected successfully and all plan permissions have been assigned.'
            );
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
                'title'       => $title,
                'description' => $descriptions[$index] ?? null,
                'icon'        => $icons[$index] ?? null,
                'sort_order'  => $sortOrders[$index] ?? $index,
                'is_active'   => isset($activeItems[$index]),
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
}