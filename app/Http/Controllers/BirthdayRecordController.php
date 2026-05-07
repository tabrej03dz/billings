<?php

namespace App\Http\Controllers;

use App\Models\ApiKey;
use App\Models\BirthdayWishLog;
use Illuminate\Http\Request;
use App\Models\BirthdayRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class BirthdayRecordController extends Controller
{
    // ✅ List + Search + Filter
//    public function index(Request $request)
//    {
//        $user = $request->user();
//
//        $q = BirthdayRecord::query();
//
//        // ✅ Role based access
//        if (!$user->hasRole('super admin')) {
//            // ✅ normal roles: only own created records
//            // (use whichever column you have: created_by OR user_id)
//            $q->where('user_id', $user->id);
//            // or: $q->where('user_id', $user->id);
//        }
//
//        // ✅ Search
//        if ($request->filled('search')) {
//            $s = trim($request->search);
//            $q->where(function($qq) use ($s){
//                $qq->where('name', 'like', "%{$s}%")
//                    ->orWhere('phone', 'like', "%{$s}%");
//            });
//        }
//
//        // ✅ DOB filters
//        if ($request->filled('dob_from')) {
//            $q->whereDate('date_of_birth', '>=', $request->dob_from);
//        }
//        if ($request->filled('dob_to')) {
//            $q->whereDate('date_of_birth', '<=', $request->dob_to);
//        }
//
//        $records = $q->latest('id')->paginate(20)->withQueryString();
//
//        return view('birthday_records.index', compact('records'));
//    }


//    public function index(Request $request)
//    {
//        $user = $request->user();
//
//        $q = BirthdayRecord::query()->with('user:id,name');
//
//        // ✅ Role based access
//        if (!$user->hasRole('super admin')) {
//            $q->where('user_id', $user->id);
//        }
//
//        // ✅ Search
//        if ($request->filled('search')) {
//            $s = trim($request->search);
//            $q->where(function ($qq) use ($s) {
//                $qq->where('name', 'like', "%{$s}%")
//                    ->orWhere('phone', 'like', "%{$s}%");
//            });
//        }
//
//        // ✅ Month filter
//        if ($request->filled('month')) {
//            $month = (int) $request->month;
//            if ($month >= 1 && $month <= 12) {
//                $q->whereMonth('date_of_birth', $month);
//            }
//        }
//
//        // ✅ Day filter (1-31)
//        if ($request->filled('day')) {
//            $day = (int) $request->day;
//            if ($day >= 1 && $day <= 31) {
//                $q->whereDay('date_of_birth', $day);
//            }
//        }
//
//        // ✅ Existing DOB range filters (aap chaho to keep/remove)
//        if ($request->filled('dob_from')) {
//            $q->whereDate('date_of_birth', '>=', $request->dob_from);
//        }
//        if ($request->filled('dob_to')) {
//            $q->whereDate('date_of_birth', '<=', $request->dob_to);
//        }
//
//        // ✅ Upcoming birthdays logic (next N days)
//        $upcomingOnly = (bool) $request->upcoming_only;
//        $days = (int) ($request->upcoming_days ?: 30);
//        $days = max(1, min(365, $days));
//
//        if ($upcomingOnly) {
//            $today = Carbon::today();
//            $to    = $today->copy()->addDays($days);
//
//            // Covers year-end wrapping too
//            // We compare mm-dd in cyclic way using two ranges when needed
//            $fromMd = $today->format('m-d');
//            $toMd   = $to->format('m-d');
//
//            if ($fromMd <= $toMd) {
//                $q->whereRaw("DATE_FORMAT(date_of_birth, '%m-%d') BETWEEN ? AND ?", [$fromMd, $toMd]);
//            } else {
//                // wrap: e.g. Dec 26 -> Jan 10
//                $q->where(function ($w) use ($fromMd, $toMd) {
//                    $w->whereRaw("DATE_FORMAT(date_of_birth, '%m-%d') >= ?", [$fromMd])
//                        ->orWhereRaw("DATE_FORMAT(date_of_birth, '%m-%d') <= ?", [$toMd]);
//                });
//            }
//
//            // ✅ Upcoming first ordering (closest first)
//            $q->orderByRaw("
//            CASE
//              WHEN STR_TO_DATE(CONCAT(YEAR(CURDATE()), '-', DATE_FORMAT(date_of_birth, '%m-%d')), '%Y-%m-%d') >= CURDATE()
//              THEN STR_TO_DATE(CONCAT(YEAR(CURDATE()), '-', DATE_FORMAT(date_of_birth, '%m-%d')), '%Y-%m-%d')
//              ELSE STR_TO_DATE(CONCAT(YEAR(CURDATE())+1, '-', DATE_FORMAT(date_of_birth, '%m-%d')), '%Y-%m-%d')
//            END ASC
//        ");
//        } else {
//            // ✅ Default ordering: month-day wise (Jan -> Dec)
//            $q->orderByRaw("DATE_FORMAT(date_of_birth, '%m-%d') ASC");
//        }
//
//        $records = $q->paginate(20)->withQueryString();
//
//        return view('birthday_records.index', compact('records'));
//    }


//    public function index(Request $request)
//    {
//        $user = $request->user();
//
//        $q = BirthdayRecord::query()->with('user:id,name');
//
//        // ✅ Role based access
//        if (!$user->hasRole('super admin')) {
//            $q->where('user_id', $user->id);
//        }
//
//        // ✅ Search
//        if ($request->filled('search')) {
//            $s = trim((string) $request->search);
//            $q->where(function ($qq) use ($s) {
//                $qq->where('name', 'like', "%{$s}%")
//                    ->orWhere('phone', 'like', "%{$s}%");
//            });
//        }
//
//        // ✅ Month filter
//        if ($request->filled('month')) {
//            $month = (int) $request->month;
//            if ($month >= 1 && $month <= 12) {
//                $q->whereMonth('date_of_birth', $month);
//            }
//        }
//
//        // ✅ Day filter (1-31)
//        if ($request->filled('day')) {
//            $day = (int) $request->day;
//            if ($day >= 1 && $day <= 31) {
//                $q->whereDay('date_of_birth', $day);
//            }
//        }
//
//        // ✅ Optional: DOB range filters (as you had)
//        if ($request->filled('dob_from')) {
//            $q->whereDate('date_of_birth', '>=', $request->dob_from);
//        }
//        if ($request->filled('dob_to')) {
//            $q->whereDate('date_of_birth', '<=', $request->dob_to);
//        }
//
//        /**
//         * ✅ Upcoming filter WITHOUT checkbox:
//         * - If upcoming_days is filled (and > 0), upcoming filter is ON automatically
//         * - Current year only: Today -> min(Today+days, 31 Dec)
//         */
//        $daysInput  = $request->input('upcoming_days');
//        $upcomingOn = $request->filled('upcoming_days') && (int)$daysInput > 0;
//
//        $days = (int) ($daysInput ?: 30);
//        $days = max(1, min(365, $days));
//
//        if ($upcomingOn) {
//            $tz = config('app.timezone', 'Asia/Kolkata');
//
//            $today   = Carbon::now($tz)->startOfDay();
//            $yearEnd = Carbon::create($today->year, 12, 31, 0, 0, 0, $tz);
//
//            $to = $today->copy()->addDays($days);
//            if ($to->gt($yearEnd)) $to = $yearEnd;
//
//            $year = $today->year;
//
//            // ✅ Filter by "current year birthday date" BETWEEN today and $to
//            $q->whereRaw("
//            STR_TO_DATE(CONCAT(?, '-', DATE_FORMAT(date_of_birth,'%m-%d')), '%Y-%m-%d')
//            BETWEEN ? AND ?
//        ", [$year, $today->toDateString(), $to->toDateString()]);
//
//            // ✅ Order upcoming nearest first
//            $q->orderByRaw("
//            STR_TO_DATE(CONCAT(?, '-', DATE_FORMAT(date_of_birth,'%m-%d')), '%Y-%m-%d') ASC
//        ", [$year]);
//
//        } else {
//            // ✅ Default order (month-day)
//            $q->orderByRaw("DATE_FORMAT(date_of_birth, '%m-%d') ASC");
//        }
//
//        $records = $q->paginate(20)->withQueryString();
//
//        return view('birthday_records.index', compact('records'));
//    }

    public function index(Request $request)
    {
        $user = $request->user();

        $q = BirthdayRecord::query()->with('user:id,name');

        // ✅ Role based access
        if (!$user->hasRole('super admin')) {
            $q->where('user_id', $user->id);
        }

        // ✅ Search
        if ($request->filled('search')) {
            $s = trim((string) $request->search);
            $q->where(function ($qq) use ($s) {
                $qq->where('name', 'like', "%{$s}%")
                    ->orWhere('phone', 'like', "%{$s}%");
            });
        }

        // ✅ Month filter
        if ($request->filled('month')) {
            $month = (int) $request->month;
            if ($month >= 1 && $month <= 12) {
                $q->whereMonth('date_of_birth', $month);
            }
        }

        // ✅ Day filter
        if ($request->filled('day')) {
            $day = (int) $request->day;
            if ($day >= 1 && $day <= 31) {
                $q->whereDay('date_of_birth', $day);
            }
        }

        // ✅ Optional: DOB range filters
        if ($request->filled('dob_from')) {
            $q->whereDate('date_of_birth', '>=', $request->dob_from);
        }
        if ($request->filled('dob_to')) {
            $q->whereDate('date_of_birth', '<=', $request->dob_to);
        }

        /**
         * ✅ Upcoming badge/order always:
         * - If upcoming_days filled => that range will be "upcoming"
         * - Otherwise default upcoming range = 30 days
         * - Upcoming birthdays will ALWAYS come on top (even if you are not filtering)
         * - Current year only (today -> 31 Dec)
         */
        $tz = config('app.timezone', 'Asia/Kolkata');

        $today   = Carbon::now($tz)->startOfDay();
        $yearEnd = Carbon::create($today->year, 12, 31, 0, 0, 0, $tz);

        $daysInput = $request->input('upcoming_days');
        $days = (int)($daysInput ?: 30);
        $days = max(1, min(365, $days));

        $to = $today->copy()->addDays($days);
        if ($to->gt($yearEnd)) $to = $yearEnd;

        $year = $today->year;

        // ✅ Optional: If you want "only upcoming" filter when days filled
        // (aap chahe to ye block hata sakte ho)
        $upcomingOnly = $request->boolean('upcoming_only'); // if you still keep checkbox
        $upcomingOn   = $request->filled('upcoming_days') && (int)$daysInput > 0;

        if ($upcomingOnly || $upcomingOn) {
            $q->whereRaw("
            STR_TO_DATE(CONCAT(?, '-', DATE_FORMAT(date_of_birth,'%m-%d')), '%Y-%m-%d')
            BETWEEN ? AND ?
        ", [$year, $today->toDateString(), $to->toDateString()]);
        }

        /**
         * ✅ ORDERING (UPCOMING ON TOP)
         * 1) First: birthdays between today and $to (range) => top
         * 2) Then: rest in month-day order
         */
        $q->orderByRaw("
        CASE
            WHEN STR_TO_DATE(CONCAT(?, '-', DATE_FORMAT(date_of_birth,'%m-%d')), '%Y-%m-%d')
                 BETWEEN ? AND ?
            THEN 0
            ELSE 1
        END ASC
    ", [$year, $today->toDateString(), $to->toDateString()]);

        // inside upcoming: closest first, outside upcoming: month-day order
        $q->orderByRaw("
        STR_TO_DATE(CONCAT(?, '-', DATE_FORMAT(date_of_birth,'%m-%d')), '%Y-%m-%d') ASC
    ", [$year]);

        $records = $q->paginate(20)->withQueryString();

        return view('birthday_records.index', compact('records'));
    }
    public function create()
    {
        return view('birthday_records.create');
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'business_id'    => ['nullable','integer'],
            'name'           => ['nullable','string','max:255'],
            'phone'          => ['required','string','max:20'],
            'date_of_birth'  => ['required','date'],
            'wish_time' => ['nullable', 'date_format:H:i'],
        ]);

        $data['user_id'] = $user?->id;

        // ✅ optional: ensure unique per business + phone
        if (!array_key_exists('business_id', $data)) $data['business_id'] = null;

        // upsert behavior: same business+phone -> update
        BirthdayRecord::updateOrCreate(
            ['business_id' => $data['business_id'], 'phone' => $data['phone']],
            $data
        );

        return redirect()->route('birthday-records.index')->with('success', 'Record saved!');
    }

    public function show(BirthdayRecord $birthdayRecord)
    {
        return view('birthday_records.show', ['record' => $birthdayRecord]);
    }

    public function edit(BirthdayRecord $birthdayRecord)
    {
        return view('birthday_records.edit', ['record' => $birthdayRecord]);
    }

    public function update(Request $request, BirthdayRecord $birthdayRecord)
    {
        $data = $request->validate([
            'business_id'    => ['nullable','integer'],
            'name'           => ['nullable','string','max:255'],
            'phone'          => ['required','string','max:20'],
            'date_of_birth'  => ['required','date'],
            'wish_time' => ['nullable', 'date_format:H:i'],
        ]);

        // ✅ unique check (business_id + phone) except current
        $exists = BirthdayRecord::where('business_id', $data['business_id'] ?? null)
            ->where('phone', $data['phone'])
            ->where('id', '!=', $birthdayRecord->id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['phone' => 'This phone already exists in this business.'])->withInput();
        }

        $birthdayRecord->update($data);

        return redirect()->route('birthday-records.index')->with('success', 'Record updated!');
    }

    public function destroy(BirthdayRecord $birthdayRecord)
    {
        $birthdayRecord->delete();
        return back()->with('success', 'Record deleted!');
    }

    /**
     * ✅ Excel Upload Page (optional)
     */
    public function importForm()
    {
        return view('birthday_records.import');
    }

    /**
     * ✅ Excel Import (Bulk Create/Update)
     * Expected columns: name, phone, date_of_birth
     */
    // public function import(Request $request)
    // {
    //     $request->validate([
    //         'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
    //         'business_id' => ['nullable','integer'],
    //     ]);

    //     $user = $request->user();
    //     $businessId = $request->get('business_id');

    //     // Excel::toArray returns all sheets
    //     $sheets = Excel::toArray([], $request->file('file'));
    //     $rows = $sheets[0] ?? [];

    //     if (count($rows) < 2) {
    //         return back()->withErrors(['file' => 'Excel file is empty or header missing.']);
    //     }

    //     // First row = header
    //     // First row = header
    //     $rawHeader = $rows[0] ?? [];
    //     $header = array_map(function ($h) {
    //         $h = strtolower(trim((string)$h));

    //         // normalize: spaces -> underscore, remove non-alnum/underscore
    //         $h = preg_replace('/\s+/', '_', $h);           // "date of birth" => "date_of_birth"
    //         $h = preg_replace('/[^a-z0-9_]/', '', $h);     // remove special chars
    //         return $h;
    //     }, $rawHeader);

    //     // Accept multiple header options
    //     $phoneKeys = ['phone', 'mobile', 'mobile_no', 'phoneno', 'contact', 'contact_no'];
    //     $dobKeys   = ['date_of_birth', 'dob', 'birth_date', 'birthdate', 'dateofbirth'];
    //     $nameKeys  = ['name', 'full_name', 'customer_name'];

    //     // helper to find first match
    //     $findIndex = function(array $keys) use ($header) {
    //         foreach ($keys as $k) {
    //             $idx = array_search($k, $header, true);
    //             if ($idx !== false) return $idx;
    //         }
    //         return false;
    //     };

    //     $idxPhone = $findIndex($phoneKeys);
    //     $idxDob   = $findIndex($dobKeys);
    //     $idxName  = $findIndex($nameKeys);

    //     // Debug (optional) - agar kabhi doubt ho header kya aa raha
    //     // dd($header, $idxPhone, $idxDob, $idxName);

    //     if ($idxPhone === false || $idxDob === false) {
    //         return back()->withErrors([
    //             'file' => 'Header must include phone and date_of_birth (Accepted: "date of birth", "dob", "birth_date").'
    //         ]);
    //     }


    //     $inserted = 0;
    //     $updated = 0;
    //     $skipped = 0;
    //     $errors = [];

    //     DB::beginTransaction();
    //     try {
    //         foreach (array_slice($rows, 1) as $i => $row) {

    //             $rowNumber = $i + 2; // considering header at row 1

    //             $name  = $idxName !== false ? trim((string)($row[$idxName] ?? '')) : null;
    //             $phone = trim((string)($row[$idxPhone] ?? ''));
    //             $dobRaw = $row[$idxDob] ?? null;

    //             if ($phone === '' || empty($dobRaw)) {
    //                 $skipped++;
    //                 continue;
    //             }

    //             // ✅ date parse (supports Excel date, Y-m-d, d-m-Y)
    //             $dob = $this->parseExcelDate($dobRaw);
    //             if (!$dob) {
    //                 $errors[] = "Row {$rowNumber}: Invalid date_of_birth";
    //                 $skipped++;
    //                 continue;
    //             }

    //             // ✅ validate each row
    //             $v = Validator::make([
    //                 'phone' => $phone,
    //                 'date_of_birth' => $dob->format('Y-m-d'),
    //             ], [
    //                 'phone' => ['required','string','max:20'],
    //                 'date_of_birth' => ['required','date'],
    //             ]);

    //             if ($v->fails()) {
    //                 $errors[] = "Row {$rowNumber}: " . implode(', ', $v->errors()->all());
    //                 $skipped++;
    //                 continue;
    //             }

    //             $payload = [
    //                 'user_id' => $user?->id,
    //                 'business_id' => $businessId,
    //                 'name' => $name ?: null,
    //                 'phone' => $phone,
    //                 'date_of_birth' => $dob->format('Y-m-d'),
    //             ];

    //             // ✅ upsert by (business_id + phone)
    //             $existing = BirthdayRecord::where('business_id', $businessId)
    //                 ->where('phone', $phone)
    //                 ->first();

    //             if ($existing) {
    //                 $existing->update($payload);
    //                 $updated++;
    //             } else {
    //                 BirthdayRecord::create($payload);
    //                 $inserted++;
    //             }
    //         }

    //         DB::commit();
    //     } catch (\Throwable $e) {
    //         DB::rollBack();
    //         return back()->withErrors(['file' => 'Import failed: '.$e->getMessage()]);
    //     }

    //     // Optional: show row errors in session
    //     if (!empty($errors)) {
    //         return redirect()->route('birthday-records.index')
    //             ->with('success', "Import done. Inserted: {$inserted}, Updated: {$updated}, Skipped: {$skipped}")
    //             ->with('import_errors', $errors);
    //     }

    //     return redirect()->route('birthday-records.index')
    //         ->with('success', "Import done. Inserted: {$inserted}, Updated: {$updated}, Skipped: {$skipped}");
    // }


    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
            'business_id' => ['nullable', 'integer'],
        ]);

        $user = $request->user();
        $businessId = $request->get('business_id');

        $sheets = Excel::toArray([], $request->file('file'));
        $rows = $sheets[0] ?? [];

        if (count($rows) < 2) {
            return back()->withErrors(['file' => 'Excel file is empty or header missing.']);
        }

        $rawHeader = $rows[0] ?? [];

        $header = array_map(function ($h) {
            $h = strtolower(trim((string) $h));
            $h = preg_replace('/\s+/', '_', $h);
            $h = preg_replace('/[^a-z0-9_]/', '', $h);
            return $h;
        }, $rawHeader);

        $phoneKeys = ['phone', 'mobile', 'mobile_no', 'phoneno', 'contact', 'contact_no'];
        $dobKeys   = ['date_of_birth', 'dob', 'birth_date', 'birthdate', 'dateofbirth'];
        $nameKeys  = ['name', 'full_name', 'customer_name'];

        // ✅ wish_time accepted headers
        $wishTimeKeys = [
            'wish_time',
            'wishtime',
            'time',
            'send_time',
            'birthday_time',
            'message_time',
        ];

        $findIndex = function (array $keys) use ($header) {
            foreach ($keys as $k) {
                $idx = array_search($k, $header, true);
                if ($idx !== false) {
                    return $idx;
                }
            }
            return false;
        };

        $idxPhone    = $findIndex($phoneKeys);
        $idxDob      = $findIndex($dobKeys);
        $idxName     = $findIndex($nameKeys);
        $idxWishTime = $findIndex($wishTimeKeys);

        if ($idxPhone === false || $idxDob === false) {
            return back()->withErrors([
                'file' => 'Header must include phone and date_of_birth. Accepted DOB headers: date of birth, dob, birth_date.'
            ]);
        }

        $inserted = 0;
        $updated = 0;
        $skipped = 0;
        $errors = [];

        DB::beginTransaction();

        try {
            foreach (array_slice($rows, 1) as $i => $row) {
                $rowNumber = $i + 2;

                $name  = $idxName !== false ? trim((string) ($row[$idxName] ?? '')) : null;
                $phone = trim((string) ($row[$idxPhone] ?? ''));
                $dobRaw = $row[$idxDob] ?? null;

                // ✅ wish_time optional
                $wishTimeRaw = $idxWishTime !== false ? ($row[$idxWishTime] ?? null) : null;
                $wishTime = $this->parseExcelTime($wishTimeRaw);

                if ($phone === '' || empty($dobRaw)) {
                    $skipped++;
                    continue;
                }

                $dob = $this->parseExcelDate($dobRaw);

                if (!$dob) {
                    $errors[] = "Row {$rowNumber}: Invalid date_of_birth";
                    $skipped++;
                    continue;
                }

                if (!empty($wishTimeRaw) && !$wishTime) {
                    $errors[] = "Row {$rowNumber}: Invalid wish_time. Use format like 09:00 or 09:00 AM";
                    $skipped++;
                    continue;
                }

                $v = Validator::make([
                    'phone' => $phone,
                    'date_of_birth' => $dob->format('Y-m-d'),
                    'wish_time' => $wishTime,
                ], [
                    'phone' => ['required', 'string', 'max:20'],
                    'date_of_birth' => ['required', 'date'],
                    'wish_time' => ['nullable', 'date_format:H:i:s'],
                ]);

                if ($v->fails()) {
                    $errors[] = "Row {$rowNumber}: " . implode(', ', $v->errors()->all());
                    $skipped++;
                    continue;
                }

                $payload = [
                    'user_id' => $user?->id,
                    'business_id' => $businessId,
                    'name' => $name ?: null,
                    'phone' => $phone,
                    'date_of_birth' => $dob->format('Y-m-d'),
                    'wish_time' => $wishTime, // ✅ added
                ];

                $existing = BirthdayRecord::where('business_id', $businessId)
                    ->where('phone', $phone)
                    ->first();

                if ($existing) {
                    $existing->update($payload);
                    $updated++;
                } else {
                    BirthdayRecord::create($payload);
                    $inserted++;
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withErrors([
                'file' => 'Import failed: ' . $e->getMessage()
            ]);
        }

        return redirect()->route('birthday-records.index')
            ->with('success', "Import done. Inserted: {$inserted}, Updated: {$updated}, Skipped: {$skipped}")
            ->with('import_errors', $errors);
    }

    /**
     * Helper to parse Excel date values
     */
    private function parseExcelDate($value): ?Carbon
    {
        // If numeric (Excel date serial)
        if (is_numeric($value)) {
            try {
                // Excel serial -> Carbon
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value));
            } catch (\Throwable $e) {
                return null;
            }
        }

        $v = trim((string)$value);

        // Try Y-m-d
        try {
            return Carbon::createFromFormat('Y-m-d', $v);
        } catch (\Throwable $e) {}

        // Try d-m-Y
        try {
            return Carbon::createFromFormat('d-m-Y', $v);
        } catch (\Throwable $e) {}

        // fallback parse
        try {
            return Carbon::parse($v);
        } catch (\Throwable $e) {
            return null;
        }
    }


    private function parseExcelTime($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            // Excel numeric time e.g. 0.375 = 09:00
            if (is_numeric($value)) {
                return ExcelDate::excelToDateTimeObject($value)->format('H:i:s');
            }

            $value = trim((string) $value);

            $formats = [
                'H:i:s',
                'H:i',
                'h:i A',
                'h:i a',
                'g:i A',
                'g:i a',
            ];

            foreach ($formats as $format) {
                try {
                    return Carbon::createFromFormat($format, $value)->format('H:i:s');
                } catch (\Throwable $e) {
                    // try next format
                }
            }

            return Carbon::parse($value)->format('H:i:s');
        } catch (\Throwable $e) {
            return null;
        }
    }


