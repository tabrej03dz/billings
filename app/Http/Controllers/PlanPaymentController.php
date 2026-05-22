<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\UserPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Spatie\Permission\PermissionRegistrar;

class PlanPaymentController extends Controller
{
    // public function show(Plan $plan)
    // {
    //     return view('plans.payment', compact('plan'));
    // }


    public function show(Plan $plan)
    {
        return view('plans.payment', compact('plan'));
    }

    public function createOrder(Request $request, Plan $plan)
    {
        $amount = (int) ($plan->price * 100);

        $api = new \Razorpay\Api\Api(
            config('services.razorpay.key'),
            config('services.razorpay.secret')
        );

        $order = $api->order->create([
            'receipt' => 'plan_' . $plan->id . '_' . time(),
            'amount' => $amount,
            'currency' => 'INR',
            'notes' => [
                'plan_id' => (string) $plan->id,
                'plan_name' => preg_replace('/[^A-Za-z0-9 ]/', '', $plan->name),
            ],
        ]);

        return response()->json([
            'key' => config('services.razorpay.key'),
            'order_id' => $order['id'],
            'amount' => $amount,
            'plan_name' => preg_replace('/[^A-Za-z0-9 ]/', '', $plan->name),
        ]);
    }

    public function success(Request $request, Plan $plan)
    {
        $request->validate([
            'razorpay_order_id' => 'required',
            'razorpay_payment_id' => 'required',
            'razorpay_signature' => 'required',
        ]);

        $signature = hash_hmac(
            'sha256',
            $request->razorpay_order_id . '|' . $request->razorpay_payment_id,
            config('services.razorpay.secret')
        );

        if ($signature !== $request->razorpay_signature) {
            return back()->with('error', 'Payment verification failed.');
        }

        $user = Auth::user();

        $plan = Plan::with('permissions')->findOrFail($plan->id);

        $businessId = $user->current_business_id
            ?? session('active_business_id')
            ?? $user->businesses()->pluck('businesses.id')->first();

        if (!$businessId) {
            return back()->with('error', 'Business not found. Please select business first.');
        }

        DB::transaction(function () use ($businessId, $user, $plan) {
            UserPlan::where('business_id', $businessId)
                ->where('status', 1)
                ->update([
                    'status' => 0,
                ]);

            UserPlan::create([
                'business_id' => $businessId,
                'user_id'     => $user->id,
                'plan_id'     => $plan->id,
                'start_date'  => Carbon::today(),
                'expiry_date' => Carbon::today()->addDays((int) ($plan->duration_days ?? 30)),
                'status'      => 1,
            ]);

            $permissions = $plan->permissions->pluck('name')->toArray();

            $user->syncPermissions($permissions);

            app(PermissionRegistrar::class)->forgetCachedPermissions();
        });

        return redirect()
            ->route('bill-templates.choose')
            ->with('success', 'Payment successful. Plan activated and permissions assigned.');
    }

}
