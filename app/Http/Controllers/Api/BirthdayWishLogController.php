<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BirthdayWishLog;
use Illuminate\Http\Request;

class BirthdayWishLogController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $q = BirthdayWishLog::query()
            ->latest('id');

        // 🔐 ROLE BASED ACCESS
        if (!$user->hasRole('super admin')) {
            $q->whereHas('record', function ($qr) use ($user) {
                $qr->where('user_id', $user->id);
            });
        }

        // ================= FILTERS =================

        if ($request->filled('business_id')) {
            $q->where('business_id', $request->business_id);
        }

        if ($request->filled('birthday_record_id')) {
            $q->where('birthday_record_id', $request->birthday_record_id);
        }

        if ($request->filled('phone')) {
            $q->where('phone', 'like', '%' . trim($request->phone) . '%');
        }

        if ($request->filled('status')) {
            $q->where('status', $request->status); // pending/success/failed
        }

        // 📅 wish_date filter
        $from = $request->filled('from') ? Carbon::parse($request->from)->toDateString() : null;
        $to   = $request->filled('to')   ? Carbon::parse($request->to)->toDateString()   : null;

        if ($from && $to) {
            $q->whereBetween('wish_date', [$from, $to]);
        } elseif ($from) {
            $q->whereDate('wish_date', '>=', $from);
        } elseif ($to) {
            $q->whereDate('wish_date', '<=', $to);
        }

        if ($request->filled('wish_year')) {
            $q->where('wish_year', (int) $request->wish_year);
        }

        // pagination
        $perPage = max(1, min(200, (int) $request->get('per_page', 50)));
        $logs = $q->paginate($perPage)->withQueryString();

        return response()->json([
            'success' => true,
            'message' => 'Birthday wish logs fetched successfully.',
            'data'    => $logs->items(),
            'meta'    => [
                'current_page' => $logs->currentPage(),
                'per_page'     => $logs->perPage(),
                'total'        => $logs->total(),
                'last_page'    => $logs->lastPage(),
                'from'         => $logs->firstItem(),
                'to'           => $logs->lastItem(),
            ],
            'links' => [
                'first' => $logs->url(1),
                'last'  => $logs->url($logs->lastPage()),
                'prev'  => $logs->previousPageUrl(),
                'next'  => $logs->nextPageUrl(),
            ],
        ]);
    }

    /**
     * GET /api/birthday-wish-logs/{id}
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();

        $q = BirthdayWishLog::query();

        // 🔐 ROLE BASED ACCESS
        if (!$user->hasRole('super admin')) {
            $q->whereHas('record', function ($qr) use ($user) {
                $qr->where('user_id', $user->id);
            });
        }

        $log = $q->findOrFail($id);

        // Optional: relations
        // $log->load(['record', 'business']);

        return response()->json([
            'success' => true,
            'message' => 'Birthday wish log fetched successfully.',
            'data'    => $log,
        ]);
    }
}
