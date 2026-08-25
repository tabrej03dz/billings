<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Plan;
use App\Models\User;
use App\Models\UserPlan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

class UserPlanController extends Controller
{
    // public function index(Request $request)
    // {
    //     $q = trim((string) $request->get('q'));
    //     $businessId = $request->get('business_id');

    //     $userPlans = UserPlan::with(['user', 'plan', 'business'])
    //         ->when($businessId, function ($query) use ($businessId) {
    //             $query->where('business_id', $businessId);
    //         })
    //         ->when($q !== '', function ($query) use ($q) {
    //             $query->where(function ($query) use ($q) {
    //                 $query->whereHas('user', function ($subQuery) use ($q) {
    //                     $subQuery->where('name', 'like', "%{$q}%")
    //                         ->orWhere('email', 'like', "%{$q}%");
    //                 })
    //                     ->orWhereHas('plan', function ($subQuery) use ($q) {
    //                         $subQuery->where('name', 'like', "%{$q}%");
    //                     })
    //                     ->orWhereHas('business', function ($subQuery) use ($q) {
    //                         $subQuery->where('name', 'like', "%{$q}%")
    //                             ->orWhere('business_name', 'like', "%{$q}%");
    //                     });
    //             });
    //         })
    //         ->latest()
    //         ->paginate(15)
    //         ->withQueryString();

    //     $businesses = Business::query()
    //         ->orderBy('name')
    //         ->get();

