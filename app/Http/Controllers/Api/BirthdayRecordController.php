<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use App\Models\BirthdayRecord;
use App\Models\BirthdayWishLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class BirthdayRecordController extends Controller
{
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

        // ✅ DOB range filters
        if ($request->filled('dob_from')) {
            $q->whereDate('date_of_birth', '>=', $request->dob_from);
        }
        if ($request->filled('dob_to')) {
            $q->whereDate('date_of_birth', '<=', $request->dob_to);
        }

        // ✅ Upcoming logic (same as your controller)
        $tz = config('app.timezone', 'Asia/Kolkata');
        $today   = Carbon::now($tz)->startOfDay();
        $yearEnd = Carbon::create($today->year, 12, 31, 0, 0, 0, $tz);

        $daysInput = $request->input('upcoming_days');
        $days = (int) ($daysInput ?: 30);
        $days = max(1, min(365, $days));

        $to = $today->copy()->addDays($days);
        if ($to->gt($yearEnd)) $to = $yearEnd;

        $year = $today->year;

        $upcomingOnly = $request->boolean('upcoming_only');
        $upcomingOn   = $request->filled('upcoming_days') && (int)$daysInput > 0;

        if ($upcomingOnly || $upcomingOn) {
            $q->whereRaw("
                STR_TO_DATE(CONCAT(?, '-', DATE_FORMAT(date_of_birth,'%m-%d')), '%Y-%m-%d')
                BETWEEN ? AND ?
            ", [$year, $today->toDateString(), $to->toDateString()]);
        }

        // ✅ Upcoming always on top
        $q->orderByRaw("
            CASE
                WHEN STR_TO_DATE(CONCAT(?, '-', DATE_FORMAT(date_of_birth,'%m-%d')), '%Y-%m-%d')
                    BETWEEN ? AND ?
                THEN 0
                ELSE 1
            END ASC
        ", [$year, $today->toDateString(), $to->toDateString()]);

        $q->orderByRaw("
            STR_TO_DATE(CONCAT(?, '-', DATE_FORMAT(date_of_birth,'%m-%d')), '%Y-%m-%d') ASC
        ", [$year]);

        $perPage = (int) ($request->input('per_page') ?: 20);
        $perPage = max(1, min(200, $perPage));

        $records = $q->paginate($perPage)->withQueryString();

        return response()->json([
            'status' => true,
            'message' => 'Birthday records fetched',
            'data' => $records,
        ]);
    }

    /**
     * POST /api/birthday-records
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'business_id'    => ['nullable','integer'],
            'name'           => ['nullable','string','max:255'],
            'phone'          => ['required','string','max:20'],
            'date_of_birth'  => ['required','date'],
        ]);

        $data['user_id'] = $user->id;
        if (!array_key_exists('business_id', $data)) $data['business_id'] = null;

        $record = BirthdayRecord::updateOrCreate(
            ['business_id' => $data['business_id'], 'phone' => $data['phone']],
            $data
        );

        return response()->json([
            'status' => true,
            'message' => 'Record saved',
            'data' => $record,
        ], 201);
    }

    /**
     * GET /api/birthday-records/{birthdayRecord}
     */
    public function show(Request $request, BirthdayRecord $birthdayRecord)
    {
        $this->authorizeAccess($request->user(), $birthdayRecord);

        $birthdayRecord->load('user:id,name');

        return response()->json([
            'status' => true,
            'message' => 'Record detail',
            'data' => $birthdayRecord,
        ]);
    }

    /**
     * PUT /api/birthday-records/{birthdayRecord}
     */
    public function update(Request $request, BirthdayRecord $birthdayRecord)
    {
        $this->authorizeAccess($request->user(), $birthdayRecord);

        $data = $request->validate([
            'business_id'    => ['nullable','integer'],
            'name'           => ['nullable','string','max:255'],
            'phone'          => ['required','string','max:20'],
            'date_of_birth'  => ['required','date'],
        ]);

        $exists = BirthdayRecord::where('business_id', $data['business_id'] ?? null)
            ->where('phone', $data['phone'])
            ->where('id', '!=', $birthdayRecord->id)
            ->exists();

        if ($exists) {
            return response()->json([
                'status' => false,
                'message' => 'This phone already exists in this business.',
                'errors' => ['phone' => ['This phone already exists in this business.']],
            ], 422);
        }

        $birthdayRecord->update($data);

        return response()->json([
            'status' => true,
            'message' => 'Record updated',
            'data' => $birthdayRecord->fresh(),
        ]);
    }

    /**
     * DELETE /api/birthday-records/{birthdayRecord}
     */
    public function destroy(Request $request, BirthdayRecord $birthdayRecord)
    {
        $this->authorizeAccess($request->user(), $birthdayRecord);

        $birthdayRecord->delete();

        return response()->json([
            'status' => true,
            'message' => 'Record deleted',
        ]);
    }

    /**
     * POST /api/birthday-records/import
     * form-data:
     * - file (xlsx/xls/csv)
     * - business_id (optional)
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
            'business_id' => ['nullable','integer'],
        ]);

        $user = $request->user();
        $businessId = $request->get('business_id');

        $sheets = Excel::toArray([], $request->file('file'));
        $rows = $sheets[0] ?? [];

        if (count($rows) < 2) {
            return response()->json([
                'status' => false,
                'message' => 'Excel file is empty or header missing.',
            ], 422);
        }

        $rawHeader = $rows[0] ?? [];
        $header = array_map(function ($h) {
            $h = strtolower(trim((string)$h));
            $h = preg_replace('/\s+/', '_', $h);
            $h = preg_replace('/[^a-z0-9_]/', '', $h);
            return $h;
        }, $rawHeader);

        $phoneKeys = ['phone', 'mobile', 'mobile_no', 'phoneno', 'contact', 'contact_no'];
        $dobKeys   = ['date_of_birth', 'dob', 'birth_date', 'birthdate', 'dateofbirth'];
        $nameKeys  = ['name', 'full_name', 'customer_name'];

        $findIndex = function(array $keys) use ($header) {
            foreach ($keys as $k) {
                $idx = array_search($k, $header, true);
                if ($idx !== false) return $idx;
            }
            return false;
        };

        $idxPhone = $findIndex($phoneKeys);
        $idxDob   = $findIndex($dobKeys);
        $idxName  = $findIndex($nameKeys);

        if ($idxPhone === false || $idxDob === false) {
            return response()->json([
                'status' => false,
                'message' => 'Header must include phone and date_of_birth (Accepted: "date of birth", "dob", "birth_date").',
            ], 422);
        }

        $inserted = 0;
        $updated  = 0;
        $skipped  = 0;
        $errors   = [];

        DB::beginTransaction();
        try {
            foreach (array_slice($rows, 1) as $i => $row) {
                $rowNumber = $i + 2;

                $name   = $idxName !== false ? trim((string)($row[$idxName] ?? '')) : null;
                $phone  = trim((string)($row[$idxPhone] ?? ''));
                $dobRaw = $row[$idxDob] ?? null;

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

                $v = Validator::make([
                    'phone' => $phone,
                    'date_of_birth' => $dob->format('Y-m-d'),
                ], [
                    'phone' => ['required','string','max:20'],
                    'date_of_birth' => ['required','date'],
                ]);

                if ($v->fails()) {
                    $errors[] = "Row {$rowNumber}: " . implode(', ', $v->errors()->all());
                    $skipped++;
                    continue;
                }

                $payload = [
                    'user_id' => $user->id,
                    'business_id' => $businessId,
                    'name' => $name ?: null,
                    'phone' => $phone,
                    'date_of_birth' => $dob->format('Y-m-d'),
                ];

                $existing = BirthdayRecord::where('business_id', $businessId)
                    ->where('phone', $phone)
                    ->first();

                if ($existing) {
                    // ✅ normal user should not override someone else’s data
                    $this->authorizeAccess($user, $existing);

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

            return response()->json([
                'status' => false,
                'message' => 'Import failed: '.$e->getMessage(),
            ], 500);
        }

        return response()->json([
            'status' => true,
            'message' => 'Import done',
            'summary' => compact('inserted','updated','skipped'),
            'row_errors' => $errors,
        ]);
    }

    /**
     * POST /api/birthday-records/{birthdayRecord}/send-wish
     */
    public function sendWish(Request $request, BirthdayRecord $birthdayRecord)
    {
        $this->authorizeAccess($request->user(), $birthdayRecord);

        $today = Carbon::now()->timezone(config('app.timezone'));
        $year  = (int) $today->format('Y');

        $already = BirthdayWishLog::where('birthday_record_id', $birthdayRecord->id)
            ->where('wish_year', $year)
            ->exists();

        if ($already) {
            return response()->json([
                'status' => false,
                'message' => 'Wishes already sent for this record this year.',
            ], 409);
        }

        $api = ApiKey::withoutGlobalScope('business')
            ->where('user_id', $birthdayRecord->user_id)
            ->first();

        if (!$api || !$api->wishes_api) {
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

            return response()->json([
                'status' => false,
                'message' => 'Wishes API URL not found for this user.',
            ], 422);
        }

        $url = $api->wishes_api;

        $to = preg_replace('/\D+/', '', (string) $birthdayRecord->phone);
        if (strlen($to) === 10) $to = '91' . $to;

        $name = $birthdayRecord->name ?: 'Dear';
        $message = "🎉 Happy Birthday {$name}! 🎂\nGod bless you with health, happiness & success.\n\n— Real Victory Groups";

        $payload = [
            'number' => $to,
            'Video'  => asset('asset/video/birthday-wish.mp4'),
            // 'message' => $message, // if supported
        ];

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

            Log::info('WA WEBHOOK RES', ['status' => $res->status(), 'body' => $res->body()]);

            $log->update([
                'status'   => $res->successful() ? 'success' : 'failed',
                'response' => "HTTP {$res->status()} | " . $res->body(),
            ]);

            return response()->json([
                'status' => $res->successful(),
                'message' => $res->successful() ? 'Wishes sent successfully' : 'Wishes failed to send',
                'log' => $log->fresh(),
                'provider' => [
                    'http_status' => $res->status(),
                    'body' => $res->json() ?? $res->body(),
                ],
            ], $res->successful() ? 200 : 502);

        } catch (\Throwable $e) {
            $log->update([
                'status'   => 'failed',
                'response' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Exception: ' . $e->getMessage(),
                'log' => $log->fresh(),
            ], 500);
        }
    }

    // -------------------
    // Helpers
    // -------------------

    private function authorizeAccess($user, BirthdayRecord $record): void
    {
        if ($user->hasRole('super admin')) return;

        if ((int)$record->user_id !== (int)$user->id) {
            abort(response()->json([
                'status' => false,
                'message' => 'Forbidden',
            ], 403));
        }
    }

    private function parseExcelDate($value): ?Carbon
    {
        if (is_numeric($value)) {
            try {
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value));
            } catch (\Throwable $e) {
                return null;
            }
        }

        $v = trim((string)$value);

        try { return Carbon::createFromFormat('Y-m-d', $v); } catch (\Throwable $e) {}
        try { return Carbon::createFromFormat('d-m-Y', $v); } catch (\Throwable $e) {}

        try { return Carbon::parse($v); } catch (\Throwable $e) { return null; }
    }
}
