<?php

namespace App\Http\Controllers;

use App\Exports\InvoicesExport;
use App\Models\Business;
use App\Models\BusinessCaAssignment;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class CaReportController extends Controller
{
    public function index(Request $request)
    {
        $businesses = $this->assignedBusinesses($request);

        abort_if($businesses->isEmpty(), 403, 'Aapko abhi kisi business ne CA access assign nahi kiya hai.');

        $businessId = $this->resolveSelectedBusinessId($request, $businesses);
        $business = $businesses->firstWhere('id', $businessId);
        $type = $this->normalizeType($request->get('type', 'tax'));

        [$fromDate, $toDate] = $this->resolveDateRange(
            $request,
            strtolower(trim((string) $request->get('date_range', 'last_month')))
        );

        $query = $this->buildQuery($request, $businessId, $type, $fromDate, $toDate);

        $summaryQuery = clone $query;
        $summary = [
            'invoice_count' => (clone $summaryQuery)->count(),
            'total' => (float) (clone $summaryQuery)->sum('total'),
            'received' => (float) (clone $summaryQuery)->sum('received_amount'),
            'balance' => (float) (clone $summaryQuery)->sum('balance'),
        ];

        $rows = $query
            ->orderByDesc('invoice_date')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('ca.reports', [
            'businesses' => $businesses,
            'business' => $business,
            'rows' => $rows,
            'summary' => $summary,
            'activeType' => $type,
            'filters' => [
                'business_id' => $businessId,
                'search' => (string) $request->get('search', ''),
                'date_range' => (string) $request->get('date_range', 'last_month'),
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'status' => (string) $request->get('status', ''),
                'file_format' => (string) $request->get('file_format', 'excel'),
            ],
        ]);
    }

    public function export(Request $request)
    {
        $businesses = $this->assignedBusinesses($request);
        abort_if($businesses->isEmpty(), 403, 'Aapko abhi kisi business ne CA access assign nahi kiya hai.');

        $businessId = $this->resolveSelectedBusinessId($request, $businesses);
        $business = $businesses->firstWhere('id', $businessId);
        abort_unless($business, 403);

        $type = $this->normalizeType($request->get('type', 'tax'));
        $dateRange = strtolower(trim((string) $request->get('date_range', 'last_month')));
        [$fromDate, $toDate] = $this->resolveDateRange($request, $dateRange);

        $rows = $this->buildQuery($request, $businessId, $type, $fromDate, $toDate)
            ->orderBy('invoice_date')
            ->orderBy('id')
            ->get();

        $filters = [
            'search' => trim((string) $request->get('search', '')),
            'status' => strtolower(trim((string) $request->get('status', ''))),
            'date_range' => $dateRange,
            'from_date' => $fromDate,
            'to_date' => $toDate,
        ];

        $reportTitle = $business->name . ' - ' . ucfirst($type) . ' Report';
        $safeBusiness = preg_replace('/[^A-Za-z0-9_-]+/', '-', $business->name) ?: 'business';
        $fileName = $safeBusiness . '-' . ($fromDate ?: 'all') . '-to-' . ($toDate ?: 'all');
        $fileFormat = strtolower(trim((string) $request->get('file_format', 'excel')));

        if ($fileFormat === 'pdf') {
            $pdf = Pdf::loadView('invoices.exports.pdf', [
                'rows' => $rows,
                'reportTitle' => $reportTitle,
                'type' => $type,
                'filters' => $filters,
                'business' => $business,
                'user' => $request->user(),
            ])->setPaper('a4', 'landscape');

            return $pdf->download($fileName . '.pdf');
        }

        return Excel::download(
            new InvoicesExport($rows, $reportTitle, $filters),
            $fileName . '.xlsx'
        );
    }

    private function assignedBusinesses(Request $request)
    {
        return Business::query()
            ->select('businesses.*')
            ->join('business_ca_assignments as bca', 'bca.business_id', '=', 'businesses.id')
            ->where('bca.user_id', $request->user()->id)
            ->where('bca.is_active', true)
            ->orderBy('businesses.name')
            ->get();
    }

    private function resolveSelectedBusinessId(Request $request, $businesses): int
    {
        $requested = $request->integer('business_id');

        if ($requested && $businesses->contains('id', $requested)) {
            return $requested;
        }

        return (int) $businesses->first()->id;
    }

    private function normalizeType($type): string
    {
        $type = strtolower(trim((string) $type));
        return in_array($type, ['tax', 'proforma', 'quotation'], true) ? $type : 'tax';
    }

    private function buildQuery(Request $request, int $businessId, string $type, ?string $fromDate, ?string $toDate): Builder
    {
        // CA business_user pivot ka member nahi hota, isliye model global scope ko
        // bypass karke immediately verified assigned business_id se re-restrict karna zaroori hai.
        $query = Invoice::withoutGlobalScope('business')
            ->with([
                'client' => fn ($q) => $q->withoutGlobalScope('business')
                    ->select('id', 'name', 'mobile', 'gstin', 'pan', 'address'),
            ])
            ->where('business_id', $businessId)
            ->where('invoice_type', $type);

        $search = trim((string) $request->get('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhere('total', 'like', "%{$search}%")
                    ->orWhere('balance', 'like', "%{$search}%")
                    ->orWhere('received_amount', 'like', "%{$search}%")
                    ->orWhereHas('client', function ($client) use ($search) {
                        $client->withoutGlobalScope('business')
                            ->where(function ($c) use ($search) {
                                $c->where('name', 'like', "%{$search}%")
                                    ->orWhere('mobile', 'like', "%{$search}%")
                                    ->orWhere('gstin', 'like', "%{$search}%")
                                    ->orWhere('pan', 'like', "%{$search}%")
                                    ->orWhere('address', 'like', "%{$search}%");
                            });
                    });
            });
        }

        if ($fromDate) {
            $query->whereDate('invoice_date', '>=', $fromDate);
        }
        if ($toDate) {
            $query->whereDate('invoice_date', '<=', $toDate);
        }

        $status = strtolower(trim((string) $request->get('status', '')));
        if ($status === 'paid') {
            $query->where('balance', '<=', 0);
        } elseif ($status === 'unpaid') {
            $query->where('received_amount', '<=', 0);
        } elseif ($status === 'partial') {
            $query->where('received_amount', '>', 0)->where('balance', '>', 0);
        }

        return $query;
    }

    private function resolveDateRange(Request $request, string $dateRange): array
    {
        $now = Carbon::now();

        switch ($dateRange) {
            case 'last_month':
                $period = $now->copy()->subMonthNoOverflow();
                return [$period->copy()->startOfMonth()->toDateString(), $period->copy()->endOfMonth()->toDateString()];

            case 'quarter':
                $previousQuarter = $now->copy()->subQuarter();
                return [$previousQuarter->copy()->startOfQuarter()->toDateString(), $previousQuarter->copy()->endOfQuarter()->toDateString()];

            case 'half_year':
                if ($now->month <= 6) {
                    return [$now->copy()->subYear()->startOfYear()->addMonths(6)->toDateString(), $now->copy()->subYear()->endOfYear()->toDateString()];
                }
                return [$now->copy()->startOfYear()->toDateString(), $now->copy()->startOfYear()->addMonths(6)->subDay()->toDateString()];

            case 'last_year':
                $year = $now->copy()->subYear();
                return [$year->copy()->startOfYear()->toDateString(), $year->copy()->endOfYear()->toDateString()];

            case 'custom':
                $from = $request->get('from_date');
                $to = $request->get('to_date');

                if ($from && $to && $from > $to) {
                    [$from, $to] = [$to, $from];
                }
                if (!$from && $to) {
                    $from = Carbon::parse($to)->startOfMonth()->toDateString();
                }
                if ($from && !$to) {
                    $to = $now->toDateString();
                }

                return [$from ?: null, $to ?: null];

            case 'all':
                return [null, null];

            default:
                $period = $now->copy()->subMonthNoOverflow();
                return [$period->copy()->startOfMonth()->toDateString(), $period->copy()->endOfMonth()->toDateString()];
        }
    }
}