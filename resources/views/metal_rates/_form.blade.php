<div class="space-y-4">

    {{-- Date --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">
            Rate Date <span class="text-red-500">*</span>
        </label>
        <input
            type="date"
            name="rate_date"
            value="{{ old('rate_date', optional($metalRate)->rate_date ? $metalRate->rate_date->format('Y-m-d') : now()->format('Y-m-d')) }}"
            class="w-full border rounded px-3 py-2 text-sm dark:bg-neutral-800 dark:border-neutral-700 dark:text-white"
        >
        @error('rate_date')
        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Metal Type --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">
            Metal Type <span class="text-red-500">*</span>
        </label>
        <select
            name="metal_type"
            class="w-full border rounded px-3 py-2 text-sm dark:bg-neutral-800 dark:border-neutral-700 dark:text-white"
        >
            <option value="">Select metal</option>
            <option value="gold"   @selected(old('metal_type', optional($metalRate)->metal_type) === 'gold')>Gold</option>
            <option value="silver" @selected(old('metal_type', optional($metalRate)->metal_type) === 'silver')>Silver</option>
        </select>
        @error('metal_type')
        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Purity --}}
    @php
        $selectedPurity = old('purity', optional($metalRate)->purity);
    @endphp
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">
            Purity <span class="text-red-500">*</span>
        </label>

        <select
            name="purity"
            class="w-full border rounded px-3 py-2 text-sm dark:bg-neutral-800 dark:border-neutral-700 dark:text-white"
        >
            <option value="">Select purity</option>

            <optgroup label="Gold">
                @foreach (['24K','23K','22K','20K','18K','14K'] as $p)
                    <option value="{{ $p }}" @selected($selectedPurity === $p)>{{ $p }}</option>
                @endforeach
            </optgroup>

            <optgroup label="Silver">
                @foreach (['999','925','900'] as $p)
                    <option value="{{ $p }}" @selected($selectedPurity === $p)>{{ $p }}</option>
                @endforeach
            </optgroup>
        </select>

        @error('purity')
        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Rate per gram --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">
            Rate per gram (₹) <span class="text-red-500">*</span>
        </label>
        <input
            type="number"
            step="0.01"
            min="0"
            name="rate_per_gram"
            value="{{ old('rate_per_gram', optional($metalRate)->rate_per_gram) }}"
            placeholder="Enter rate per gram"
            class="w-full border rounded px-3 py-2 text-sm dark:bg-neutral-800 dark:border-neutral-700 dark:text-white"
        >
        @error('rate_per_gram')
        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Active toggle --}}
    <div class="flex items-center gap-2">
        <input
            type="checkbox"
            name="is_active"
            value="1"
            id="is_active"
            @checked(old('is_active', optional($metalRate)->is_active ?? true))
        >
        <label for="is_active" class="text-sm text-gray-700 dark:text-gray-200">
            Active rate
        </label>
    </div>
    @error('is_active')
    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
    @enderror

    {{-- Buttons --}}
    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-neutral-800">
        <a href="{{ route('metal-rates.index') }}"
           class="px-4 py-2 text-sm rounded border border-gray-300 text-gray-700 hover:bg-gray-50 dark:border-neutral-700 dark:text-gray-200 dark:hover:bg-neutral-800">
            Cancel
        </a>
        <button type="submit"
                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded hover:bg-blue-700">
            {{ $metalRate ? 'Update Rate' : 'Save Rate' }}
        </button>
    </div>
</div>
