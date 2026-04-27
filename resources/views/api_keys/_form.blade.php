@php
    $isEdit = isset($apiKey) && $apiKey?->exists;
@endphp

<div class="space-y-6">

    <div>
        <label class="block text-sm font-medium mb-1">Base URL</label>
        <input type="url" name="base_url"
               value="{{ old('base_url', $apiKey->base_url ?? '') }}"
               class="w-full rounded-lg border border-gray-300 dark:border-neutral-700 bg-slate-200 dark:bg-[#242833] text-gray-900 dark:text-white px-4 py-2 focus:ring-blue-500 focus:border-blue-500"
               placeholder="https://api.example.com">
        @error('base_url') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">API Key</label>
        <input type="text" name="key"
               value="{{ old('key', $apiKey->key ?? '') }}"
               class="w-full rounded-lg border border-gray-300 dark:border-neutral-700 bg-slate-200 dark:bg-[#242833] text-gray-900 dark:text-white px-4 py-2 focus:ring-blue-500 focus:border-blue-500"
               placeholder="Enter API Key">
        @error('key') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Secret</label>
        <input type="text" name="secret"
               value="{{ old('secret', $apiKey->secret ?? '') }}"
               class="w-full rounded-lg border border-gray-300 dark:border-neutral-700 bg-slate-200 dark:bg-[#242833] text-gray-900 dark:text-white px-4 py-2 focus:ring-blue-500 focus:border-blue-500"
               placeholder="Enter Secret">
        @error('secret') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Wishes API</label>
        <input type="url" name="wishes_api"
               value="{{ old('wishes_api', $apiKey->wishes_api ?? '') }}"
               class="w-full rounded-lg border border-gray-300 dark:border-neutral-700 bg-slate-200 dark:bg-[#242833] text-gray-900 dark:text-white px-4 py-2"
               placeholder="Wishes API URL">
        @error('wishes_api') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Installment Reminder API</label>
        <input type="url" name="installment_reminder_api"
               value="{{ old('installment_reminder_api', $apiKey->installment_reminder_api ?? '') }}"
               class="w-full rounded-lg border border-gray-300 dark:border-neutral-700 bg-slate-200 dark:bg-[#242833] text-gray-900 dark:text-white px-4 py-2"
               placeholder="Installment Reminder API URL">
        @error('installment_reminder_api') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Birthday Wish Video Absolute Path</label>
        <input type="text" name="birthday_wish_video_absolute_path"
               value="{{ old('birthday_wish_video_absolute_path', $apiKey->birthday_wish_video_absolute_path ?? '') }}"
               class="w-full rounded-lg border border-gray-300 dark:border-neutral-700 bg-slate-200 dark:bg-[#242833] text-gray-900 dark:text-white px-4 py-2"
               placeholder="/home/user/video.mp4">
        @error('birthday_wish_video_absolute_path') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Birthday Wish Media Manager Video URL</label>
        <input type="url" name="birthday_wish_media_manager_video_url"
               value="{{ old('birthday_wish_media_manager_video_url', $apiKey->birthday_wish_media_manager_video_url ?? '') }}"
               class="w-full rounded-lg border border-gray-300 dark:border-neutral-700 bg-slate-200 dark:bg-[#242833] text-gray-900 dark:text-white px-4 py-2"
               placeholder="https://example.com/video.mp4">
        @error('birthday_wish_media_manager_video_url') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Birthday Wish Video URL Updated On</label>
        <input type="date" name="birthday_wish_video_url_updated_on"
               value="{{ old('birthday_wish_video_url_updated_on', optional($apiKey->birthday_wish_video_url_updated_on ?? null)->format('Y-m-d')) }}"
               class="w-full rounded-lg border border-gray-300 dark:border-neutral-700 bg-slate-200 dark:bg-[#242833] text-gray-900 dark:text-white px-4 py-2">
        @error('birthday_wish_video_url_updated_on') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">File</label>
        <input type="text" name="file"
               value="{{ old('file', $apiKey->file ?? '') }}"
               class="w-full rounded-lg border border-gray-300 dark:border-neutral-700 bg-slate-200 dark:bg-[#242833] text-gray-900 dark:text-white px-4 py-2"
               placeholder="File path or URL">
        @error('file') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
    </div>

    <div class="flex justify-end gap-3 pt-4">
        <a href="{{ route('api-keys.index') }}"
           class="px-4 py-2 rounded-lg bg-red-500 text-white hover:bg-red-600">
            Cancel
        </a>

        <button type="submit"
                class="px-5 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
            {{ $isEdit ? 'Update' : 'Save' }}
        </button>
    </div>

</div>