<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
            'name'        => 'required|string|max:255|unique:categories,name',
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
            'name'        => 'required|string|max:255|unique:categories,name,' . $category->id,
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

    /**
     * Optional: Show details of a single category (if you ever need it).
     */
    public function show(Category $category)
    {
        return view('categories.show', compact('category'));
    }
}
