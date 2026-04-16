<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Mail\RegisterOtpMail;
use App\Models\User;
use App\Models\Business;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class RegisterController extends Controller
{
    public function show()
    {
        return view('auth.register');
    }

    public function sendEmailOtp(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
        ]);

        $email = strtolower(trim($request->email));

        // 6 digit OTP
        $otp = (string) random_int(100000, 999999);

        // 10 min ke liye store
        Cache::put('register_otp_' . $email, $otp, now()->addMinutes(10));
        Cache::put('register_otp_verified_' . $email, false, now()->addMinutes(10));

        Mail::to($email)->send(new RegisterOtpMail($otp));

        return response()->json([
            'success' => true,
            'message' => 'OTP email par bhej diya gaya hai.',
        ]);
    }

    public function verifyEmailOtp(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'otp'   => ['required', 'digits:6'],
        ]);

        $email = strtolower(trim($request->email));
        $savedOtp = Cache::get('register_otp_' . $email);

        if (!$savedOtp) {
            return response()->json([
                'success' => false,
                'message' => 'OTP expire ho gaya hai. Dobara send kijiye.',
            ], 422);
        }

        if ((string) $savedOtp !== (string) $request->otp) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP.',
            ], 422);
        }

        Cache::put('register_otp_verified_' . $email, true, now()->addMinutes(30));
        session(['register_email_verified' => $email]);

        return response()->json([
            'success' => true,
            'message' => 'Email successfully verify ho gaya.',
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'                 => ['required', 'string', 'max:255'],
            'email'                => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'             => ['required', 'confirmed', 'min:6'],

            'business_name'        => ['required', 'string', 'max:255'],
            'business_email'       => ['required', 'email', 'max:255'],
            'mobile'               => ['required', 'string', 'max:20'],
            'gstin'                => ['nullable', 'string', 'max:15'],
            'type'                 => ['required', 'string', 'max:100'],
            'address'              => ['nullable', 'string'],
            'state'                => ['nullable', 'string', 'max:100'],
            'state_code'           => ['nullable', 'string', 'max:10'],

            'gst_enabled'          => ['nullable', 'in:0,1'],
            'invoice_base_prefix'  => ['nullable', 'string', 'max:50'],
            'rounding_mode'        => ['nullable', 'in:none,nearest,up,down'],
            'rounding_step'        => ['nullable', 'numeric'],
            'terms'                => ['accepted'],
        ]);

        $email = strtolower(trim($request->email));
        $verifiedEmail = session('register_email_verified');
        $isOtpVerified = Cache::get('register_otp_verified_' . $email);

        if ($verifiedEmail !== $email || !$isOtpVerified) {
            throw ValidationException::withMessages([
                'email' => ['Pehle email OTP verify kijiye.'],
            ]);
        }

        DB::beginTransaction();

        try {
            $user = User::create([
                'name'     => $request->name,
                'email'    => $email,
                'password' => Hash::make($request->password),
            ]);

            // Agar aapke project me role system hai
            if (method_exists($user, 'assignRole')) {
                $user->assignRole('owner');
            }

            // Business model ke fields apne table ke hisab se adjust kar lena
            $business = Business::create([
                'name'                => $request->business_name,
                'email'               => $request->business_email,
                'mobile'              => $request->mobile,
                'gstin'               => $request->gstin,
                'type'                => $request->type,
                'address'             => $request->address,
                'state'               => $request->state,
                'state_code'          => $request->state_code,
                'gst_enabled'         => $request->gst_enabled ?? 1,
                'invoice_base_prefix' => $request->invoice_base_prefix ?? 'RV/SL',
                'rounding_mode'       => $request->rounding_mode ?? 'nearest',
                'rounding_step'       => $request->rounding_step ?? 1.00,
                'created_by'          => $user->id,
            ]);

            // Agar owner mapping field hai to use update kar sakte ho
            if (\Schema::hasColumn('businesses', 'owner_id')) {
                $business->owner_id = $user->id;
                $business->save();
            }

            // User ko business se map karna ho to
            if (\Schema::hasColumn('users', 'business_id')) {
                $user->business_id = $business->id;
                $user->save();
            }

            DB::commit();

            Cache::forget('register_otp_' . $email);
            Cache::forget('register_otp_verified_' . $email);
            session()->forget('register_email_verified');

            Auth::login($user);

            return redirect()->route('dashboard')->with('success', 'Account successfully create ho gaya.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->withErrors([
                    'error' => 'Registration failed: ' . $e->getMessage(),
                ]);
        }
    }
}
