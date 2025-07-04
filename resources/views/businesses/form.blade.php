<x-layouts.app :title="isset($business) && $business->id ? __('Edit Business') : __('Create Business')">
    @php $isEdit = isset($business) && $business->id; @endphp

    <div class="max-w-4xl mx-auto py-10">
        <div class="bg-white dark:bg-neutral-900 border border-gray-200 dark:border-neutral-700 shadow-xl rounded-2xl p-8">
            <h2 class="text-3xl font-bold text-gray-800 dark:text-white mb-8">
                {{ $isEdit ? 'Edit Business' : 'Create New Business' }}
            </h2>

            @if ($errors->any())
                <div class="mb-6 bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 p-4 rounded-lg">
                    <ul class="space-y-1 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ $isEdit ? route('businesses.update', $business->id) : route('businesses.store') }}"
                  method="POST" enctype="multipart/form-data" class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                @csrf

                <!-- Business Name -->
                <div class="col-span-1">
                    <label for="name" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Business Name</label>
                    <input type="text" name="name" id="name" required
                           value="{{ old('name', $business->name ?? '') }}"
                           class="w-full rounded-xl border border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-gray-900 dark:text-white px-4 py-2 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                </div>

                <!-- Email -->
                <div class="col-span-1">
                    <label for="email" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Email</label>
                    <input type="email" name="email" id="email" required
                           value="{{ old('email', $business->email ?? '') }}"
                           class="w-full rounded-xl border border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-gray-900 dark:text-white px-4 py-2 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                </div>

                <!-- Mobile -->
                <div class="col-span-1">
                    <label for="mobile" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Mobile</label>
                    <input type="text" name="mobile" id="mobile" required
                           value="{{ old('mobile', $business->mobile ?? '') }}"
                           class="w-full rounded-xl border border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-gray-900 dark:text-white px-4 py-2 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                </div>

                <!-- GSTIN -->
                <div class="col-span-1">
                    <label for="gstin" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">GSTIN</label>
                    <input type="text" name="gstin" id="gstin"
                           value="{{ old('gstin', $business->gstin ?? '') }}"
                           class="w-full rounded-xl border border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-gray-900 dark:text-white px-4 py-2 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                </div>

                <!-- Address -->
                <div class="col-span-1 sm:col-span-2">
                    <label for="address" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Address</label>
                    <textarea name="address" id="address" rows="3"
                              class="w-full rounded-xl border border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-gray-900 dark:text-white px-4 py-2 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition resize-none">{{ old('address', $business->address ?? '') }}</textarea>
                </div>

                <!-- Logo -->
                <div class="col-span-1 sm:col-span-2">
                    <label for="logo" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Business Logo</label>
                    @if($isEdit && $business->logo)
                        <div class="mb-3">
                            <img src="{{ asset('storage/' . $business->logo) }}" alt="Logo" class="w-20 h-20 object-cover rounded-full shadow-md">
                        </div>
                    @endif
                    <input type="file" name="logo" id="logo"
                           class="w-full text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-neutral-800 border border-gray-300 dark:border-neutral-700 file:rounded-md file:px-4 file:py-1.5 file:bg-blue-100 file:text-blue-800 file:border-0 file:font-semibold">
                </div>

                <!-- Buttons -->
                <div class="col-span-1 sm:col-span-2 flex justify-start pt-4">
                    <button type="submit"
                            class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white font-semibold px-6 py-2 rounded-xl shadow hover:from-indigo-600 hover:to-blue-700 transition">
                        {{ $isEdit ? 'Update Business' : 'Create Business' }}
                    </button>
                    <a href="{{ route('businesses.index') }}"
                       class="ml-4 text-gray-600 dark:text-gray-300 hover:underline font-medium transition">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
