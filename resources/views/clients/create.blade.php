<x-layouts.app :title="__('Add Client')">
    <div class="flex flex-col gap-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Add Client</h1>

        <form method="POST" action="{{ route('clients.store') }}" class="space-y-5">
            @csrf

            <div class="grid md:grid-cols-2 gap-4">
                <!-- Name -->
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
                    <input type="text" name="name" value="{{ old('name') }}"
                           class="border border-gray-300 dark:border-gray-600 bg-white dark:bg-neutral-800 text-black dark:text-white rounded-md px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-400"
                           required>
                    @error('name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Mobile -->
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Mobile</label>
                    <input type="text" name="mobile" value="{{ old('mobile') }}"
                           class="border border-gray-300 dark:border-gray-600 bg-white dark:bg-neutral-800 text-black dark:text-white rounded-md px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-400"
                           required>
                    @error('mobile')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- GSTIN -->
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">GSTIN</label>
                    <input type="text" name="gstin" value="{{ old('gstin') }}"
                           class="border border-gray-300 dark:border-gray-600 bg-white dark:bg-neutral-800 text-black dark:text-white rounded-md px-4 py-2 w-full">
                    @error('gstin')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- PAN -->
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">PAN</label>
                    <input type="text" name="pan" value="{{ old('pan') }}"
                           class="border border-gray-300 dark:border-gray-600 bg-white dark:bg-neutral-800 text-black dark:text-white rounded-md px-4 py-2 w-full">
                    @error('pan')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- State -->
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">State</label>
                    <input type="text" name="state" value="{{ old('state') }}"
                           class="border border-gray-300 dark:border-gray-600 bg-white dark:bg-neutral-800 text-black dark:text-white rounded-md px-4 py-2 w-full">
                    @error('state')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Business Dropdown (only for Super Admin) -->
                @role('super admin')
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Select Business</label>
                    <select name="business_id"
                            class="border border-gray-300 dark:border-gray-600 bg-white dark:bg-neutral-800 text-black dark:text-white rounded-md px-4 py-2 w-full"
                            required>
                        <option value="">Choose Business</option>
                        @foreach($businesses as $business)
                            <option value="{{ $business->id }}" {{ old('business_id') == $business->id ? 'selected' : '' }}>
                                {{ $business->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('business_id')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                @endrole

                <!-- Address -->
                <div class="md:col-span-2">
                    <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Address</label>
                    <textarea name="address" rows="3"
                              class="border border-gray-300 dark:border-gray-600 bg-white dark:bg-neutral-800 text-black dark:text-white rounded-md px-4 py-2 w-full"
                              required>{{ old('address') }}</textarea>
                    @error('address')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Submit Button -->
            <div>
                <button type="submit"
                        class="px-6 py-2 text-white bg-green-600 hover:bg-green-700 rounded-lg">
                    Save Client
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>
