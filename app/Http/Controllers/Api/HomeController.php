<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Invoice;
use App\Models\Item;
use App\Models\MetalRate;
use App\Models\OnboardingRegistration;
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
use Illuminate\Support\Facades\Http;


class HomeController extends Controller
{

    // SKIP OTP LOGIN FOR TESTING PURPOSES
    // public function login(Request $request)
    // {
    //     $data = $request->validate([
    //         'phone'       => ['required', 'digits:10'],
    //         'device_name' => ['nullable', 'string', 'max:100'],
    //     ]);

    //     $user = User::where('phone', $data['phone'])->first();

    //     if (!$user) {
    //         throw ValidationException::withMessages([
    //             'phone' => ['Mobile number not registered.'],
    //         ]);
    //     }

    //     // Single-device login chahiye to is line ko uncomment karein
    //     // $user->tokens()->delete();

    //     $deviceName = $data['device_name'] ?? 'authToken';

    //     // OTP ke bina direct login token
    //     $token = $user->createToken($deviceName)->plainTextToken;

    //     // Purana OTP pada ho to clear kar denge
    //     $user->update([
    //         'otp' => null,
    //         'otp_expires_at' => null,
    //     ]);

    //     return response()->json([
    //         'status' => true,
    //         'message' => 'Login successful',
    //         'token_type' => 'Bearer',
    //         'token' => $token,
    //         'user' => [
    //             'id' => $user->id,
    //             'name' => $user->name,
    //             'email' => $user->email,
    //             'phone' => $user->phone,
    //             'business' => $user->businesses,
    //         ],
    //     ], 200);
    // }

