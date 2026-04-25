<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BillTemplate;
use App\Models\Business;
use Illuminate\Http\Request;

class BillTemplateController extends Controller
{
    public function apiChoose(Request $request)
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
            ?? $request->get('business_id')
            ?? $request->user()->businesses()->pluck('businesses.id')->first();

        $business = $businessId ? Business::find($businessId) : null;

        return response()->json([
            'status' => true,
            'message' => 'Bill templates fetched successfully.',
            'data' => [
                'business' => $business,
                'selected_template_id' => $business?->pdf_template_id,
                'templates' => $billTemplates,
            ],
        ]);
    }

    public function apiSaveChosen(Request $request)
    {
        $validated = $request->validate([
            'template_id' => ['required', 'exists:bill_templates,id'],
            'business_id' => ['nullable', 'exists:businesses,id'],
        ]);

        $businessId = $request->user()->current_business_id
            ?? $validated['business_id'] ?? null
            ?? $request->user()->businesses()->pluck('businesses.id')->first();

        if (!$businessId) {
            return response()->json([
                'status' => false,
                'message' => 'Active business not found.',
            ], 404);
        }

        $business = Business::find($businessId);

        if (!$business) {
            return response()->json([
                'status' => false,
                'message' => 'Business record not found.',
            ], 404);
        }

        $business->pdf_template_id = $validated['template_id'];
        $business->save();

        return response()->json([
            'status' => true,
            'message' => 'Bill template selected successfully.',
            'data' => [
                'business_id' => $business->id,
                'selected_template_id' => $business->pdf_template_id,
            ],
        ]);
    }
}
