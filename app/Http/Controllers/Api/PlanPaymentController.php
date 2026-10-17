<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Plan;
use App\Models\PlanPayment;
use App\Models\UserPlan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Spatie\Permission\PermissionRegistrar;
use Razorpay\Api\Api;

class PlanPaymentController extends Controller
{
    public function createOrder(Request $request, Plan $plan)
    {
        try {
            $amount = (int) ($plan->price * 100);

            $api = new Api(
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
                'status' => true,
                'message' => 'Order created successfully.',
                'key' => config('services.razorpay.key'),
                'order_id' => $order['id'],
                'amount' => $amount,
                'currency' => 'INR',
                'plan_id' => $plan->id,
                'plan_name' => preg_replace('/[^A-Za-z0-9 ]/', '', $plan->name),
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Order create failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function verifyPayment(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'razorpay_order_id' => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature' => 'required|string',
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
        ]);

        $generatedSignature = hash_hmac(
            'sha256',
            $request->razorpay_order_id . '|' . $request->razorpay_payment_id,
            config('services.razorpay.secret')
        );

        if (! hash_equals($generatedSignature, $request->razorpay_signature)) {
            return response()->json([
                'status' => false,
                'message' => 'Payment verification failed.',
            ], 400);
        }

        $plan = Plan::with('permissions')->findOrFail($request->plan_id);

        DB::transaction(function () use ($request, $plan) {

            PlanPayment::updateOrCreate(
                [
                    'transaction_id' => $request->razorpay_payment_id,
                ],
                [
                    'plan_id' => $plan->id,
                    'user_id' => Auth::id(),
                    'payment_status' => 'success',
                    'payment_gateway' => 'razorpay',
                    'payment_method' => 'online',
                    'amount' => $plan->price,
                    'name' => $request->name,
                    'email' => $request->email,
                    'gateway_response' => [
                        'razorpay_order_id' => $request->razorpay_order_id,
                        'razorpay_payment_id' => $request->razorpay_payment_id,
                        'razorpay_signature' => $request->razorpay_signature,
                    ],
                ]
            );

            if (Auth::check()) {
                $user = Auth::user();

                $businessId = $user->current_business_id
                    ?? $user->businesses()->pluck('businesses.id')->first();

                if ($businessId) {
                    UserPlan::where('business_id', $businessId)
                        ->where('status', 1)
                        ->update(['status' => 0]);

                    UserPlan::create([
                        'business_id' => $businessId,
                        'user_id' => $user->id,
                        'plan_id' => $plan->id,
                        'start_date' => Carbon::today(),
                        'expiry_date' => Carbon::today()->addDays((int) ($plan->duration_days ?? 30)),
                        'status' => 1,
                    ]);

                    $permissions = $plan->permissions->pluck('name')->toArray();

                    if (! empty($permissions)) {
                        $user->syncPermissions($permissions);
                    }

                    app(PermissionRegistrar::class)->forgetCachedPermissions();
                }
            }
        });

        return response()->json([
            'status' => true,
            'message' => 'Payment verified successfully.',
            'payment_done' => true,
            'plan' => [
                'id' => $plan->id,
                'name' => $plan->name,
                'price' => $plan->price,
                'duration_days' => $plan->duration_days,
            ],
        ]);
    }

    public function myActivePlan(Request $request)
    {
        $user = $request->user();

        $businessId = $user->current_business_id
            ?? $user->businesses()->pluck('businesses.id')->first();

        if (! $businessId) {
            return response()->json([
                'status' => false,
                'message' => 'Business not found.',
            ], 404);
        }

        $userPlan = UserPlan::with('plan')
            ->where('business_id', $businessId)
            ->where('user_id', $user->id)
            ->where('status', 1)
            ->latest()
            ->first();

        if (! $userPlan) {
            return response()->json([
                'status' => false,
                'message' => 'No active plan found.',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'active_plan' => $userPlan,
        ]);
    }
}
