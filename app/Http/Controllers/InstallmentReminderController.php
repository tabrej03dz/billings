<?php

namespace App\Http\Controllers;

use App\Imports\InstallmentReminderImport;
use App\Models\InstallmentReminder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Maatwebsite\Excel\Facades\Excel;

class InstallmentReminderController extends Controller
{
//    public function index(Request $request)
//    {
//        $q = InstallmentReminder::query();
//
//        if ($request->filled('status')) {
//            $q->where('status', $request->status);
//        }
//
//        if ($request->filled('from') && $request->filled('to')) {
//            $q->whereBetween('reminder_date', [
//                Carbon::parse($request->from)->startOfDay(),
//                Carbon::parse($request->to)->endOfDay(),
//            ]);
//        }
//
//        if ($request->filled('q')) {
//            $term = trim((string)$request->q);
//            $q->where(function ($qq) use ($term) {
//                $qq->where('contact_number', 'like', "%{$term}%")
//                    ->orWhere('snme_number', 'like', "%{$term}%");
//            });
//        }
//
//        $reminders = $q->orderByDesc('reminder_date')->paginate(20)->withQueryString();
//
//        return view('installment_reminders.index', compact('reminders'));
//    }


    public function index(Request $request)
    {
        $q = InstallmentReminder::query();

        if ($request->filled('status')) {
            $q->where('status', $request->status);
        }

        if ($request->filled('from') && $request->filled('to')) {
            $q->whereBetween('reminder_date', [
                Carbon::parse($request->from)->startOfDay(),
                Carbon::parse($request->to)->endOfDay(),
            ]);
        }

        if ($request->filled('q')) {
            $term = trim((string)$request->q);
            $q->where(function ($qq) use ($term) {
                $qq->where('contact_number', 'like', "%{$term}%")
                    ->orWhere('snme_number', 'like', "%{$term}%");
            });
        }

        $today = now(config('app.timezone'))->toDateString();

        // ✅ Upcoming first, Past last
        $q->orderByRaw("CASE WHEN reminder_date >= ? THEN 0 ELSE 1 END", [$today])
            ->orderBy('reminder_date', 'asc')
            ->orderBy('reminder_time', 'asc');

        $reminders = $q->paginate(20)->withQueryString();

        return view('installment_reminders.index', compact('reminders'));
    }

    // ✅ Form page
    public function create()
    {
        return view('installment_reminders.create');
    }

    // ✅ Save
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

        if (empty($data['status'])) {
            $data['status'] = 'uploaded';
        }

        InstallmentReminder::create($data);

        return redirect()
            ->route('installment-reminders.index')
            ->with('success', 'Installment reminder created successfully.');
    }

    // ✅ Details page
    public function show(InstallmentReminder $installmentReminder)
    {
        return view('installment_reminders.show', compact('installmentReminder'));
    }

    // ✅ Edit page
    public function edit(InstallmentReminder $installmentReminder)
    {
        return view('installment_reminders.edit', compact('installmentReminder'));
    }

    // ✅ Update
    public function update(Request $request, InstallmentReminder $installmentReminder)
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

        $installmentReminder->update($data);

        return redirect()
            ->route('installment-reminders.index')
            ->with('success', 'Installment reminder updated successfully.');
    }

    // ✅ Delete
    public function destroy(InstallmentReminder $installmentReminder)
    {
        $installmentReminder->delete();

        return redirect()
            ->route('installment-reminders.index')
            ->with('success', 'Installment reminder deleted successfully.');
    }

    // ✅ Optional: only status update (dropdown se)
    public function statusUpdate(Request $request, InstallmentReminder $installmentReminder)
    {
        $data = $request->validate([
            'status' => 'required|string|max:30',
        ]);

        $installmentReminder->update($data);

        return redirect()
            ->back()
            ->with('success', 'Status updated successfully.');
    }

    public function importForm()
    {
        return view('installment_reminders.import');

    }

    public function importStore(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // 10MB
        ]);

        try {
            Excel::import(new InstallmentReminderImport, $request->file('file'));

            return redirect()
                ->route('installment-reminders.index')
                ->with('success', 'Excel imported successfully.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    public function run(Request $request)
    {

        // ✅ Run artisan command
        Artisan::call('app:run-installment-reminders');

        return response()->json([
            'ok' => true,
            'message' => 'Installment Reminder command executed',
            'output' => Artisan::output(),
        ]);
    }


    
    public function driveScanPdf(){
        Artisan::call('app:drive-scan-pdfs');
        return response()->json([
            'ok' => true,
            'message' => 'Drive Scan Command executed',
            'output' => Artisan::output(),
        ]);
    }

    public function sendUploadedInvoice(){
        Artisan::call('app:send-uploaded-invoices');
        return response()->json([
            'ok' => true,
            'message' => 'Send Uploaded Invoices executed',
            'output' => Artisan::output(),
        ]);
    }
}
