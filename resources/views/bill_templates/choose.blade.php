<x-layouts.app :title="__('Choose Bill Template')">
    <div class="flex flex-col gap-4">

        <x-billing-setup-guide :step="1" />

        @if(session('success'))
            <div class="p-3 rounded-lg bg-green-50 text-green-700 border border-green-200">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="p-3 rounded-lg bg-red-50 text-red-700 border border-red-200">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="p-3 rounded-lg bg-red-50 text-red-700 border border-red-200">
                <ul class="list-disc pl-5 text-sm space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="flex flex-wrap items-center justify-between gap-3 bg-[#BFE0E0] dark:bg-[#354A54] p-6 rounded-xl">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Choose Bill Template</h1>
                <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">
                    Apne business ke liye PDF template select kijiye
                </p>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                <form method="GET" class="flex items-center gap-2">
                    <input type="text"
                           name="q"
                           value="{{ request('q') }}"
                           placeholder="Search name / page name / description..."
                           class="border rounded px-3 py-2 text-sm w-64 dark:bg-neutral-800 dark:border-neutral-700 dark:text-white" />
                    <button class="px-3 py-2 text-sm rounded bg-gray-500 hover:bg-gray-600 text-white">
                        Search
                    </button>

                    @if(request('q'))
                        <a href="{{ route('bill-templates.choose') }}"
                           class="text-sm text-gray-700 dark:text-gray-200 hover:underline">
                            Clear
                        </a>
                    @endif
                </form>

                {{-- <a href="{{route('businesses.edit', session('active_business_id'))}}"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700">
                    Business Profile
                </a> --}}

                <a href="{{ route('bill-templates.index') }}"
                   class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                    Back
                </a>
            </div>
        </div>

        @if($business)
            <div class="p-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-neutral-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">Current Business</p>
                <h2 class="text-lg font-semibold text-gray-800 dark:text-white">
                    {{ $business->name ?? $business->business_name ?? 'Business' }}
                </h2>
            </div>
        @endif

        @if($billTemplates->count())
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                @foreach($billTemplates as $billTemplate)
                    @php
                        $isSelected = (int) $selectedTemplateId === (int) $billTemplate->id;
                        $preview = $billTemplate->preview;
                        $ext = $preview ? strtolower(pathinfo($preview, PATHINFO_EXTENSION)) : null;
                        $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'webp']);
                        $isPdf = $ext === 'pdf';
                    @endphp

                    <div class="rounded-xl border overflow-hidden shadow-sm bg-white dark:bg-neutral-900 dark:border-gray-700 transition-all duration-200 {{ $isSelected ? 'border-green-500 ring-2 ring-green-200 dark:ring-green-800' : 'border-gray-200 hover:shadow-md' }}">

                        {{-- Preview --}}
                        <div class="bg-gray-100 dark:bg-neutral-800 p-4">
                            <div class="mx-auto bg-white rounded-lg shadow border max-w-[260px] h-[320px] overflow-hidden flex items-center justify-center">

                                @if($preview && $isImage)
                                    <img src="{{ asset('storage/' . $preview) }}"
                                         alt="{{ $billTemplate->name }}"
                                         class="w-full h-full object-cover">

                                @elseif($preview && $isPdf)
                                    <iframe
                                        src="{{ asset('storage/' . $preview) }}#toolbar=0&navpanes=0&scrollbar=0&view=FitH"
                                        class="w-full h-full"
                                        style="border: 0;"
                                        loading="lazy">
                                    </iframe>

                                @else
                                    <div class="text-center text-gray-400 p-6">
                                        <div class="text-4xl mb-2">🖼️</div>
                                        <p class="text-sm">No Preview</p>
                                    </div>
                                @endif

                            </div>
                        </div>

                        {{-- Content --}}
                        <div class="p-5">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-800 dark:text-white">
                                        {{ $billTemplate->name }}
                                    </h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 break-all">
                                        Page: {{ $billTemplate->page_name }}
                                    </p>
                                </div>

                                @if($isSelected)
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700 border border-green-200 whitespace-nowrap">
                                        Selected
                                    </span>
                                @endif
                            </div>

                            <p class="mt-3 text-sm text-gray-600 dark:text-gray-300 min-h-[48px]">
                                {{ \Illuminate\Support\Str::limit($billTemplate->description, 100) ?: 'No description available.' }}
                            </p>

                            <div class="mt-5 flex items-center justify-between gap-2">
                                <a href="{{ asset('storage/' . $preview) }}"
                                   class="bg-blue-500 text-white px-4 py-2 hover:bg-blue-600 rounded inline-block text-sm">
                                    View
                                </a>

                                <form action="{{ route('bill-templates.saveChosen') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="template_id" value="{{ $billTemplate->id }}">

                                    <button type="submit"
                                            class="px-4 py-2 rounded text-sm font-medium text-white {{ $isSelected ? 'bg-green-600 hover:bg-green-700' : 'bg-indigo-600 hover:bg-indigo-700' }}">
                                        {{ $isSelected ? 'Selected' : 'Choose Template' }}
                                    </button>
                                </form>

                               <a href="{{ route('bill-templates.customize', $billTemplate->id) }}"
                                class="px-4 py-2 rounded text-sm font-medium text-white bg-yellow-500 hover:bg-yellow-600">
                                    Customize
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-neutral-900 p-10 text-center">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white">No bill templates found</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                    Pehle bill templates create kijiye.
                </p>

                <a href="{{ route('bill-templates.create') }}"
                   class="inline-flex items-center px-4 py-2 mt-4 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700">
                    + New Bill Template
                </a>
            </div>
        @endif
    </div>
</x-layouts.app>