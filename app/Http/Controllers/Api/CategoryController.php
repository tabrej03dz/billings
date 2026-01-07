<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $q       = trim((string) $request->query('q', ''));
        $active  = $request->query('active'); // '1' | '0' | null
        $perPage = (int) $request->query('per_page', 50);
        $perPage = max(1, min($perPage, 200));

        // ✅ API-friendly business resolve
        $user = $request->user();

        $bid = (int) ($request->header('X-Business-Id')
            ?? $request->query('business_id')
            ?? $user?->current_business_id
            ?? 0);

        if (!$bid) {
            return response()->json([
                'ok' => false,
                'message' => 'Business not resolved. Send X-Business-Id header or business_id query param.',
            ], 422);
        }

        $categories = Category::query()
            ->where('business_id', $bid) // ✅ IMPORTANT
            ->when($q !== '', function ($w) use ($q) {
                $w->where(function ($s) use ($q) {
                    $s->where('name', 'like', "%{$q}%")
                        ->orWhere('description', 'like', "%{$q}%")
                        ->orWhere('slug', 'like', "%{$q}%");
                });
            })
            ->when($active !== null && $active !== '', fn ($w) => $w->where('is_active', (int)$active))
            ->latest()
            ->paginate($perPage);

        return response()->json([
            'ok' => true,
            'q' => $q,
            'active' => $active,
            'business_id' => $bid,
            'data' => $categories,
        ]);
    }


    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => ['required','string','max:255', Rule::unique('categories','name')],
            'description' => ['nullable','string'],
            'is_active'   => ['sometimes','boolean'],
            'business_id' => ['required', 'integer'],
        ]);

        // default if not sent
        $data['is_active'] = (bool)($data['is_active'] ?? true);

        $category = Category::create([
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'is_active'   => $data['is_active'],
            'slug'        => Str::slug($data['name']),
            'business_id' => $data['business_id'],
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Category created successfully!',
            'category' => $category,
        ], 201);
    }


    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name'        => ['required','string','max:255', Rule::unique('categories','name')->ignore($category->id)],
            'description' => ['nullable','string'],
            'is_active'   => ['sometimes','boolean'],
            'business_id' => ['required', 'integer'],
        ]);

        $category->update([
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'is_active'   => array_key_exists('is_active', $data) ? (bool)$data['is_active'] : $category->is_active,
            'slug'        => Str::slug($data['name']),
            'business_id' => $data['business_id'],
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Category updated successfully!',
            'category' => $category->fresh(),
        ]);
    }

    public function destroy(Request $request, Category $category)
    {
        try {
            $category->delete();

            return response()->json([
                'ok' => true,
                'message' => 'Category deleted.',
            ]);
        } catch (\Throwable $e) {
            $msg = ((string)$e->getCode() === '23000')
                ? 'Cannot delete: Category linked with items.'
                : ('Delete failed: ' . $e->getMessage());

            return response()->json([
                'ok' => false,
                'message' => $msg,
            ], 422);
        }
    }
}
