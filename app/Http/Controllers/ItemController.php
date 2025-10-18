<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $q           = trim($request->get('q', ''));
        $category_id = $request->integer('category_id');
        $active      = $request->get('active'); // '1' | '0' | null

        $items = Item::query()
            ->with('category:id,name') // eager-load for table
            ->when($q !== '', function ($w) use ($q) {
                $w->where(function ($s) use ($q) {
                    $s->where('name', 'like', "%{$q}%")
                        ->orWhere('sku', 'like', "%{$q}%")
                        ->orWhere('description', 'like', "%{$q}%");
                });
            })
            ->when($category_id, fn($w) => $w->where('category_id', $category_id))
            ->when($active !== null && $active !== '', fn($w) => $w->where('is_active', (bool)$active))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        // current business ki categories (BelongsToBusiness scope ke sath)
        $categories = Category::orderBy('name')->get(['id','name']);

        return view('items.index', compact('items', 'categories', 'q', 'category_id', 'active'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get(['id','name']);
        return view('items.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $bid = $request->user()->current_business_id ?? session('active_business_id');

        $data = $request->validate([
            'name'        => ['required','string','max:255'],
            'sku'         => [
                'nullable','string','max:100',
                Rule::unique('items','sku')->where(fn($q)=>$q->where('business_id',$bid)),
            ],
            'category_id' => ['nullable','integer','exists:categories,id'],
            'description' => ['nullable','string','max:2000'],
            'price'       => ['required','numeric','min:0'],
            'making_charge'=> ['nullable','numeric','min:0'],
            'cost_price'  => ['nullable','numeric','min:0'],
            'stock_qty'   => ['required','integer','min:0'],
            'unit'        => ['nullable','string','max:50'],
            'tax_rate'    => ['required','numeric','min:0','max:100'],
            'is_active'   => ['nullable','boolean'],

        ]);

        // ensure category current business ka hi ho (optional but safer)
        if (!empty($data['category_id'])) {
            $ok = Category::where('id',$data['category_id'])->exists(); // scoped by trait to active business
            abort_unless($ok, 422, 'Invalid category for this business.');
        }

        $data['business_id'] = $bid;
        $data['is_active']   = $request->boolean('is_active');

        Item::create($data);

        return redirect()->route('items.index')->with('success','Item created successfully.');
    }

    public function edit(Item $item)
    {
        // Global scope ensures this $item belongs to active business
        $categories = Category::orderBy('name')->get(['id','name']);
        return view('items.edit', compact('item','categories'));
    }

    public function update(Request $request, Item $item)
    {
        $bid = $request->user()->current_business_id ?? session('active_business_id');

        $data = $request->validate([
            'name'        => ['required','string','max:255'],
            'sku'         => [
                'nullable','string','max:100',
                Rule::unique('items','sku')
                    ->ignore($item->id)
                    ->where(fn($q)=>$q->where('business_id',$bid)),
            ],
            'category_id' => ['nullable','integer','exists:categories,id'],
            'description' => ['nullable','string','max:2000'],
            'price'       => ['required','numeric','min:0'],
            'making_charge'=> ['nullable','numeric','min:0'],
            'cost_price'  => ['nullable','numeric','min:0'],
            'stock_qty'   => ['required','integer','min:0'],
            'unit'        => ['nullable','string','max:50'],
            'tax_rate'    => ['required','numeric','min:0','max:100'],
            'is_active'   => ['nullable','boolean'],
        ]);

        if (!empty($data['category_id'])) {
            $ok = Category::where('id',$data['category_id'])->exists(); // scoped to active business
            abort_unless($ok, 422, 'Invalid category for this business.');
        }

        $data['is_active'] = $request->boolean('is_active');

        $item->update($data);

        return redirect()->route('items.index')->with('success','Item updated successfully.');
    }

    public function destroy(Item $item)
    {
        $item->delete();
        return redirect()->route('items.index')->with('success','Item deleted successfully.');
    }

    public function show(\App\Models\Item $item)
    {
        // BelongsToBusiness scope ensure same business
        return response()->json([
            'id' => $item->id,
            'name' => $item->name,
            'sku' => $item->sku,
            'price' => (float)$item->price,
            'tax_rate' => (float)$item->tax_rate,
            'description' => $item->description,
        ]);
    }

}
