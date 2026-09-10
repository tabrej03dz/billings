<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    /**
     * Category Listing
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $businessId = session('active_business_id')
            ?? $user?->business_id;

        /*
        |--------------------------------------------------------------------------
        | Sirf Main Categories load karenge
        | Aur unke saath sub categories bhi
        |--------------------------------------------------------------------------
        */

        $categories = Category::query()
            ->whereNull('parent_id')
            ->when($businessId, function ($query) use ($businessId) {
                $query->where('business_id', $businessId);
            })
            ->with([
                'children' => function ($query) use ($businessId) {
                    if ($businessId) {
                        $query->where('business_id', $businessId);
                    }

                    $query->orderBy('name');
                }
            ])
            ->latest()
            ->paginate(50);

        /*
        |--------------------------------------------------------------------------
        | Dropdown ke liye Main Categories
        |--------------------------------------------------------------------------
        */

        $parentCategories = Category::query()
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->when($businessId, function ($query) use ($businessId) {
                $query->where('business_id', $businessId);
            })
            ->orderBy('name')
            ->get();

        return view('categories.index', compact(
            'categories',
            'parentCategories'
        ));
    }

    /**
     * Store Category / Sub Category
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $businessId = session('active_business_id')
            ?? $user?->business_id;

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',

                Rule::unique('categories', 'name')
                    ->where(function ($query) use ($businessId, $request) {

                        if ($businessId) {
                            $query->where('business_id', $businessId);
                        }

                        if ($request->filled('parent_id')) {
                            $query->where('parent_id', $request->parent_id);
                        } else {
                            $query->whereNull('parent_id');
                        }
                    }),
            ],

            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id')
                    ->where(function ($query) use ($businessId) {
                        if ($businessId) {
                            $query->where('business_id', $businessId);
                        }
                    }),
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],
        ]);

        $slug = $this->generateUniqueSlug(
            $validated['name'],
            $businessId
        );

        $category = Category::create([
            'business_id' => $businessId,

            'parent_id' => $validated['parent_id'] ?? null,

            'name' => $validated['name'],

            'slug' => $slug,

            'description' => $validated['description'] ?? null,

            'is_active' => $request->boolean('is_active', true),
        ]);

        $category->load('parent');

        if ($request->ajax()) {
            return response()->json([
                'success' => true,

                'message' => $category->parent_id
                    ? 'Sub category created successfully!'
                    : 'Category created successfully!',

                'category' => $category,
            ], 201);
        }

        return redirect()
            ->route('categories.index')
            ->with(
                'success',
                $category->parent_id
                    ? 'Sub category created successfully!'
                    : 'Category created successfully!'
            );
    }

    /**
     * Update Category / Sub Category
     */
    public function update(
        Request $request,
        Category $category
    ) {
        $user = $request->user();

        $businessId = session('active_business_id')
            ?? $user?->business_id;

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',

                Rule::unique('categories', 'name')
                    ->ignore($category->id)
                    ->where(function ($query) use ($businessId, $request) {

                        if ($businessId) {
                            $query->where('business_id', $businessId);
                        }

                        if ($request->filled('parent_id')) {
                            $query->where(
                                'parent_id',
                                $request->parent_id
                            );
                        } else {
                            $query->whereNull('parent_id');
                        }
                    }),
            ],

            'parent_id' => [
                'nullable',
                'integer',

                Rule::exists('categories', 'id')
                    ->where(function ($query) use ($businessId) {
                        if ($businessId) {
                            $query->where('business_id', $businessId);
                        }
                    }),
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Category khud ki parent nahi ban sakti
        |--------------------------------------------------------------------------
        */

        if (
            isset($validated['parent_id']) &&
            (int) $validated['parent_id'] === (int) $category->id
        ) {
            return back()
                ->withErrors([
                    'parent_id' => 'Category cannot be its own parent.',
                ])
                ->withInput();
        }

        $slug = $category->name !== $validated['name']
            ? $this->generateUniqueSlug(
                $validated['name'],
                $businessId,
                $category->id
            )
            : $category->slug;

        $category->update([
            'parent_id' => $validated['parent_id'] ?? null,

            'name' => $validated['name'],

            'slug' => $slug,

            'description' => $validated['description'] ?? null,

            'is_active' => $request->boolean('is_active'),
        ]);

        $category->load('parent');

        if ($request->ajax()) {
            return response()->json([
                'success' => true,

                'message' => 'Category updated successfully!',

                'category' => $category,
            ]);
        }

        return redirect()
            ->route('categories.index')
            ->with(
                'success',
                'Category updated successfully!'
            );
    }

    /**
     * Delete Category
     */
    public function destroy(
        Request $request,
        Category $category
    ) {
        try {

            /*
            |--------------------------------------------------------------------------
            | Agar category ke sub categories hain
            |--------------------------------------------------------------------------
            */

            if ($category->children()->exists()) {

                $message = 'Cannot delete this category because it has sub categories. Delete or move sub categories first.';

                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => $message,
                    ], 422);
                }

                return back()->withErrors($message);
            }

            $category->delete();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Category deleted successfully.',
                ]);
            }

            return back()->with(
                'success',
                'Category deleted successfully.'
            );

        } catch (\Throwable $e) {

            $message = $e->getCode() === '23000'
                ? 'Cannot delete: Category is linked with items.'
                : 'Delete failed: ' . $e->getMessage();

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 422);
            }

            return back()->withErrors($message);
        }
    }

    /**
     * Quick Category Create
     */
    public function quickStore(Request $request)
    {
        $user = $request->user();

        $businessId = session('active_business_id')
            ?? $user?->business_id;

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:150',

                Rule::unique('categories', 'name')
                    ->where(function ($query) use (
                        $businessId,
                        $request
                    ) {
                        if ($businessId) {
                            $query->where(
                                'business_id',
                                $businessId
                            );
                        }

                        if ($request->filled('parent_id')) {
                            $query->where(
                                'parent_id',
                                $request->parent_id
                            );
                        } else {
                            $query->whereNull('parent_id');
                        }
                    }),
            ],

            'parent_id' => [
                'nullable',
                'integer',

                Rule::exists('categories', 'id')
                    ->where(function ($query) use ($businessId) {
                        if ($businessId) {
                            $query->where(
                                'business_id',
                                $businessId
                            );
                        }
                    }),
            ],

            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);

        $category = Category::create([
            'business_id' => $businessId,

            'parent_id' => $validated['parent_id'] ?? null,

            'name' => $validated['name'],

            'slug' => $this->generateUniqueSlug(
                $validated['name'],
                $businessId
            ),

            'description' => $validated['description'] ?? null,

            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,

            'message' => $category->parent_id
                ? 'Sub category successfully created.'
                : 'Category successfully created.',

            'category' => [
                'id' => $category->id,

                'parent_id' => $category->parent_id,

                'name' => $category->name,

                'slug' => $category->slug,
            ],
        ], 201);
    }

    /**
     * Show
     */
    public function show(Category $category)
    {
        $category->load('parent', 'children');

        return view(
            'categories.show',
            compact('category')
        );
    }

    /**
     * Generate Unique Slug
     */
    private function generateUniqueSlug(
        string $name,
        ?int $businessId = null,
        ?int $ignoreId = null
    ): string {
        $baseSlug = Str::slug($name);

        $slug = $baseSlug;

        $counter = 1;

        while (
            Category::query()
                ->when($businessId, function ($query) use ($businessId) {
                    $query->where('business_id', $businessId);
                })
                ->when($ignoreId, function ($query) use ($ignoreId) {
                    $query->where('id', '!=', $ignoreId);
                })
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter;

            $counter++;
        }

        return $slug;
    }
}