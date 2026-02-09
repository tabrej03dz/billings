@props(['business' => null])

@php
    $isEdit = filled($business?->id);


    $states = [
        ['code'=>'01','name'=>'Jammu and Kashmir'],
        ['code'=>'02','name'=>'Himachal Pradesh'],
        ['code'=>'03','name'=>'Punjab'],
        ['code'=>'04','name'=>'Chandigarh'],
        ['code'=>'05','name'=>'Uttarakhand'],
        ['code'=>'06','name'=>'Haryana'],
        ['code'=>'07','name'=>'Delhi'],
        ['code'=>'08','name'=>'Rajasthan'],
        ['code'=>'09','name'=>'Uttar Pradesh'],
        ['code'=>'10','name'=>'Bihar'],
        ['code'=>'11','name'=>'Sikkim'],
        ['code'=>'12','name'=>'Arunachal Pradesh'],
        ['code'=>'13','name'=>'Nagaland'],
        ['code'=>'14','name'=>'Manipur'],
        ['code'=>'15','name'=>'Mizoram'],
        ['code'=>'16','name'=>'Tripura'],
        ['code'=>'17','name'=>'Meghalaya'],
        ['code'=>'18','name'=>'Assam'],
        ['code'=>'19','name'=>'West Bengal'],
        ['code'=>'20','name'=>'Jharkhand'],
        ['code'=>'21','name'=>'Odisha'],
        ['code'=>'22','name'=>'Chhattisgarh'],
        ['code'=>'23','name'=>'Madhya Pradesh'],
        ['code'=>'24','name'=>'Gujarat'],
        ['code'=>'26','name'=>'Dadra and Nagar Haveli and Daman and Diu'],
        ['code'=>'27','name'=>'Maharashtra'],
        ['code'=>'29','name'=>'Karnataka'],
        ['code'=>'30','name'=>'Goa'],
        ['code'=>'31','name'=>'Lakshadweep'],
        ['code'=>'32','name'=>'Kerala'],
        ['code'=>'33','name'=>'Tamil Nadu'],
        ['code'=>'34','name'=>'Puducherry'],
        ['code'=>'35','name'=>'Andaman and Nicobar Islands'],
        ['code'=>'36','name'=>'Telangana'],
        ['code'=>'37','name'=>'Andhra Pradesh'],
        ['code'=>'38','name'=>'Ladakh'],
    ];

 $selectedState = old('state');
    if (!$selectedState && !empty($business?->state_code) && !empty($business?->state)) {
        $selectedState = $business->state_code . ',' . $business->state; // "09,Uttar Pradesh"
    }

    $businessTypes = [
        'jewellery'     => 'Jewellery',
        'retail'        => 'Retail',
        'wholesale'     => 'Wholesale',
        'manufacturer'  => 'Manufacturer',
        'service'       => 'Service Provider',
        'trading'       => 'Trading',
        'ecommerce'     => 'E-Commerce',
        'agency'        => 'Agency',
        'other'         => 'Other',
    ];

    $selectedBusinessType = old('type', $business->type ?? '');
@endphp

