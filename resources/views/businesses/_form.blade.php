@props(['business' => null])

@php
    $isEdit = filled($business?->id);
@endphp

<div class="space-y-6">
    <div class="grid md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1">Name <span class="text-red-600">*</span></label>
            <input type="text" name="name" value="{{ old('name', $business->name ?? '') }}"
                   class="mt-1 w-full border rounded px-3 py-2"
                   required>
            @error('name') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Slug (optional)</label>
            <input type="text" name="slug" value="{{ old('slug', $business->slug ?? '') }}"
                   placeholder="auto-from-name if left blank"
                   class="mt-1 w-full border rounded px-3 py-2">
            @error('slug') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Email <span class="text-red-600">*</span></label>
            <input type="email" name="email" value="{{ old('email', $business->email ?? '') }}"
                   class="mt-1 w-full border rounded px-3 py-2" required>
            @error('email') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Mobile </label>
            <input type="text" name="mobile" value="{{ old('mobile', $business->mobile ?? '') }}"
                   class="mt-1 w-full border rounded px-3 py-2" >
            @error('mobile') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">GSTIN</label>
            <input type="text" name="gstin" value="{{ old('gstin', $business->gstin ?? '') }}"
                   class="mt-1 w-full border rounded px-3 py-2">
            @error('gstin') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm font-medium mb-1">Address</label>
            <textarea name="address" rows="3" class="mt-1 w-full border rounded px-3 py-2"
                      placeholder="Optional">{{ old('address', $business->address ?? '') }}</textarea>
            @error('address') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
    </div>

    <div class="grid md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1">Logo</label>
            <input type="file" name="logo" accept="image/*" class="mt-1 w-full border rounded px-3 py-2">
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
            <input type="file" name="signature" accept="image/*" class="mt-1 w-full border rounded px-3 py-2">
            @error('logo') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            @if($isEdit && $business->logo)
                <div class="mt-3 flex items-center gap-4">
                    <img src="{{ asset('storage/'.$business->signature) }}" class="w-14 h-14 rounded object-cover" alt="logo">
                    <label class="inline-flex items-center gap-2 text-sm">
                        <input type="checkbox" name="remove_sgnature" value="1" class="rounded border-gray-300">
                        Remove current signature
                    </label>
                </div>
            @endif
        </div>
    </div>

    <div class="grid md:grid-cols-2 gap-4">
        <div class="md:col-span-2">
            <label class="block text-sm font-medium mb-1">Terms & Conditions</label>
            <textarea name="terms" rows="3" class="mt-1 w-full border rounded px-3 py-2"
                      placeholder="Optional">{{ old('terms', $business->terms ?? '') }}</textarea>
            @error('terms') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
    </div>

    <div class="flex items-center gap-3">
        <button type="submit"
                class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">
            {{ $isEdit ? 'Update Business' : 'Create Business' }}
        </button>
        <a href="{{ route('businesses.index') }}" class="text-gray-600 hover:underline">Cancel</a>
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
