<x-layouts.app :title="__('Create Bill Template')">

    <div class="space-y-6">

        <div class="flex flex-wrap items-center justify-between gap-3 bg-[#BFE0E0] dark:bg-[#354A54] p-6 rounded-xl">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">
                    Create Bill Template
                </h1>

                <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">
                    Add a new bill template for your billing pages.
                </p>
            </div>

            <a href="{{ route('bill-templates.index') }}"
               class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-gray-600 rounded-lg hover:bg-gray-700">
                Back
            </a>
        </div>


        @if ($errors->any())
            <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-700">
                <ul class="list-disc pl-5 space-y-1 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif


        <div class="bg-white dark:bg-neutral-900 rounded-xl border border-gray-200 dark:border-gray-700 p-6">

            <form action="{{ route('bill-templates.store') }}"
                  method="POST"
                  enctype="multipart/form-data"
                  class="space-y-6">

                @csrf


                {{-- Business Type --}}
                <div>
                    <label for="business_type_id"
                           class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">

                        Business Type
                        <span class="text-red-500">*</span>

                    </label>

                    <select name="business_type_id"
                            id="business_type_id"
                            required
                            class="w-full border rounded-lg px-4 py-2.5 text-sm
                                   dark:bg-neutral-800
                                   dark:border-neutral-700
                                   dark:text-white
                                   focus:ring-2
                                   focus:ring-cyan-500
                                   focus:outline-none">

                        <option value="">Select Business Type</option>

                        @foreach ($businessTypes as $businessType)

                            <option value="{{ $businessType->id }}"
                                {{ old('business_type_id') == $businessType->id ? 'selected' : '' }}>

                                {{ $businessType->name }}

                            </option>

                        @endforeach

                    </select>

                    @error('business_type_id')
                        <p class="text-red-500 text-sm mt-1">
                            {{ $message }}
                        </p>
                    @enderror
                </div>


                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    {{-- Template Name --}}
                    <div>

                        <label for="name"
                               class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">

                            Template Name
                            <span class="text-red-500">*</span>

                        </label>

                        <input type="text"
                               id="name"
                               name="name"
                               value="{{ old('name') }}"
                               placeholder="Enter template name"
                               required
                               class="w-full border rounded-lg px-4 py-2.5 text-sm
                                      dark:bg-neutral-800
                                      dark:border-neutral-700
                                      dark:text-white
                                      focus:ring-2
                                      focus:ring-cyan-500
                                      focus:outline-none">

                        @error('name')
                            <p class="text-red-500 text-sm mt-1">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>


                    {{-- Page Name --}}
                    <div>

                        <label for="page_name"
                               class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">

                            Page Name
                            <span class="text-red-500">*</span>

                        </label>

                        <input type="text"
                               id="page_name"
                               name="page_name"
                               value="{{ old('page_name') }}"
                               placeholder="Example: pdf.simple_bill"
                               required
                               class="w-full border rounded-lg px-4 py-2.5 text-sm
                                      dark:bg-neutral-800
                                      dark:border-neutral-700
                                      dark:text-white
                                      focus:ring-2
                                      focus:ring-cyan-500
                                      focus:outline-none">

                        @error('page_name')
                            <p class="text-red-500 text-sm mt-1">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>

                </div>


                {{-- Description --}}
                <div>

                    <label for="description"
                           class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                        Description
                    </label>

                    <textarea id="description"
                              name="description"
                              rows="5"
                              placeholder="Enter description"
                              class="w-full border rounded-lg px-4 py-2.5 text-sm
                                     dark:bg-neutral-800
                                     dark:border-neutral-700
                                     dark:text-white
                                     focus:ring-2
                                     focus:ring-cyan-500
                                     focus:outline-none">{{ old('description') }}</textarea>

                    @error('description')
                        <p class="text-red-500 text-sm mt-1">
                            {{ $message }}
                        </p>
                    @enderror

                </div>


                {{-- Preview --}}
                <div>

                    <label for="preview"
                           class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                        Preview Image / PDF
                    </label>

                    <input type="file"
                           id="preview"
                           name="preview"
                           accept=".jpg,.jpeg,.png,.webp,.pdf"
                           class="w-full border rounded-lg px-4 py-2.5 text-sm
                                  dark:bg-neutral-800
                                  dark:border-neutral-700
                                  dark:text-white
                                  file:mr-4
                                  file:rounded-md
                                  file:border-0
                                  file:bg-cyan-600
                                  file:px-4
                                  file:py-2
                                  file:text-sm
                                  file:font-medium
                                  file:text-white
                                  hover:file:bg-cyan-700">

                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                        Allowed files: JPG, JPEG, PNG, WEBP, PDF. Max size: 5MB.
                    </p>

                    @error('preview')
                        <p class="text-red-500 text-sm mt-1">
                            {{ $message }}
                        </p>
                    @enderror

                </div>


                {{-- Notes --}}
                <div class="rounded-xl border border-dashed border-gray-300 dark:border-neutral-700 p-4 bg-gray-50 dark:bg-neutral-800/50">

                    <h3 class="text-sm font-semibold text-gray-800 dark:text-white mb-2">
                        Notes
                    </h3>

                    <ul class="list-disc pl-5 space-y-1 text-sm text-gray-600 dark:text-gray-300">

                        <li>
                            <strong>Business Type</strong>
                            select karein jiske liye ye bill template available hoga.
                        </li>

                        <li>
                            <strong>Template Name</strong>
                            card me dikhne wala naam hoga.
                        </li>

                        <li>
                            <strong>Page Name</strong>
                            actual blade/view name hoga.
                            Example:
                            <code>pdf.simple_bill</code>
                        </li>

                        <li>
                            <strong>Preview</strong>
                            choose page me dikhne wali image ya PDF file hogi.
                        </li>

                    </ul>

                </div>


                {{-- Buttons --}}
                <div class="flex items-center gap-3">

                    <button type="submit"
                            class="inline-flex items-center px-5 py-2.5 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700">

                        Save Template

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