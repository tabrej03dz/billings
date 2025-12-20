<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BankAccountController extends Controller
{
    private function bid(Request $request): int
    {
        $bid = $request->user()->current_business_id ?? session('active_business_id');
        abort_unless($bid, 403, 'Active business not selected.');
        return (int) $bid;
    }

    public function index(Request $request)
    {
        $bid = $this->bid($request);

        $banks = BankAccount::where('business_id', $bid)
            ->orderByDesc('is_default')
            ->orderByDesc('is_active')
            ->orderByDesc('id')
            ->get();

        return view('bank_accounts.index', compact('banks'));
    }

    public function create(Request $request)
    {
        $bid = $this->bid($request);
        return view('bank_accounts.create');
    }

    public function store(Request $request)
    {
        $bid = $this->bid($request);

        $data = $request->validate([
            'label'          => ['nullable','string','max:120'],
            'account_holder' => ['nullable','string','max:120'],
            'account_no'     => ['nullable','string','max:50'],
            'ifsc'           => ['nullable','string','max:20'],
            'bank_name'      => ['nullable','string','max:120'],
            'branch'         => ['nullable','string','max:120'],
            'upi_id'         => ['nullable','string','max:120'],
            'notes'          => ['nullable','string','max:2000'],
            'is_default'     => ['nullable'],
            'is_active'      => ['nullable'],
        ]);

        // ✅ At least one of UPI or Account No should exist
        if (empty($data['upi_id']) && empty($data['account_no'])) {
            return back()->withErrors(['upi_id' => 'UPI ID या Account Number में से कम से कम एक भरें.'])
                ->withInput();
        }

        $data['business_id'] = $bid;
        $data['is_default'] = !empty($data['is_default']);
        $data['is_active']  = array_key_exists('is_active', $data) ? !empty($data['is_active']) : true;

        DB::transaction(function () use ($bid, &$data) {
            // ✅ if default set, remove default from others
            if ($data['is_default']) {
                BankAccount::where('business_id', $bid)->update(['is_default' => 0]);
            }
            BankAccount::create($data);
        });

        return redirect()->back()->with('success', 'Bank account added.');
    }

    public function edit(Request $request, BankAccount $bankAccount)
    {
        $bid = $this->bid($request);
        abort_unless((int)$bankAccount->business_id === $bid, 403);

        return view('bank_accounts.edit', compact('bankAccount'));
    }

    public function update(Request $request, BankAccount $bankAccount)
    {
        $bid = $this->bid($request);
        abort_unless((int)$bankAccount->business_id === $bid, 403);

        $data = $request->validate([
            'label'          => ['nullable','string','max:120'],
            'account_holder' => ['nullable','string','max:120'],
            'account_no'     => ['nullable','string','max:50'],
            'ifsc'           => ['nullable','string','max:20'],
            'bank_name'      => ['nullable','string','max:120'],
            'branch'         => ['nullable','string','max:120'],
            'upi_id'         => ['nullable','string','max:120'],
            'notes'          => ['nullable','string','max:2000'],
            'is_default'     => ['nullable'],
            'is_active'      => ['nullable'],
        ]);

        if (empty($data['upi_id']) && empty($data['account_no'])) {
            return back()->withErrors(['upi_id' => 'UPI ID या Account Number में से कम से कम एक भरें.'])
                ->withInput();
        }

        $data['is_default'] = !empty($data['is_default']);
        $data['is_active']  = array_key_exists('is_active', $data) ? !empty($data['is_active']) : true;

        DB::transaction(function () use ($bid, $bankAccount, $data) {
            if ($data['is_default']) {
                BankAccount::where('business_id', $bid)->update(['is_default' => 0]);
            }
            $bankAccount->update($data);
        });

        return redirect()->back()->with('success', 'Bank account updated.');
    }

    public function destroy(Request $request, BankAccount $bankAccount)
    {
        $bid = $this->bid($request);
        abort_unless((int)$bankAccount->business_id === $bid, 403);

        $bankAccount->delete();

        return redirect()->back()->with('success', 'Bank account removed.');
    }

    // ✅ Default set (quick action button)
    public function makeDefault(Request $request, BankAccount $bankAccount)
    {
        $bid = $this->bid($request);
        abort_unless((int)$bankAccount->business_id === $bid, 403);

        DB::transaction(function () use ($bid, $bankAccount) {
            BankAccount::where('business_id', $bid)->update(['is_default' => 0]);
            $bankAccount->update(['is_default' => 1, 'is_active' => 1]);
        });

        return redirect()->back()->with('success', 'Default bank updated.');
    }

    // ✅ JSON list for invoice dropdown
    public function listJson(Request $request)
    {
        $bid = $this->bid($request);

        $banks = BankAccount::where('business_id', $bid)
            ->where('is_active', 1)
            ->orderByDesc('is_default')
            ->orderByDesc('id')
            ->get(['id','label','account_holder','account_no','ifsc','upi_id','bank_name']);

        return response()->json([
            'banks' => $banks->map(function($b){
                $title = $b->label ?: ($b->bank_name ?: ($b->account_holder ?: 'Bank'));
                $line  = $b->upi_id ?: $b->account_no;
                return [
                    'id' => $b->id,
                    'title' => $title,
                    'line' => $line,
                    'upi_id' => $b->upi_id,
                    'account_no' => $b->account_no,
                    'ifsc' => $b->ifsc,
                    'account_holder' => $b->account_holder,
                ];
            })->values()
        ]);
    }
}
