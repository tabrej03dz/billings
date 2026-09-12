<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    // public function index(Request $request)
    // {
    //     $q       = trim((string) $request->query('q', ''));
    //     $active  = $request->query('active'); // '1' | '0' | null
    //     $perPage = (int) $request->query('per_page', 50);
    //     $perPage = max(1, min($perPage, 200));

    //     // ✅ API-friendly business resolve
    //     $user = $request->user();

    //     $bid = (int) ($request->header('X-Business-Id')
    //         ?? $request->query('business_id')
    //         ?? $user?->current_business_id
    //         ?? 0);

    //     if (!$bid) {
    //         return response()->json([
    //             'ok' => false,
    //             'message' => 'Business not resolved. Send X-Business-Id header or business_id query param.',
    //         ], 422);
    //     }

    //     $categories = Category::query()
    //         ->where('business_id', $bid) // ✅ IMPORTANT
    //         ->when($q !== '', function ($w) use ($q) {
    //             $w->where(function ($s) use ($q) {
    //                 $s->where('name', 'like', "%{$q}%")
    //                     ->orWhere('description', 'like', "%{$q}%")
    //                     ->orWhere('slug', 'like', "%{$q}%");
    //             });
    //         })
    //         ->when($active !== null && $active !== '', fn ($w) => $w->where('is_active', (int)$active))
    //         ->latest()
    //         ->paginate($perPage);

    //     return response()->json([
    //         'ok' => true,
    //         'q' => $q,
    //         'active' => $active,
    //         'business_id' => $bid,
    //         'data' => $categories,
    //     ]);
    // }


    public function index(Request $request)
    {
        $q       = trim((string) $request->query('q', ''));
        $active  = $request->query('active'); // '1' | '0' | null
        $perPage = (int) $request->query('per_page', 50);
        $perPage = max(1, min($perPage, 200));

        // API-friendly business resolve
        $user = $request->user();

        $bid = (int) (
            $request->header('X-Business-Id')
            ?? $request->query('business_id')
            ?? $user?->current_business_id
            ?? 0
        );

        if (!$bid) {
            return response()->json([
                'ok' => false,
                'message' => 'Business not resolved. Send X-Business-Id header or business_id query param.',
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Main Categories + Sub Categories
        |--------------------------------------------------------------------------
        |
        | Sirf parent categories paginate hongi.
        | Unke andar children relation me sub categories aayengi.
        |
        */

        $categories = Category::query()

            // Sirf main categories
            ->whereNull('parent_id')

            // Current business
            ->where('business_id', $bid)

            // Parent + Children relations
            ->with([

                'parent',

                'children' => function ($query) use ($bid, $active, $q) {

                    $query->where('business_id', $bid);

                    // Active filter children par bhi
                    if ($active !== null && $active !== '') {
                        $query->where('is_active', (int) $active);
                    }

                    /*
                    * Search diya ho to children ko bhi filter karenge.
                    *
                    * Agar aap chahte ho search ke time parent ki sari
                    * subcategories aaye to ye $q wala block hata sakte ho.
                    */
                    if ($q !== '') {
                        $query->where(function ($search) use ($q) {
                            $search->where('name', 'like', "%{$q}%")
                                ->orWhere('description', 'like', "%{$q}%")
                                ->orWhere('slug', 'like', "%{$q}%");
                        });
                    }

                    $query->orderBy('name');
                },

            ])

            /*
            |--------------------------------------------------------------------------
            | Search
            |--------------------------------------------------------------------------
            |
            | Parent category match kare
            | YA uski koi sub category match kare.
            |
            */

            ->when($q !== '', function ($query) use ($q, $bid) {

                $query->where(function ($search) use ($q, $bid) {

                    $search
                        ->where('name', 'like', "%{$q}%")
                        ->orWhere('description', 'like', "%{$q}%")
                        ->orWhere('slug', 'like', "%{$q}%")

                        // Sub category search
                        ->orWhereHas('children', function ($child) use ($q, $bid) {

                            $child->where('business_id', $bid)
                                ->where(function ($childSearch) use ($q) {
                                    $childSearch
                                        ->where('name', 'like', "%{$q}%")
                                        ->orWhere('description', 'like', "%{$q}%")
                                        ->orWhere('slug', 'like', "%{$q}%");
                                });

                        });

                });

            })

            // Parent active filter
            ->when(
                $active !== null && $active !== '',
                fn ($query) => $query->where('is_active', (int) $active)
            )

            ->latest()

            ->paginate($perPage);


        /*
        |--------------------------------------------------------------------------
        | Response Structure Same
        |--------------------------------------------------------------------------
        */

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
            'name'        => ['required','string','max:255'],
            'description' => ['nullable','string'],
            'is_active'   => ['sometimes','boolean'],
            'business_id' => ['required', 'integer'],
            'parent_id'   => ['nullable', 'integer', Rule::exists('categories', 'id')],
        ]);

        // default if not sent
        $data['is_active'] = (bool)($data['is_active'] ?? true);

        $category = Category::create([
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'is_active'   => $data['is_active'],
            'slug'        => Str::slug($data['name']),
            'business_id' => $data['business_id'],
            'parent_id'   => $data['parent_id'] ?? null,
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
            'name'        => ['required','string','max:255'],
            'description' => ['nullable','string'],
            'is_active'   => ['sometimes','boolean'],
            'business_id' => ['required', 'integer'],
            'parent_id'   => ['nullable', 'integer', Rule::exists('categories', 'id')],
        ]);

        $category->update([
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'is_active'   => array_key_exists('is_active', $data) ? (bool)$data['is_active'] : $category->is_active,
            'slug'        => Str::slug($data['name']),
            'business_id' => $data['business_id'],
            'parent_id'   => $data['parent_id'] ?? null,
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
