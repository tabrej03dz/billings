<div>
    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Business ID</label>
    <input type="number" name="business_id"
           value="{{ old('business_id', $record->business_id ?? '') }}"
           class="mt-1 w-full rounded-lg border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white"
           placeholder="Optional">

    @error('business_id')
        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
    @enderror
</div>

<div>
    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
    <input type="text" name="name"
           value="{{ old('name', $record->name ?? '') }}"
           class="mt-1 w-full rounded-lg border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white"
           placeholder="Enter name">

    @error('name')
        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
    @enderror
</div>

<div>
    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Phone</label>
    <input type="text" name="phone"
           value="{{ old('phone', $record->phone ?? '') }}"
           class="mt-1 w-full rounded-lg border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white"
           placeholder="Enter phone number">

    @error('phone')
        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
    @enderror
</div>

<div>
    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Date of Anniversary</label>
    <input type="date" name="date_of_anniversary"
           value="{{ old('date_of_anniversary', isset($record) && $record->date_of_anniversary ? \Carbon\Carbon::parse($record->date_of_anniversary)->format('Y-m-d') : '') }}"
           class="mt-1 w-full rounded-lg border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white">

    @error('date_of_anniversary')
        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
    @enderror
</div>