<?php

namespace App\Http\Controllers;

use App\Exports\InvoicesExport;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class InvoiceReportController extends Controller
{
    public function reportsPage(Request $request)
    {
        $me = $request->user();

        $bid = $me->current_business_id ?? session('active_business_id');
        if (!$bid) {
            $bid = $me->businesses()->pluck('businesses.id')->first();
        }

        if (!$bid) {
            return back()->withErrors(['business' => 'Active business select/attach nahi hai.']);
        }

        $type = strtolower(trim((string) $request->get('type', 'tax')));
        if (!in_array($type, ['tax', 'proforma', 'quotation'], true)) {
            $type = 'tax';
        }

        // page open hone do
        return view('invoices.reports', [
            'activeType' => $type,
            'filters' => [
                'search'      => (string) $request->get('search', ''),
                'date_range'  => (string) $request->get('date_range', 'quarter'),
                'from_date'   => $request->get('from_date', ''),
                'to_date'     => $request->get('to_date', ''),
                'status'      => $request->get('status', ''),
                'file_format' => $request->get('file_format', 'excel'),
            ],
        ]);
    }

    // public function export(Request $request)
    // {
    //     $me = $request->user();

    //     $bid = $me->current_business_id ?? session('active_business_id');
    //     if (!$bid) {
    //         $bid = $me->businesses()->pluck('businesses.id')->first();
    //     }

    //     if (!$bid) {
    //         return back()->withErrors(['business' => 'Active business select/attach nahi hai.']);
    //     }

    //     $type = strtolower(trim((string) $request->get('type', 'tax')));
    //     if (!in_array($type, ['tax', 'proforma', 'quotation'], true)) {
    //         $type = 'tax';
    //     }

    //     $permByType = [
    //         'tax'       => 'show invoices',
    //         'proforma'  => 'show proformas',
    //         'quotation' => 'show quotations',
    //     ];

    //     $requiredPerm = $permByType[$type] ?? 'show invoices';

    //     // admin bypass
    //     if (!$me->hasRole(['super_admin', 'admin']) && !$me->can($requiredPerm)) {
    //         abort(403, "You don't have permission: {$requiredPerm}");
    //     }

    //     $search      = trim((string) $request->get('search', ''));
    //     $status      = $request->get('status');
    //     $dateRange   = strtolower(trim((string) $request->get('date_range', 'quarter')));
    //     $fileFormat  = strtolower(trim((string) $request->get('file_format', 'excel')));

    //     [$fromDate, $toDate] = $this->resolveDateRange($request, $dateRange);

    //     $q = Invoice::query()
    //         ->with(['client:id,name,mobile,gstin,pan,address'])
    //         ->where('business_id', $bid)
    //         ->where('invoice_type', $type);

    //     if ($search !== '') {
    //         $q->where(function ($w) use ($search) {
    //             $w->where('invoice_number', 'like', "%{$search}%")
    //                 ->orWhere('total', 'like', "%{$search}%")
    //                 ->orWhere('balance', 'like', "%{$search}%")
    //                 ->orWhere('received_amount', 'like', "%{$search}%")
    //                 ->orWhereHas('client', function ($c) use ($search) {
    //                     $c->where('name', 'like', "%{$search}%")
    //                         ->orWhere('mobile', 'like', "%{$search}%")
    //                         ->orWhere('gstin', 'like', "%{$search}%")
    //                         ->orWhere('pan', 'like', "%{$search}%")
    //                         ->orWhere('address', 'like', "%{$search}%");
    //                 });
    //         });
    //     }

    //     if ($fromDate) {
    //         $q->whereDate('invoice_date', '>=', $fromDate);
    //     }

    //     if ($toDate) {
    //         $q->whereDate('invoice_date', '<=', $toDate);
    //     }

    //     if (!empty($status)) {
    //         if ($status === 'paid') {
    //             $q->where('balance', '<=', 0);
    //         } elseif ($status === 'unpaid') {
    //             $q->where('received_amount', '<=', 0);
    //         } elseif ($status === 'partial') {
    //             $q->where('received_amount', '>', 0)->where('balance', '>', 0);
    //         }
    //     }

    //     $rows = $q->orderByDesc('invoice_date')
    //         ->orderByDesc('id')
    //         ->get();

    //     $reportTitle = ucfirst($type) . ' Report';

    //     if ($fileFormat === 'pdf') {
    //         $pdf = Pdf::loadView('invoices.exports.pdf', [
    //             'rows'        => $rows,
    //             'reportTitle' => $reportTitle,
    //             'type'        => $type,
    //             'filters'     => [
    //                 'search'     => $search,
    //                 'status'     => $status,
    //                 'date_range' => $dateRange,
    //                 'from_date'  => $fromDate,
    //                 'to_date'    => $toDate,
    //             ],
    //             'business'    => method_exists($me, 'businesses')
    //                 ? $me->businesses()->where('businesses.id', $bid)->first()
    //                 : null,
    //             'user'        => $me,
    //         ])->setPaper('a4', 'landscape');

    //         return $pdf->download(
    //             'invoice-report-' . $type . '-' . now()->format('Y-m-d_H-i-s') . '.pdf'
    //         );
    //     }

    //     return Excel::download(
    //         new InvoicesExport($rows, $reportTitle, [
    //             'search'     => $search,
    //             'status'     => $status,
    //             'date_range' => $dateRange,
    //             'from_date'  => $fromDate,
    //             'to_date'    => $toDate,
    //         ]),
    //         'invoice-report-' . $type . '-' . now()->format('Y-m-d_H-i-s') . '.xlsx'
    //     );
    // }


    public function export(Request $request)
{
    $me = $request->user();

    $bid = $me->current_business_id ?? session('active_business_id');
    if (!$bid) {
        $bid = $me->businesses()->pluck('businesses.id')->first();
    }

    if (!$bid) {
        return back()->withErrors(['business' => 'Active business select/attach nahi hai.']);
    }

    $type = strtolower(trim((string) $request->get('type', 'tax')));
    if (!in_array($type, ['tax', 'proforma', 'quotation'], true)) {
        $type = 'tax';
    }

    $search      = trim((string) $request->get('search', ''));
    $status      = $request->get('status');
    $dateRange   = strtolower(trim((string) $request->get('date_range', 'quarter')));
    $fileFormat  = strtolower(trim((string) $request->get('file_format', 'excel')));

    [$fromDate, $toDate] = $this->resolveDateRange($request, $dateRange);

    $q = Invoice::query()
        ->with(['client:id,name,mobile,gstin,pan,address'])
        ->where('business_id', $bid)
        ->where('invoice_type', $type);

    if ($search !== '') {
        $q->where(function ($w) use ($search) {
            $w->where('invoice_number', 'like', "%{$search}%")
                ->orWhere('total', 'like', "%{$search}%")
                ->orWhere('balance', 'like', "%{$search}%")
                ->orWhere('received_amount', 'like', "%{$search}%")
                ->orWhereHas('client', function ($c) use ($search) {
                    $c->where('name', 'like', "%{$search}%")
                        ->orWhere('mobile', 'like', "%{$search}%")
                        ->orWhere('gstin', 'like', "%{$search}%")
                        ->orWhere('pan', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%");
                });
        });
    }

    if ($fromDate) {
        $q->whereDate('invoice_date', '>=', $fromDate);
    }

    if ($toDate) {
        $q->whereDate('invoice_date', '<=', $toDate);
    }

    if (!empty($status)) {
        if ($status === 'paid') {
            $q->where('balance', '<=', 0);
        } elseif ($status === 'unpaid') {
            $q->where('received_amount', '<=', 0);
        } elseif ($status === 'partial') {
            $q->where('received_amount', '>', 0)->where('balance', '>', 0);
        }
    }

    $rows = $q->orderByDesc('invoice_date')
        ->orderByDesc('id')
        ->get();

    $reportTitle = ucfirst($type) . ' Report';

    if ($fileFormat === 'pdf') {
        $pdf = Pdf::loadView('invoices.exports.pdf', [
            'rows'        => $rows,
            'reportTitle' => $reportTitle,
            'type'        => $type,
            'filters'     => [
                'search'     => $search,
                'status'     => $status,
                'date_range' => $dateRange,
                'from_date'  => $fromDate,
                'to_date'    => $toDate,
            ],
            'business'    => method_exists($me, 'businesses')
                ? $me->businesses()->where('businesses.id', $bid)->first()
                : null,
            'user'        => $me,
        ])->setPaper('a4', 'landscape');

        return $pdf->download(
            'invoice-report-' . $type . '-' . now()->format('Y-m-d_H-i-s') . '.pdf'
        );
    }

    return Excel::download(
        new InvoicesExport($rows, $reportTitle, [
            'search'     => $search,
            'status'     => $status,
            'date_range' => $dateRange,
            'from_date'  => $fromDate,
            'to_date'    => $toDate,
        ]),
        'invoice-report-' . $type . '-' . now()->format('Y-m-d_H-i-s') . '.xlsx'
    );
}
    private function resolveDateRange(Request $request, string $dateRange): array
    {
        $today = now()->endOfDay();

        switch ($dateRange) {
            case 'last_year':
                $fromDate = now()->subYear()->startOfDay()->toDateString();
                $toDate   = $today->toDateString();
                break;

            case 'half_year':
                $fromDate = now()->subMonths(6)->startOfDay()->toDateString();
                $toDate   = $today->toDateString();
                break;

            case 'quarter':
                $fromDate = now()->subMonths(3)->startOfDay()->toDateString();
                $toDate   = $today->toDateString();
                break;

            case 'custom':
                $fromDate = $request->get('from_date');
                $toDate   = $request->get('to_date');

                if (empty($fromDate) && !empty($toDate)) {
                    $fromDate = Carbon::parse($toDate)->startOfMonth()->toDateString();
                }

                if (!empty($fromDate) && empty($toDate)) {
                    $toDate = now()->toDateString();
                }

                if (!empty($fromDate) && !empty($toDate) && $fromDate > $toDate) {
                    [$fromDate, $toDate] = [$toDate, $fromDate];
                }
                break;

            default:
                $fromDate = now()->subMonths(3)->startOfDay()->toDateString();
                $toDate   = $today->toDateString();
                break;
        }

        return [$fromDate, $toDate];
    }
}