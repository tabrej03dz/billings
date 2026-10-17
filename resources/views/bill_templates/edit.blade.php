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


        {{-- Header --}}
        <div class="flex flex-wrap items-center justify-between gap-3 bg-[#BFE0E0] dark:bg-[#354A54] p-6 rounded-xl">

            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">
                    Edit Bill Template
                </h1>

                <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">
                    Update bill template details.
                </p>
            </div>

            <a href="{{ route('bill-templates.index') }}"
               class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-gray-600 rounded-lg hover:bg-gray-700">
                Back
            </a>

        </div>


        {{-- Form Card --}}
        <div class="bg-white dark:bg-neutral-900 rounded-xl border border-gray-200 dark:border-gray-700 p-6">

            <form action="{{ route('bill-templates.update', $billTemplate->id) }}"
                  method="POST"
                  enctype="multipart/form-data"
                  class="space-y-6">

                @csrf
                @method('PUT')


                {{-- Business Type --}}
                <div>

                    <label for="business_type_id"
                           class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-200">

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

                        <option value="">
                            Select Business Type
                        </option>

                        @foreach($businessTypes as $businessType)

                            <option value="{{ $businessType->id }}"
                                {{ (string) old('business_type_id', $billTemplate->business_type_id) === (string) $businessType->id ? 'selected' : '' }}>

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


                {{-- Name + Page Name --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    {{-- Template Name --}}
                    <div>

                        <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-200">

                            Template Name
                            <span class="text-red-500">*</span>

                        </label>

                        <input type="text"
                               name="name"
                               value="{{ old('name', $billTemplate->name) }}"
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

                        <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-200">

                            Page Name
                            <span class="text-red-500">*</span>

                        </label>

                        <input type="text"
                               name="page_name"
                               value="{{ old('page_name', $billTemplate->page_name) }}"
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

                    <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-200">
                        Description
                    </label>

                    <textarea name="description"
                              rows="5"
                              class="w-full border rounded-lg px-4 py-2.5 text-sm
                                     dark:bg-neutral-800
                                     dark:border-neutral-700
                                     dark:text-white
                                     focus:ring-2
                                     focus:ring-cyan-500
                                     focus:outline-none">{{ old('description', $billTemplate->description) }}</textarea>

                    @error('description')
                        <p class="text-red-500 text-sm mt-1">
                            {{ $message }}
                        </p>
                    @enderror

                </div>


                {{-- Preview Upload --}}
                <div>

                    <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-200">
                        Preview Image / PDF
                    </label>

                    <input type="file"
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

                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                        Allowed: JPG, JPEG, PNG, WEBP, PDF (Max: 5MB)
                    </p>

                    @error('preview')
                        <p class="text-red-500 text-sm mt-1">
                            {{ $message }}
                        </p>
                    @enderror

                </div>


                {{-- Current Preview --}}
                @if($billTemplate->preview)

                    <div class="rounded-xl border border-gray-200 dark:border-neutral-700 p-4">

                        <p class="text-sm font-medium text-gray-700 dark:text-gray-200 mb-3">
                            Current Preview
                        </p>

                        @php
                            $ext = strtolower(
                                pathinfo(
                                    $billTemplate->preview,
                                    PATHINFO_EXTENSION
                                )
                            );
                        @endphp


                        @if(in_array($ext, ['jpg', 'jpeg', 'png', 'webp']))

                            <img src="{{ asset('storage/' . $billTemplate->preview) }}"
                                 alt="Bill Template Preview"
                                 class="h-40 max-w-full object-contain rounded-lg border shadow">

                        @elseif($ext === 'pdf')

                            <a href="{{ asset('storage/' . $billTemplate->preview) }}"
                               target="_blank"
                               class="inline-flex items-center gap-2 px-4 py-2
                                      bg-blue-50 text-blue-700
                                      border border-blue-200
                                      rounded-lg hover:bg-blue-100">

                                📄 View PDF Preview

                            </a>

                        @endif

                    </div>

                @endif


                {{-- Notes --}}
                <div class="rounded-xl border border-dashed border-gray-300 dark:border-neutral-700 p-4 bg-gray-50 dark:bg-neutral-800/50">

                    <h3 class="text-sm font-semibold text-gray-800 dark:text-white mb-2">
                        Notes
                    </h3>

                    <ul class="list-disc pl-5 space-y-1 text-sm text-gray-600 dark:text-gray-300">

                        <li>
                            <strong>Business Type</strong>
                            select karein jiske liye ye template available rahega.
                        </li>

                        <li>
                            Agar preview change nahi karna hai to file select karne ki zarurat nahi hai.
                        </li>

                        <li>
                            <strong>Page Name</strong>
                            actual Blade view ka naam hona chahiye.
                        </li>

                    </ul>

                </div>


                {{-- Buttons --}}
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