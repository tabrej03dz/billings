@php
    $inputClass = "mt-1 w-full rounded-xl border px-3 py-2 text-sm
        bg-white text-gray-900 border-gray-300
        placeholder:text-gray-400
        focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500
        dark:bg-neutral-800 dark:text-white dark:border-neutral-700 dark:placeholder:text-neutral-400";

    $labelClass = "text-sm font-semibold text-gray-700 dark:text-neutral-200";
    $hintClass  = "text-xs text-gray-500 dark:text-neutral-400 mt-1";
@endphp

@if($errors->any())
    <div class="p-3 rounded-xl bg-red-50 text-red-700 border border-red-200 dark:bg-red-500/10 dark:text-red-200 dark:border-red-500/30">
        <ul class="list-disc ml-5">
            @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
        </ul>
    </div>
@endif

<div class="grid md:grid-cols-2 gap-5">

    <div>
        <label class="{{ $labelClass }}">Label</label>
        <input type="text" name="label"
               value="{{ old('label', $bankAccount->label) }}"
               placeholder="Main UPI / HDFC Current"
               class="{{ $inputClass }}">
        <p class="{{ $hintClass }}">Example: Main UPI / HDFC Current</p>
    </div>

    <div>
        <label class="{{ $labelClass }}">Account Holder</label>
        <input type="text" name="account_holder"
               value="{{ old('account_holder', $bankAccount->account_holder) }}"
               placeholder="Ravi Pandey / Business Name"
               class="{{ $inputClass }}">
    </div>

    <div>
        <label class="{{ $labelClass }}">Account No</label>
        <input type="text" name="account_no"
               value="{{ old('account_no', $bankAccount->account_no) }}"
               placeholder="1234 5678 9012"
               class="{{ $inputClass }}">
    </div>

    <div>
        <label class="{{ $labelClass }}">IFSC</label>
        <input type="text" name="ifsc"
               value="{{ old('ifsc', $bankAccount->ifsc) }}"
               placeholder="HDFC0001234"
               class="{{ $inputClass }}">
    </div>

    <div>
        <label class="{{ $labelClass }}">Bank Name</label>
        <input type="text" name="bank_name"
               value="{{ old('bank_name', $bankAccount->bank_name) }}"
               placeholder="HDFC / SBI / ICICI"
               class="{{ $inputClass }}">
    </div>

    <div>
        <label class="{{ $labelClass }}">Branch</label>
        <input type="text" name="branch"
               value="{{ old('branch', $bankAccount->branch) }}"
               placeholder="Lakhanpur / Panki / Main Branch"
               class="{{ $inputClass }}">
    </div>

    <div class="md:col-span-2">
        <label class="{{ $labelClass }}">UPI ID</label>
        <input type="text" name="upi_id"
               value="{{ old('upi_id', $bankAccount->upi_id) }}"
               placeholder="name@upi"
               class="{{ $inputClass }}">
        <p class="{{ $hintClass }}">Example: name@upi</p>
    </div>

    <div class="md:col-span-2">
        <label class="{{ $labelClass }}">Notes</label>
        <textarea name="notes" rows="4"
                  placeholder="Optional notes..."
                  class="{{ $inputClass }}">{{ old('notes', $bankAccount->notes) }}</textarea>
    </div>

    <div class="md:col-span-2 flex flex-wrap gap-8 pt-1">
        <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-neutral-200">
            <input type="checkbox" name="is_active" value="1"
                   class="rounded border-gray-300 dark:border-neutral-700"
                {{ old('is_active', $bankAccount->is_active ?? true) ? 'checked' : '' }}>
            <span>Active</span>
        </label>

        <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-neutral-200">
            <input type="checkbox" name="is_default" value="1"
                   class="rounded border-gray-300 dark:border-neutral-700"
                {{ old('is_default', $bankAccount->is_default ?? false) ? 'checked' : '' }}>
            <span>Make Default</span>
        </label>
    </div>
</div>

<div class="flex items-center justify-end gap-2 pt-5">
    <button type="submit"
            class="px-5 py-2 rounded-xl bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700">
        Save
    </button>
</div>
