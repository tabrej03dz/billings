<?php

// namespace App\Http\Controllers;

// use App\Exports\InvoicesExport;
// use App\Models\Invoice;
// use Barryvdh\DomPDF\Facade\Pdf;
// use Carbon\Carbon;
// use Illuminate\Http\Request;
// use Maatwebsite\Excel\Facades\Excel;

// class InvoiceReportController extends Controller
// {
//     public function reportsPage(Request $request)
//     {
//         $me = $request->user();

//         $bid = $me->current_business_id ?? session('active_business_id');
//         if (!$bid) {
//             $bid = $me->businesses()->pluck('businesses.id')->first();
//         }

//         if (!$bid) {
//             return back()->withErrors(['business' => 'Active business select/attach nahi hai.']);
//         }

//         $type = strtolower(trim((string) $request->get('type', 'tax')));
//         if (!in_array($type, ['tax', 'proforma', 'quotation'], true)) {
//             $type = 'tax';
//         }

//         // page open hone do
//         return view('invoices.reports', [
//             'activeType' => $type,
//             'filters' => [
//                 'search'      => (string) $request->get('search', ''),
//                 'date_range'  => (string) $request->get('date_range', 'last_month'),
//                 'from_date'   => $request->get('from_date', ''),
//                 'to_date'     => $request->get('to_date', ''),
//                 'status'      => $request->get('status', ''),
//                 'file_format' => $request->get('file_format', 'excel'),
//             ],
//         ]);
//     }

    

//     public function export(Request $request)
//     {
//         $me = $request->user();

//         $bid = $me->current_business_id ?? session('active_business_id');
//         if (!$bid) {
//             $bid = $me->businesses()->pluck('businesses.id')->first();
//         }

//         if (!$bid) {
//             return back()->withErrors(['business' => 'Active business select/attach nahi hai.']);
//         }

//         $type = strtolower(trim((string) $request->get('type', 'tax')));
//         if (!in_array($type, ['tax', 'proforma', 'quotation'], true)) {
//             $type = 'tax';
//         }

//         $search      = trim((string) $request->get('search', ''));
//         $status      = $request->get('status');
//         $dateRange   = strtolower(trim((string) $request->get('date_range', 'quarter')));
//         $fileFormat  = strtolower(trim((string) $request->get('file_format', 'excel')));

//         [$fromDate, $toDate] = $this->resolveDateRange($request, $dateRange);

//         $q = Invoice::query()
//             ->with(['client:id,name,mobile,gstin,pan,address'])
//             ->where('business_id', $bid)
//             ->where('invoice_type', $type);

//         if ($search !== '') {
//             $q->where(function ($w) use ($search) {
//                 $w->where('invoice_number', 'like', "%{$search}%")
//                     ->orWhere('total', 'like', "%{$search}%")
//                     ->orWhere('balance', 'like', "%{$search}%")
//                     ->orWhere('received_amount', 'like', "%{$search}%")
//                     ->orWhereHas('client', function ($c) use ($search) {
//                         $c->where('name', 'like', "%{$search}%")
//                             ->orWhere('mobile', 'like', "%{$search}%")
//                             ->orWhere('gstin', 'like', "%{$search}%")
//                             ->orWhere('pan', 'like', "%{$search}%")
//                             ->orWhere('address', 'like', "%{$search}%");
//                     });
//             });
//         }

//         if ($fromDate) {
//             $q->whereDate('invoice_date', '>=', $fromDate);
//         }

//         if ($toDate) {
//             $q->whereDate('invoice_date', '<=', $toDate);
//         }

//         if (!empty($status)) {
//             if ($status === 'paid') {
//                 $q->where('balance', '<=', 0);
//             } elseif ($status === 'unpaid') {
//                 $q->where('received_amount', '<=', 0);
//             } elseif ($status === 'partial') {
//                 $q->where('received_amount', '>', 0)->where('balance', '>', 0);
//             }
//         }

