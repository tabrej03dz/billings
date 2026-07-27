<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\OnboardingRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class OnboardingRegistrationController extends Controller
{


    /**
     * Onboarding registrations listing.
     */
    public function index(Request $request)
    {
        $query = OnboardingRegistration::query()
            ->with('user:id,name,email,phone');

        /*
         * Search by name, phone or user information.
         */
        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($subQuery) use ($search) {
                $subQuery
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        /*
         * Registration status filter.
         */
        if ($request->filled('registration_status')) {
            $query->where(
                'registration_status',
                $request->registration_status
            );
        }

        /*
         * Last completed step filter.
         */
        if ($request->filled('last_completed_step')) {
            $query->where(
                'last_completed_step',
                $request->integer('last_completed_step')
            );
        }

        /*
         * Phone verification filter.
         */
        if ($request->filled('phone_verification')) {
            if ($request->phone_verification === 'verified') {
                $query->whereNotNull('phone_verified_at');
            }

            if ($request->phone_verification === 'unverified') {
                $query->whereNull('phone_verified_at');
            }
        }

        /*
         * User linked/unlinked filter.
         */
        if ($request->filled('user_link_status')) {
            if ($request->user_link_status === 'linked') {
                $query->whereNotNull('user_id');
            }

            if ($request->user_link_status === 'unlinked') {
                $query->whereNull('user_id');
            }
        }

        /*
         * Date filters.
         */
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        /*
         * Sorting.
         */
        $allowedSortColumns = [
            'id',
            'name',
            'phone',
            'last_completed_step',
            'registration_status',
            'phone_verified_at',
            'completed_at',
            'created_at',
            'updated_at',
        ];

        $sortBy = in_array($request->sort_by, $allowedSortColumns, true)
            ? $request->sort_by
            : 'id';

        $sortDirection = $request->sort_direction === 'asc'
            ? 'asc'
            : 'desc';

        $registrations = $query
            ->orderBy($sortBy, $sortDirection)
            ->paginate(20)
            ->withQueryString();

        /*
         * Dashboard summary cards.
         */
        $summary = [
            'total' => OnboardingRegistration::count(),

            'verified' => OnboardingRegistration::whereNotNull(
                'phone_verified_at'
            )->count(),

            'unverified' => OnboardingRegistration::whereNull(
                'phone_verified_at'
            )->count(),

            'completed' => OnboardingRegistration::where(
                'registration_status',
                'completed'
            )->count(),

            'registered' => OnboardingRegistration::where(
                'registration_status',
                'registered'
            )->count(),

            'linked_users' => OnboardingRegistration::whereNotNull(
                'user_id'
            )->count(),
        ];

        return view(
            'onboarding-registrations.index',
            compact('registrations', 'summary')
        );
    }

    /**
     * Create form.
     *
     * Store function जानबूझकर नहीं बनाया गया है।
     */
    public function create()
    {
        return view('onboarding-registrations.create');
    }

    /**
     * Show a single onboarding registration.
     */
    public function show(OnboardingRegistration $onboardingRegistration)
    {
        $onboardingRegistration->load([
            'user',
        ]);

        return view(
            'onboarding-registrations.show',
            compact('onboardingRegistration')
        );
    }

    /**
     * Edit form.
     */
    public function edit(OnboardingRegistration $onboardingRegistration)
    {
        $onboardingRegistration->load('user');

        return view(
            'onboarding-registrations.edit',
            compact('onboardingRegistration')
        );
    }

    /**
     * Update onboarding registration.
     */
    public function update(
        Request $request,
        OnboardingRegistration $onboardingRegistration
    ) {
        $validated = $request->validate([
            'user_id' => [
                'nullable',
                'integer',
                'exists:users,id',
            ],

            'name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'phone' => [
                'required',
                'digits:10',
                Rule::unique('onboarding_registrations', 'phone')
                    ->ignore($onboardingRegistration->id),
            ],

            'phone_verified_at' => [
                'nullable',
                'date',
            ],

            'business_data' => [
                'nullable',
            ],

            'billing_data' => [
                'nullable',
            ],

            'last_completed_step' => [
                'required',
                'integer',
                'min:1',
                'max:255',
            ],

            'registration_status' => [
                'required',
                'string',
                'max:50',
                Rule::in([
                    'registered',
                    'phone_verified',
                    'business_pending',
                    'business_completed',
                    'billing_pending',
                    'billing_completed',
                    'completed',
                    'cancelled',
                    'blocked',
                ]),
            ],

            'completed_at' => [
                'nullable',
                'date',
            ],
        ], [
            'phone.required' => 'Phone number required hai.',
            'phone.digits' => 'Phone number exactly 10 digits ka hona chahiye.',
            'phone.unique' => 'Ye phone number pehle se registered hai.',
            'user_id.exists' => 'Selected user available nahi hai.',
            'registration_status.in' => 'Invalid registration status selected.',
        ]);

        try {
            DB::beginTransaction();

            $businessData = $this->prepareJsonData(
                $request->input('business_data')
            );

            $billingData = $this->prepareJsonData(
                $request->input('billing_data')
            );

            /*
             * Status completed है और completed_at blank है,
             * तो current timestamp लगा देंगे।
             */
            $completedAt = $validated['completed_at'] ?? null;

            if (
                $validated['registration_status'] === 'completed'
                && empty($completedAt)
            ) {
                $completedAt = now();
            }

            /*
             * Status completed नहीं है तो optional रूप से completed_at
             * null कर सकते हैं।
             */
            if ($validated['registration_status'] !== 'completed') {
                $completedAt = null;
            }

            $onboardingRegistration->update([
                'user_id' => $validated['user_id'] ?? null,
                'name' => $validated['name'] ?? null,
                'phone' => $validated['phone'],
                'phone_verified_at' => $validated['phone_verified_at'] ?? null,
                'business_data' => $businessData,
                'billing_data' => $billingData,
                'last_completed_step' => $validated['last_completed_step'],
                'registration_status' => $validated['registration_status'],
                'completed_at' => $completedAt,
            ]);

            DB::commit();

            return redirect()
                ->route(
                    'onboarding-registrations.show',
                    $onboardingRegistration
                )
                ->with(
                    'success',
                    'Onboarding registration successfully update ho gaya.'
                );
        } catch (\Throwable $exception) {
            DB::rollBack();

            report($exception);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Registration update nahi ho paya. Please dobara try karein.'
                );
        }
    }

    /**
     * Delete onboarding registration.
     */
    public function destroy(
        OnboardingRegistration $onboardingRegistration
    ) {
        try {
            $onboardingRegistration->delete();

            return redirect()
                ->route('onboarding-registrations.index')
                ->with(
                    'success',
                    'Onboarding registration successfully delete ho gaya.'
                );
        } catch (\Throwable $exception) {
            report($exception);

            return back()->with(
                'error',
                'Registration delete nahi ho paya.'
            );
        }
    }

    /**
     * Mark phone as verified.
     */
    public function markPhoneVerified(
        OnboardingRegistration $onboardingRegistration
    ) {
        $onboardingRegistration->update([
            'phone_verified_at' => now(),
            'registration_status' =>
                $onboardingRegistration->registration_status === 'registered'
                    ? 'phone_verified'
                    : $onboardingRegistration->registration_status,
        ]);

        return back()->with(
            'success',
            'Phone number verified mark kar diya gaya hai.'
        );
    }

    /**
     * Mark phone as unverified.
     */
    public function markPhoneUnverified(
        OnboardingRegistration $onboardingRegistration
    ) {
        $onboardingRegistration->update([
            'phone_verified_at' => null,
        ]);

        return back()->with(
            'success',
            'Phone number unverified mark kar diya gaya hai.'
        );
    }

    /**
     * Mark registration as completed.
     */
    public function markCompleted(
        OnboardingRegistration $onboardingRegistration
    ) {
        $onboardingRegistration->update([
            'registration_status' => 'completed',
            'completed_at' => now(),
        ]);

        return back()->with(
            'success',
            'Registration completed mark kar diya gaya hai.'
        );
    }

    /**
     * Change registration status.
     */
    public function changeStatus(
        Request $request,
        OnboardingRegistration $onboardingRegistration
    ) {
        $validated = $request->validate([
            'registration_status' => [
                'required',
                Rule::in([
                    'registered',
                    'phone_verified',
                    'business_pending',
                    'business_completed',
                    'billing_pending',
                    'billing_completed',
                    'completed',
                    'cancelled',
                    'blocked',
                ]),
            ],
        ]);

        $completedAt = $onboardingRegistration->completed_at;

        if ($validated['registration_status'] === 'completed') {
            $completedAt = $completedAt ?: now();
        } else {
            $completedAt = null;
        }

        $onboardingRegistration->update([
            'registration_status' => $validated['registration_status'],
            'completed_at' => $completedAt,
        ]);

        return back()->with(
            'success',
            'Registration status successfully change ho gaya.'
        );
    }

    /**
     * JSON input को array में convert करना।
     *
     * यह method JSON string और array दोनों accept करता है।
     */
    private function prepareJsonData(mixed $value): ?array
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_array($value)) {
            return $value;
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                abort(
                    422,
                    'Business ya billing data valid JSON format me nahi hai.'
                );
            }

            return $decoded;
        }

        return null;
    }
}