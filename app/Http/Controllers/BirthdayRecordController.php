<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BirthdayRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class BirthdayRecordController extends Controller
{
    // ✅ List + Search + Filter
    public function index(Request $request)
    {
        $q = BirthdayRecord::query();

        // optional: sirf current user ke business ke records
        // $q->where('business_id', auth()->user()->business_id);

        if ($request->filled('search')) {
            $s = trim($request->search);
            $q->where(function($qq) use ($s){
                $qq->where('name', 'like', "%{$s}%")
                    ->orWhere('phone', 'like', "%{$s}%");
            });
        }

        if ($request->filled('dob_from')) {
            $q->whereDate('date_of_birth', '>=', $request->dob_from);
        }
        if ($request->filled('dob_to')) {
            $q->whereDate('date_of_birth', '<=', $request->dob_to);
        }

        $records = $q->latest('id')->paginate(20)->withQueryString();

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
    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
            'business_id' => ['nullable','integer'],
        ]);

        $user = $request->user();
        $businessId = $request->get('business_id');

        // Excel::toArray returns all sheets
        $sheets = Excel::toArray([], $request->file('file'));
        $rows = $sheets[0] ?? [];

        if (count($rows) < 2) {
            return back()->withErrors(['file' => 'Excel file is empty or header missing.']);
        }

        // First row = header
        // First row = header
        $rawHeader = $rows[0] ?? [];
        $header = array_map(function ($h) {
            $h = strtolower(trim((string)$h));

            // normalize: spaces -> underscore, remove non-alnum/underscore
            $h = preg_replace('/\s+/', '_', $h);           // "date of birth" => "date_of_birth"
            $h = preg_replace('/[^a-z0-9_]/', '', $h);     // remove special chars
            return $h;
        }, $rawHeader);

// Accept multiple header options
        $phoneKeys = ['phone', 'mobile', 'mobile_no', 'phoneno', 'contact', 'contact_no'];
        $dobKeys   = ['date_of_birth', 'dob', 'birth_date', 'birthdate', 'dateofbirth'];
        $nameKeys  = ['name', 'full_name', 'customer_name'];

// helper to find first match
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

        // Debug (optional) - agar kabhi doubt ho header kya aa raha
        // dd($header, $idxPhone, $idxDob, $idxName);

        if ($idxPhone === false || $idxDob === false) {
            return back()->withErrors([
                'file' => 'Header must include phone and date_of_birth (Accepted: "date of birth", "dob", "birth_date").'
            ]);
        }


        $inserted = 0;
        $updated = 0;
        $skipped = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach (array_slice($rows, 1) as $i => $row) {

                $rowNumber = $i + 2; // considering header at row 1

                $name  = $idxName !== false ? trim((string)($row[$idxName] ?? '')) : null;
                $phone = trim((string)($row[$idxPhone] ?? ''));
                $dobRaw = $row[$idxDob] ?? null;

                if ($phone === '' || empty($dobRaw)) {
                    $skipped++;
                    continue;
                }

                // ✅ date parse (supports Excel date, Y-m-d, d-m-Y)
                $dob = $this->parseExcelDate($dobRaw);
                if (!$dob) {
                    $errors[] = "Row {$rowNumber}: Invalid date_of_birth";
                    $skipped++;
                    continue;
                }

                // ✅ validate each row
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
                    'user_id' => $user?->id,
                    'business_id' => $businessId,
                    'name' => $name ?: null,
                    'phone' => $phone,
                    'date_of_birth' => $dob->format('Y-m-d'),
                ];

                // ✅ upsert by (business_id + phone)
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
            return back()->withErrors(['file' => 'Import failed: '.$e->getMessage()]);
        }

        // Optional: show row errors in session
        if (!empty($errors)) {
            return redirect()->route('birthday-records.index')
                ->with('success', "Import done. Inserted: {$inserted}, Updated: {$updated}, Skipped: {$skipped}")
                ->with('import_errors', $errors);
        }

        return redirect()->route('birthday-records.index')
            ->with('success', "Import done. Inserted: {$inserted}, Updated: {$updated}, Skipped: {$skipped}");
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
}
