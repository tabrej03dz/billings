<?php

namespace App\Http\Controllers;

use App\Models\DemoRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class DemoRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = DemoRequest::query()->with('updatedBy');

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('business_name', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%")
                    ->orWhere('note', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $demoRequests = $query->latest()->paginate(20);

        return view('demo_requests.index', compact('demoRequests'));
    }

    public function create()
    {
        return view('demo_requests.create');
    }

    // public function store(Request $request)
    // {
    //    $validated = $request->validate([
    //         'name'          => 'required|string|max:255',
    //         'mobile'        => [
    //             'required',
    //             'digits:10',
    //             'regex:/^[6-9][0-9]{9}$/',
    //         ],
    //         'city'          => 'nullable|string|max:255',
    //         'business_name' => 'nullable|string|max:255',
    //         'message'       => 'nullable|string',
    //     ], [
    //         'mobile.required' => 'Mobile number required hai.',
    //         'mobile.digits'   => 'Mobile number 10 digit ka hona chahiye.',
    //         'mobile.regex'    => 'Mobile number 6, 7, 8 ya 9 se start hona chahiye.',
    //     ]);

    //     DemoRequest::create($validated);

    //       return redirect()
    //         ->to(url()->previous() . '#contact')
    //         ->with('success', 'Demo request successfully submit ho gayi.');
    // }


public function store(Request $request)
{
    $validated = $request->validate([
        'name'          => 'required|string|max:255',
        'mobile'        => [
            'required',
            'digits:10',
            'regex:/^[6-9][0-9]{9}$/',
        ],
        'city'          => 'nullable|string|max:255',
        'business_name' => 'nullable|string|max:255',
        'message'       => 'nullable|string',
    ], [
        'mobile.required' => 'Mobile number required hai.',
        'mobile.digits'   => 'Mobile number 10 digit ka hona chahiye.',
        'mobile.regex'    => 'Mobile number 6, 7, 8 ya 9 se start hona chahiye.',
    ]);

    $demoRequest = DemoRequest::create($validated);

    $mailBody =
        "New Demo Request Aayi Hai\n\n" .
        "Name: " . $demoRequest->name . "\n" .
        "Mobile: " . $demoRequest->mobile . "\n" .
        "City: " . ($demoRequest->city ?? 'N/A') . "\n" .
        "Business Name: " . ($demoRequest->business_name ?? 'N/A') . "\n" .
        "Message: " . ($demoRequest->message ?? 'N/A') . "\n";

    $superAdminEmails = User::role('super admin')
        ->whereNotNull('email')
        ->pluck('email')
        ->toArray();

    if (!empty($superAdminEmails)) {
        Mail::raw($mailBody, function ($message) use ($superAdminEmails) {
            $message->to($superAdminEmails)
                ->subject('New Demo Request Received');
        });
    }

    return redirect()
        ->to(url()->previous() . '#contact')
        ->with('success', 'Demo request successfully submit ho gayi.');
}

    public function show(DemoRequest $demoRequest)
    {
        $demoRequest->load('updatedBy');

        return view('demo_requests.show', compact('demoRequest'));
    }

    public function edit(DemoRequest $demoRequest)
    {
        return view('demo_requests.edit', compact('demoRequest'));
    }

    public function update(Request $request, DemoRequest $demoRequest)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'mobile'        => [
                'required',
                'digits:10',
                'regex:/^[6-9][0-9]{9}$/',
            ],
            'city'          => 'nullable|string|max:255',
            'business_name' => 'nullable|string|max:255',
            'message'       => 'nullable|string',
            'status'        => 'required|in:pending,contacted,converted,rejected',
            'note'          => 'nullable|string',
        ], [
            'mobile.required' => 'Mobile number required hai.',
            'mobile.digits'   => 'Mobile number 10 digit ka hona chahiye.',
            'mobile.regex'    => 'Mobile number 6, 7, 8 ya 9 se start hona chahiye.',
            'status.required' => 'Status select karna zaroori hai.',
            'status.in'       => 'Invalid status selected.',
        ]);

        $validated['updated_by'] = auth()->id();

        $demoRequest->update($validated);

        return redirect()
            ->route('demo-requests.index')
            ->with('success', 'Demo request updated successfully.');
    }

    public function destroy(DemoRequest $demoRequest)
    {
        $demoRequest->delete();

        return back()->with('success', 'Demo request deleted successfully.');
    }

    public function updateStatus(Request $request, DemoRequest $demoRequest)
    {
        $request->validate([
            'status' => 'required|in:pending,contacted,converted,rejected',
            'note'   => 'nullable|string',
        ]);

        $demoRequest->update([
            'status'     => $request->status,
            'note'       => $request->note,
            'updated_by' => auth()->id(),
        ]);

        return back()->with('success', 'Status updated successfully.');
    }
}