//         $rows = $q->orderBy('invoice_date', 'asc')
//             ->orderBy('id', 'asc')
//             ->get();

//         $reportTitle = ucfirst($type) . ' Report';

//         $fileName = $fromDate . '_to_' . $toDate;

//         if ($fileFormat === 'pdf') {
//             $pdf = Pdf::loadView('invoices.exports.pdf', [
//                 'rows'        => $rows,
//                 'reportTitle' => $reportTitle,
//                 'type'        => $type,
//                 'filters'     => [
//                     'search'     => $search,
//                     'status'     => $status,
//                     'date_range' => $dateRange,
//                     'from_date'  => $fromDate,
//                     'to_date'    => $toDate,
//                 ],
//                 'business'    => method_exists($me, 'businesses')
//                     ? $me->businesses()->where('businesses.id', $bid)->first()
//                     : null,
//                 'user'        => $me,
//             ])->setPaper('a4', 'landscape');

//             // return $pdf->download(
//             //     'invoice-report-' . $type . '-' . now()->format('Y-m-d_H-i-s') . '.pdf'
//             // );
//             return $pdf->download($fileName . '.pdf');
//         }

//         return Excel::download(
//             new InvoicesExport($rows, $reportTitle, [
//                 'search'     => $search,
//                 'status'     => $status,
//                 'date_range' => $dateRange,
//                 'from_date'  => $fromDate,
//                 'to_date'    => $toDate,
//             ]),
//             // 'invoice-report-' . $type . '-' . now()->format('Y-m-d_H-i-s') . '.xlsx'
//             $fileName . '.xlsx'
//         );
//     }

//     private function resolveDateRange(Request $request, string $dateRange): array
//     {
//         $today = now()->endOfDay();

//         switch ($dateRange) {
//             case 'last_month':
//                 $fromDate = now()->subMonthNoOverflow()->startOfDay()->toDateString();
//                 $toDate   = $today->toDateString();
//                 break;

//             case 'last_year':
//                 $fromDate = now()->subYear()->startOfDay()->toDateString();
//                 $toDate   = $today->toDateString();
//                 break;

//             case 'half_year':
//                 $fromDate = now()->subMonths(6)->startOfDay()->toDateString();
//                 $toDate   = $today->toDateString();
//                 break;

//             case 'quarter':
//                 $fromDate = now()->subMonths(3)->startOfDay()->toDateString();
//                 $toDate   = $today->toDateString();
//                 break;

//             case 'custom':
//                 $fromDate = $request->get('from_date');
//                 $toDate   = $request->get('to_date');

//                 if (empty($fromDate) && !empty($toDate)) {
//                     $fromDate = Carbon::parse($toDate)
//                         ->startOfMonth()
//                         ->toDateString();
//                 }

//                 if (!empty($fromDate) && empty($toDate)) {
//                     $toDate = now()->toDateString();
//                 }

//                 if (
//                     !empty($fromDate) &&
//                     !empty($toDate) &&
//                     $fromDate > $toDate
//                 ) {
//                     [$fromDate, $toDate] = [$toDate, $fromDate];
//                 }

//                 break;

//             default:
//                 $fromDate = now()->subMonths(3)->startOfDay()->toDateString();
//                 $toDate   = $today->toDateString();
//                 break;
//         }

//         return [$fromDate, $toDate];
//     }
// }



namespace App\Http\Controllers;

