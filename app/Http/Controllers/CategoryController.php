<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::latest()->paginate(50); // ya ->get()
        return view('categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new category.
     */


    /**
     * Store a newly created category in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
        ]);

        $category = \App\Models\Category::create([
            ...$validated,
            'slug' => \Illuminate\Support\Str::slug($validated['name']),
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Category created successfully!',
                'category' => $category
            ]);
        }

        return redirect()->route('categories.index')->with('success', 'Category created successfully!');
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
        ]);

        $category->update([
            ...$validated,
            'slug' => \Illuminate\Support\Str::slug($validated['name']),
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully!',
                'category' => $category
            ]);
        }

        return redirect()->route('categories.index')->with('success', 'Category updated successfully!');
    }


    /**
     * Remove the specified category from storage.
     */
    // app/Http/Controllers/CategoryController.php
    public function destroy(\Illuminate\Http\Request $request, \App\Models\Category $category)
    {
        try {
            $category->delete(); // FK issue ho to yahin exception aayega

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Category deleted.',
                ]);
            }

            return back()->with('success', 'Category deleted.');
        } catch (\Throwable $e) {
            // FK constraint / koi aur error
            $msg = $e->getCode() === '23000'
                ? 'Cannot delete: Category linked with items.'
                : ('Delete failed: ' . $e->getMessage());

            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->withErrors($msg);
        }
    }

    
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
                    ->where(function ($query) use ($businessId) {
                        if ($businessId) {
                            $query->where('business_id', $businessId);
                        }
                    }),
            ],

            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);

        $categoryData = [
            'name' => $validated['name'],

            'description' => $validated['description'] ?? null,

            'is_active' => true,
        ];

        /*
        |--------------------------------------------------------------------------
        | Multi-business software
        |--------------------------------------------------------------------------
        | Agar categories table me business_id column hai, tab ye add hoga.
        */

        if ($businessId) {
            $categoryData['business_id'] = $businessId;
        }

        /*
        |--------------------------------------------------------------------------
        | User ID
        |--------------------------------------------------------------------------
        | Agar categories table me created_by column hai tab hi ise rakhein.
        */

        // $categoryData['created_by'] = $user->id;

        $category = Category::create($categoryData);

        return response()->json([
            'success' => true,

            'message' => 'Category successfully created.',

            'category' => [
                'id' => $category->id,
                'name' => $category->name,
            ],
        ], 201);
    }

    /**
     * Optional: Show details of a single category (if you ever need it).
     */
    public function show(Category $category)
    {
        return view('categories.show', compact('category'));
    }
}
