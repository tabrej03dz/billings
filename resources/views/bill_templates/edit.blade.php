<x-layouts.app :title="__('Edit Bill Template')">
    <div class="flex flex-col gap-4">

        @if(session('success'))
            <div class="p-3 rounded-lg bg-green-50 text-green-700 border border-green-200">
                {{ session('success') }}
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
            <form action="{{ route('bill-templates.update', $billTemplate->id) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                            Template Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               id="name"
                               name="name"
                               value="{{ old('name', $billTemplate->name) }}"
                               placeholder="Enter template name"
                               class="w-full border rounded-lg px-4 py-2.5 text-sm dark:bg-neutral-800 dark:border-neutral-700 dark:text-white focus:ring-2 focus:ring-cyan-500 focus:outline-none">
                        @error('name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="page_name" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                            Page Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               id="page_name"
                               name="page_name"
                               value="{{ old('page_name', $billTemplate->page_name) }}"
                               placeholder="Enter page name"
                               class="w-full border rounded-lg px-4 py-2.5 text-sm dark:bg-neutral-800 dark:border-neutral-700 dark:text-white focus:ring-2 focus:ring-cyan-500 focus:outline-none">
                        @error('page_name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                        Description
                    </label>
                    <textarea id="description"
                              name="description"
                              rows="5"
                              placeholder="Enter description"
                              class="w-full border rounded-lg px-4 py-2.5 text-sm dark:bg-neutral-800 dark:border-neutral-700 dark:text-white focus:ring-2 focus:ring-cyan-500 focus:outline-none">{{ old('description', $billTemplate->description) }}</textarea>
                    @error('description')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center gap-3">
                    <button type="submit"
                            class="inline-flex items-center px-5 py-2.5 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700">
                        Update Template
                    </button>

                    <a href="{{ route('bill-templates.index') }}"
                       class="inline-flex items-center px-5 py-2.5 text-sm font-medium text-white bg-gray-500 rounded-lg hover:bg-gray-600">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>