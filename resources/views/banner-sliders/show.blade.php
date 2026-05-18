<x-layouts.app :title="__('Banner Slider Details')">
    <div class="flex flex-col gap-4">

        <div class="flex items-center justify-between bg-[#BFE0E0] dark:bg-[#354A54] p-6">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Banner Slider Details</h1>

            <div class="flex gap-2">
                <a href="{{ route('banner-sliders.edit', $bannerSlider->id) }}"
                   class="px-4 py-2 text-sm bg-yellow-500 text-white rounded hover:bg-yellow-600">
                    Edit
                </a>

                <a href="{{ route('banner-sliders.index') }}"
                   class="px-4 py-2 text-sm bg-gray-600 text-white rounded hover:bg-gray-700">
                    Back
                </a>
            </div>
        </div>

        <div class="bg-white dark:bg-neutral-900 p-6 rounded-xl border border-gray-200 dark:border-gray-700">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <div>
                    <h2 class="text-sm font-semibold mb-2">Desktop Image</h2>
                    @if($bannerSlider->image)
                        <img src="{{ asset('storage/' . $bannerSlider->image) }}"
                             class="w-full max-w-md rounded border object-cover">
                    @else
                        <p>—</p>
                    @endif
                </div>

                <div>
                    <h2 class="text-sm font-semibold mb-2">Mobile Image</h2>
                    @if($bannerSlider->mobile_image)
                        <img src="{{ asset('storage/' . $bannerSlider->mobile_image) }}"
                             class="w-full max-w-xs rounded border object-cover">
                    @else
                        <p>—</p>
                    @endif
                </div>

                <div>
                    <strong>Title:</strong>
                    <p>{{ $bannerSlider->title ?? '—' }}</p>
                </div>

                <div>
                    <strong>Subtitle:</strong>
                    <p>{{ $bannerSlider->subtitle ?? '—' }}</p>
                </div>

                <div>
                    <strong>Button Text:</strong>
                    <p>{{ $bannerSlider->button_text ?? '—' }}</p>
                </div>

                <div>
                    <strong>Button URL:</strong>
                    <p>
                        @if($bannerSlider->button_url)
                            <a href="{{ $bannerSlider->button_url }}" target="_blank" class="text-blue-600 underline">
                                {{ $bannerSlider->button_url }}
                            </a>
                        @else
                            —
                        @endif
                    </p>
                </div>

                <div>
                    <strong>Alt Text:</strong>
                    <p>{{ $bannerSlider->alt_text ?? '—' }}</p>
                </div>

                <div>
                    <strong>Sort Order:</strong>
                    <p>{{ $bannerSlider->sort_order }}</p>
                </div>

                <div>
                    <strong>Status:</strong>
                    <p>
                        @if($bannerSlider->is_active)
                            <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-700">
                                Active
                            </span>
                        @else
                            <span class="px-2 py-1 text-xs rounded bg-red-100 text-red-700">
                                Inactive
                            </span>
                        @endif
                    </p>
                </div>

                <div class="md:col-span-2">
                    <strong>Description:</strong>
                    <p>{{ $bannerSlider->description ?? '—' }}</p>
                </div>

            </div>
        </div>
    </div>
</x-layouts.app>