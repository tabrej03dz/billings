<div class="grid grid-cols-1 md:grid-cols-2 gap-5">

    <div>
        <label class="block text-sm font-medium mb-1">Title</label>
        <input type="text"
               name="title"
               value="{{ old('title', $banner->title ?? '') }}"
               class="w-full border rounded px-3 py-2 text-sm"
               placeholder="Enter banner title">
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Subtitle</label>
        <input type="text"
               name="subtitle"
               value="{{ old('subtitle', $banner->subtitle ?? '') }}"
               class="w-full border rounded px-3 py-2 text-sm"
               placeholder="Enter subtitle">
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Desktop Image</label>
        <input type="file"
               name="image"
               class="w-full border rounded px-3 py-2 text-sm"
               {{ $banner ? '' : 'required' }}>

        @if($banner && $banner->image)
            <img src="{{ asset('storage/' . $banner->image) }}"
                 class="mt-3 h-24 w-44 object-cover rounded border">
        @endif
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Mobile Image</label>
        <input type="file"
               name="mobile_image"
               class="w-full border rounded px-3 py-2 text-sm">

        @if($banner && $banner->mobile_image)
            <img src="{{ asset('storage/' . $banner->mobile_image) }}"
                 class="mt-3 h-24 w-44 object-cover rounded border">

            <label class="flex items-center gap-2 mt-2 text-sm">
                <input type="checkbox" name="remove_mobile_image" value="1">
                Remove mobile image
            </label>
        @endif
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Button Text</label>
        <input type="text"
               name="button_text"
               value="{{ old('button_text', $banner->button_text ?? '') }}"
               class="w-full border rounded px-3 py-2 text-sm"
               placeholder="Shop Now">
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Button URL</label>
        <input type="text"
               name="button_url"
               value="{{ old('button_url', $banner->button_url ?? '') }}"
               class="w-full border rounded px-3 py-2 text-sm"
               placeholder="https://example.com">
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Alt Text</label>
        <input type="text"
               name="alt_text"
               value="{{ old('alt_text', $banner->alt_text ?? '') }}"
               class="w-full border rounded px-3 py-2 text-sm"
               placeholder="Image alt text">
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Sort Order</label>
        <input type="number"
               name="sort_order"
               value="{{ old('sort_order', $banner->sort_order ?? 0) }}"
               class="w-full border rounded px-3 py-2 text-sm"
               min="0">
    </div>

    <div class="md:col-span-2">
        <label class="block text-sm font-medium mb-1">Description</label>
        <textarea name="description"
                  rows="4"
                  class="w-full border rounded px-3 py-2 text-sm"
                  placeholder="Banner description">{{ old('description', $banner->description ?? '') }}</textarea>
    </div>

    <div class="md:col-span-2">
        <label class="flex items-center gap-2">
            <input type="checkbox"
                   name="is_active"
                   value="1"
                   {{ old('is_active', $banner->is_active ?? true) ? 'checked' : '' }}>
            <span class="text-sm font-medium">Active</span>
        </label>
    </div>

</div>

<div class="mt-6 flex gap-2">
    <button type="submit"
            class="px-5 py-2 bg-green-600 text-white rounded hover:bg-green-700">
        {{ $buttonText }}
    </button>

    <a href="{{ route('banner-sliders.index') }}"
       class="px-5 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
        Cancel
    </a>
</div>