<?php

namespace App\Http\Controllers;

use App\Models\Anniversary;
use Illuminate\Http\Request;

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

    public function update(Request $request, Anniversary $anniversary)
    {
        $data = $request->validate([
            'business_id' => ['nullable', 'integer'],
            'name' => ['nullable', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'date_of_anniversary' => ['required', 'date'],
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
}
