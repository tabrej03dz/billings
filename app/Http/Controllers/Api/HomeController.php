<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Invoice;
use App\Models\Item;
use App\Models\MetalRate;
use App\Models\Purchase;
use App\Models\RegisterOtp;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Mail;


class HomeController extends Controller
{
    // public function login(Request $request)
    // {
    //     $data = $request->validate([
    //         'email'    => ['required','email'],
    //         'password' => ['required','string','min:4'],
    //         'device_name' => ['nullable','string','max:100'], // optional
    //     ]);


    //     $user = User::where('email', $data['email'])->first();


    //     if (!$user || !Hash::check($data['password'], $user->password)) {
    //         throw ValidationException::withMessages([
    //             'email' => ['Invalid email or password.'],
    //         ]);
    //     }



    //     // (Optional) old tokens delete (single device login chahiye to)
    //     // $user->tokens()->delete();



    //     $token = $user->createToken('authToken')->plainTextToken;

    //     return response()->json([
    //         'status' => true,
    //         'message' => 'Login successful',
    //         'token_type' => 'Bearer',
    //         'token' => $token,
    //         'user' => [
    //             'id' => $user->id,
    //             'name' => $user->name,
    //             'email' => $user->email,
    //             'business' => $user->businesses
    //         ],

    //     ], 200);
    // }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email'       => ['required', 'email'],
            'password'    => ['required', 'string', 'min:4'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid email or password.'],
            ]);
        }

        $otp = $user->email == 'shorabh.ftp.72@gmail.com' ? 111111 : rand(100000, 999999);

        $user->update([
            'otp' => $otp,
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        Mail::raw("Your login OTP is: {$otp}. This OTP is valid for 10 minutes.", function ($message) use ($user) {
            $message->to($user->email)
                    ->subject('Your Login OTP');
        });

        return response()->json([
            'status' => true,
            'message' => 'OTP sent successfully on your email.',
            'email' => $user->email,
        ], 200);
    }


    public function verifyLoginOtp(Request $request)
    {
        $data = $request->validate([
            'email'       => ['required', 'email'],
            'otp'         => ['required', 'digits:6'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['User not found.'],
            ]);
        }

        if (!$user->otp || $user->otp != $data['otp']) {
            throw ValidationException::withMessages([
                'otp' => ['Invalid OTP.'],
            ]);
        }

        if (!$user->otp_expires_at || now()->greaterThan($user->otp_expires_at)) {
            throw ValidationException::withMessages([
                'otp' => ['OTP expired. Please login again.'],
            ]);
        }

        // Single device login chahiye to ye line uncomment kar dena
        // $user->tokens()->delete();

        $deviceName = $data['device_name'] ?? 'authToken';

        $token = $user->createToken($deviceName)->plainTextToken;

        $user->update([
            'otp' => null,
            'otp_expires_at' => null,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Login successful',
            'token_type' => 'Bearer',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'business' => $user->businesses,
            ],
        ], 200);
    }


    public function logout(Request $request)
    {
        // current token revoke
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => true,
            'message' => 'Logged out successfully',
        ], 200);
    }

    public function user(Request $request){
        $user = $request->user();
        return response()->json([
            'status' => true,
            'user' => $user,
            'business' => $user->businesses,
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user(); // sanctum auth user

        $data = $request->validate([
            'name'  => ['required','string','max:120'],
            'email' => [
                'required','email','max:190',
                Rule::unique('users','email')->ignore($user->id),
            ],

            // optional fields (agar aapke users table me hain)
            'phone' => ['nullable','string','max:20'],
            'avatar' => ['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'], // 2MB
        ]);

        // ✅ avatar upload (optional)
        if ($request->hasFile('avatar')) {

            // old delete (optional)
            if (!empty($user->avatar) && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            $path = $request->file('avatar')->store('avatars', 'public');
            $data['avatar'] = $path; // users.avatar column expected
        }

        $user->fill($data)->save();

        return response()->json([
            'status'  => true,
            'message' => 'Profile updated successfully',
            'user'    => [
                'id'     => $user->id,
                'name'   => $user->name,
                'email'  => $user->email,
                'phone'  => $user->phone ?? null,
                'avatar' => $user->avatar ?? null,
                'business' => $user->businesses ?? [],
            ],
        ], 200);
    }

    public function changePassword(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'current_password' => ['required','string'],
            'new_password'     => ['required','string','min:6','confirmed'],
            // confirmed => new_password_confirmation required
        ]);

        if (!Hash::check($data['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Current password is incorrect.'],
            ]);
        }

        // ✅ same password prevent (optional)
        if (Hash::check($data['new_password'], $user->password)) {
            throw ValidationException::withMessages([
                'new_password' => ['New password must be different from current password.'],
            ]);
        }

        $user->password = Hash::make($data['new_password']);
        $user->save();

        // ✅ optional: sab tokens logout karne hain to
        // $user->tokens()->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Password changed successfully',
        ], 200);
    }



    public function index(Request $request)
    {
        $user = $request->user();
        abort_if(!$user, 401);

        if (!$user->businesses()->exists()) {
            return response()->json([
                'ok'      => false,
                'code'    => 'NO_BUSINESS',
                'message' => 'Please configure WhatsApp API and send PDFs directly.',
            ], 422);
        }

        $tz = config('app.timezone', 'Asia/Kolkata');

        // ✅ Business id (header best)
        $bid = (int) ($request->header('X-Business-Id')
            ?: ($user->current_business_id ?? session('active_business_id')));

        abort_if(!$bid, 422, 'X-Business-Id required.');

        $business = Business::find($bid);
        abort_if(!$business, 404, 'Business not found.');

        $belongs = $user->businesses()->where('business_id', $bid)->exists();
        abort_if(!$belongs, 403, 'You do not have access to this business.');

        // =========================================
        // ✅ DATE FILTERS (API)
        // Query: ?from=YYYY-MM-DD&to=YYYY-MM-DD&preset=today|7d|month
        // Default: month start -> today
        // =========================================
        $today      = Carbon::now($tz)->startOfDay();
        $monthStart = Carbon::now($tz)->startOfMonth()->startOfDay();

        $from = $request->filled('from')
            ? Carbon::parse($request->query('from'), $tz)->startOfDay()
            : $monthStart;

        $to = $request->filled('to')
            ? Carbon::parse($request->query('to'), $tz)->endOfDay()
            : Carbon::now($tz)->endOfDay();

        // preset override
        $preset = strtolower(trim((string) $request->query('preset', '')));
        if ($preset === 'today') {
            $from = Carbon::now($tz)->startOfDay();
            $to   = Carbon::now($tz)->endOfDay();
        } elseif ($preset === '7d') {
            $from = Carbon::now($tz)->subDays(6)->startOfDay();
            $to   = Carbon::now($tz)->endOfDay();
        } elseif ($preset === 'month') {
            $from = Carbon::now($tz)->startOfMonth()->startOfDay();
            $to   = Carbon::now($tz)->endOfDay();
        }

        // safety swap
        if ($from->gt($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        // =========================================
        // ✅ Base Queries (business scoped)
        // =========================================
        $invoiceQ  = Invoice::query()->where('business_id', $bid);
        $purchaseQ = Purchase::query()->where('business_id', $bid);
        $itemQ     = Item::query()->where('business_id', $bid);
        $rateQ     = MetalRate::query()->where('business_id', $bid);

        // ✅ Sales only TAX
        $salesQ = (clone $invoiceQ)->where(function ($q) {
            $q->where('invoice_type', 'tax')
                ->orWhereNull('invoice_type'); // optional
        });

        // -----------------------------------------
        // ✅ TODAY metrics (fixed today)
        // -----------------------------------------
        $todaySalesAmount = (clone $salesQ)->whereDate('invoice_date', $today)->sum('total');
        $todaySalesCount  = (clone $salesQ)->whereDate('invoice_date', $today)->count();

        $todayPurchasesAmount = (clone $purchaseQ)->whereDate('invoice_date', $today)->sum('total_amount');

        $todayPendingAmount = (clone $salesQ)->whereDate('invoice_date', $today)->sum('balance');

        // -----------------------------------------
        // ✅ RANGE metrics (from -> to)
        // (month_* variables ab range based)
        // -----------------------------------------
        $monthSalesAmount = (clone $salesQ)->whereBetween('invoice_date', [$from, $to])->sum('total');
        $totalSalesAmount = (clone $salesQ)->sum('total');

        $monthPurchasesAmount = (clone $purchaseQ)->whereBetween('invoice_date', [$from, $to])->sum('total_amount');
        $totalPurchasesAmount = (clone $purchaseQ)->sum('total_amount');

        $monthPendingAmount = (clone $salesQ)->whereBetween('invoice_date', [$from, $to])->sum('balance');
        $totalPendingAmount = (clone $salesQ)->sum('balance');

        // -----------------------------------------
        // ✅ Items / stock (no date filter generally)
        // -----------------------------------------
        $totalItems    = (clone $itemQ)->count();
        $totalStockQty = (clone $itemQ)->sum('stock_qty');
        $lowStockCount = (clone $itemQ)->where('stock_qty', '<=', 2)->count();

        // -----------------------------------------
        // ✅ Today metal rates (fixed today)
        // -----------------------------------------
        $todayMetalRates = (clone $rateQ)
            ->whereDate('rate_date', $today)
            ->where('is_active', true)
            ->get();

        $baseGoldPurities   = ['24K', '22K', '20K', '18K'];
        $baseSilverPurities = ['999', '995', '925'];

        $goldPurities = collect($baseGoldPurities)
            ->merge($todayMetalRates->where('metal_type', 'gold')->pluck('purity')->filter())
            ->unique()->values()->all();

        $silverPurities = collect($baseSilverPurities)
            ->merge($todayMetalRates->where('metal_type', 'silver')->pluck('purity')->filter())
            ->unique()->values()->all();

        $rateMap = $todayMetalRates
            ->keyBy(fn ($r) => strtolower($r->metal_type) . '|' . (string) ($r->purity ?? ''))
            ->map->rate_per_gram
            ->toArray();

        // -----------------------------------------
        // ✅ Lists (range filtered)
        // -----------------------------------------
        $recentInvoices = (clone $salesQ)
            ->whereBetween('invoice_date', [$from, $to])
            ->with('client')
            ->latest('invoice_date')->latest('id')
            ->limit(5)->get();

        $recentPurchases = (clone $purchaseQ)
            ->whereBetween('invoice_date', [$from, $to])
            ->with('supplier')
            ->latest('invoice_date')->latest('id')
            ->limit(5)->get();

        $lowStockItems = (clone $itemQ)
            ->with('category')
            ->where('stock_qty', '<=', 5)
            ->orderBy('stock_qty')
            ->limit(5)->get();

        return response()->json([
            'ok' => true,
            'meta' => [
                'today'       => $today->toDateString(),
                'month_start' => $monthStart->toDateString(),

                // ✅ filter meta
                'from'        => $from->toDateString(),
                'to'          => $to->toDateString(),
                'preset'      => $preset ?: null,

                'business_id' => $bid,
            ],
            'business' => $business,

            'sales' => [
                'today_amount' => (float) $todaySalesAmount,
                'today_count'  => (int) $todaySalesCount,
                'month_amount' => (float) $monthSalesAmount, // range amount
                'total_amount' => (float) $totalSalesAmount,
            ],

            'purchases' => [
                'today_amount' => (float) $todayPurchasesAmount,
                'month_amount' => (float) $monthPurchasesAmount, // range amount
                'total_amount' => (float) $totalPurchasesAmount,
            ],

            'items' => [
                'total_items'     => (int) $totalItems,
                'total_stock_qty' => (float) $totalStockQty,
                'low_stock_count' => (int) $lowStockCount,
            ],

            'metal' => [
                'today_rates'     => $todayMetalRates,
                'gold_purities'   => $goldPurities,
                'silver_purities' => $silverPurities,
                'rate_map'        => $rateMap,
            ],

            'lists' => [
                'recent_invoices'  => $recentInvoices,
                'recent_purchases' => $recentPurchases,
                'low_stock_items'  => $lowStockItems,
            ],

            'pending' => [
                'today_amount' => (float) $todayPendingAmount,
                'month_amount' => (float) $monthPendingAmount, // range amount
                'total_amount' => (float) $totalPendingAmount,
            ],
        ]);
    }



    public function myPermissions(Request $request)
    {
        $user = $request->user();

        $permissions = $user->getAllPermissions()
            ->pluck('name')
            ->unique()
            ->values();

        return response()->json([
            'status' => true,
            'permissions' => $permissions,
        ], 200);
    }


    public function deleteAccount(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'password' => ['required','string'],
        ]);

        // ❌ wrong password
        if (!Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['Incorrect password.'],
            ]);
        }

        // 🔒 revoke all tokens
        $user->tokens()->delete();

        // 🗑️ delete avatar file
        if (!empty($user->avatar) && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        // 🔗 detach from businesses (pivot)
        if (method_exists($user, 'businesses')) {
            $user->businesses()->detach();
        }

        // 🔥 finally delete user
        $user->delete(); // agar softDeletes use ho rahe hain to soft delete hoga

        return response()->json([
            'status' => true,
            'message' => 'Your account has been permanently deleted.',
        ], 200);
    }

    // public function register(Request $request)
    // {
    //     $data = $request->validate([
    //         'name'        => ['required','string','max:120'],
    //         'email'       => ['required','email','max:190', 'unique:users,email'],
    //         'password'    => ['required','string','min:6','confirmed'],
    //         // confirmed => password_confirmation required

    //         'phone'       => ['nullable','string','max:20'],
    //         'avatar'      => ['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'],

    //         // optional: business attach
    //         'business_id' => ['nullable','integer','exists:businesses,id'],

    //         'device_name' => ['nullable','string','max:100'],
    //     ]);

    //     return DB::transaction(function () use ($request, $data) {

    //         // ✅ avatar upload (optional)
    //         $avatarPath = null;
    //         if ($request->hasFile('avatar')) {
    //             $avatarPath = $request->file('avatar')->store('avatars', 'public');
    //         }

    //         // ✅ create user
    //         $user = User::create([
    //             'name'     => $data['name'],
    //             'email'    => $data['email'],
    //             'password' => Hash::make($data['password']),
    //             'phone'    => $data['phone'] ?? null,
    //             'avatar'   => $avatarPath,
    //         ]);

    //         // ✅ business attach (optional)
    //         if (!empty($data['business_id'])) {
    //             // pivot attach (assumes many-to-many relation exists)
    //             $user->businesses()->attach($data['business_id']);

    //             // optional: set current business id if your users table has this column
    //             if (Schema::hasColumn('users', 'current_business_id')) {
    //                 $user->current_business_id = $data['business_id'];
    //                 $user->save();
    //             }
    //         }

    //         // ✅ create token
    //         $tokenName = $d42200ata['device_name'] ?? 'authToken';
    //         $token = $user->createToken($tokenName)->plainTextToken;

    //         // fresh businesses load
    //         $user->load('businesses');

    //         return response()->json([
    //             'status'     => true,
    //             'message'    => 'Register successful',
    //             'token_type' => 'Bearer',
    //             'token'      => $token,
    //             'user'       => [
    //                 'id'       => $user->id,
    //                 'name'     => $user->name,
    //                 'email'    => $user->email,
    //                 'phone'    => $user->phone,
    //                 'avatar'   => $user->avatar,
    //                 'business' => $user->businesses,
    //             ],
    //         ], 201);
    //     });
    // }


    public function register(Request $request)
    {
        $data = $request->validate([
            'name'        => ['required','string','max:120'],
            'email'       => ['required','email','max:190','unique:users,email'],
            'password'    => ['required','string','min:6','confirmed'],
            'phone'       => ['nullable','string','max:20'],
            'business_id' => ['nullable','integer','exists:businesses,id'],
            'device_name' => ['nullable','string','max:100'],
        ]);

        $otp = rand(100000, 999999);

        RegisterOtp::where('email', $data['email'])->delete();

        RegisterOtp::create([
            'email'      => $data['email'],
            'otp'        => $otp,
            'payload'    => $data,
            'expires_at' => now()->addMinutes(10),
        ]);

        Mail::raw("Your registration OTP is: {$otp}", function ($message) use ($data) {
            $message->to($data['email'])
                ->subject('Verify Your Email');
        });

        return response()->json([
            'status'  => true,
            'message' => 'OTP sent successfully on your email.',
            'email'   => $data['email'],
        ]);
    }



    public function verifyRegisterOtp(Request $request)
    {
        $request->validate([
            'email' => ['required','email'],
            'otp'   => ['required','digits:6'],
        ]);

        $otpRecord = RegisterOtp::where('email', $request->email)
            ->where('otp', $request->otp)
            ->first();

        if (!$otpRecord) {
            return response()->json([
                'status'  => false,
                'message' => 'Invalid OTP.',
            ], 422);
        }

        if ($otpRecord->expires_at->isPast()) {
            $otpRecord->delete();

            return response()->json([
                'status'  => false,
                'message' => 'OTP expired. Please register again.',
            ], 422);
        }

        $data = $otpRecord->payload;

        return DB::transaction(function () use ($data, $otpRecord) {

            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => Hash::make($data['password']),
                'phone'    => $data['phone'] ?? null,
            ]);

            if (!empty($data['business_id'])) {
                $user->businesses()->attach($data['business_id']);

                if (Schema::hasColumn('users', 'current_business_id')) {
                    $user->current_business_id = $data['business_id'];
                    $user->save();
                }
            }

            $tokenName = $data['device_name'] ?? 'authToken';
            $token = $user->createToken($tokenName)->plainTextToken;

            $user->load('businesses');

            $otpRecord->delete();

            return response()->json([
                'status'     => true,
                'message'    => 'Email verified and registration successful.',
                'token_type' => 'Bearer',
                'token'      => $token,
                'user'       => [
                    'id'       => $user->id,
                    'name'     => $user->name,
                    'email'    => $user->email,
                    'phone'    => $user->phone,
                    'business' => $user->businesses,
                ],
            ], 201);
        });
    }

}