use App\Exports\InvoicesExport;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class InvoiceReportController extends Controller
{
    /**
     * Invoice Reports Page
     */
    public function reportsPage(Request $request)
    {
        $me = $request->user();

        /*
        |--------------------------------------------------------------------------
        | Resolve Active Business
        |--------------------------------------------------------------------------
        */

        $bid = $me->current_business_id ?? session('active_business_id');

        if (!$bid) {
            $bid = $me->businesses()
                ->pluck('businesses.id')
                ->first();
        }

        if (!$bid) {
            return back()->withErrors([
                'business' => 'Active business select/attach nahi hai.'
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Invoice Type
        |--------------------------------------------------------------------------
        */

        $type = strtolower(
            trim((string) $request->get('type', 'tax'))
        );

        if (!in_array($type, ['tax', 'proforma', 'quotation'], true)) {
            $type = 'tax';
        }

        /*
        |--------------------------------------------------------------------------
        | Open Reports Page
        |--------------------------------------------------------------------------
        */

        return view('invoices.reports', [
            'activeType' => $type,

            'filters' => [
                'search'      => (string) $request->get('search', ''),
                'date_range'  => (string) $request->get('date_range', 'last_month'),
                'from_date'   => $request->get('from_date', ''),
                'to_date'     => $request->get('to_date', ''),
                'status'      => $request->get('status', ''),
                'file_format' => $request->get('file_format', 'excel'),
            ],
        ]);
    }


    /**
     * Export Invoice Report
     */
    public function export(Request $request)
    {
        $me = $request->user();

        /*
        |--------------------------------------------------------------------------
        | Resolve Active Business
        |--------------------------------------------------------------------------
        */

        $bid = $me->current_business_id ?? session('active_business_id');

        if (!$bid) {
            $bid = $me->businesses()
                ->pluck('businesses.id')
                ->first();
        }

        if (!$bid) {
            return back()->withErrors([
                'business' => 'Active business select/attach nahi hai.'
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Invoice Type
        |--------------------------------------------------------------------------
        */

        $type = strtolower(
            trim((string) $request->get('type', 'tax'))
        );

        if (!in_array($type, ['tax', 'proforma', 'quotation'], true)) {
            $type = 'tax';
        }

        /*
        |--------------------------------------------------------------------------
        | Filters
        |--------------------------------------------------------------------------
        */

        $search = trim(
            (string) $request->get('search', '')
        );

        $status = strtolower(
            trim((string) $request->get('status', ''))
        );

        $dateRange = strtolower(
            trim((string) $request->get('date_range', 'last_month'))
        );

        $fileFormat = strtolower(
            trim((string) $request->get('file_format', 'excel'))
        );

        /*
        |--------------------------------------------------------------------------
        | Resolve Date Range
        |--------------------------------------------------------------------------
        */

        [$fromDate, $toDate] = $this->resolveDateRange(
            $request,
            $dateRange
        );

        /*
        |--------------------------------------------------------------------------
        | Invoice Query
        |--------------------------------------------------------------------------
        */

        $q = Invoice::query()
            ->with([
                'client:id,name,mobile,gstin,pan,address'
            ])
            ->where('business_id', $bid)
            ->where('invoice_type', $type);

        /*
        |--------------------------------------------------------------------------
        | Search Filter
        |--------------------------------------------------------------------------
        */

        if ($search !== '') {

            $q->where(function ($query) use ($search) {

                $query
                    ->where(
                        'invoice_number',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'total',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'balance',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'received_amount',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhereHas(
                        'client',
                        function ($clientQuery) use ($search) {

                            $clientQuery
                                ->where(
                                    'name',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'mobile',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'gstin',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'pan',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'address',
                                    'like',
                                    "%{$search}%"
                                );
                        }
                    );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Date Filter
        |--------------------------------------------------------------------------
        */

        if (!empty($fromDate)) {
            $q->whereDate(
                'invoice_date',
                '>=',
                $fromDate
            );
        }

        if (!empty($toDate)) {
            $q->whereDate(
                'invoice_date',
                '<=',
                $toDate
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Payment Status Filter
        |--------------------------------------------------------------------------
        */

        if ($status !== '') {

            switch ($status) {

                case 'paid':

                    $q->where(
                        'balance',
                        '<=',
                        0
                    );

                    break;


                case 'unpaid':

                    $q->where(
                        'received_amount',
                        '<=',
                        0
                    );

                    break;


                case 'partial':

                    $q->where(
                        'received_amount',
                        '>',
                        0
                    )
                    ->where(
                        'balance',
                        '>',
                        0
                    );

                    break;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Fetch Records
        |--------------------------------------------------------------------------
        */

        $rows = $q
            ->orderBy('invoice_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Report Title
        |--------------------------------------------------------------------------
        */

        $reportTitle = ucfirst($type) . ' Report';

        /*
        |--------------------------------------------------------------------------
        | File Name
        |--------------------------------------------------------------------------
        */

        $fileName = $fromDate . '_to_' . $toDate;

        /*
        |--------------------------------------------------------------------------
        | PDF Export
        |--------------------------------------------------------------------------
        */

        if ($fileFormat === 'pdf') {

            $business = method_exists($me, 'businesses')
                ? $me->businesses()
                    ->where('businesses.id', $bid)
                    ->first()
                : null;

            $pdf = Pdf::loadView(
                'invoices.exports.pdf',
                [
                    'rows'        => $rows,
                    'reportTitle' => $reportTitle,
                    'type'        => $type,

                    'filters' => [
                        'search'     => $search,
                        'status'     => $status,
                        'date_range' => $dateRange,
                        'from_date'  => $fromDate,
                        'to_date'    => $toDate,
                    ],

                    'business' => $business,
                    'user'     => $me,
                ]
            )->setPaper('a4', 'landscape');

            return $pdf->download(
                $fileName . '.pdf'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Excel Export
        |--------------------------------------------------------------------------
        */

        return Excel::download(

            new InvoicesExport(
                $rows,
                $reportTitle,
                [
                    'search'     => $search,
                    'status'     => $status,
                    'date_range' => $dateRange,
                    'from_date'  => $fromDate,
                    'to_date'    => $toDate,
                ]
            ),

            $fileName . '.xlsx'
        );
    }


    /**
     * Resolve Report Date Range
     *
     * Last Month:
     * Previous complete calendar month
     *
     * Last Quarter:
     * Previous complete calendar quarter
     *
     * Last Half-Year:
     * Previous complete Jan-Jun / Jul-Dec period
     *
     * Last Year:
     * Previous complete calendar year
     */
    private function resolveDateRange(
        Request $request,
        string $dateRange
    ): array {

        $now = Carbon::now();

        switch ($dateRange) {

            /*
            |--------------------------------------------------------------------------
            | LAST MONTH
            |--------------------------------------------------------------------------
            |
            | Example:
            |
            | Today = 11 Aug 2026
            |
            | From = 01 Jul 2026
            | To   = 31 Jul 2026
            |
            */

            case 'last_month':

                $previousMonth = $now
                    ->copy()
                    ->subMonthNoOverflow();

                $fromDate = $previousMonth
                    ->copy()
                    ->startOfMonth()
                    ->toDateString();

                $toDate = $previousMonth
                    ->copy()
                    ->endOfMonth()
                    ->toDateString();

                break;


            /*
            |--------------------------------------------------------------------------
            | LAST QUARTER
            |--------------------------------------------------------------------------
            |
            | Calendar Quarter:
            |
            | Q1 = Jan - Mar
            | Q2 = Apr - Jun
            | Q3 = Jul - Sep
            | Q4 = Oct - Dec
            |
            | Example:
            |
            | Today = 11 Aug 2026 (Q3)
            |
            | Previous Quarter = Q2
            |
            | From = 01 Apr 2026
            | To   = 30 Jun 2026
            |
            */

            case 'quarter':

                $previousQuarterDate = $now
                    ->copy()
                    ->subQuarter();

                $fromDate = $previousQuarterDate
                    ->copy()
                    ->startOfQuarter()
                    ->toDateString();

                $toDate = $previousQuarterDate
                    ->copy()
                    ->endOfQuarter()
                    ->toDateString();

                break;


            /*
            |--------------------------------------------------------------------------
            | LAST HALF YEAR
            |--------------------------------------------------------------------------
            |
            | Half 1 = Jan - Jun
            | Half 2 = Jul - Dec
            |
            | Example:
            |
            | Today = 11 Aug 2026
            |
            | Current Half = Jul-Dec
            | Previous Half = Jan-Jun
            |
            | From = 01 Jan 2026
            | To   = 30 Jun 2026
            |
            */

            case 'half_year':

                $currentMonth = (int) $now->month;

                /*
                 * Current date Jan-Jun me hai.
                 *
                 * To previous half:
                 * Jul-Dec previous year.
                 */
                if ($currentMonth <= 6) {

                    $previousYear = $now->year - 1;

                    $fromDate = Carbon::create(
                        $previousYear,
                        7,
                        1
                    )
                        ->startOfDay()
                        ->toDateString();

                    $toDate = Carbon::create(
                        $previousYear,
                        12,
                        31
                    )
                        ->endOfDay()
                        ->toDateString();

                } else {

                    /*
                     * Current date Jul-Dec me hai.
                     *
                     * Previous half:
                     * Jan-Jun current year.
                     */

                    $currentYear = $now->year;

                    $fromDate = Carbon::create(
                        $currentYear,
                        1,
                        1
                    )
                        ->startOfDay()
                        ->toDateString();

                    $toDate = Carbon::create(
                        $currentYear,
                        6,
                        30
                    )
                        ->endOfDay()
                        ->toDateString();
                }

                break;


            /*
            |--------------------------------------------------------------------------
            | LAST YEAR
            |--------------------------------------------------------------------------
            |
            | Example:
            |
            | Today = 11 Aug 2026
            |
            | From = 01 Jan 2025
            | To   = 31 Dec 2025
            |
            */

            case 'last_year':

                $previousYear = $now
                    ->copy()
                    ->subYear();

                $fromDate = $previousYear
                    ->copy()
                    ->startOfYear()
                    ->toDateString();

                $toDate = $previousYear
                    ->copy()
                    ->endOfYear()
                    ->toDateString();

                break;


            /*
            |--------------------------------------------------------------------------
            | CUSTOM RANGE
            |--------------------------------------------------------------------------
            */

            case 'custom':

                $fromDate = $request->get('from_date');
                $toDate   = $request->get('to_date');

                /*
                 * Only To Date selected
                 *
                 * From date automatically us month ki
                 * first date ho jayegi.
                 */
                if (
                    empty($fromDate) &&
                    !empty($toDate)
                ) {

                    $fromDate = Carbon::parse($toDate)
                        ->startOfMonth()
                        ->toDateString();
                }

                /*
                 * Only From Date selected
                 *
                 * To Date = Today
                 */
                if (
                    !empty($fromDate) &&
                    empty($toDate)
                ) {

                    $toDate = $now
                        ->copy()
                        ->toDateString();
                }

                /*
                 * Dono dates present hain to
                 * proper Y-m-d format me convert karo.
                 */
                if (!empty($fromDate)) {

                    $fromDate = Carbon::parse($fromDate)
                        ->toDateString();
                }

                if (!empty($toDate)) {

                    $toDate = Carbon::parse($toDate)
                        ->toDateString();
                }

                /*
                 * Agar user galti se:
                 *
                 * From = 20 Aug
                 * To   = 10 Aug
                 *
                 * select kare to dates swap kar denge.
                 */
                if (
                    !empty($fromDate) &&
                    !empty($toDate) &&
                    Carbon::parse($fromDate)->gt(
                        Carbon::parse($toDate)
                    )
                ) {

                    [$fromDate, $toDate] = [
                        $toDate,
                        $fromDate
                    ];
                }

                break;


            /*
            |--------------------------------------------------------------------------
            | DEFAULT
            |--------------------------------------------------------------------------
            |
            | Invalid range aaye to previous complete month.
            |
            */

            default:

                $previousMonth = $now
                    ->copy()
                    ->subMonthNoOverflow();

                $fromDate = $previousMonth
                    ->copy()
                    ->startOfMonth()
                    ->toDateString();

                $toDate = $previousMonth
                    ->copy()
                    ->endOfMonth()
                    ->toDateString();

                break;
        }


        return [
            $fromDate,
            $toDate
        ];
    }
}