    //     return view('user-plans.index', compact(
    //         'userPlans',
    //         'q',
    //         'businesses',
    //         'businessId'
    //     ));
    // }


public function index(Request $request)
{
    $q = trim((string) $request->get('q', ''));
    $businessId = $request->get('business_id');
    $tab = $request->get('tab', 'regular');

    if (!in_array($tab, ['regular', 'trial'], true)) {
        $tab = 'regular';
    }

    /*
    |--------------------------------------------------------------------------
    | Base Query
    |--------------------------------------------------------------------------
    */
    $query = UserPlan::with([
        'user',
        'plan',
        'business',
    ]);

    /*
    |--------------------------------------------------------------------------
    | Business Filter
    |--------------------------------------------------------------------------
    */
    $query->when($businessId, function ($query) use ($businessId) {
        $query->where('business_id', $businessId);
    });

    /*
    |--------------------------------------------------------------------------
    | Search
    |--------------------------------------------------------------------------
    */
    $query->when($q !== '', function ($query) use ($q) {
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
    });

    /*
    |--------------------------------------------------------------------------
    | Trial / Regular Filter
    |--------------------------------------------------------------------------
    |
    | Trial   : 31 दिनों से कम
    | Regular : 31 दिन या उससे अधिक
    |
    */
    if ($tab === 'trial') {
        $query->whereNotNull('start_date')
            ->whereNotNull('expiry_date')
            ->whereRaw('DATEDIFF(expiry_date, start_date) <= 31');
    } else {
        $query->where(function ($query) {
            $query->whereNull('start_date')
                ->orWhereNull('expiry_date')
                ->orWhereRaw('DATEDIFF(expiry_date, start_date) > 31');
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */
    $userPlans = $query
        ->latest()
        ->paginate(15)
        ->withQueryString();

    /*
    |--------------------------------------------------------------------------
    | Counts Base Query
    |--------------------------------------------------------------------------
    */
    $countBaseQuery = UserPlan::query();

    if ($businessId) {
        $countBaseQuery->where('business_id', $businessId);
    }

    if ($q !== '') {
        $countBaseQuery->where(function ($query) use ($q) {
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
    }

    /*
    |--------------------------------------------------------------------------
    | Trial Count: Less Than 31 Days
    |--------------------------------------------------------------------------
    */
    $trialCount = (clone $countBaseQuery)
        ->whereNotNull('start_date')
        ->whereNotNull('expiry_date')
        ->whereRaw('DATEDIFF(expiry_date, start_date) < 31')
        ->count();

    /*
    |--------------------------------------------------------------------------
    | Regular Count: 31 Days Or More
    |--------------------------------------------------------------------------
    */
    $regularCount = (clone $countBaseQuery)
        ->where(function ($query) {
            $query->whereNull('start_date')
                ->orWhereNull('expiry_date')
                ->orWhereRaw('DATEDIFF(expiry_date, start_date) >= 31');
        })
        ->count();

    /*
    |--------------------------------------------------------------------------
    | Businesses
    |--------------------------------------------------------------------------
    */
    $businesses = Business::query()
        ->orderBy('name')
        ->get();

    return view('user-plans.index', compact(
        'userPlans',
        'q',
        'businesses',
        'businessId',
        'tab',
        'trialCount',
        'regularCount'
    ));
}


    public function create(Request $request)
{
    $selectedBusinessId = $request->get('business_id');

    // Agar business se create page open hua hai,
    // to sirf wahi business load hoga.
    if ($selectedBusinessId) {
        $businesses = Business::query()
            ->where('id', $selectedBusinessId)
            ->get();

        $selectedBusiness = Business::findOrFail($selectedBusinessId);
    } else {
        $businesses = Business::query()
            ->orderBy('name')
            ->get();

        $selectedBusiness = null;
    }

    $users = User::query()
        ->orderBy('name')
        ->get();

    $plans = Plan::query()
        ->where('status', 1)
        ->orderBy('name')
        ->get();

    return view('user-plans.create', compact(
        'businesses',
        'users',
        'plans',
        'selectedBusinessId',
        'selectedBusiness'
    ));
}

    public function store(Request $request)
    {
        $validated = $request->validate([
            'business_id' => [
                'nullable',
                'required_without:user_id',
                'exists:businesses,id',
            ],

            'user_id' => [
                'nullable',
                'exists:users,id',
            ],

            'plan_id' => [
                'required',
                'exists:plans,id',
            ],

            'number_of_office' => [
                'required',
                'integer',
                'min:1',
            ],

            'number_of_user' => [
                'required',
                'integer',
                'min:1',
            ],

            'start_date' => [
                'nullable',
                'date',
            ],

            'expiry_date' => [
                'nullable',
                'date',
                'after_or_equal:start_date',
            ],

            'status' => [
                'nullable',
                'boolean',
            ],
        ], [
            'business_id.required_without' =>
                'Business select karna zaroori hai jab user select nahi kiya gaya ho.',

            'business_id.exists' =>
                'Selected business valid nahi hai.',

            'user_id.exists' =>
                'Selected user valid nahi hai.',

            'plan_id.required' =>
                'Please select a plan.',

            'plan_id.exists' =>
                'Selected plan valid nahi hai.',

            'number_of_office.required' =>
                'Number of offices required hai.',

            'number_of_office.integer' =>
                'Number of offices valid number hona chahiye.',

            'number_of_office.min' =>
                'Kam se kam 1 office hona chahiye.',

            'number_of_user.required' =>
                'Number of users required hai.',

            'number_of_user.integer' =>
                'Number of users valid number hona chahiye.',

            'number_of_user.min' =>
                'Kam se kam 1 user hona chahiye.',

            'expiry_date.after_or_equal' =>
                'Expiry date start date se pehle nahi ho sakti.',
        ]);

        $userPlan = DB::transaction(function () use ($request, $validated) {

            $plan = Plan::query()
                ->with('permissions')
                ->findOrFail($validated['plan_id']);

            $startDate = !empty($validated['start_date'])
                ? Carbon::parse($validated['start_date'])->startOfDay()
                : now()->startOfDay();

            $expiryDate = !empty($validated['expiry_date'])
                ? Carbon::parse($validated['expiry_date'])->startOfDay()
                : $startDate->copy()->addDays(
                    max(0, (int) ($plan->duration_days ?? 0))
                );

            $userPlan = UserPlan::create([
                'business_id'     => $validated['business_id'] ?? null,
                'user_id'         => $validated['user_id'] ?? null,
                'plan_id'         => $validated['plan_id'],

                'number_of_office' => $validated['number_of_office'],
                'number_of_user'   => $validated['number_of_user'],

                'start_date'      => $startDate->toDateString(),
                'expiry_date'     => $expiryDate->toDateString(),

                'status'          => $request->has('status')
                    ? $request->boolean('status')
                    : true,
            ]);

            $this->assignPlanPermissions(
                userPlan: $userPlan,
                plan: $plan
            );

            return $userPlan;
        });

        return redirect()
            ->route('user-plans.index', array_filter([
                'business_id' => $userPlan->business_id,
            ]))
            ->with(
                'success',
                $userPlan->user_id
                    ? 'User plan created and all plan permissions assigned successfully.'
                    : 'Business plan created and permissions assigned to all business users successfully.'
            );
    }


    public function show($id)
    {
        $userPlan = UserPlan::with([
            'user',
            'plan.permissions',
            'business',
        ])->findOrFail($id);

        return view('user-plans.show', compact('userPlan'));
    }

    public function edit($id)
    {
        $userPlan = UserPlan::with([
            'user',
            'plan',
            'business',
        ])->findOrFail($id);

        $businesses = Business::query()
            ->orderBy('name')
            ->get();

        $users = User::query()
            ->orderBy('name')
            ->get();

        $plans = Plan::query()
            ->where('status', 1)
            ->orderBy('name')
            ->get();

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
            'business_id' => [
                'nullable',
                'required_without:user_id',
                'exists:businesses,id',
            ],

            'user_id' => [
                'nullable',
                'exists:users,id',
            ],

            'plan_id' => [
                'required',
                'exists:plans,id',
            ],

            'number_of_office' => [
                'required',
                'integer',
                'min:1',
            ],

            'number_of_user' => [
                'required',
                'integer',
                'min:1',
            ],

            'start_date' => [
                'nullable',
                'date',
            ],

            'expiry_date' => [
                'nullable',
                'date',
                'after_or_equal:start_date',
            ],

            'status' => [
                'nullable',
                'boolean',
            ],
        ], [
            'business_id.required_without' =>
                'Business select karna zaroori hai jab user select nahi kiya gaya ho.',

            'business_id.exists' =>
                'Selected business valid nahi hai.',

            'user_id.exists' =>
                'Selected user valid nahi hai.',

            'plan_id.required' =>
                'Please select a plan.',

            'plan_id.exists' =>
                'Selected plan valid nahi hai.',

            'number_of_office.required' =>
                'Number of offices required hai.',

            'number_of_office.integer' =>
                'Number of offices valid number hona chahiye.',

            'number_of_office.min' =>
                'Kam se kam 1 office hona chahiye.',

            'number_of_user.required' =>
                'Number of users required hai.',

            'number_of_user.integer' =>
                'Number of users valid number hona chahiye.',

            'number_of_user.min' =>
                'Kam se kam 1 user hona chahiye.',

            'expiry_date.after_or_equal' =>
                'Expiry date start date se pehle nahi ho sakti.',
        ]);

        DB::transaction(function () use (
            $request,
            $validated,
            $userPlan
        ) {

            $plan = Plan::query()
                ->with('permissions')
                ->findOrFail($validated['plan_id']);

            $startDate = !empty($validated['start_date'])
                ? Carbon::parse($validated['start_date'])->startOfDay()
                : now()->startOfDay();

            $expiryDate = !empty($validated['expiry_date'])
                ? Carbon::parse($validated['expiry_date'])->startOfDay()
                : $startDate->copy()->addDays(
                    max(0, (int) ($plan->duration_days ?? 0))
                );

            $userPlan->update([
                'business_id'      => $validated['business_id'] ?? null,
                'user_id'          => $validated['user_id'] ?? null,
                'plan_id'          => $validated['plan_id'],

                'number_of_office' => $validated['number_of_office'],
                'number_of_user'   => $validated['number_of_user'],

                'start_date'       => $startDate->toDateString(),
                'expiry_date'      => $expiryDate->toDateString(),

                'status'           => $request->has('status')
                    ? $request->boolean('status')
                    : true,
            ]);

            $this->assignPlanPermissions(
                userPlan: $userPlan->fresh(),
                plan: $plan
            );
        });

        $userPlan->refresh();

        return redirect()
            ->route('user-plans.index', array_filter([
                'business_id' => $userPlan->business_id,
            ]))
            ->with(
                'success',
                $userPlan->user_id
                    ? 'User plan updated and all plan permissions assigned successfully.'
                    : 'Business plan updated and permissions assigned to all business users successfully.'
            );
    }

    public function destroy($id)
    {
        $userPlan = UserPlan::findOrFail($id);
        $businessId = $userPlan->business_id;

        $userPlan->delete();

        return redirect()
            ->route('user-plans.index', array_filter([
                'business_id' => $businessId,
            ]))
            ->with('success', 'User plan deleted successfully.');
    }

    public function index1(Request $request)
    {
        $q = trim((string) $request->get('q'));

        $businessId = $request->user()->current_business_id
            ?? session('active_business_id')
            ?? $request->get('business_id')
            ?? $request->user()
                ->businesses()
                ->pluck('businesses.id')
                ->first();

        abort_if(!$businessId, 404, 'Business not found.');

        $userPlans = UserPlan::with([
            'user',
            'plan',
            'business',
        ])
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

    /**
     * Plan permissions assign karega:
     *
     * 1. user_id available hai:
     *    Sirf selected user ko permissions milengi.
     *
     * 2. user_id null hai:
     *    Selected business ke sabhi users ko permissions milengi.
     */
    private function assignPlanPermissions(
        UserPlan $userPlan,
        Plan $plan
    ): void {
        $plan->loadMissing('permissions');

        if ($plan->permissions->isEmpty()) {
            return;
        }

        $users = $this->getPermissionTargetUsers($userPlan);

        if ($users->isEmpty()) {
            return;
        }

        foreach ($users as $user) {
            /*
             * givePermissionTo existing direct permissions ko remove nahi karta.
             * Sirf plan ki missing permissions add karta hai.
             */
            $user->givePermissionTo($plan->permissions);

            /*
             * Loaded permission relation ko clear kar rahe hain,
             * taaki fresh permissions turant reflect hon.
             */
            $user->unsetRelation('permissions');
            $user->unsetRelation('roles');
        }

        /*
         * Spatie permission cache clear.
         */
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Permission receive karne wale users return karega.
     */
    private function getPermissionTargetUsers(
        UserPlan $userPlan
    ): Collection {
        /*
         * Specific user selected hai.
         */
        if (!empty($userPlan->user_id)) {
            return User::query()
                ->whereKey($userPlan->user_id)
                ->get();
        }

        /*
         * user_id null hai to business ke sabhi users.
         *
         * User model me businesses() relation use ho raha hai,
         * jo aapke index1 method me bhi already available hai.
         */
        if (!empty($userPlan->business_id)) {
            return User::query()
                ->whereHas('businesses', function ($query) use ($userPlan) {
                    $query->where(
                        'businesses.id',
                        $userPlan->business_id
                    );
                })
                ->distinct()
                ->get();
        }

        return collect();
    }
}