<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Imports\InstallmentReminderImport;
use App\Models\InstallmentReminder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Maatwebsite\Excel\Facades\Excel;

class InstallmentReminderController extends Controller
{
    public function index(Request $request)
    {
        $q = InstallmentReminder::query();

        // Filters
        if ($request->filled('status')) {
            $q->where('status', $request->string('status')->toString());
        }

        if ($request->filled('from') && $request->filled('to')) {
            $q->whereBetween('reminder_date', [
                Carbon::parse($request->input('from'))->startOfDay(),
                Carbon::parse($request->input('to'))->endOfDay(),
            ]);
        }

        if ($request->filled('q')) {
            $term = trim((string) $request->input('q'));
            $q->where(function ($qq) use ($term) {
                $qq->where('contact_number', 'like', "%{$term}%")
                  ->orWhere('snme_number', 'like', "%{$term}%");
            });
        }

        // Sorting: upcoming first, then past
        $today = now(config('app.timezone'))->toDateString();
        $q->orderByRaw("CASE WHEN reminder_date >= ? THEN 0 ELSE 1 END", [$today])
          ->orderBy('reminder_date', 'asc')
          ->orderBy('reminder_time', 'asc');

        $perPage = (int) ($request->input('per_page', 20));
        $reminders = $q->paginate(min(max($perPage, 1), 100))->withQueryString();

        return response()->json([
            'ok' => true,
            'data' => $reminders,
        ]);
    }

    // ✅ Create
    public function store(Request $request)
    {
        $data = $request->validate([
            'contact_number'      => 'required|string|max:20',
            'reminder_date'       => 'required|date',
            'reminder_time'       => 'required',
            'snme_number'         => 'nullable|string|max:50',
            'installment_amount'  => 'required|numeric|min:0',
            'installment_date'    => 'required|date',
            'status'              => 'nullable|string|max:30',
        ]);

        $data['status'] = $data['status'] ?: 'uploaded';

        $reminder = InstallmentReminder::create($data);

        return response()->json([
            'ok' => true,
            'message' => 'Installment reminder created successfully.',
            'data' => $reminder,
        ], 201);
    }

    // ✅ Detail
    public function show(InstallmentReminder $reminder)
    {
        return response()->json([
            'ok' => true,
            'data' => $reminder,
        ]);
    }

    // ✅ Update
    public function update(Request $request, InstallmentReminder $reminder)
    {

        $data = $request->validate([
            'contact_number'      => 'required|string|max:20',
            'reminder_date'       => 'required|date',
            'reminder_time'       => 'required',
            'snme_number'         => 'nullable|string|max:50',
            'installment_amount'  => 'required|numeric|min:0',
            'installment_date'    => 'required|date',
            'status'              => 'required|string|max:30',
        ]);


        $reminder->update($data);

        return response()->json([
            'ok' => true,
            'message' => 'Installment reminder updated successfully.',
            'data' => $reminder->fresh(),
        ]);
    }

// public function update(Request $request, InstallmentReminder $reminder)
// {
//     return response()->json([
//         'content_type' => $request->header('Content-Type'),
//         'all' => $request->all(),
//         'json' => $request->json()->all(),
//     ]);
// }

    // ✅ Delete
    public function destroy(InstallmentReminder $reminder)
    {
        $reminder->delete();

        return response()->json([
            'ok' => true,
            'message' => 'Installment reminder deleted successfully.',
        ]);
    }

    // ✅ Status Update only
    public function statusUpdate(Request $request, InstallmentReminder $reminder)
    {
        $data = $request->validate([
            'status' => 'required|string|max:30',
        ]);

        $reminder->update($data);

        return response()->json([
            'ok' => true,
            'message' => 'Status updated successfully.',
            'data' => $reminder->fresh(),
        ]);
    }

    // ✅ Import Excel/CSV
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            Excel::import(new InstallmentReminderImport, $request->file('file'));

            return response()->json([
                'ok' => true,
                'message' => 'Excel imported successfully.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Import failed: ' . $e->getMessage(),
            ], 422);
        }
    }

    // ✅ Run artisan command
    public function run(Request $request)
    {
        Artisan::call('app:run-installment-reminders');

        return response()->json([
            'ok' => true,
            'message' => 'Installment Reminder command executed',
            'output' => Artisan::output(),
        ]);
    }
}
