<?php

namespace App\Http\Controllers;

use App\Models\ApiKey;
use App\Models\InvoiceSend;
use App\Services\InvoiceSendService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class InvoiceSendController extends Controller
{
    public function index(Request $request)
    {
        $today      = Carbon::today();
        $monthStart = Carbon::now()->startOfMonth();

        $channel = $request->get('channel');
        $from    = $request->get('from') ?: $monthStart->toDateString();
        $to      = $request->get('to')   ?: $today->toDateString();

        $authUser = $request->user();

        $query = InvoiceSend::with('user');

        if (! $authUser->hasRole('super admin')) {
            $query->where('user_id', $authUser->id);
            $query->whereBetween('sent_at', [$from.' 00:00:00', $to.' 23:59:59']);
        }

        if ($channel) {
            $query->where('channel', $channel);
        }

        $perUser = (clone $query)
            ->selectRaw('user_id, count(*) as total, sum(case when status = "success" then 1 else 0 end) as success_count')
            ->groupBy('user_id')
            ->with('user')
            ->get();

        $latestSends = (clone $query)
            ->latest('sent_at')
            ->with(['user', 'invoice'])
            ->paginate(30)
            ->withQueryString();

        return view('reports.invoice_sends', compact('perUser','latestSends','from','to','channel'));
    }

    public function destroy(InvoiceSend $invoice)
    {
        try {
            // agar file_url public storage ka hai
            if ($invoice->file_url) {
                $path = str_replace(Storage::disk('public')->url(''), '', $invoice->file_url);

                if (Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            }

            $invoice->delete();

            return back()->with('success', 'Invoice deleted successfully.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Delete failed: ' . $e->getMessage());
        }
    }



}
