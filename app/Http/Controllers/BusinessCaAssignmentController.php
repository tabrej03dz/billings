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
            ->with('ca:id,name,email,mobile')
            ->where('business_id', $business->id)
            ->latest('id')
            ->get();

        return view('ca.manage', compact('business', 'assignments'));
    }

    public function store(Request $request)
    {
        $business = $this->resolveManageableBusiness($request);

        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'mobile' => ['nullable', 'string', 'max:20'],
        ]);

        $email = strtolower(trim($data['email']));

        $ca = User::withTrashed()->whereRaw('LOWER(email) = ?', [$email])->first();
        $newAccount = false;

        if ($ca && $ca->trashed()) {
            return back()->withErrors([
                'email' => 'Is email ka account deleted hai. Pehle account restore karein.',
            ])->withInput();
        }

        if (!$ca) {
            $ca = User::create([
                'name' => filled($data['name'] ?? null) ? trim($data['name']) : 'CA',
                'email' => $email,
                'mobile' => filled($data['mobile'] ?? null) ? trim($data['mobile']) : null,
                'password' => Str::random(40),
            ]);

            $newAccount = true;
        } else {
            $updates = [];
            if (blank($ca->mobile) && filled($data['mobile'] ?? null)) {
                $updates['mobile'] = trim($data['mobile']);
            }
            if (blank($ca->name) && filled($data['name'] ?? null)) {
                $updates['name'] = trim($data['name']);
            }
            if ($updates) {
                $ca->update($updates);
            }
        }

        BusinessCaAssignment::updateOrCreate(
            [
                'business_id' => $business->id,
                'user_id' => $ca->id,
            ],
            [
                'assigned_by' => $request->user()->id,
                'is_active' => true,
                'assigned_at' => now(),
            ]
        );

        $message = "{$ca->name} ko {$business->name} ka CA access de diya gaya hai.";

        if ($newAccount) {
            $status = Password::sendResetLink(['email' => $ca->email]);

            if ($status === Password::RESET_LINK_SENT) {
                $message .= ' Naya account bana hai aur password set karne ka link email kar diya gaya hai.';
            } else {
                $message .= ' Naya account bana hai, lekin password email send nahi ho saka. Forgot Password se password set kar sakte hain.';
            }
        }

        return back()->with('success', $message);
    }

    public function destroy(Request $request, BusinessCaAssignment $assignment)
    {
        $business = $this->resolveManageableBusiness($request);

        abort_unless((int) $assignment->business_id === (int) $business->id, 403);

        $assignment->update(['is_active' => false]);

        return back()->with('success', 'CA access revoke kar diya gaya hai.');
    }

    public function reactivate(Request $request, BusinessCaAssignment $assignment)
    {
        $business = $this->resolveManageableBusiness($request);

        abort_unless((int) $assignment->business_id === (int) $business->id, 403);

        $assignment->update([
            'is_active' => true,
            'assigned_by' => $request->user()->id,
            'assigned_at' => now(),
        ]);

        return back()->with('success', 'CA access dobara active kar diya gaya hai.');
    }

    private function resolveManageableBusiness(Request $request): Business
    {
        $user = $request->user();

        $isSuperAdmin = method_exists($user, 'hasRole') && (
            $user->hasRole('super_admin') ||
            $user->hasRole('superadmin') ||
            $user->hasRole('super admin')
        );

        $requestedBusinessId = $request->integer('business_id');
        $candidateId = $requestedBusinessId
            ?: (int) ($user->current_business_id ?? 0)
            ?: (int) session('active_business_id');

        if ($isSuperAdmin && $candidateId) {
            return Business::query()->findOrFail($candidateId);
        }

        $membershipQuery = DB::table('business_user')
            ->where('user_id', $user->id)
            ->whereIn('role', ['owner', 'admin']);

        if ($candidateId) {
            $allowed = (clone $membershipQuery)
                ->where('business_id', $candidateId)
                ->exists();

            if ($allowed) {
                return Business::query()->findOrFail($candidateId);
            }
        }

        $fallbackId = $membershipQuery->value('business_id');

        abort_unless($fallbackId, 403, 'CA assign karne ke liye owner/admin business access required hai.');

        return Business::query()->findOrFail($fallbackId);
    }
}
