@props(['client' => null])

@php $isEdit = filled($client?->id); @endphp

<div class="space-y-6">
    <div class="grid md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1">Name <span class="text-red-600">*</span></label>
            <input type="text" name="name" required
                   value="{{ old('name', $client->name ?? '') }}"
                   class="mt-1 w-full border rounded px-3 py-2">
            @error('name') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Mobile <span class="text-red-600">*</span></label>
            <input type="text" name="mobile" required
                   value="{{ old('mobile', $client->mobile ?? '') }}"
                   class="mt-1 w-full border rounded px-3 py-2">
            @error('mobile') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">GSTIN</label>
            <input type="text" name="gstin"
                   value="{{ old('gstin', $client->gstin ?? '') }}"
                   class="mt-1 w-full border rounded px-3 py-2">
            @error('gstin') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">PAN</label>
            <input type="text" name="pan"
                   value="{{ old('pan', $client->pan ?? '') }}"
                   class="mt-1 w-full border rounded px-3 py-2">
            @error('pan') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">State</label>
            <input type="text" name="state"
                   value="{{ old('state', $client->state ?? '') }}"
                   class="mt-1 w-full border rounded px-3 py-2">
            @error('state') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm font-medium mb-1">Address</label>
            <textarea name="address" rows="3"
                      class="mt-1 w-full border rounded px-3 py-2"
                      placeholder="Optional">{{ old('address', $client->address ?? '') }}</textarea>
            @error('address') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
    </div>

    <div class="flex items-center gap-3">
        <button type="submit" class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">
            {{ $isEdit ? 'Update Client' : 'Create Client' }}
        </button>
        <a href="{{ route('clients.index') }}" class="text-gray-600 hover:underline">Cancel</a>
    </div>
</div>
