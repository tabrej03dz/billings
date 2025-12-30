<?php

namespace App\Http\Controllers;

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
        // Priority: URL param -> user current_business_id -> session -> first business
        if ($business) return (int) $business;

        $bid = $request->user()->current_business_id ?? session('active_business_id');
        if ($bid) return (int) $bid;

        $first = $request->user()->businesses()->pluck('businesses.id')->first();
        abort_if(!$first, 403, 'No business attached.');
        return (int) $first;
    }

//    public function index(Request $request, $business = null)
//    {
//        $bid = $this->resolveBusinessId($request, $business);
//
//        $businessRow = Business::select('id','name','slug')->findOrFail($bid);
//
//        $bankAccounts = BankAccount::where('business_id', $bid)
//            ->orderByDesc('is_default')
//            ->orderByDesc('is_active')
//            ->latest('id')
//            ->paginate(15);
//
//        return view('bank_accounts.index', compact('bankAccounts', 'businessRow'));
//    }

    public function index(Request $request, $business = null)
    {
        $bid = $this->resolveBusinessId($request, $business);

        $businessRow = Business::select('id','name','slug')->findOrFail($bid);

        // ✅ Bank accounts list
        $bankAccounts = BankAccount::where('business_id', $bid)
            ->orderByDesc('is_default')
            ->orderByDesc('is_active')
            ->latest('id')
            ->paginate(15);

        // ✅ Total Bank Balance (bank_accounts table ka sum)
        $totalBankBalance = (float) BankAccount::where('business_id', $bid)->sum('balance');

        // ✅ Cash Balance (invoice_payments table se cash_amount ka sum)
        // NOTE: abhi cash OUT/expense nahi hai, isliye yeh "cash received" ko cash balance maan raha hai
        $cashBalance = (float) InvoicePayment::where('business_id', $bid)->sum('cash_amount');

        return view('bank_accounts.index', compact(
            'bankAccounts',
            'businessRow',
            'totalBankBalance',
            'cashBalance'
        ));
    }
    public function create(Request $request, $business = null)
    {
        $bid = $this->resolveBusinessId($request, $business);
        $businessRow = Business::select('id','name','slug')->findOrFail($bid);

        $bankAccount = new BankAccount();
        return view('bank_accounts.create', compact('bankAccount', 'businessRow'));
    }

    public function store(Request $request, $business = null)
    {
        $bid = $this->resolveBusinessId($request, $business);

        $data = $request->validate([
            'label'          => ['nullable','string','max:255'],
            'account_holder' => ['nullable','string','max:255'],
            'account_no'     => [
                'nullable','string','max:255',
                Rule::unique('bank_accounts','account_no')->where(fn($q)=>$q->where('business_id',$bid)),
            ],
            'ifsc'           => ['nullable','string','max:20'],
            'bank_name'      => ['nullable','string','max:120'],
            'branch'         => ['nullable','string','max:120'],
            'upi_id'         => [
                'nullable','string','max:120',
                Rule::unique('bank_accounts','upi_id')->where(fn($q)=>$q->where('business_id',$bid)),
            ],
            'notes'          => ['nullable','string'],
            'is_active'      => ['nullable'],
            'is_default'     => ['nullable'],
        ]);

        $data['business_id'] = $bid;
        $data['is_active']   = $request->boolean('is_active', true);
        $data['is_default']  = $request->boolean('is_default', false);

        DB::transaction(function () use ($bid, $data) {
            // If creating as default, reset other defaults
            if (!empty($data['is_default'])) {
                BankAccount::where('business_id', $bid)->update(['is_default' => false]);
            }
            BankAccount::create($data);
        });

        return redirect()->route('bank-accounts.index', $bid)->with('success', 'Bank account added successfully.');
    }

    public function edit(Request $request, BankAccount $bankAccount)
    {
        $bid = (int) $bankAccount->business_id;
        $businessRow = Business::select('id','name','slug')->findOrFail($bid);

        return view('bank_accounts.edit', compact('bankAccount', 'businessRow'));
    }

    public function update(Request $request, BankAccount $bankAccount)
    {
        $bid = (int) $bankAccount->business_id;

        $data = $request->validate([
            'label'          => ['nullable','string','max:255'],
            'account_holder' => ['nullable','string','max:255'],
            'account_no'     => [
                'nullable','string','max:255',
                Rule::unique('bank_accounts','account_no')
                    ->where(fn($q)=>$q->where('business_id',$bid))
                    ->ignore($bankAccount->id),
            ],
            'ifsc'           => ['nullable','string','max:20'],
            'bank_name'      => ['nullable','string','max:120'],
            'branch'         => ['nullable','string','max:120'],
            'upi_id'         => [
                'nullable','string','max:120',
                Rule::unique('bank_accounts','upi_id')
                    ->where(fn($q)=>$q->where('business_id',$bid))
                    ->ignore($bankAccount->id),
            ],
            'notes'          => ['nullable','string'],
            'is_active'      => ['nullable'],
            'is_default'     => ['nullable'],
        ]);

        $data['is_active']  = $request->boolean('is_active', true);
        $data['is_default'] = $request->boolean('is_default', false);

        DB::transaction(function () use ($bid, $bankAccount, $data) {
            if (!empty($data['is_default'])) {
                BankAccount::where('business_id', $bid)->update(['is_default' => false]);
            }
            $bankAccount->update($data);
        });

        return redirect()->route('bank-accounts.index', $bid)->with('success', 'Bank account updated successfully.');
    }

    public function destroy(Request $request, BankAccount $bankAccount)
    {
        $bid = (int) $bankAccount->business_id;

        $bankAccount->delete();

        return redirect()->route('bank-accounts.index', $bid)->with('success', 'Bank account deleted successfully.');
    }

    public function makeDefault(Request $request, BankAccount $bankAccount)
    {
        $bid = (int) $bankAccount->business_id;

        DB::transaction(function () use ($bid, $bankAccount) {
            BankAccount::where('business_id', $bid)->update(['is_default' => false]);
            $bankAccount->update(['is_default' => true, 'is_active' => true]);
        });

        return redirect()->route('bank-accounts.index', $bid)->with('success', 'Default bank set successfully.');
    }
}
