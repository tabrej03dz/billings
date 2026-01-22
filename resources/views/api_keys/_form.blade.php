<div class="space-y-6">

    {{-- Base URL --}}
    <div>
        <label class="block text-sm font-medium mb-1">Base URL</label>
        <input type="text" name="base_url"
               value="{{ old('base_url', $apiKey->base_url ?? '') }}"
               class="w-full rounded-lg border-gray-500 dark:border-neutral-700 dark:text-white px-4 py-2 focus:ring-blue-500 focus:border-blue-500 bg-slate-200 dark:bg-[#242833]"
               placeholder="https://api.example.com">
        @error('base_url')
        <p class="text-red-500 text-sm">{{ $message }}</p>
        @enderror
    </div>

    {{-- API Key --}}
    <div>
        <label class="block text-sm font-medium mb-1">API Key</label>
        <input type="text" name="key"
               value="{{ old('key', $apiKey->key ?? '') }}"
               class="w-full rounded-lg border-gray-300 dark:border-neutral-700 bg-slate-200 dark:bg-[#242833]dark:text-white px-4 py-2 focus:ring-blue-500 focus:border-blue-500"
               placeholder="Enter API Key">
        @error('key')
        <p class="text-red-500 text-sm">{{ $message }}</p>
        @enderror
    </div>

    {{-- Secret --}}
    <div>
        <label class="block text-sm font-medium mb-1">Secret</label>
        <input type="text" name="secret"
               value="{{ old('secret', $apiKey->secret ?? '') }}"
               class="w-full rounded-lg border-gray-300 dark:border-neutral-700 bg-slate-200 dark:bg-[#242833] dark:text-white px-4 py-2 focus:ring-blue-500 focus:border-blue-500"
               placeholder="Enter Secret">
        @error('secret')
        <p class="text-red-500 text-sm">{{ $message }}</p>
        @enderror
    </div>

    {{-- Action Buttons --}}
    <div class="flex justify-end gap-3">
        <a href="{{ route('api-keys.index') }}"
           class="px-4 py-2 rounded-lg border border-gray-300 dark:border-neutral-700 text-white dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-neutral-800 bg-red-400 p-2 ">
            Cancel
        </a>

        <button type="submit"
                class="px-5 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
            {{ $apiKey ? 'Update' : 'Save' }}
        </button>
    </div>

</div>
