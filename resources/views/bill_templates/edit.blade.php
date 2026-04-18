<x-layouts.app :title="__('Edit Bill Template')">
    <div class="flex flex-col gap-4">

        @if(session('success'))
            <div class="p-3 rounded-lg bg-green-50 text-green-700 border border-green-200">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-700">
                <ul class="list-disc pl-5 space-y-1 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="flex flex-wrap items-center justify-between gap-3 bg-[#BFE0E0] dark:bg-[#354A54] p-6 rounded-xl">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Edit Bill Template</h1>
                <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">
                    Update bill template details.
                </p>
            </div>

            <a href="{{ route('bill-templates.index') }}"
               class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-gray-600 rounded-lg hover:bg-gray-700">
                Back
            </a>
        </div>

        <div class="bg-white dark:bg-neutral-900 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <form action="{{ route('bill-templates.update', $billTemplate->id) }}"
                  method="POST"
                  enctype="multipart/form-data"
                  class="space-y-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-200">
                            Template Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               name="name"
                               value="{{ old('name', $billTemplate->name) }}"
                               class="w-full border rounded-lg px-4 py-2.5 text-sm dark:bg-neutral-800 dark:border-neutral-700 dark:text-white">
                        @error('name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-200">
                            Page Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               name="page_name"
                               value="{{ old('page_name', $billTemplate->page_name) }}"
                               placeholder="Example: pdf.simple_bill"
                               class="w-full border rounded-lg px-4 py-2.5 text-sm dark:bg-neutral-800 dark:border-neutral-700 dark:text-white">
                        @error('page_name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-200">
                        Description
                    </label>
                    <textarea name="description"
                              rows="5"
                              class="w-full border rounded-lg px-4 py-2.5 text-sm dark:bg-neutral-800 dark:border-neutral-700 dark:text-white">{{ old('description', $billTemplate->description) }}</textarea>
                </div>

                {{-- ✅ Preview Upload --}}
                <div>
                    <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-200">
                        Preview Image / PDF
                    </label>

                    <input type="file"
                           name="preview"
                           accept=".jpg,.jpeg,.png,.webp,.pdf"
                           class="w-full border rounded-lg px-4 py-2.5 text-sm dark:bg-neutral-800 dark:border-neutral-700 dark:text-white">

                    <p class="text-xs text-gray-500 mt-2">
                        Allowed: JPG, PNG, WEBP, PDF (Max: 5MB)
                    </p>

                    @error('preview')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- ✅ Current Preview Show --}}
                @if($billTemplate->preview)
                    <div class="mt-4">
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                            Current Preview:
                        </p>

                        @php
                            $ext = strtolower(pathinfo($billTemplate->preview, PATHINFO_EXTENSION));
                        @endphp

                        @if(in_array($ext, ['jpg','jpeg','png','webp']))
                            <img src="{{ asset('storage/'.$billTemplate->preview) }}"
                                 class="h-40 rounded border shadow">
                        @elseif($ext === 'pdf')
                            <a href="{{ asset('storage/'.$billTemplate->preview) }}"
                               target="_blank"
                               class="text-blue-600 underline">
                                📄 View PDF Preview
                            </a>
                        @endif
                    </div>
                @endif

                <div class="flex items-center gap-3">
                    <button type="submit"
                            class="px-5 py-2.5 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        Update Template
                    </button>

                    <a href="{{ route('bill-templates.index') }}"
                       class="px-5 py-2.5 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>