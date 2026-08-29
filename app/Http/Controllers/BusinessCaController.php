<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\BusinessCaAssignment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class BusinessCaController extends Controller
{
    public function index(Request $request)
    {
        $business = $this->resolveManageableBusiness($request);

        $assignments = BusinessCaAssignment::query()
            ->with('ca:id,name,email,phone')
            ->where('business_id', $business->id)
            ->latest('id')
            ->get();

        return view('ca.manage', compact('business', 'assignments'));
    }

    public function store(Request $request)
    {
        $business = $this->resolveManageableBusiness($request);

        $data = $request->validate([
            'name'   => ['nullable', 'string', 'max:255'],
            'email'  => ['required', 'email', 'max:255'],
            'mobile' => ['nullable', 'string', 'max:20'],
        ]);

        $email = strtolower(trim($data['email']));

        $ca = User::withTrashed()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->first();

        $newAccount = false;

        if ($ca && $ca->trashed()) {
            return back()->withErrors([
                'email' => 'Is email ka account deleted hai. Pehle account restore karein.',
            ])->withInput();
        }

        if (!$ca) {
            $ca = User::create([
                'name' => !empty($data['name'])
                    ? trim($data['name'])
                    : 'CA',

                'email' => $email,

                'mobile' => !empty($data['mobile'])
                    ? trim($data['mobile'])
                    : null,

                'password' => bcrypt(Str::random(40)),
            ]);

            $newAccount = true;
        } else {
            $updates = [];

            if (empty($ca->mobile) && !empty($data['mobile'])) {
                $updates['mobile'] = trim($data['mobile']);
            }

            if (empty($ca->name) && !empty($data['name'])) {
                $updates['name'] = trim($data['name']);
            }

            if (!empty($updates)) {
                $ca->update($updates);
            }
        }

        BusinessCaAssignment::updateOrCreate(
            [
                'business_id' => $business->id,
                'user_id'     => $ca->id,
            ],
            [
                'assigned_by' => $request->user()->id,
                'is_active'   => true,
                'assigned_at' => now(),
            ]
        );

        $message = $ca->name . ' ko ' . $business->name . ' ka CA access de diya gaya hai.';

        if ($newAccount) {
            try {
                $status = Password::sendResetLink([
                    'email' => $ca->email,
                ]);

                if ($status === Password::RESET_LINK_SENT) {
                    $message .= ' Password set karne ka link CA ke email par bhej diya gaya hai.';
                }
            } catch (\Throwable $e) {
                // mail fail hone par assignment fail nahi hoga
            }
        }

        return back()->with('success', $message);
    }

    public function destroy(
        Request $request,
        BusinessCaAssignment $assignment
    ) {
        $business = $this->resolveManageableBusiness($request);

        abort_unless(
            (int) $assignment->business_id === (int) $business->id,
            403
        );

        $assignment->update([
            'is_active' => false,
        ]);

        return back()->with(
            'success',
            'CA access revoke kar diya gaya hai.'
        );
    }

    public function reactivate(
        Request $request,
        BusinessCaAssignment $assignment
    ) {
        $business = $this->resolveManageableBusiness($request);

        abort_unless(
            (int) $assignment->business_id === (int) $business->id,
            403
        );

        $assignment->update([
            'is_active'   => true,
            'assigned_by' => $request->user()->id,
            'assigned_at' => now(),
        ]);

        return back()->with(
            'success',
            'CA access dobara active kar diya gaya hai.'
        );
    }

    private function resolveManageableBusiness(
        Request $request
    ): Business {
        $user = $request->user();

        abort_unless($user, 401);

        $isSuperAdmin = method_exists($user, 'hasRole')
            && (
                $user->hasRole('super_admin')
                || $user->hasRole('superadmin')
                || $user->hasRole('super admin')
            );

        $requestedBusinessId = (int) $request->input(
            'business_id',
            0
        );

        $candidateId =
            $requestedBusinessId
            ?: (int) ($user->current_business_id ?? 0)
            ?: (int) session('active_business_id', 0);

        /*
        |--------------------------------------------------------------------------
        | Super Admin
        |--------------------------------------------------------------------------
        */

        if ($isSuperAdmin && $candidateId) {
            return Business::query()
                ->findOrFail($candidateId);
        }

        /*
        |--------------------------------------------------------------------------
        | Normal Business Owner/Admin
        |--------------------------------------------------------------------------
        */

        $membershipQuery = DB::table('business_user')
            ->where('user_id', $user->id)
            ->whereIn('role', [
                'owner',
                'admin',
            ]);

        if ($candidateId) {
            $allowed = (clone $membershipQuery)
                ->where(
                    'business_id',
                    $candidateId
                )
                ->exists();

            if ($allowed) {
                return Business::query()
                    ->findOrFail($candidateId);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Fallback Business
        |--------------------------------------------------------------------------
        */

        $fallbackId = (clone $membershipQuery)
            ->value('business_id');

        abort_unless(
            $fallbackId,
            403,
            'CA assign karne ke liye owner/admin business access required hai.'
        );

        return Business::query()
            ->findOrFail($fallbackId);
    }
}