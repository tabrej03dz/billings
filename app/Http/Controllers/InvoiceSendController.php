<?php

namespace App\Http\Controllers;

use App\Models\InvoiceSend;
use Carbon\Carbon;
use Illuminate\Http\Request;

class InvoiceSendController extends Controller
{
    public function index(Request $request)
    {
        $today      = Carbon::today();
        $monthStart = Carbon::now()->startOfMonth();

        // filters (optional)
        $channel = $request->get('channel'); // whatsapp/email
        $from    = $request->get('from') ?: $monthStart->toDateString();
        $to      = $request->get('to')   ?: $today->toDateString();

        $authUser = $request->user();

        $query = InvoiceSend::with('user')
            ->whereBetween('sent_at', [$from.' 00:00:00', $to.' 23:59:59']);

        // ✅ Role-based visibility
        // Super admin => all records, others => only own
        if (! $authUser->hasRole('super admin')) {
            $query->where('user_id', $authUser->id);
        }

        if ($channel) {
            $query->where('channel', $channel);
        }

        // per-user aggregated
        $perUser = (clone $query)
            ->selectRaw('user_id, count(*) as total, sum(case when status = "success" then 1 else 0 end) as success_count')
            ->groupBy('user_id')
            ->with('user')
            ->get();

        // total rows list (latest)
        $latestSends = (clone $query)
            ->latest('sent_at')
            ->limit(50)
            ->with(['user', 'invoice'])
            ->get();

        return view('reports.invoice_sends', compact(
            'perUser',
            'latestSends',
            'from',
            'to',
            'channel'
        ));
    }

}
