<?php

namespace App\Http\Controllers;

use App\Models\BillTemplate;
use App\Models\Business;
use App\Models\BusinessBillTemplateSetting;
use App\Models\BusinessType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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

    public function create()
    {
        $businessTypes = BusinessType::orderBy('name')->get();

        return view('bill_templates.create', compact('businessTypes'));
    }

    // public function store(Request $request)
    // {
    //     $validated = $request->validate([
    //         'name'        => ['required', 'string', 'max:255'],
    //         'page_name'   => ['required', 'string', 'max:255'],
    //         'description' => ['nullable', 'string'],
    //         'preview'     => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
    //     ]);

    //     if ($request->hasFile('preview')) {
    //         $validated['preview'] = $request->file('preview')->store('bill_templates/previews', 'public');
    //     }

    //     BillTemplate::create($validated);

    //     return redirect()
    //         ->route('bill-templates.index')
    //         ->with('success', 'Bill template created successfully.');
    // }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'business_type_id' => [
                'nullable',
                'exists:business_types,id',
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'page_name' => [
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'preview' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,webp,pdf',
                'max:5120',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Preview Upload
        |--------------------------------------------------------------------------
        */
        if ($request->hasFile('preview')) {
            $validated['preview'] = $request
                ->file('preview')
                ->store('bill_templates/previews', 'public');
        }

        /*
        |--------------------------------------------------------------------------
        | Create Bill Template
        |--------------------------------------------------------------------------
        */
        BillTemplate::create([
            'business_type_id' => $validated['business_type_id'],
            'name' => $validated['name'],
            'page_name' => $validated['page_name'],
            'description' => $validated['description'] ?? null,
            'preview' => $validated['preview'] ?? null,
        ]);

        return redirect()
            ->route('bill-templates.index')
            ->with('success', 'Bill template created successfully.');
    }

    public function show($id)
    {
        $billTemplate = BillTemplate::findOrFail($id);

        return view('bill_templates.show', compact('billTemplate'));
    }

    // public function edit($id)
    // {
    //     $billTemplate = BillTemplate::findOrFail($id);

    //     return view('bill_templates.edit', compact('billTemplate'));
    // }


    public function edit($id)
    {
        $billTemplate = BillTemplate::findOrFail($id);

        $businessTypes = BusinessType::orderBy('name')->get();

        return view('bill_templates.edit', compact('billTemplate', 'businessTypes'));
    }

    // public function update(Request $request, $id)
    // {
    //     $billTemplate = BillTemplate::findOrFail($id);

    //     $validated = $request->validate([
    //         'name'        => ['required', 'string', 'max:255'],
    //         'page_name'   => ['required', 'string', 'max:255'],
    //         'description' => ['nullable', 'string'],
    //         'preview'     => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
    //     ]);

    //     if ($request->hasFile('preview')) {
    //         if ($billTemplate->preview && \Storage::disk('public')->exists($billTemplate->preview)) {
    //             Storage::disk('public')->delete($billTemplate->preview);
    //         }

    //         $validated['preview'] = $request->file('preview')->store('bill_templates/previews', 'public');
    //     }

    //     $billTemplate->update($validated);

    //     return redirect()
    //         ->route('bill-templates.index')
    //         ->with('success', 'Bill template updated successfully.');
    // }

    public function update(Request $request, $id)
    {
        $billTemplate = BillTemplate::findOrFail($id);

        $validated = $request->validate([
            'business_type_id' => 'required|exists:business_types,id',
            'name' => 'required|string|max:255',
            'page_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'preview' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
        ]);

        $billTemplate->business_type_id = $validated['business_type_id'];
        $billTemplate->name = $validated['name'];
        $billTemplate->page_name = $validated['page_name'];
        $billTemplate->description = $validated['description'] ?? null;

        if ($request->hasFile('preview')) {
            $billTemplate->preview = $request->file('preview')
                ->store('bill_templates/previews', 'public');
        }

        $billTemplate->save();

        return redirect()
            ->route('bill-templates.index')
            ->with('success', 'Bill template updated successfully.');
    }

    public function destroy($id)
    {
        $billTemplate = BillTemplate::findOrFail($id);
        $billTemplate->delete();

        return redirect()
            ->route('bill-templates.index')
            ->with('success', 'Bill template deleted successfully.');
    }

    public function choose(Request $request)
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
            ->get();

        $businessId = $request->user()->current_business_id
            ?? session('active_business_id')
            ?? $request->user()->businesses()->pluck('businesses.id')->first();

        $business = $businessId ? Business::find($businessId) : null;
        $selectedTemplateId = $business?->pdf_template_id;

        return view('bill_templates.choose', compact(
            'billTemplates',
            'q',
            'business',
            'selectedTemplateId'
        ));
    }


    // public function customize(BillTemplate $template, Request $request)
    // {

    //     $businessId = $request->user()->current_business_id
    //         ?? session('active_business_id')
    //         ?? $request->user()->businesses()->pluck('businesses.id')->first();

    //     $business = $businessId ? Business::find($businessId) : null;

    //     if (!$business) {
    //         return redirect()
    //             ->route('bill-templates.choose')
    //             ->with('error', 'Please select business first.');
    //     }

    //     $setting = BusinessBillTemplateSetting::firstOrCreate(
    //         [
    //             'business_id' => $business->id,
    //             'bill_template_id' => $template->id,
    //         ],
    //         [

    //             'primary_color' => '#d60000',
    //             'secondary_color' => '#dbd9d6',
    //             'text_color' => '#111111',
    //             'font_family' => 'DejaVu Sans',
    //             'show_logo' => true,
    //             'show_tagline' => true,
    //             'show_signature' => true,
    //             'show_terms' => true,
    //         ]
    //     );

    //     return view('invoices.customize.'.$template->page_name, compact(
    //         'template',
    //         'business',
    //         'setting'
    //     ));
    // }


public function customize(BillTemplate $template, Request $request)
{
    $businessId = $request->user()->current_business_id
        ?? session('active_business_id')
        ?? $request->user()->businesses()->pluck('businesses.id')->first();

    $business = $businessId ? Business::find($businessId) : null;

    if (!$business) {
        return redirect()
            ->route('bill-templates.choose')
            ->with('error', 'Please select business first.');
    }

    $setting = BusinessBillTemplateSetting::where('business_id', $business->id)
        ->where('bill_template_id', $template->id)
        ->first();

    $billTemplate = $template;


    return view('invoices.customize.' . $template->page_name, compact(
        'template',
        'billTemplate',
        'business',
        'setting'
    ));
}


    public function saveChosen(Request $request)
    {
        $validated = $request->validate([
            'template_id' => ['required', 'exists:bill_templates,id'],
        ]);

        $businessId = $request->user()->current_business_id
            ?? session('active_business_id')
            ?? $request->user()->businesses()->pluck('businesses.id')->first();

        if (!$businessId) {
            return redirect()
                ->back()
                ->with('error', 'Active business not found.');
        }

        $business = Business::find($businessId);

        if (!$business) {
            return redirect()
                ->back()
                ->with('error', 'Business record not found.');
        }

        $business->pdf_template_id = $validated['template_id'];
        $business->save();

        return redirect()
            ->route('bill-templates.choose')
            ->with('success', 'Bill template selected successfully.');
    }
}