<div class="space-y-6">
    <div class="grid md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1">Name <span class="text-red-600">*</span></label>
            <input type="text" name="name" value="{{ old('name', $business->name ?? '') }}"
                   class="mt-1 w-full border rounded px-3 py-2 bg-slate-200 dark:bg-[#242833]"
                   required>
            @error('name') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Slug (optional)</label>
            <input type="text" name="slug" value="{{ old('slug', $business->slug ?? '') }}"
                   placeholder="auto-from-name if left blank"
                   class="mt-1 w-full border rounded px-3 py-2 bg-slate-200 dark:bg-[#242833]">
            @error('slug') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Invoice Base Prefix</label>
            <input type="text" name="invoice_base_prefix" value="{{ old('invoice_base_prefix', $business->invoice_base_prefix ?? '') }}"
                   placeholder="RV/SL"
                   class="mt-1 w-full border rounded px-3 py-2 bg-slate-200 dark:bg-[#242833]">
            @error('invoice_base_prefix') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Email <span class="text-red-600">*</span></label>
            <input type="email" name="email" value="{{ old('email', $business->email ?? '') }}"
                   class="mt-1 w-full border rounded px-3 py-2 bg-slate-200 dark:bg-[#242833]" required>
            @error('email') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Mobile </label>
            <input type="text" name="mobile" value="{{ old('mobile', $business->mobile ?? '') }}"
                   class="mt-1 w-full border rounded px-3 py-2 bg-slate-200 dark:bg-[#242833]" >
            @error('mobile') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">GSTIN</label>
            <input type="text" name="gstin" value="{{ old('gstin', $business->gstin ?? '') }}"
                   class="mt-1 w-full border rounded px-3 py-2 bg-slate-200 dark:bg-[#242833]">
            @error('gstin') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">
                Business Type <span class="text-red-600">*</span>
            </label>

            <select
                name="type"
                required
                class="mt-1 w-full border rounded px-3 py-2
               bg-gray-300 dark:bg-[#242833] text-gray-400 border-gray-600
               focus:border-blue-500 focus:ring-blue-500"
            >
                <option value="" class="bg-gray-300 dark:bg-[#242833] text-gray-400">
                    -- Select Business Type --
                </option>

                @foreach($businessTypes as $key => $label)
                    <option value="{{ $key }}"
                            class="bg-gray-300 dark:bg-[#242833] text-gray-400"
                        {{ $selectedBusinessType === $key ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>

            @error('type')
            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">
                State (GST Code) <span class="text-red-600">*</span>
            </label>

            <select
                name="state"
                class="mt-1 w-full border rounded px-3 py-2 bg-gray-300 dark:bg-[#242833] text-gray-400 border-gray-600
           focus:border-blue-500 focus:ring-blue-500"
                required
            >
                <option value="" class="bg-gray-300 dark:bg-[#242833] text-gray-400">-- Select State --</option>

                @foreach($states as $st)
                    @php $value = $st['code'].','.$st['name']; @endphp

                    <option value="{{ $value }}"
                            class="bg-gray-300 dark:bg-[#242833] text-gray-400"
                        {{ $selectedState === $value ? 'selected' : '' }}>
                        {{ $st['name'] }} ({{ $st['code'] }})
                    </option>
                @endforeach
            </select>

            @error('state')
            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>


        @php
            $currentTemplate = old('pdf_template_id', $business->pdf_template_id ?? 'pdf_simple');
        @endphp

        <div>
            <label class="block text-sm font-medium mb-1">
                Bill Template <span class="text-red-600">*</span>
            </label>

            <select name="pdf_template_id" required
                    class="mt-1 w-full border rounded px-3 py-2 bg-gray-300 dark:bg-[#242833] text-gray-400 border-gray-600
             focus:border-blue-500 focus:ring-blue-500">

                <option value="" class="bg-gray-300 dark:bg-[#242833] text-gray-400">-- Select template --</option>

                <option value="pdf_simple" class="bg-gray-300 dark:bg-[#242833] text-gray-400"
                    {{ $currentTemplate === 'pdf_simple' ? 'selected' : '' }}>
                    Simple Format
                </option>

                <option value="pdf_rvg_format" class="bg-gray-300 dark:bg-[#242833] text-gray-400"
                    {{ $currentTemplate === 'pdf_rvg_format' ? 'selected' : '' }}>
                    RVG Format
                </option>

                <option value="pdf_rvg_format" class="bg-gray-300 dark:bg-[#242833] text-gray-400"
                    {{ $currentTemplate === 'pdf_krinoscco' ? 'selected' : '' }}>
                    Krinoscco Format
                </option>
            </select>

            @error('pdf_template_id')
            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>


        <div class="md:col-span-2">
            <label class="block text-sm font-medium mb-1">Address</label>
            <textarea name="address" rows="3" class="mt-1 w-full border rounded px-3 py-2 bg-slate-200 dark:bg-[#242833]"
                      placeholder="Optional">{{ old('address', $business->address ?? '') }}</textarea>
            @error('address') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
    </div>

    <div class="grid md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1">Logo</label>
            <input type="file" name="logo" accept="image/*" class="mt-1 w-full border rounded px-3 py-2 bg-slate-200 dark:bg-[#242833]">
            @error('logo') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            @if($isEdit && $business->logo)
                <div class="mt-3 flex items-center gap-4">
                    <img src="{{ asset('storage/'.$business->logo) }}" class="w-14 h-14 rounded object-cover" alt="logo">
                    <label class="inline-flex items-center gap-2 text-sm">
                        <input type="checkbox" name="remove_logo" value="1" class="rounded border-gray-300">
                        Remove current logo
                    </label>
                </div>
            @endif
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Signature</label>
            <input type="file" name="signature" accept="image/*" class="mt-1 w-full border rounded px-3 py-2 bg-slate-200 dark:bg-[#242833]">
            @error('signature') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            @if($isEdit && $business->signature)
                <div class="mt-3 flex items-center gap-4">
                    <img src="{{ asset('storage/'.$business->signature) }}" class="w-14 h-14 rounded object-cover" alt="signature">
                    <label class="inline-flex items-center gap-2 text-sm">
                        <input type="checkbox" name="remove_signature" value="1" class="rounded border-gray-300">
                        Remove current signature
                    </label>
                </div>
            @endif
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Letter Head</label>
            <input type="file" name="letter_head" accept="image/*" class="mt-1 w-full border rounded px-3 py-2 bg-slate-200 dark:bg-[#242833]">
            @error('letter_head') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            @if($isEdit && $business->letter_head)
                <div class="mt-3 flex items-center gap-4">
                    <img src="{{ asset('storage/'.$business->letter_head) }}" class="w-14 h-14 rounded object-cover" alt="letter_head">
                    <label class="inline-flex items-center gap-2 text-sm">
                        <input type="checkbox" name="remove_letter_head" value="1" class="rounded border-gray-300 bg-slate-200 dark:bg-[#242833]">
                        Remove current letter_head
                    </label>
                </div>
            @endif
        </div>
    </div>


    <div class="grid md:grid-cols-2 gap-4">
        <div class="md:col-span-2">
            <label class="block text-sm font-medium mb-1">Terms & Conditions</label>
            <textarea name="terms" rows="3" class="mt-1 w-full border rounded px-3 py-2 bg-slate-200 dark:bg-[#242833]"
                      placeholder="Optional">{{ old('terms', $business->terms ?? '') }}</textarea>
            @error('terms') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
    </div>

    <div class="flex items-center gap-3">
        <button type="submit"
                class="px-4 py-2 rounded bg-green-600 text-white hover:bg-green-700">
            {{ $isEdit ? 'Update Business' : 'Create Business' }}
        </button>
        <a href="{{ route('businesses.index') }}" class=" bg-amber-400 p-2 rounded-lg text-white hover:underline">Cancel</a>
    </div>
</div>

{{-- Optional: auto-fill slug from name --}}
<script>
    document.addEventListener('alpine:init', () => {});
    document.addEventListener('DOMContentLoaded', () => {
        const name = document.querySelector('input[name="name"]');
        const slug = document.querySelector('input[name="slug"]');
        if (name && slug) {
            let edited = slug.value?.length > 0;
            slug.addEventListener('input', () => edited = slug.value.length > 0);
            name.addEventListener('input', () => {
                if (!edited) {
                    slug.value = name.value.trim()
                        .toLowerCase()
                        .replace(/[^a-z0-9]+/g, '-')
                        .replace(/(^-|-$)/g, '');
                }
            });
        }
    });
</script>
