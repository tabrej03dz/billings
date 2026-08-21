<x-layouts.app :title="__('Edit Item')">

    <div class="min-h-screen bg-slate-100 py-5 dark:bg-[#0f1419] sm:py-8">
        <div class="mx-auto max-w-5xl px-3 sm:px-6 lg:px-8">

            {{-- Page Header --}}
            <div class="relative mb-6 overflow-hidden rounded-2xl
                        bg-gradient-to-r from-indigo-700 via-blue-600 to-cyan-600
                        px-5 py-6 shadow-lg sm:px-8 sm:py-8">

                {{-- Decorative circles --}}
                <div class="absolute -right-14 -top-14 h-40 w-40 rounded-full bg-white/10"></div>
                <div class="absolute -bottom-20 -left-10 h-44 w-44 rounded-full bg-white/10"></div>

                <div class="relative flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">

                    <div>
                        <div class="mb-2 flex items-center gap-2">

                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-white/20">
                                <svg
                                    class="h-5 w-5 text-white"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                    />
                                </svg>
                            </span>

                            <span class="rounded-full bg-white/15 px-3 py-1 text-xs font-semibold text-white">
                                Inventory Management
                            </span>

                        </div>

                        <h1 class="text-2xl font-bold text-white sm:text-3xl">
                            Edit Item
                        </h1>

                        <p class="mt-2 max-w-2xl text-sm text-blue-50 sm:text-base">
                            {{ $item->name }} ki details, pricing aur stock information update karein.
                        </p>
                    </div>

                    <a
                        href="{{ route('items.index') }}"
                        class="inline-flex w-fit items-center justify-center gap-2 rounded-xl
                               border border-white/30 bg-white/15 px-4 py-2.5
                               text-sm font-semibold text-white backdrop-blur-sm
                               transition hover:bg-white hover:text-blue-700"
                    >
                        <svg
                            class="h-4 w-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M15 19l-7-7 7-7"
                            />
                        </svg>

                        Back to Items
                    </a>

                </div>
            </div>

            {{-- Main Card --}}
            <div class="overflow-hidden rounded-2xl border border-slate-200
                        bg-white shadow-xl shadow-slate-200/60
                        dark:border-slate-700 dark:bg-[#171c22]
                        dark:shadow-none">

                {{-- Card Header --}}
                <div class="border-b border-slate-200 bg-slate-50
                            px-5 py-4 dark:border-slate-700
                            dark:bg-[#1d242c] sm:px-7">

                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

                        <div class="flex items-center gap-3">

                            <div class="flex h-10 w-10 items-center justify-center
                                        rounded-xl bg-blue-100 text-blue-700
                                        dark:bg-blue-900/40 dark:text-blue-300">
                                <svg
                                    class="h-5 w-5"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l3.414 3.414A1 1 0 0117 7.414V19a2 2 0 01-2 2z"
                                    />
                                </svg>
                            </div>

                            <div>
                                <h2 class="text-lg font-bold text-slate-900 dark:text-white">
                                    Item Information
                                </h2>

                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                    Star (*) wali fields required hain
                                </p>
                            </div>

                        </div>

                        <div class="inline-flex w-fit items-center gap-2 rounded-lg
                                    bg-slate-200 px-3 py-1.5 text-xs font-semibold
                                    text-slate-700 dark:bg-slate-700
                                    dark:text-slate-200">
                            Item ID: #{{ $item->id }}
                        </div>

                    </div>
                </div>

                {{-- Validation Errors --}}
                @if($errors->any())
                    <div class="mx-5 mt-5 rounded-xl border border-red-200
                                bg-red-50 p-4 dark:border-red-900
                                dark:bg-red-950/30 sm:mx-7">

                        <div class="flex items-start gap-3">

                            <div class="mt-0.5 flex h-8 w-8 flex-none items-center
                                        justify-center rounded-lg bg-red-100
                                        text-red-600 dark:bg-red-900/50">
                                <svg
                                    class="h-5 w-5"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.667 1.73-3L13.73 4c-.77-1.333-2.69-1.333-3.46 0L3.34 16c-.77 1.333.19 3 1.73 3z"
                                    />
                                </svg>
                            </div>

                            <div>
                                <p class="text-sm font-bold text-red-800 dark:text-red-300">
                                    Please correct the following errors:
                                </p>

                                <ul class="mt-2 list-inside list-disc space-y-1
                                           text-sm text-red-700 dark:text-red-400">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>

                        </div>
                    </div>
                @endif

                {{-- Update Form --}}
                <form
                    action="{{ route('items.update', $item->id) }}" enctype="multipart/form-data"
                    method="POST"
                    id="editItemForm"
                    class="p-5 sm:p-7"
                >
                    @csrf
                    @method('PUT')

                    @include('items._form', [
                        'item' => $item,
                        'categories' => $categories,
                        'units' => $units,
                        'allowedFields' => $allowedFields ?? []
                    ])
                </form>

            </div>

            {{-- Information Box --}}
            <div class="mt-5 flex items-start gap-3 rounded-xl
                        border border-amber-200 bg-amber-50 p-4
                        dark:border-amber-900 dark:bg-amber-950/30">

                <svg
                    class="mt-0.5 h-5 w-5 flex-none text-amber-600 dark:text-amber-400"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                    />
                </svg>

                <p class="text-sm leading-6 text-amber-800 dark:text-amber-300">
                    Item update karne ke baad purani information replace ho jayegi.
                    Save karne se pehle price, stock aur category verify kar lein.
                </p>

            </div>

        </div>
    </div>

</x-layouts.app>