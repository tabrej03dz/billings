<x-layouts.app :title="__('Edit Plan')">
    <div class="max-w-5xl mx-auto flex flex-col gap-4">

        @if ($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-red-700">
                <strong class="block mb-2">Please fix the following errors:</strong>
                <ul class="list-disc ml-5 text-sm space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="flex items-center justify-between bg-[#BFE0E0] dark:bg-[#354A54] p-6 rounded-xl">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Edit Plan</h1>

            <a href="{{ route('plans.index') }}"
               class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-gray-600 rounded-lg hover:bg-gray-700">
                Back
            </a>
        </div>

        <form action="{{ route('plans.update', $plan->id) }}" method="POST"
              class="bg-white dark:bg-neutral-900 rounded-xl border border-gray-200 dark:border-gray-700 p-6 space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium mb-1 dark:text-white">Plan Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $plan->name) }}"
                           class="w-full border rounded-lg px-3 py-2 dark:bg-neutral-800 dark:border-neutral-700 dark:text-white">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1 dark:text-white">Subtitle</label>
                    <input type="text" name="subtitle" value="{{ old('subtitle', $plan->subtitle) }}"
                           class="w-full border rounded-lg px-3 py-2 dark:bg-neutral-800 dark:border-neutral-700 dark:text-white">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1 dark:text-white">Slug</label>
                    <input type="text" name="slug" value="{{ old('slug', $plan->slug) }}"
                           class="w-full border rounded-lg px-3 py-2 dark:bg-neutral-800 dark:border-neutral-700 dark:text-white">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1 dark:text-white">Price <span class="text-red-500">*</span></label>
                    <input type="number" step="0.01" name="price" value="{{ old('price', $plan->price) }}"
                           class="w-full border rounded-lg px-3 py-2 dark:bg-neutral-800 dark:border-neutral-700 dark:text-white">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1 dark:text-white">Duration Days <span class="text-red-500">*</span></label>
                    <input type="number" name="duration_days" value="{{ old('duration_days', $plan->duration_days) }}"
                           class="w-full border rounded-lg px-3 py-2 dark:bg-neutral-800 dark:border-neutral-700 dark:text-white">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1 dark:text-white">Sort Order</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $plan->sort_order) }}"
                           class="w-full border rounded-lg px-3 py-2 dark:bg-neutral-800 dark:border-neutral-700 dark:text-white">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1 dark:text-white">Description</label>
                <textarea name="description" rows="4"
                          class="w-full border rounded-lg px-3 py-2 dark:bg-neutral-800 dark:border-neutral-700 dark:text-white">{{ old('description', $plan->description) }}</textarea>
            </div>

            <div class="rounded-xl border border-gray-200 dark:border-neutral-700 p-4">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <label class="block text-sm font-semibold dark:text-white">Plan Features</label>
                        <p class="text-xs text-gray-500">Multiple features add/edit/remove kar sakte hain.</p>
                    </div>

                    <button type="button" id="addFeatureBtn"
                            class="px-4 py-2 text-sm rounded-lg bg-blue-600 text-white hover:bg-blue-700">
                        + Add Feature
                    </button>
                </div>

                <div id="featuresWrapper" class="space-y-4">
                    @php
                        $existingFeatures = $plan->planFeatures ?? collect();

                        $oldTitles = old('feature_titles');

                        if ($oldTitles !== null) {
                            $featureRows = collect($oldTitles)->map(function ($title, $index) {
                                return [
                                    'title' => $title,
                                    'description' => old('feature_descriptions.' . $index),
                                    'icon' => old('feature_icons.' . $index),
                                    'sort_order' => old('feature_sort_orders.' . $index, $index),
                                    'is_active' => old('feature_is_active.' . $index) ? true : false,
                                ];
                            });
                        } else {
                            $featureRows = $existingFeatures->count()
                                ? $existingFeatures->map(function ($feature) {
                                    return [
                                        'title' => $feature->title,
                                        'description' => $feature->description,
                                        'icon' => $feature->icon,
                                        'sort_order' => $feature->sort_order,
                                        'is_active' => $feature->is_active,
                                    ];
                                })
                                : collect([
                                    [
                                        'title' => '',
                                        'description' => '',
                                        'icon' => '',
                                        'sort_order' => 0,
                                        'is_active' => true,
                                    ]
                                ]);
                        }
                    @endphp

                    @foreach($featureRows as $index => $featureRow)
                        <div class="feature-item rounded-lg border border-gray-200 dark:border-neutral-700 p-4 bg-gray-50 dark:bg-neutral-800">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium mb-1 dark:text-white">Feature Title</label>
                                    <input type="text" name="feature_titles[]" value="{{ $featureRow['title'] }}"
                                           class="w-full border rounded-lg px-3 py-2 dark:bg-neutral-900 dark:border-neutral-700 dark:text-white"
                                           placeholder="Example: GST Billing">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium mb-1 dark:text-white">Icon</label>
                                    <input type="text" name="feature_icons[]" value="{{ $featureRow['icon'] }}"
                                           class="w-full border rounded-lg px-3 py-2 dark:bg-neutral-900 dark:border-neutral-700 dark:text-white"
                                           placeholder="Example: receipt / check / star">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium mb-1 dark:text-white">Sort Order</label>
                                    <input type="number" name="feature_sort_orders[]" value="{{ $featureRow['sort_order'] }}"
                                           class="w-full border rounded-lg px-3 py-2 dark:bg-neutral-900 dark:border-neutral-700 dark:text-white">
                                </div>

                                <div class="flex items-center gap-3 pt-6">
                                    <input type="checkbox" name="feature_is_active[{{ $index }}]" value="1"
                                           {{ $featureRow['is_active'] ? 'checked' : '' }}
                                           class="rounded border-gray-300 text-green-600 shadow-sm">
                                    <span class="text-sm font-medium dark:text-white">Feature Active</span>
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium mb-1 dark:text-white">Feature Description</label>
                                    <textarea name="feature_descriptions[]" rows="2"
                                              class="w-full border rounded-lg px-3 py-2 dark:bg-neutral-900 dark:border-neutral-700 dark:text-white"
                                              placeholder="Optional feature detail">{{ $featureRow['description'] }}</textarea>
                                </div>
                            </div>

                            <div class="mt-3 text-right">
                                <button type="button"
                                        class="remove-feature px-3 py-1.5 text-sm rounded bg-red-500 text-white hover:bg-red-600">
                                    Remove
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-6">
                <label class="flex items-center gap-3">
                    <input type="checkbox" name="status" value="1"
                           {{ old('status', $plan->status) ? 'checked' : '' }}
                           class="rounded border-gray-300 text-green-600 shadow-sm">
                    <span class="text-sm font-medium dark:text-white">Active</span>
                </label>

                <label class="flex items-center gap-3">
                    <input type="checkbox" name="is_recommended" value="1"
                           {{ old('is_recommended', $plan->is_recommended) ? 'checked' : '' }}
                           class="rounded border-gray-300 text-yellow-600 shadow-sm">
                    <span class="text-sm font-medium dark:text-white">Recommended Plan</span>
                </label>
            </div>

            <div>
                <label class="block text-sm font-medium mb-3 dark:text-white">Assign Permissions</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 border rounded-xl p-4 dark:border-neutral-700">
                    @foreach($permissions as $permission)
                        <label class="flex items-center gap-2 text-sm dark:text-gray-200">
                            <input type="checkbox" name="permission_ids[]" value="{{ $permission->id }}"
                                   {{ in_array($permission->id, old('permission_ids', $selectedPermissions ?? [])) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-blue-600 shadow-sm">
                            <span>{{ $permission->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <button type="submit" class="px-5 py-2.5 rounded-lg bg-green-600 text-white font-medium hover:bg-green-700">
                Update Plan
            </button>
        </form>
    </div>

    <script>
        let featureIndex = document.querySelectorAll('.feature-item').length;

        document.getElementById('addFeatureBtn').addEventListener('click', function () {
            const wrapper = document.getElementById('featuresWrapper');

            const html = `
                <div class="feature-item rounded-lg border border-gray-200 dark:border-neutral-700 p-4 bg-gray-50 dark:bg-neutral-800">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1 dark:text-white">Feature Title</label>
                            <input type="text" name="feature_titles[]"
                                   class="w-full border rounded-lg px-3 py-2 dark:bg-neutral-900 dark:border-neutral-700 dark:text-white"
                                   placeholder="Example: GST Billing">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1 dark:text-white">Icon</label>
                            <input type="text" name="feature_icons[]"
                                   class="w-full border rounded-lg px-3 py-2 dark:bg-neutral-900 dark:border-neutral-700 dark:text-white"
                                   placeholder="Example: receipt / check / star">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1 dark:text-white">Sort Order</label>
                            <input type="number" name="feature_sort_orders[]" value="${featureIndex}"
                                   class="w-full border rounded-lg px-3 py-2 dark:bg-neutral-900 dark:border-neutral-700 dark:text-white">
                        </div>

                        <div class="flex items-center gap-3 pt-6">
                            <input type="checkbox" name="feature_is_active[${featureIndex}]" value="1" checked
                                   class="rounded border-gray-300 text-green-600 shadow-sm">
                            <span class="text-sm font-medium dark:text-white">Feature Active</span>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium mb-1 dark:text-white">Feature Description</label>
                            <textarea name="feature_descriptions[]" rows="2"
                                      class="w-full border rounded-lg px-3 py-2 dark:bg-neutral-900 dark:border-neutral-700 dark:text-white"
                                      placeholder="Optional feature detail"></textarea>
                        </div>
                    </div>

                    <div class="mt-3 text-right">
                        <button type="button"
                                class="remove-feature px-3 py-1.5 text-sm rounded bg-red-500 text-white hover:bg-red-600">
                            Remove
                        </button>
                    </div>
                </div>
            `;

            wrapper.insertAdjacentHTML('beforeend', html);
            featureIndex++;
        });

        document.addEventListener('click', function (e) {
            if (e.target.classList.contains('remove-feature')) {
                const items = document.querySelectorAll('.feature-item');

                if (items.length <= 1) {
                    e.target.closest('.feature-item').querySelectorAll('input, textarea').forEach(function (field) {
                        if (field.type === 'checkbox') {
                            field.checked = true;
                        } else {
                            field.value = '';
                        }
                    });

                    return;
                }

                e.target.closest('.feature-item').remove();
            }
        });
    </script>
</x-layouts.app>