//    public function send(BirthdayRecord $birthdayRecord){
//
//        $phone = $birthdayRecord->phone;
//
//
//        $api = ApiKey::withoutGlobalScope('business')
//            ->where('user_id', $birthdayRecord->user_id)
//            ->first();
//
//
//        $url = $api->wishes_api;
//        $to = preg_replace('/\D+/', '', $phone);
//
//        // If 10 digit => add 91 (India)
//        if (strlen($to) === 10) $to = '91' . $to;
//        // ✅ Payload (adjust keys if your provider expects different)
//        $payload = [
//            'number'      => $to,
//            'Video' => asset('asset/video/birthday-wish.mp4'),
//        ];
//
//        Log::info('WA WEBHOOK REQ', ['url'=>$url, 'payload'=>$payload]);
//
//        $res = Http::timeout(60)->acceptJson()->post($url, $payload);
//
//        Log::info('WA WEBHOOK RES', [
//            'status' => $res->status(),
//            'body'   => $res->body(),
//        ]);
//        return back()->with('success', 'Wishes sent successfully');
//    }

    public function send(BirthdayRecord $birthdayRecord)
    {
        $today = Carbon::now()->timezone(config('app.timezone'));
        $year  = (int) $today->format('Y');

        // ✅ already sent check (same year)
        $already = BirthdayWishLog::where('birthday_record_id', $birthdayRecord->id)
            ->where('wish_year', $year)
            ->exists();

        if ($already) {
            return back()->with('error', 'Wishes already sent for this record this year.');
        }

        // ✅ API key (business scope ignore as you already did)
        $api = ApiKey::withoutGlobalScope('business')
            ->where('user_id', $birthdayRecord->user_id)
            ->first();

        if (!$api || !$api->wishes_api) {
            // optionally log failed attempt too (up to you)
            BirthdayWishLog::create([
                'birthday_record_id' => $birthdayRecord->id,
                'business_id'        => $birthdayRecord->business_id,
                'phone'              => $birthdayRecord->phone,
                'wish_date'          => $today->toDateString(),
                'wish_year'          => $year,
                'status'             => 'failed',
                'message'            => null,
                'response'           => 'Missing wishes_api URL',
            ]);

            return back()->with('error', 'Wishes API URL not found for this user.');
        }

        $url = $api->wishes_api;

        // ✅ phone sanitize
        $to = preg_replace('/\D+/', '', (string) $birthdayRecord->phone);
        if (strlen($to) === 10) $to = '91' . $to;

        // ✅ optional message (log me store karne ke liye)
        $name = $birthdayRecord->name ?: 'Dear';
        $message = "🎉 Happy Birthday {$name}! 🎂\nGod bless you with health, happiness & success.\n\n— Real Victory Groups";

        // ✅ payload
        $payload = [
            'number' => $to,
            'Video' => asset('asset/video/birthday-wish.mp4'),
            // if your webhook supports message too, send it:
            // 'message' => $message,
            'name' => $name,
            'image' => asset('asset/img/birthday-wish.jpeg'),
        ];

        // ✅ Create pending log first
        $log = BirthdayWishLog::create([
            'birthday_record_id' => $birthdayRecord->id,
            'business_id'        => $birthdayRecord->business_id,
            'phone'              => $birthdayRecord->phone,
            'wish_date'          => $today->toDateString(),
            'wish_year'          => $year,
            'status'             => 'pending',
            'message'            => $message,
        ]);

        try {
            Log::info('WA WEBHOOK REQ', ['url' => $url, 'payload' => $payload]);

            $res = Http::timeout(60)->acceptJson()->post($url, $payload);

            Log::info('WA WEBHOOK RES', [
                'status' => $res->status(),
                'body'   => $res->body(),
            ]);

            // ✅ update log with response
            $log->update([
                'status'   => $res->successful() ? 'success' : 'failed',
                'response' => "HTTP {$res->status()} | " . $res->body(),
            ]);

            return back()->with(
                $res->successful() ? 'success' : 'error',
                $res->successful() ? 'Wishes sent successfully' : 'Wishes failed to send'
            );

        } catch (\Throwable $e) {

            $log->update([
                'status'   => 'failed',
                'response' => $e->getMessage(),
            ]);

            return back()->with('error', 'Exception: ' . $e->getMessage());
        }
    }

}
