<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\Business;
use App\Models\InvoicePayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class BankAccountController extends Controller
{
    protected function resolveBusinessId(Request $request, $business = null): int
    {
        if (!empty($business)) return (int) $business;

        $bid = (int) ($request->header('X-Business-Id')
            ?: ($request->user()->current_business_id ?? session('active_business_id')));

        if ($bid > 0) return $bid;

        $first = $request->user()
            ->businesses()
            ->pluck('businesses.id')
            ->first();

        abort_if(!$first, 403, 'No business attached.');
        return (int) $first;
    }

    /**
     * Security: ensure user has access to business (unless super admin)
     */
    protected function ensureBusinessAccess(Request $request, int $bid): void
    {
        $user = $request->user();

        if ($user && method_exists($user, 'hasRole') && $user->hasRole('super admin')) {
            return;
        }

        $has = $user->businesses()->where('businesses.id', $bid)->exists();
        abort_if(!$has, 403, 'You do not have access to this business.');
    }

    // ✅ GET /api/bank-accounts  (or /api/businesses/{business}/bank-accounts)
    public function index(Request $request, $business = null)
    {
        $bid = $this->resolveBusinessId($request, $business);
        $this->ensureBusinessAccess($request, $bid);

        $businessRow = Business::select('id', 'name', 'slug')->findOrFail($bid);

        $perPage = (int) $request->query('per_page', 15);
        if ($perPage < 1 || $perPage > 200) $perPage = 15;

        $q = BankAccount::query()->where('business_id', $bid);

        // optional filters
        if ($request->filled('is_active')) {
            $q->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN));
        }
        if ($request->filled('is_default')) {
            $q->where('is_default', filter_var($request->is_default, FILTER_VALIDATE_BOOLEAN));
        }
        if ($request->filled('search')) {
            $s = trim($request->search);
            $q->where(function ($qq) use ($s) {
                $qq->where('label', 'like', "%{$s}%")
                    ->orWhere('bank_name', 'like', "%{$s}%")
                    ->orWhere('account_holder', 'like', "%{$s}%")
                    ->orWhere('account_no', 'like', "%{$s}%")
                    ->orWhere('upi_id', 'like', "%{$s}%");
            });
        }

        $bankAccounts = $q->orderByDesc('is_default')
            ->orderByDesc('is_active')
            ->latest('id')
            ->paginate($perPage);

        $totalBankBalance = (float) BankAccount::where('business_id', $bid)->sum('balance');
        $cashBalance      = (float) InvoicePayment::where('business_id', $bid)->sum('cash_amount');

        return response()->json([
            'success' => true,
            'business' => $businessRow,
            'meta' => [
                'total_bank_balance' => $totalBankBalance,
                'cash_balance'       => $cashBalance,
            ],
            'data' => $bankAccounts,
        ]);
    }

    // ✅ GET /api/bank-accounts/{bankAccount}
    public function show(Request $request, BankAccount $bankAccount)
    {
        $bid = (int) $bankAccount->business_id;
        $this->ensureBusinessAccess($request, $bid);

        return response()->json([
            'success' => true,
            'data' => $bankAccount,
        ]);
    }

    // ✅ POST /api/bank-accounts
    public function store(Request $request, $business = null)
    {
        $bid = $this->resolveBusinessId($request, $business);
        $this->ensureBusinessAccess($request, $bid);

        $data = $request->validate([
            'label'          => ['nullable', 'string', 'max:255'],
            'account_holder' => ['nullable', 'string', 'max:255'],
            'account_no'     => [
                'nullable', 'string', 'max:255',
                Rule::unique('bank_accounts', 'account_no')
                    ->where(fn ($q) => $q->where('business_id', $bid)),
            ],
            'ifsc'           => ['nullable', 'string', 'max:20'],
            'bank_name'      => ['nullable', 'string', 'max:120'],
            'branch'         => ['nullable', 'string', 'max:120'],
            'upi_id'         => [
                'nullable', 'string', 'max:120',
                Rule::unique('bank_accounts', 'upi_id')
                    ->where(fn ($q) => $q->where('business_id', $bid)),
            ],
            'notes'          => ['nullable', 'string'],
            'is_active'      => ['nullable'],
            'is_default'     => ['nullable'],
            'balance'        => ['nullable', 'numeric'], // if you have this column
        ]);

        $data['business_id'] = $bid;
        $data['is_active']   = $request->boolean('is_active', true);
        $data['is_default']  = $request->boolean('is_default', false);

        $bankAccount = null;

        DB::transaction(function () use ($bid, $data, &$bankAccount) {
            if (!empty($data['is_default'])) {
                BankAccount::where('business_id', $bid)->update(['is_default' => false]);
            }
            $bankAccount = BankAccount::create($data);
        });

        return response()->json([
            'success' => true,
            'message' => 'Bank account added successfully.',
            'data' => $bankAccount,
        ], 201);
    }

    // ✅ PUT/PATCH /api/bank-accounts/{bankAccount}
    public function update(Request $request, BankAccount $bankAccount)
    {
        $bid = (int) $bankAccount->business_id;
        $this->ensureBusinessAccess($request, $bid);

        $data = $request->validate([
            'label'          => ['nullable', 'string', 'max:255'],
            'account_holder' => ['nullable', 'string', 'max:255'],
            'account_no'     => [
                'nullable', 'string', 'max:255',
                Rule::unique('bank_accounts', 'account_no')
                    ->where(fn ($q) => $q->where('business_id', $bid))
                    ->ignore($bankAccount->id),
            ],
            'ifsc'           => ['nullable', 'string', 'max:20'],
            'bank_name'      => ['nullable', 'string', 'max:120'],
            'branch'         => ['nullable', 'string', 'max:120'],
            'upi_id'         => [
                'nullable', 'string', 'max:120',
                Rule::unique('bank_accounts', 'upi_id')
                    ->where(fn ($q) => $q->where('business_id', $bid))
                    ->ignore($bankAccount->id),
            ],
            'notes'          => ['nullable', 'string'],
            'is_active'      => ['nullable'],
            'is_default'     => ['nullable'],
            'balance'        => ['nullable', 'numeric'], // if you have this column
        ]);

        $data['is_active']  = $request->boolean('is_active', true);
        $data['is_default'] = $request->boolean('is_default', false);

        DB::transaction(function () use ($bid, $bankAccount, $data) {
            if (!empty($data['is_default'])) {
                BankAccount::where('business_id', $bid)->update(['is_default' => false]);
            }
            $bankAccount->update($data);
        });

        return response()->json([
            'success' => true,
            'message' => 'Bank account updated successfully.',
            'data' => $bankAccount->fresh(),
        ]);
    }

    // ✅ DELETE /api/bank-accounts/{bankAccount}
    public function destroy(Request $request, BankAccount $bankAccount)
    {
        $bid = (int) $bankAccount->business_id;
        $this->ensureBusinessAccess($request, $bid);

        $bankAccount->delete();

        return response()->json([
            'success' => true,
            'message' => 'Bank account deleted successfully.',
        ]);
    }

    // ✅ POST /api/bank-accounts/{bankAccount}/make-default
    public function makeDefault(Request $request, BankAccount $bankAccount)
    {
        $bid = (int) $bankAccount->business_id;
        $this->ensureBusinessAccess($request, $bid);

        DB::transaction(function () use ($bid, $bankAccount) {
            BankAccount::where('business_id', $bid)->update(['is_default' => false]);
            $bankAccount->update(['is_default' => true, 'is_active' => true]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Default bank set successfully.',
            'data' => $bankAccount->fresh(),
        ]);
    }
}
