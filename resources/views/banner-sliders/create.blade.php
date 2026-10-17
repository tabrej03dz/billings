<x-layouts.app :title="__('Create Banner Slider')">
    <div class="flex flex-col gap-4">

        <div class="flex items-center justify-between bg-[#BFE0E0] dark:bg-[#354A54] p-6">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Create Banner Slider</h1>

            <a href="{{ route('banner-sliders.index') }}"
               class="px-4 py-2 text-sm bg-gray-600 text-white rounded hover:bg-gray-700">
                Back
            </a>
        </div>

        @if ($errors->any())
            <div class="p-3 rounded-lg bg-red-50 text-red-700 border border-red-200">
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('banner-sliders.store') }}"
              method="POST"
              enctype="multipart/form-data"
              class="bg-white dark:bg-neutral-900 p-6 rounded-xl border border-gray-200 dark:border-gray-700">

            @csrf

            @include('banner-sliders.form', [
                'banner' => null,
                'buttonText' => 'Create Banner'
            ])

        </form>
    </div>
</x-layouts.app>