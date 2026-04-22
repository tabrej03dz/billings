<x-layouts.app :title="__('Edit User Plan')">
    <div class="flex flex-col gap-4">

        <div class="flex items-center justify-between bg-[#BFE0E0] dark:bg-[#354A54] p-6 rounded-xl">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Edit User Plan</h1>

            <a href="{{ route('user-plans.index') }}"
               class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-gray-600 rounded-lg hover:bg-gray-700">
                Back
            </a>
        </div>

        <div class="bg-white dark:bg-neutral-900 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <form action="{{ route('user-plans.update', $userPlan->id) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-200">User</label>
                        <select name="user_id" class="w-full rounded-lg border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white">
                            <option value="">Select User</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}"
                                    {{ old('user_id', $userPlan->user_id) == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->email }})
                                </option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-200">Plan</label>
                        <select name="plan_id" id="plan_id" class="w-full rounded-lg border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white">
                            <option value="">Select Plan</option>
                            @foreach($plans as $plan)
                                <option value="{{ $plan->id }}"
                                        data-duration="{{ $plan->duration_days }}"
                                    {{ old('plan_id', $userPlan->plan_id) == $plan->id ? 'selected' : '' }}>
                                    {{ $plan->name }} - ₹{{ number_format($plan->price, 2) }} / {{ $plan->duration_days }} days
                                </option>
                            @endforeach
                        </select>
                        @error('plan_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-200">Start Date</label>
                        <input type="date" name="start_date" id="start_date"
                               value="{{ old('start_date', optional($userPlan->start_date)->format('Y-m-d') ?? \Carbon\Carbon::parse($userPlan->start_date)->format('Y-m-d')) }}"
                               class="w-full rounded-lg border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white">
                        @error('start_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-200">Expiry Date</label>
                        <input type="date" name="expiry_date" id="expiry_date"
                               value="{{ old('expiry_date', optional($userPlan->expiry_date)->format('Y-m-d') ?? \Carbon\Carbon::parse($userPlan->expiry_date)->format('Y-m-d')) }}"
                               class="w-full rounded-lg border-gray-300 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white">
                        @error('expiry_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="inline-flex items-center gap-2">
                            <input type="hidden" name="status" value="0">
                            <input type="checkbox" name="status" value="1"
                                   {{ old('status', $userPlan->status) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Active</span>
                        </label>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <button type="submit"
                            class="px-5 py-2.5 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700">
                        Update
                    </button>

                    <a href="{{ route('user-plans.index') }}"
                       class="px-5 py-2.5 text-sm font-medium text-white bg-gray-500 rounded-lg hover:bg-gray-600">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>