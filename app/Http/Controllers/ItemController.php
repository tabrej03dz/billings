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
        if (!$bid) {
            $bid = $request->user()->businesses()->pluck('businesses.id')->first();
        }
        abort_unless($bid, 422, 'Active business not found.');

        $data = $request->validate([
            'name'          => ['required','string','max:255'],

            'sku'           => [
                'nullable','string','max:100',
                Rule::unique('items','sku')->where(fn($q) => $q->where('business_id', $bid)),
            ],

            'category_id'   => ['nullable','integer'],
            'sac'           => ['nullable','string','max:32'],

            'description'   => ['nullable','string','max:2000'],

            'price'         => ['nullable','numeric','min:0'],
            'cost_price'    => ['nullable','numeric','min:0'],
            'making_charge' => ['nullable','numeric','min:0'],

            'stock_qty'     => ['required','integer','min:0'],
            'unit'          => ['nullable','string','max:50'],
            'weight'        => ['nullable','numeric','min:0'],

            'tax_rate'      => ['required','numeric','min:0','max:100'],
            'is_active'     => ['nullable'],

            // old metal fields
            'metal_type'    => ['nullable', Rule::in(['gold','silver','other'])],
            'purity'        => ['nullable','string','max:50'],

            'gross_weight'  => ['nullable','numeric','min:0'],
            'metal_weight'  => ['nullable','numeric','min:0'],
            'stone_weight'  => ['nullable','numeric','min:0'],
            'stone_charges' => ['nullable','numeric','min:0'],

            // ✅ new columns you added
            'gold_weight'     => ['nullable','numeric','min:0'],
            'gold_purity'     => ['nullable','string','max:50'],
            'silver_weight'   => ['nullable','numeric','min:0'],
            'silver_purity'   => ['nullable','string','max:50'],
            'diamond_weight'  => ['nullable','numeric','min:0'],
            'diamond_charges' => ['nullable','numeric','min:0'],
        ]);

        // category business-scope check (important)
        if (!empty($data['category_id'])) {
            $ok = Category::where('id', $data['category_id'])
                ->where('business_id', $bid)
                ->exists();
            abort_unless($ok, 422, 'Invalid category for this business.');
        }

        // assign fixed fields
        $data['business_id'] = $bid;
        $data['is_active']   = $request->boolean('is_active');

        Item::create($data);

        return redirect()
            ->route('items.index')
            ->with('success', 'Item created successfully.');
    }

    public function edit(Item $item)
    {
        // Global scope ensures this $item belongs to active business
        $categories = Category::orderBy('name')->get(['id','name']);
        return view('items.edit', compact('item','categories'));
    }

//    public function update(Request $request, Item $item)
//    {
//        $bid = $request->user()->current_business_id ?? session('active_business_id');
//
//        $data = $request->validate([
//            'name'        => ['required','string','max:255'],
//            'sku'         => [
//                'nullable','string','max:100',
//                Rule::unique('items','sku')
//                    ->ignore($item->id)
//                    ->where(fn($q)=>$q->where('business_id',$bid)),
//            ],
//            'category_id' => ['nullable','integer','exists:categories,id'],
//            'description' => ['nullable','string','max:2000'],
//            'price'       => ['nullable','numeric','min:0'],
//            'making_charge'=> ['nullable','numeric','min:0'],
//            'cost_price'  => ['nullable','numeric','min:0'],
//            'stock_qty'   => ['required','integer','min:0'],
//            'unit'        => ['nullable','string','max:50'],
//            'tax_rate'    => ['required','numeric','min:0','max:100'],
//            'is_active'   => ['nullable','boolean'],
//            'metal_weight' => '',
//            'metal_type' => '',
//            'purity' => '',
//            'gross_weight' => '',
//            'stone_weight' => '',
//            'stone_charges' => '',
//        ]);
//
//        if (!empty($data['category_id'])) {
//            $ok = Category::where('id',$data['category_id'])->exists(); // scoped to active business
//            abort_unless($ok, 422, 'Invalid category for this business.');
//        }
//
//        $data['is_active'] = $request->boolean('is_active');
//
//        $item->update($data);
//
//        return redirect()->route('items.index')->with('success','Item updated successfully.');
//    }

    public function update(Request $request, Item $item)
    {
        $bid = $request->user()->current_business_id ?? session('active_business_id');
        if (!$bid) {
            $bid = $request->user()->businesses()->pluck('businesses.id')->first();
        }
        abort_unless($bid, 422, 'Active business not found.');

        // Safety: ensure item belongs to same business
        abort_unless((int)$item->business_id === (int)$bid, 403, 'Unauthorized item.');

        $data = $request->validate([
            'name'          => ['required','string','max:255'],

            'sku'           => [
                'nullable','string','max:100',
                Rule::unique('items','sku')
                    ->ignore($item->id)
                    ->where(fn($q) => $q->where('business_id', $bid)),
            ],

            'category_id'   => ['nullable','integer'],
            'sac'           => ['nullable','string','max:32'],

            'description'   => ['nullable','string','max:2000'],

            'price'         => ['nullable','numeric','min:0'],
            'making_charge' => ['nullable','numeric','min:0'],
            'cost_price'    => ['nullable','numeric','min:0'],

            'stock_qty'     => ['required','integer','min:0'],
            'unit'          => ['nullable','string','max:50'],
            'weight'        => ['nullable','numeric','min:0'],

            'tax_rate'      => ['required','numeric','min:0','max:100'],
            'is_active'     => ['nullable'],

            // old metal fields (optional)
            'metal_type'    => ['nullable', Rule::in(['gold','silver','other'])],
            'purity'        => ['nullable','string','max:50'],

            'gross_weight'  => ['nullable','numeric','min:0'],
            'metal_weight'  => ['nullable','numeric','min:0'],
            'stone_weight'  => ['nullable','numeric','min:0'],
            'stone_charges' => ['nullable','numeric','min:0'],

            // ✅ new columns
            'gold_weight'     => ['nullable','numeric','min:0'],
            'gold_purity'     => ['nullable','string','max:50'],
            'silver_weight'   => ['nullable','numeric','min:0'],
            'silver_purity'   => ['nullable','string','max:50'],
            'diamond_weight'  => ['nullable','numeric','min:0'],
            'diamond_charges' => ['nullable','numeric','min:0'],
        ]);

        // category business-scope check
        if (!empty($data['category_id'])) {
            $ok = Category::where('id', $data['category_id'])
                ->where('business_id', $bid)
                ->exists();
            abort_unless($ok, 422, 'Invalid category for this business.');
        }

        $data['is_active'] = $request->boolean('is_active');

        $item->update($data);

        return redirect()
            ->route('items.index')
            ->with('success', 'Item updated successfully.');
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
