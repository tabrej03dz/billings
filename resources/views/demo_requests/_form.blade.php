<div>
    <label class="text-xs text-gray-600 dark:text-gray-400">Name</label>
    <input type="text" name="name"
           value="{{ old('name', optional($demoRequest)->name) }}"
           class="mt-1 w-full rounded-lg border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white"
           required>
    @error('name')
        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
    @enderror
</div>

<div>
    <label class="text-xs text-gray-600 dark:text-gray-400">Mobile</label>
    <input type="text" name="mobile"
           value="{{ old('mobile', optional($demoRequest)->mobile) }}"
           maxlength="10"
           pattern="[6-9][0-9]{9}"
           inputmode="numeric"
           class="mt-1 w-full rounded-lg border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white"
           required>
    @error('mobile')
        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
    @enderror
</div>

<div>
    <label class="text-xs text-gray-600 dark:text-gray-400">City</label>
    <input type="text" name="city"
           value="{{ old('city', optional($demoRequest)->city) }}"
           class="mt-1 w-full rounded-lg border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white">
    @error('city')
        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
    @enderror
</div>

<div>
    <label class="text-xs text-gray-600 dark:text-gray-400">Business Name</label>
    <input type="text" name="business_name"
           value="{{ old('business_name', optional($demoRequest)->business_name) }}"
           class="mt-1 w-full rounded-lg border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white">
    @error('business_name')
        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
    @enderror
</div>

<div class="md:col-span-2">
    <label class="text-xs text-gray-600 dark:text-gray-400">Message</label>
    <textarea name="message" rows="3"
              class="mt-1 w-full rounded-lg border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white">{{ old('message', optional($demoRequest)->message) }}</textarea>
    @error('message')
        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
    @enderror
</div>

<div>
    <label class="text-xs text-gray-600 dark:text-gray-400">Status</label>
    <select name="status"
            class="mt-1 w-full rounded-lg border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white">
        <option value="pending" @selected(old('status', optional($demoRequest)->status ?? 'pending') == 'pending')>Pending</option>
        <option value="contacted" @selected(old('status', optional($demoRequest)->status) == 'contacted')>Contacted</option>
        <option value="converted" @selected(old('status', optional($demoRequest)->status) == 'converted')>Converted</option>
        <option value="rejected" @selected(old('status', optional($demoRequest)->status) == 'rejected')>Rejected</option>
    </select>
    @error('status')
        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
    @enderror
</div>

<div class="md:col-span-2">
    <label class="text-xs text-gray-600 dark:text-gray-400">Note</label>
    <textarea name="note" rows="3"
              class="mt-1 w-full rounded-lg border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white">{{ old('note', optional($demoRequest)->note) }}</textarea>
    @error('note')
        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
    @enderror
</div>