    public function login(Request $request)
    {
        $data = $request->validate([
            'phone'       => ['required', 'digits:10'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        $user = User::where('phone', $data['phone'])->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'phone' => ['Mobile number not registered.'],
            ]);
        }

        $otp = $user->phone == '7753800444' || $user->phone == '8948467535'
            ? 111111
            : rand(100000, 999999);

        $user->update([
            'otp' => $otp,
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        $msg = "Dear Customer, {$otp} this is your login verification OTP. Please do not share with anyone. Best Regards, Real Victory Groups https://myvictory.in/";

        $response = Http::get('https://kutility.org/app/smsapi/index.php', [
            'key'         => '5620360CF8C9B4',
            'campaign'    => '12754',
            'routeid'     => '7',
            'type'        => 'text',
            'contacts'    => $user->phone,
            'senderid'    => 'RVGRPS',
            'msg'         => $msg,
            'template_id' => '1707178057481157648',
            'pe_id'       => '1701164032595209992',
        ]);

        return response()->json([
            'status' => true,
            'message' => 'OTP sent successfully on your mobile number.',
            'phone' => $user->phone,
            'sms_response' => $response->body(),
        ], 200);
    }


    // SKIP VERIFY
    // public function verifyLoginOtp(Request $request)
    // {
    //     $data = $request->validate([
    //         'phone'       => ['required', 'digits:10'],
    //         'otp'         => ['nullable', 'digits:6'],
    //         'device_name' => ['nullable', 'string', 'max:100'],
    //     ]);

    //     $user = User::where('phone', $data['phone'])->first();

    //     if (!$user) {
    //         throw ValidationException::withMessages([
    //             'phone' => ['User not found.'],
    //         ]);
    //     }

    //     /*
    //     |--------------------------------------------------------------------------
    //     | OTP Verification
    //     |--------------------------------------------------------------------------
    //     | Local और testing environment में OTP check bypass रहेगा।
    //     | Production में OTP और expiry दोनों check होंगे।
    //     */

    //     if (!app()->environment(['local', 'testing'])) {
    //         if (!$user->otp || $user->otp != $data['otp']) {
    //             throw ValidationException::withMessages([
    //                 'otp' => ['Invalid OTP.'],
    //             ]);
    //         }

    //         if (
    //             !$user->otp_expires_at ||
    //             now()->greaterThan($user->otp_expires_at)
    //         ) {
    //             throw ValidationException::withMessages([
    //                 'otp' => ['OTP expired. Please login again.'],
    //             ]);
    //         }
    //     }

    //     // Single device login चाहिए तो uncomment करें
    //     // $user->tokens()->delete();

    //     $deviceName = $data['device_name'] ?? 'authToken';

    //     $token = $user->createToken($deviceName)->plainTextToken;

    //     $user->update([
    //         'otp'            => null,
    //         'otp_expires_at' => null,
    //     ]);

    //     $user->load('businesses');

    //     return response()->json([
    //         'status'     => true,
    //         'message'    => 'Login successful',
    //         'token_type' => 'Bearer',
    //         'token'      => $token,
    //         'user'       => [
    //             'id'       => $user->id,
    //             'name'     => $user->name,
    //             'email'    => $user->email,
    //             'phone'    => $user->phone,
    //             'business' => $user->businesses,
    //         ],
    //     ], 200);
    // }


    public function verifyLoginOtp(Request $request)
    {
        $data = $request->validate([
            'phone'       => ['required', 'digits:10'],
            'otp'         => ['required', 'digits:6'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

      

        $user = User::where('phone', $data['phone'])->first();



        if (!$user) {
            throw ValidationException::withMessages([
                'phone' => ['User not found.'],
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

        // Single device login chahiye to uncomment
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
                'phone' => $user->phone,
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



    // public function index(Request $request)
    // {
    //     $user = $request->user();
    //     abort_if(!$user, 401);

    //     if (!$user->businesses()->exists()) {
    //         return response()->json([
    //             'ok'      => false,
    //             'code'    => 'NO_BUSINESS',
    //             'message' => 'Please configure WhatsApp API and send PDFs directly.',
    //         ], 422);
    //     }

    //     $tz = config('app.timezone', 'Asia/Kolkata');

    //     // ✅ Business id (header best)
    //     $bid = (int) ($request->header('X-Business-Id')
    //         ?: ($user->current_business_id ?? session('active_business_id')));

    //     abort_if(!$bid, 422, 'X-Business-Id required.');

    //     $business = Business::find($bid);
    //     abort_if(!$business, 404, 'Business not found.');

    //     $belongs = $user->businesses()->where('business_id', $bid)->exists();
    //     abort_if(!$belongs, 403, 'You do not have access to this business.');

    //     // =========================================
    //     // ✅ DATE FILTERS (API)
    //     // Query: ?from=YYYY-MM-DD&to=YYYY-MM-DD&preset=today|7d|month
    //     // Default: month start -> today
    //     // =========================================
    //     $today      = Carbon::now($tz)->startOfDay();
    //     $monthStart = Carbon::now($tz)->startOfMonth()->startOfDay();

    //     $from = $request->filled('from')
    //         ? Carbon::parse($request->query('from'), $tz)->startOfDay()
    //         : $monthStart;

    //     $to = $request->filled('to')
    //         ? Carbon::parse($request->query('to'), $tz)->endOfDay()
    //         : Carbon::now($tz)->endOfDay();

    //     // preset override
    //     $preset = strtolower(trim((string) $request->query('preset', '')));
    //     if ($preset === 'today') {
    //         $from = Carbon::now($tz)->startOfDay();
    //         $to   = Carbon::now($tz)->endOfDay();
    //     } elseif ($preset === '7d') {
    //         $from = Carbon::now($tz)->subDays(6)->startOfDay();
    //         $to   = Carbon::now($tz)->endOfDay();
    //     } elseif ($preset === 'month') {
    //         $from = Carbon::now($tz)->startOfMonth()->startOfDay();
    //         $to   = Carbon::now($tz)->endOfDay();
    //     }

    //     // safety swap
    //     if ($from->gt($to)) {
    //         [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
    //     }

    //     // =========================================
    //     // ✅ Base Queries (business scoped)
    //     // =========================================
    //     $invoiceQ  = Invoice::query()->where('business_id', $bid);
    //     $purchaseQ = Purchase::query()->where('business_id', $bid);
    //     $itemQ     = Item::query()->where('business_id', $bid);
    //     $rateQ     = MetalRate::query()->where('business_id', $bid);

    //     // ✅ Sales only TAX
    //     $salesQ = (clone $invoiceQ)->where(function ($q) {
    //         $q->where('invoice_type', 'tax')
    //             ->orWhereNull('invoice_type'); // optional
    //     });

    //     // -----------------------------------------
    //     // ✅ TODAY metrics (fixed today)
    //     // -----------------------------------------
    //     $todaySalesAmount = (clone $salesQ)->whereDate('invoice_date', $today)->sum('total');
    //     $todaySalesCount  = (clone $salesQ)->whereDate('invoice_date', $today)->count();

    //     $todayPurchasesAmount = (clone $purchaseQ)->whereDate('invoice_date', $today)->sum('total_amount');

    //     $todayPendingAmount = (clone $salesQ)->whereDate('invoice_date', $today)->sum('balance');

    //     // -----------------------------------------
    //     // ✅ RANGE metrics (from -> to)
    //     // (month_* variables ab range based)
    //     // -----------------------------------------
    //     $monthSalesAmount = (clone $salesQ)->whereBetween('invoice_date', [$from, $to])->sum('total');
    //     $totalSalesAmount = (clone $salesQ)->sum('total');

    //     $monthPurchasesAmount = (clone $purchaseQ)->whereBetween('invoice_date', [$from, $to])->sum('total_amount');
    //     $totalPurchasesAmount = (clone $purchaseQ)->sum('total_amount');

    //     $monthPendingAmount = (clone $salesQ)->whereBetween('invoice_date', [$from, $to])->sum('balance');
    //     $totalPendingAmount = (clone $salesQ)->sum('balance');

    //     // -----------------------------------------
    //     // ✅ Items / stock (no date filter generally)
    //     // -----------------------------------------
    //     $totalItems    = (clone $itemQ)->count();
    //     $totalStockQty = (clone $itemQ)->sum('stock_qty');
    //     $lowStockCount = (clone $itemQ)->where('stock_qty', '<=', 2)->count();

    //     // -----------------------------------------
    //     // ✅ Today metal rates (fixed today)
    //     // -----------------------------------------
    //     $todayMetalRates = (clone $rateQ)
    //         ->whereDate('rate_date', $today)
    //         ->where('is_active', true)
    //         ->get();

    //     $baseGoldPurities   = ['24K', '22K', '20K', '18K'];
    //     $baseSilverPurities = ['999', '995', '925'];

    //     $goldPurities = collect($baseGoldPurities)
    //         ->merge($todayMetalRates->where('metal_type', 'gold')->pluck('purity')->filter())
    //         ->unique()->values()->all();

    //     $silverPurities = collect($baseSilverPurities)
    //         ->merge($todayMetalRates->where('metal_type', 'silver')->pluck('purity')->filter())
    //         ->unique()->values()->all();

    //     $rateMap = $todayMetalRates
    //         ->keyBy(fn ($r) => strtolower($r->metal_type) . '|' . (string) ($r->purity ?? ''))
    //         ->map->rate_per_gram
    //         ->toArray();

    //     // -----------------------------------------
    //     // ✅ Lists (range filtered)
    //     // -----------------------------------------
    //     $recentInvoices = (clone $salesQ)
    //         ->whereBetween('invoice_date', [$from, $to])
    //         ->with('client')
    //         ->latest('invoice_date')->latest('id')
    //         ->limit(5)->get();

    //     $recentPurchases = (clone $purchaseQ)
    //         ->whereBetween('invoice_date', [$from, $to])
    //         ->with('supplier')
    //         ->latest('invoice_date')->latest('id')
    //         ->limit(5)->get();

    //     $lowStockItems = (clone $itemQ)
    //         ->with('category')
    //         ->where('stock_qty', '<=', 5)
    //         ->orderBy('stock_qty')
    //         ->limit(5)->get();

    //     return response()->json([
    //         'ok' => true,
    //         'meta' => [
    //             'today'       => $today->toDateString(),
    //             'month_start' => $monthStart->toDateString(),

    //             // ✅ filter meta
    //             'from'        => $from->toDateString(),
    //             'to'          => $to->toDateString(),
    //             'preset'      => $preset ?: null,

    //             'business_id' => $bid,
    //         ],
    //         'business' => $business,

    //         'sales' => [
    //             'today_amount' => (float) $todaySalesAmount,
    //             'today_count'  => (int) $todaySalesCount,
    //             'month_amount' => (float) $monthSalesAmount, // range amount
    //             'total_amount' => (float) $totalSalesAmount,
    //         ],

    //         'purchases' => [
    //             'today_amount' => (float) $todayPurchasesAmount,
    //             'month_amount' => (float) $monthPurchasesAmount, // range amount
    //             'total_amount' => (float) $totalPurchasesAmount,
    //         ],

    //         'items' => [
    //             'total_items'     => (int) $totalItems,
    //             'total_stock_qty' => (float) $totalStockQty,
    //             'low_stock_count' => (int) $lowStockCount,
    //         ],

    //         'metal' => [
    //             'today_rates'     => $todayMetalRates,
    //             'gold_purities'   => $goldPurities,
    //             'silver_purities' => $silverPurities,
    //             'rate_map'        => $rateMap,
    //         ],

    //         'lists' => [
    //             'recent_invoices'  => $recentInvoices,
    //             'recent_purchases' => $recentPurchases,
    //             'low_stock_items'  => $lowStockItems,
    //         ],

    //         'pending' => [
    //             'today_amount' => (float) $todayPendingAmount,
    //             'month_amount' => (float) $monthPendingAmount, // range amount
    //             'total_amount' => (float) $totalPendingAmount,
    //         ],
    //     ]);
    // }

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

        $bid = (int) ($request->header('X-Business-Id')
            ?: ($user->current_business_id ?? session('active_business_id')));

        abort_if(!$bid, 422, 'X-Business-Id required.');

        $business = Business::find($bid);
        abort_if(!$business, 404, 'Business not found.');

        $belongs = $user->businesses()
            ->where('business_id', $bid)
            ->exists();

        abort_if(!$belongs, 403, 'You do not have access to this business.');

        $today = Carbon::now($tz)->startOfDay();

        $monthStart = Carbon::now($tz)
            ->startOfMonth()
            ->startOfDay();

        $from = $request->filled('from')
            ? Carbon::parse($request->query('from'), $tz)->startOfDay()
            : $monthStart->copy();

        $to = $request->filled('to')
            ? Carbon::parse($request->query('to'), $tz)->endOfDay()
            : Carbon::now($tz)->endOfDay();

        $preset = strtolower(
            trim((string) $request->query('preset', ''))
        );

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

        if ($from->gt($to)) {
            [$from, $to] = [
                $to->copy()->startOfDay(),
                $from->copy()->endOfDay(),
            ];
        }

        $invoiceQ = Invoice::query()
            ->where('business_id', $bid);

        $purchaseQ = Purchase::query()
            ->where('business_id', $bid);

        $itemQ = Item::query()
            ->where('business_id', $bid);

        $rateQ = MetalRate::query()
            ->where('business_id', $bid);

        $salesQ = (clone $invoiceQ)
            ->where(function ($query) {
                $query->where('invoice_type', 'tax')
                    ->orWhereNull('invoice_type');
            });

        $todaySalesAmount = (clone $salesQ)
            ->whereDate('invoice_date', $today->toDateString())
            ->sum('total');

        $todaySalesCount = (clone $salesQ)
            ->whereDate('invoice_date', $today->toDateString())
            ->count();

        // =========================================
        // ✅ TODAY GROSS PROFIT
        // =========================================
        $todayProfitData = DB::table('invoice_items as ii')
            ->join('invoices as inv', 'inv.id', '=', 'ii.invoice_id')
            ->leftJoin('items as i', function ($join) use ($bid) {
                $join->on('i.id', '=', 'ii.item_id')
                    ->where('i.business_id', '=', $bid);
            })
            ->where('inv.business_id', $bid)
            ->whereDate('inv.invoice_date', $today->toDateString())
            ->where(function ($query) {
                $query->where('inv.invoice_type', 'tax')
                    ->orWhereNull('inv.invoice_type');
            })
            ->selectRaw('
                COALESCE(
                    SUM(COALESCE(ii.amount, 0)),
                    0
                ) as sale_amount,

                COALESCE(
                    SUM(
                        COALESCE(ii.quantity, 0)
                        * COALESCE(i.cost_price, 0)
                    ),
                    0
                ) as cost_amount,

                COALESCE(
                    SUM(
                        COALESCE(ii.amount, 0)
                        -
                        (
                            COALESCE(ii.quantity, 0)
                            * COALESCE(i.cost_price, 0)
                        )
                    ),
                    0
                ) as profit_amount
            ')
            ->first();

        $todayItemSaleAmount = round(
            (float) ($todayProfitData->sale_amount ?? 0),
            2
        );

        $todayItemCostAmount = round(
            (float) ($todayProfitData->cost_amount ?? 0),
            2
        );

        $todayProfitAmount = round(
            (float) ($todayProfitData->profit_amount ?? 0),
            2
        );

        $todayProfitPercent = $todayItemSaleAmount > 0
            ? round(
                ($todayProfitAmount / $todayItemSaleAmount) * 100,
                2
            )
            : 0;

        $todayPurchasesAmount = (clone $purchaseQ)
            ->whereDate('invoice_date', $today->toDateString())
            ->sum('total_amount');

        $todayPendingAmount = (clone $salesQ)
            ->whereDate('invoice_date', $today->toDateString())
            ->sum('balance');

        $monthSalesAmount = (clone $salesQ)
            ->whereBetween('invoice_date', [$from, $to])
            ->sum('total');

        $totalSalesAmount = (clone $salesQ)
            ->sum('total');

        $monthPurchasesAmount = (clone $purchaseQ)
            ->whereBetween('invoice_date', [$from, $to])
            ->sum('total_amount');

        $totalPurchasesAmount = (clone $purchaseQ)
            ->sum('total_amount');

        $monthPendingAmount = (clone $salesQ)
            ->whereBetween('invoice_date', [$from, $to])
            ->sum('balance');

        $totalPendingAmount = (clone $salesQ)
            ->sum('balance');

        $totalItems = (clone $itemQ)->count();

        $totalStockQty = (clone $itemQ)
            ->sum('stock_qty');

        $lowStockCount = (clone $itemQ)
            ->where('stock_qty', '<=', 2)
            ->count();

        $todayMetalRates = (clone $rateQ)
            ->whereDate('rate_date', $today->toDateString())
            ->where('is_active', true)
            ->get();

        $baseGoldPurities = ['24K', '22K', '20K', '18K'];
        $baseSilverPurities = ['999', '995', '925'];

        $goldPurities = collect($baseGoldPurities)
            ->merge(
                $todayMetalRates
                    ->where('metal_type', 'gold')
                    ->pluck('purity')
                    ->filter()
            )
            ->unique()
            ->values()
            ->all();

        $silverPurities = collect($baseSilverPurities)
            ->merge(
                $todayMetalRates
                    ->where('metal_type', 'silver')
                    ->pluck('purity')
                    ->filter()
            )
            ->unique()
            ->values()
            ->all();

        $rateMap = $todayMetalRates
            ->keyBy(
                fn ($rate) =>
                    strtolower($rate->metal_type)
                    . '|'
                    . (string) ($rate->purity ?? '')
            )
            ->map->rate_per_gram
            ->toArray();

        $recentInvoices = (clone $salesQ)
            ->whereBetween('invoice_date', [$from, $to])
            ->with('client')
            ->latest('invoice_date')
            ->latest('id')
            ->limit(5)
            ->get();

        $recentPurchases = (clone $purchaseQ)
            ->whereBetween('invoice_date', [$from, $to])
            ->with('supplier')
            ->latest('invoice_date')
            ->latest('id')
            ->limit(5)
            ->get();

        $lowStockItems = (clone $itemQ)
            ->with('category')
            ->where('stock_qty', '<=', 5)
            ->orderBy('stock_qty')
            ->limit(5)
            ->get();

        return response()->json([
            'ok' => true,

            'meta' => [
                'today'       => $today->toDateString(),
                'month_start' => $monthStart->toDateString(),
                'from'        => $from->toDateString(),
                'to'          => $to->toDateString(),
                'preset'      => $preset ?: null,
                'business_id' => $bid,
            ],

            'business' => $business,

            'sales' => [
                'today_amount' => (float) $todaySalesAmount,
                'today_count'  => (int) $todaySalesCount,
                'month_amount' => (float) $monthSalesAmount,
                'total_amount' => (float) $totalSalesAmount,

                'today_profit' => [
                    'sale_amount'    => (float) $todayItemSaleAmount,
                    'cost_amount'    => (float) $todayItemCostAmount,
                    'profit_amount'  => (float) $todayProfitAmount,
                    'profit_percent' => (float) $todayProfitPercent,
                ],
            ],

            'purchases' => [
                'today_amount' => (float) $todayPurchasesAmount,
                'month_amount' => (float) $monthPurchasesAmount,
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
                'month_amount' => (float) $monthPendingAmount,
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
            'business' => $user->businesses,
        ], 200);

        
    }


    public function sendDeleteAccountOtp(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        if (empty($user->phone)) {
            return response()->json([
                'status' => false,
                'message' => 'Mobile number is not registered with this account.',
            ], 422);
        }

        /*
        * Testing numbers के लिए fixed OTP.
        * Production में चाहें तो fixed OTP पूरी तरह हटा दें.
        */
        $otp = in_array($user->phone, ['7753800444', '8948467535'])
            ? 111111
            : random_int(100000, 999999);

        $user->update([
            'otp' => $otp,
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        //https://kutility.org/app/smsapi/index.php?key=5620360CF8C9B4&campaign=12754&routeid=7&type=text&contacts=7753800444&senderid=RVGRPS&msg=Dear Customer, 2406 is your OTP to confirm deletion of your account on Real Victory Groups (MyVictory). This OTP is valid for 10 minutes. Please do not share it with anyone. Best Regards, Real Victory Groups https://myvictory.in/&template_id=1707178368256960218&pe_id=1701164032595209992

        $msg = "Dear Customer, {$otp} is your OTP to confirm deletion of your account on Real Victory Groups (MyVictory). This OTP is valid for 10 minutes. Please do not share it with anyone. Best Regards, Real Victory Groups https://myvictory.in";
        try {
            $response = Http::timeout(20)
                ->get('https://kutility.org/app/smsapi/index.php', [
                    'key'         => '5620360CF8C9B4',
                    'campaign'    => '12754',
                    'routeid'     => '7',
                    'type'        => 'text',
                    'contacts'    => $user->phone,
                    'senderid'    => 'RVGRPS',
                    'msg'         => $msg,
                    'template_id' => '1707178368256960218',
                    'pe_id'       => '1701164032595209992',
                ]);

            if (!$response->successful()) {
                $user->update([
                    'otp' => null,
                    'otp_expires_at' => null,
                ]);

                return response()->json([
                    'status' => false,
                    'message' => 'OTP could not be sent. Please try again.',
                ], 502);
            }
        } catch (\Throwable $exception) {
            $user->update([
                'otp' => null,
                'otp_expires_at' => null,
            ]);

            report($exception);

            return response()->json([
                'status' => false,
                'message' => 'OTP service is currently unavailable. Please try again.',
            ], 502);
        }

        return response()->json([
            'status' => true,
            'message' => 'Account deletion OTP sent successfully.',
            'phone' => $this->maskPhoneNumber($user->phone),
            'expires_in' => 600,
        ], 200);
    }


    // public function deleteAccount(Request $request)
    // {
    //     $user = $request->user();

    //     $data = $request->validate([
    //         'password' => ['required','string'],
    //     ]);

    //     // ❌ wrong password
    //     if (!Hash::check($data['password'], $user->password)) {
    //         throw ValidationException::withMessages([
    //             'password' => ['Incorrect password.'],
    //         ]);
    //     }

    //     // 🔒 revoke all tokens
    //     $user->tokens()->delete();

    //     // 🗑️ delete avatar file
    //     if (!empty($user->avatar) && Storage::disk('public')->exists($user->avatar)) {
    //         Storage::disk('public')->delete($user->avatar);
    //     }

    //     // 🔗 detach from businesses (pivot)
    //     if (method_exists($user, 'businesses')) {
    //         $user->businesses()->detach();
    //     }

    //     // 🔥 finally delete user
    //     $user->delete(); // agar softDeletes use ho rahe hain to soft delete hoga

    //     return response()->json([
    //         'status' => true,
    //         'message' => 'Your account has been permanently deleted.',
    //     ], 200);
    // }


    public function deleteAccount(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $data = $request->validate([
            'otp' => ['required', 'digits:6'],
        ]);

        if (
            empty($user->otp) ||
            (string) $user->otp !== (string) $data['otp']
        ) {
            throw ValidationException::withMessages([
                'otp' => ['Invalid account deletion OTP.'],
            ]);
        }

        if (
            empty($user->otp_expires_at) ||
            now()->greaterThan(Carbon::parse($user->otp_expires_at))
        ) {
            $user->update([
                'otp' => null,
                'otp_expires_at' => null,
            ]);

            throw ValidationException::withMessages([
                'otp' => ['OTP has expired. Please request a new OTP.'],
            ]);
        }

        DB::transaction(function () use ($user) {

            // OTP invalidate करें
            $user->update([
                'otp' => null,
                'otp_expires_at' => null,
            ]);

            // सभी login tokens revoke करें
            $user->tokens()->delete();

            /*
            * Important:
            * Business relation detach नहीं करना है।
            * Avatar भी delete नहीं करना है।
            * इससे restore के बाद पूरा account वापस आएगा।
            */

            $user->delete(); // Soft delete
        });

        return response()->json([
            'status' => true,
            'message' => 'Your account has been moved to recycle bin.',
        ], 200);
    }



    public function register(Request $request)
    {
        $data = $request->validate([
            'name'        => ['required','string','max:120'],
            'email'       => ['required','email','max:190','unique:users,email'],
            'password'    => ['required','string','min:6','confirmed'],
            'phone'       => ['required','digits:10','unique:users,phone'],
            'business_id' => ['nullable','integer','exists:businesses,id'],
            'device_name' => ['nullable','string','max:100'],
        ]);

        $otp = rand(100000, 999999);

        RegisterOtp::where('phone', $data['phone'])->delete();

        RegisterOtp::create([
            'phone'      => $data['phone'],
            'email'      => $data['email'],
            'otp'        => $otp,
            'payload'    => $data,
            'expires_at' => now()->addMinutes(10),
        ]);
        OnboardingRegistration::updateOrCreate(
            [
                'phone' => $data['phone'],
            ],
            [
                'name'   => $data['name'],
                'registration_status' => 'registering',
            ]
        );



        $msg = "Dear Customer, {$otp} this is your login verification OTP. Please do not share with anyone. Best Regards, Real Victory Groups https://myvictory.in/";

        $response = Http::get('https://kutility.org/app/smsapi/index.php', [
            'key'         => '5620360CF8C9B4',
            'campaign'    => '12754',
            'routeid'     => '7',
            'type'        => 'text',
            'contacts'    => $data['phone'],
            'senderid'    => 'RVGRPS',
            'msg'         => $msg,
            'template_id' => '1707178057481157648',
            'pe_id'       => '1701164032595209992',
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'OTP sent successfully on your mobile number.',
            'phone'   => $data['phone'],
            'sms_response' => $response->body(),
        ]);
    }
    public function verifyRegisterOtp(Request $request)
    {
        $request->validate([
            'phone' => ['required','digits:10'],
            'otp'   => ['required','digits:6'],
        ]);

        $otpRecord = RegisterOtp::where('phone', $request->phone)
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
                'phone'    => $data['phone'],
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

            $onboarding = OnboardingRegistration::where('phone', $user->phone)->first();
            if($onboarding){
                $onboarding->update(['user_id' => $user->id, 'registration_status' => 'registered']);
            }

            return response()->json([
                'status'     => true,
                'message'    => 'Mobile number verified and registration successful.',
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

        // SKIP OTP
//     public function register(Request $request)
// {
//     $data = $request->validate([
//         'name'        => ['required', 'string', 'max:120'],
//         'email'       => ['required', 'email', 'max:190', 'unique:users,email'],
//         'password'    => ['required', 'string', 'min:6', 'confirmed'],
//         'phone'       => ['required', 'digits:10', 'unique:users,phone'],
//         'business_id' => ['nullable', 'integer', 'exists:businesses,id'],
//         'device_name' => ['nullable', 'string', 'max:100'],
//     ]);

//     /*
//     |--------------------------------------------------------------------------
//     | Testing / Local Environment
//     |--------------------------------------------------------------------------
//     | Local और testing environment में SMS send नहीं होगा।
//     | Verify OTP API में कोई भी 6-digit OTP डालकर registration हो जाएगा।
//     */
//     $isTestingEnvironment = app()->environment(['local', 'testing']);

//     $otp = $isTestingEnvironment
//         ? '123456'
//         : (string) random_int(100000, 999999);

//     RegisterOtp::where('phone', $data['phone'])->delete();

//     RegisterOtp::create([
//         'phone'      => $data['phone'],
//         'email'      => $data['email'],
//         'otp'        => $otp,
//         'payload'    => $data,
//         'expires_at' => now()->addMinutes(10),
//     ]);

//     /*
//     |--------------------------------------------------------------------------
//     | Testing में SMS Skip
//     |--------------------------------------------------------------------------
//     */
//     if ($isTestingEnvironment) {
//         return response()->json([
//             'status'      => true,
//             'message'     => 'Testing mode: OTP SMS skipped. Enter any 6-digit OTP.',
//             'phone'       => $data['phone'],
//             'testing_otp' => $otp,
//         ]);
//     }

//     /*
//     |--------------------------------------------------------------------------
//     | Production में Real SMS Send
//     |--------------------------------------------------------------------------
//     */
//     $msg = "Dear Customer, {$otp} this is your login verification OTP. "
//         . "Please do not share with anyone. Best Regards, "
//         . "Real Victory Groups https://myvictory.in/";

//     try {
//         $response = Http::timeout(20)
//             ->get('https://kutility.org/app/smsapi/index.php', [
//                 'key'         => '5620360CF8C9B4',
//                 'campaign'    => '12754',
//                 'routeid'     => '7',
//                 'type'        => 'text',
//                 'contacts'    => $data['phone'],
//                 'senderid'    => 'RVGRPS',
//                 'msg'         => $msg,
//                 'template_id' => '1707178057481157648',
//                 'pe_id'       => '1701164032595209992',
//             ]);

//         if (!$response->successful()) {
//             return response()->json([
//                 'status'  => false,
//                 'message' => 'Unable to send OTP. Please try again.',
//             ], 500);
//         }

//         return response()->json([
//             'status'       => true,
//             'message'      => 'OTP sent successfully on your mobile number.',
//             'phone'        => $data['phone'],
//             'sms_response' => $response->body(),
//         ]);
//     } catch (\Throwable $e) {
//         report($e);

//         return response()->json([
//             'status'  => false,
//             'message' => 'OTP service is currently unavailable. Please try again.',
//         ], 500);
//     }
// }


// public function verifyRegisterOtp(Request $request)
// {
//     $validated = $request->validate([
//         'phone' => ['required', 'digits:10'],
//         'otp'   => ['required', 'digits:6'],
//     ]);

//     $isTestingEnvironment = app()->environment(['local', 'testing']);

//     /*
//     |--------------------------------------------------------------------------
//     | OTP Record Find
//     |--------------------------------------------------------------------------
//     | Testing में केवल phone से record मिलेगा और कोई भी 6-digit OTP चलेगा।
//     | Production में phone और OTP दोनों match होना जरूरी है।
//     */
//     $otpQuery = RegisterOtp::where('phone', $validated['phone']);

//     if (!$isTestingEnvironment) {
//         $otpQuery->where('otp', $validated['otp']);
//     }

//     $otpRecord = $otpQuery->latest('id')->first();

//     if (!$otpRecord) {
//         return response()->json([
//             'status'  => false,
//             'message' => $isTestingEnvironment
//                 ? 'Registration request not found. Please register first.'
//                 : 'Invalid OTP.',
//         ], 422);
//     }

//     /*
//     |--------------------------------------------------------------------------
//     | Production में Expiry Check
//     |--------------------------------------------------------------------------
//     | Testing environment में OTP expiry भी skip रहेगी।
//     */
//     if (
//         !$isTestingEnvironment &&
//         (!$otpRecord->expires_at || $otpRecord->expires_at->isPast())
//     ) {
//         $otpRecord->delete();

//         return response()->json([
//             'status'  => false,
//             'message' => 'OTP expired. Please register again.',
//         ], 422);
//     }

//     $data = $otpRecord->payload;

//     if (is_string($data)) {
//         $data = json_decode($data, true);
//     }

//     if (!is_array($data)) {
//         return response()->json([
//             'status'  => false,
//             'message' => 'Invalid registration data. Please register again.',
//         ], 422);
//     }

//     return DB::transaction(function () use ($data, $otpRecord) {
//         /*
//         |--------------------------------------------------------------------------
//         | Duplicate Check
//         |--------------------------------------------------------------------------
//         | OTP request के बाद किसी दूसरे request से user create हो गया हो,
//         | तो duplicate database error से बचने के लिए दोबारा check करेंगे।
//         */
//         $existingUser = User::where('phone', $data['phone'])
//             ->orWhere('email', $data['email'])
//             ->first();

//         if ($existingUser) {
//             $otpRecord->delete();

//             return response()->json([
//                 'status'  => false,
//                 'message' => 'User is already registered with this phone or email.',
//             ], 422);
//         }

//         $user = User::create([
//             'name'     => $data['name'],
//             'email'    => $data['email'],
//             'password' => Hash::make($data['password']),
//             'phone'    => $data['phone'],
//         ]);

//         if (!empty($data['business_id'])) {
//             $user->businesses()->syncWithoutDetaching([
//                 $data['business_id'],
//             ]);

//             if (Schema::hasColumn('users', 'current_business_id')) {
//                 $user->current_business_id = $data['business_id'];
//                 $user->save();
//             }
//         }

//         $tokenName = $data['device_name'] ?? 'authToken';

//         $token = $user
//             ->createToken($tokenName)
//             ->plainTextToken;

//         $user->load('businesses');

//         $otpRecord->delete();

//         return response()->json([
//             'status'     => true,
//             'message'    => 'Mobile number verified and registration successful.',
//             'token_type' => 'Bearer',
//             'token'      => $token,
//             'user'       => [
//                 'id'       => $user->id,
//                 'name'     => $user->name,
//                 'email'    => $user->email,
//                 'phone'    => $user->phone,
//                 'business' => $user->businesses,
//             ],
//         ], 201);
//     });
// }


    private function maskPhoneNumber(?string $phone): ?string
    {
        if (empty($phone) || strlen($phone) < 4) {
            return $phone;
        }

        return str_repeat('*', strlen($phone) - 4)
            . substr($phone, -4);
    }

    public function myPermissions1(Request $request)
{
$user = $request->user();

       

        return response()->json([
            'status' => true,
            'business' => $user->businesses,
        ], 200);
}

}
