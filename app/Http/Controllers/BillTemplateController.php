<?php

namespace App\Http\Controllers;

use App\Models\BillTemplate;
use Illuminate\Http\Request;

class BillTemplateController extends Controller
{
     public function index(Request $request)
    {
        $q = trim((string) $request->get('q'));

        $billTemplates = BillTemplate::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($subQuery) use ($q) {
                    $subQuery->where('name', 'like', "%{$q}%")
                        ->orWhere('page_name', 'like', "%{$q}%")
                        ->orWhere('description', 'like', "%{$q}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('bill_templates.index', compact('billTemplates', 'q'));
    }

    /**
     * Create form
     */
    public function create()
    {
        return view('bill_templates.create');
    }

    /**
     * Store record
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'page_name'   => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        BillTemplate::create($validated);

        return redirect()
            ->route('bill-templates.index')
            ->with('success', 'Bill template created successfully.');
    }

    /**
     * Show single record
     */
    public function show($id)
    {
        $billTemplate = BillTemplate::findOrFail($id);

        return view('bill_templates.show', compact('billTemplate'));
    }

    /**
     * Edit form
     */
    public function edit($id)
    {
        $billTemplate = BillTemplate::findOrFail($id);

        return view('bill_templates.edit', compact('billTemplate'));
    }

    /**
     * Update record
     */
    public function update(Request $request, $id)
    {
        $billTemplate = BillTemplate::findOrFail($id);

        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'page_name'   => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $billTemplate->update($validated);

        return redirect()
            ->route('bill-templates.index')
            ->with('success', 'Bill template updated successfully.');
    }

    /**
     * Delete record
     */
    public function destroy($id)
    {
        $billTemplate = BillTemplate::findOrFail($id);
        $billTemplate->delete();

        return redirect()
            ->route('bill-templates.index')
            ->with('success', 'Bill template deleted successfully.');
    }
}
