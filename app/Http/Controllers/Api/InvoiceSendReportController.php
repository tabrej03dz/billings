<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InvoiceSend;
use App\Services\InvoiceSendService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class InvoiceSendReportController extends Controller
{
    public function index(Request $request, InvoiceSendService $sender)
    {
        $today      = Carbon::today();
        $monthStart = Carbon::now()->startOfMonth();

        $channel = $request->query('channel'); // whatsapp/email
        $from    = $request->query('from') ?: $monthStart->toDateString();
        $to      = $request->query('to')   ?: $today->toDateString();

        $autoSend = filter_var($request->query('auto_send', false), FILTER_VALIDATE_BOOLEAN);
        $perPage  = (int) $request->query('per_page', 30);
        if ($perPage < 1 || $perPage > 200) $perPage = 30;

        $authUser = $request->user();

        // ✅ 1) OPTIONAL: Send all "uploaded" PDFs for this user
        $sendResult = null;
        if ($autoSend) {
            $sendResult = $sender->sendUploadedForUser($authUser);
        }

        // ✅ 2) REPORT QUERY
        $query = InvoiceSend::query()->with('user');

        // ✅ Normal user => only own data + date filter on sent_at
        if (! $authUser->hasRole('super admin')) {
            $query->where('user_id', $authUser->id)
                ->whereBetween('sent_at', [$from.' 00:00:00', $to.' 23:59:59']);
        }
        // ✅ Super admin => full data (no date filter), web jaisa

        // ✅ channel filter (both admin and user)
        if ($channel) {
            $query->where('channel', $channel);
        }

        // per-user aggregated
        $perUser = (clone $query)
            ->selectRaw('user_id,
            count(*) as total,
            sum(case when status = "success" then 1 else 0 end) as success_count,
            sum(case when status = "failed" then 1 else 0 end) as failed_count
        ')
            ->groupBy('user_id')
            ->with('user')
            ->get();

        // latest sends
        $latestSends = (clone $query)
            ->latest('sent_at') // null sent_at last
            ->with(['user', 'invoice'])
            ->paginate($perPage)
            ->withQueryString();

        return response()->json([
            'ok' => true,
            'filters' => [
                'from' => $from,
                'to' => $to,
                'channel' => $channel,
                'per_page' => $perPage,
                'auto_send' => $autoSend,
                'is_super_admin' => $authUser->hasRole('super admin'),
            ],
            'send_result' => $sendResult,
            'per_user' => $perUser,
            'latest_sends' => $latestSends,
        ]);
    }

}
