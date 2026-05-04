<?php

namespace App\Http\Controllers;

use App\Models\Anniversary;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Excel;

class AnniversaryController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $q = Anniversary::query()->with('user:id,name');

        if (!$user->hasRole('super admin')) {
            $q->where('user_id', $user->id);
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $q->where(function ($qq) use ($s) {
                $qq->where('name', 'like', "%{$s}%")
                    ->orWhere('phone', 'like', "%{$s}%");
            });
        }

        if ($request->filled('month')) {
            $q->whereMonth('date_of_anniversary', (int) $request->month);
        }

        if ($request->filled('day')) {
            $q->whereDay('date_of_anniversary', (int) $request->day);
        }

        $q->orderByRaw("DATE_FORMAT(date_of_anniversary, '%m-%d') ASC");

        $records = $q->paginate(20)->withQueryString();

        return view('anniversaries.index', compact('records'));
    }

    public function create()
    {
        return view('anniversaries.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'business_id' => ['nullable', 'integer'],
            'name' => ['nullable', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'date_of_anniversary' => ['required', 'date'],
            'wish_time' => ['nullable', 'date_format:H:i'],
        ]);

        $data['user_id'] = $request->user()?->id;
        $data['business_id'] = $data['business_id'] ?? null;

        Anniversary::updateOrCreate(
            [
                'business_id' => $data['business_id'],
                'phone' => $data['phone'],
            ],
            $data
        );

        return redirect()->route('anniversaries.index')
            ->with('success', 'Anniversary record saved!');
    }

    public function edit(Anniversary $anniversary)
    {
        return view('anniversaries.edit', ['record' => $anniversary]);
    }

    // public function update(Request $request, Anniversary $anniversary)
    // {
    //     $data = $request->validate([
    //         'business_id' => ['nullable', 'integer'],
    //         'name' => ['nullable', 'string', 'max:255'],
    //         'phone' => ['required', 'string', 'max:20'],
    //         'date_of_anniversary' => ['required', 'date'],
    //         'wish_time' => ['nullable', 'time'],
    //     ]);

    //     $exists = Anniversary::where('business_id', $data['business_id'] ?? null)
    //         ->where('phone', $data['phone'])
    //         ->where('id', '!=', $anniversary->id)
    //         ->exists();

    //     if ($exists) {
    //         return back()
    //             ->withErrors(['phone' => 'This phone already exists in this business.'])
    //             ->withInput();
    //     }

    //     $anniversary->update($data);

    //     return redirect()->route('anniversaries.index')
    //         ->with('success', 'Anniversary record updated!');
    // }


    public function update(Request $request, Anniversary $anniversary)
    {
        $data = $request->validate([
            'business_id' => ['nullable', 'integer'],
            'name' => ['nullable', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'date_of_anniversary' => ['required', 'date'],
            'wish_time' => ['nullable', 'date_format:H:i'],
        ]);

        $exists = Anniversary::where('business_id', $data['business_id'] ?? null)
            ->where('phone', $data['phone'])
            ->where('id', '!=', $anniversary->id)
            ->exists();

        if ($exists) {
            return back()
                ->withErrors(['phone' => 'This phone already exists in this business.'])
                ->withInput();
        }

        $anniversary->update($data);

        return redirect()->route('anniversaries.index')
            ->with('success', 'Anniversary record updated!');
    }

    public function destroy(Anniversary $anniversary)
    {
        $anniversary->delete();

        return back()->with('success', 'Anniversary record deleted!');
    }














    public function importForm()
{
    return view('anniversaries.import');
}

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
    $dateKeys  = [
        'date_of_anniversary',
        'anniversary_date',
        'anniversary',
        'dateofanniversary',
        'doa'
    ];
    $nameKeys = ['name', 'full_name', 'customer_name'];

    $findIndex = function (array $keys) use ($header) {
        foreach ($keys as $k) {
            $idx = array_search($k, $header, true);
            if ($idx !== false) {
                return $idx;
            }
        }

        return false;
    };

    $idxPhone = $findIndex($phoneKeys);
    $idxDate  = $findIndex($dateKeys);
    $idxName  = $findIndex($nameKeys);

    if ($idxPhone === false || $idxDate === false) {
        return back()->withErrors([
            'file' => 'Header must include phone and date_of_anniversary. Accepted date headers: date_of_anniversary, anniversary_date, anniversary, doa.'
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

            $name = $idxName !== false ? trim((string) ($row[$idxName] ?? '')) : null;
            $phone = trim((string) ($row[$idxPhone] ?? ''));
            $dateRaw = $row[$idxDate] ?? null;

            if ($phone === '' || empty($dateRaw)) {
                $skipped++;
                continue;
            }

            $anniversaryDate = $this->parseExcelDate($dateRaw);

            if (!$anniversaryDate) {
                $errors[] = "Row {$rowNumber}: Invalid date_of_anniversary";
                $skipped++;
                continue;
            }

            $v = Validator::make([
                'phone' => $phone,
                'date_of_anniversary' => $anniversaryDate->format('Y-m-d'),
            ], [
                'phone' => ['required', 'string', 'max:20'],
                'date_of_anniversary' => ['required', 'date'],
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
                'date_of_anniversary' => $anniversaryDate->format('Y-m-d'),
            ];

            $existing = Anniversary::where('business_id', $businessId)
                ->where('phone', $phone)
                ->first();

            if ($existing) {
                $existing->update($payload);
                $updated++;
            } else {
                Anniversary::create($payload);
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

    if (!empty($errors)) {
        return redirect()->route('anniversaries.index')
            ->with('success', "Import done. Inserted: {$inserted}, Updated: {$updated}, Skipped: {$skipped}")
            ->with('import_errors', $errors);
    }

    return redirect()->route('anniversaries.index')
        ->with('success', "Import done. Inserted: {$inserted}, Updated: {$updated}, Skipped: {$skipped}");
}

private function parseExcelDate($value): ?Carbon
{
    if (is_numeric($value)) {
        try {
            return Carbon::instance(
                \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)
            );
        } catch (\Throwable $e) {
            return null;
        }
    }

    $v = trim((string) $value);

    try {
        return Carbon::createFromFormat('Y-m-d', $v);
    } catch (\Throwable $e) {}

    try {
        return Carbon::createFromFormat('d-m-Y', $v);
    } catch (\Throwable $e) {}

    try {
        return Carbon::createFromFormat('d/m/Y', $v);
    } catch (\Throwable $e) {}

    try {
        return Carbon::parse($v);
    } catch (\Throwable $e) {
        return null;
    }
}
}
