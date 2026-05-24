<?php

namespace App\Http\Controllers;

use App\Models\BillTemplate;
use App\Models\BusinessBillTemplateSetting;
use Illuminate\Http\Request;

class BusinessBillTemplateSettingController extends Controller
{







    public function edit(Request $request, BillTemplate $template)
    {
        $businessId = session('active_business_id');

        $setting = BusinessBillTemplateSetting::firstOrCreate(
            [
                'business_id' => $businessId,
                'bill_template_id' => $template->id,
            ],
            [
                'primary_color' => '#d60000',
                'secondary_color' => '#dbd9d6',
                'text_color' => '#111111',
                'font_family' => 'DejaVu Sans',
                'show_logo' => true,
                'show_tagline' => true,
                'show_signature' => true,
                'show_terms' => true,
            ]
        );

        return view('bill_templates.customize', compact('template', 'setting'));
    }

    // public function save(Request $request, BillTemplate $template)
    // {
    //     $data = $request->validate([
    //         'primary_color' => ['required'],
    //         'secondary_color' => ['nullable'],
    //         'text_color' => ['required'],
    //         'font_family' => ['required', 'string', 'max:100'],
    //         'muted_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
    //         'border_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
    //         'light_bg_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
    //         'soft_bg_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
    //     ]);

    //     BusinessBillTemplateSetting::updateOrCreate(
    //         [
    //             'business_id' => session('active_business_id'),
    //             'bill_template_id' => $template->id,
    //         ],
    //         [
    //             'primary_color' => $data['primary_color'],
    //             'secondary_color' => $data['secondary_color'],
    //             'text_color' => $data['text_color'],
    //             'font_family' => $data['font_family'],
    //             'show_logo' => $request->boolean('show_logo'),
    //             'show_tagline' => $request->boolean('show_tagline'),
    //             'show_signature' => $request->boolean('show_signature'),
    //             'show_terms' => $request->boolean('show_terms'),

    //             'muted_color' => $data['muted_color'],
    //             'border_color' => $data['border_color'],
    //             'light_bg_color' => $data['light_bg_color'],
    //             'soft_bg_color' => $data['soft_bg_color'],
              
    //         ]
    //     );

    //     return back()->with('success', 'Template customization saved successfully.');
    // }

    public function save(Request $request, BillTemplate $template)
    {
        $data = $request->validate([
            'primary_color' => ['required'],
            'secondary_color' => ['nullable'],
            'text_color' => ['required'],
            'font_family' => ['required', 'string', 'max:100'],
            'muted_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'border_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'light_bg_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'soft_bg_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        BusinessBillTemplateSetting::updateOrCreate(
            [
                'business_id' => session('active_business_id'),
                'bill_template_id' => $template->id,
            ],
            [
                'primary_color' => $data['primary_color'],
                'secondary_color' => $data['secondary_color'] ?? null,
                'text_color' => $data['text_color'],
                'font_family' => $data['font_family'],

                'show_logo' => $request->boolean('show_logo'),
                'show_tagline' => $request->boolean('show_tagline'),
                'show_signature' => $request->boolean('show_signature'),
                'show_terms' => $request->boolean('show_terms'),

                'muted_color' => $data['muted_color'] ?? null,
                'border_color' => $data['border_color'] ?? null,
                'light_bg_color' => $data['light_bg_color'] ?? null,
                'soft_bg_color' => $data['soft_bg_color'] ?? null,
            ]
        );

        return back()->with('success', 'Template customization saved successfully.');
    }

    public function resetCustomize(Request $request, BillTemplate $template)
    {
        $businessId = $request->user()->current_business_id
            ?? session('active_business_id')
            ?? $request->user()->businesses()->pluck('businesses.id')->first();

        if (!$businessId) {
            return back()->with('error', 'Business not found.');
        }

        \App\Models\BusinessBillTemplateSetting::where('business_id', $businessId)
            ->where('bill_template_id', $template->id)
            ->delete();

        return redirect()
            ->back()
            ->with('success', 'Template reset successfully.');
    }
}
