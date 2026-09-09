<x-layouts.app :title="__('New Purchase')">

    <div class="mx-auto max-w-7xl space-y-5">

        {{-- ========================================================= --}}
        {{-- PAGE HEADER --}}
        {{-- ========================================================= --}}
        <div
            class="flex flex-col gap-4 rounded-2xl border border-slate-200
                   bg-white p-5 shadow-sm
                   dark:border-slate-700 dark:bg-[#1b2128]
                   sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <div class="flex items-center gap-3">

                    <div
                        class="flex h-11 w-11 items-center justify-center
                               rounded-xl bg-emerald-100 text-emerald-700
                               dark:bg-emerald-500/10 dark:text-emerald-400"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.8"
                            stroke="currentColor"
                            class="h-6 w-6"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M12 4.5v15m7.5-7.5h-15"
                            />
                        </svg>
                    </div>

                    <div>
                        <h1
                            class="text-xl font-bold text-slate-900
                                   dark:text-white"
                        >
                            New Purchase
                        </h1>

                        <p
                            class="mt-1 text-sm text-slate-500
                                   dark:text-slate-400"
                        >
                            Add supplier purchase, GST and stock details
                        </p>
                    </div>

                </div>
            </div>

            <a
                href="{{ route('purchases.index') }}"
                class="inline-flex items-center justify-center
                       rounded-xl border border-slate-300
                       px-4 py-2.5 text-sm font-semibold
                       text-slate-700 transition
                       hover:bg-slate-50
                       dark:border-slate-600 dark:text-slate-200
                       dark:hover:bg-slate-800"
            >
                ← Back to Purchases
            </a>
        </div>


        <form
            action="{{ route('purchases.store') }}"
            method="POST"
            enctype="multipart/form-data"
            class="space-y-5"
        >
            @csrf


            @if ($errors->any())

                <div
                    class="rounded-xl border border-red-200
                           bg-red-50 p-4 text-sm text-red-700
                           dark:border-red-900/50
                           dark:bg-red-950/30
                           dark:text-red-300"
                >
                    <div class="mb-2 font-semibold">
                        Please correct the following:
                    </div>

                    <ul class="list-disc space-y-1 pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>

            @endif


            @include(
                'purchases._form',
                [
                    'purchase' => $purchase,
                    'suppliers' => $suppliers,
                    'items' => $items,
                    'units' => $units,
                ]
            )


            <div
                class="sticky bottom-0 z-20 flex flex-col-reverse gap-3
                       rounded-2xl border border-slate-200
                       bg-white/95 p-4 shadow-lg backdrop-blur
                       dark:border-slate-700 dark:bg-[#1b2128]/95
                       sm:flex-row sm:items-center sm:justify-end"
            >

                <a
                    href="{{ route('purchases.index') }}"
                    class="inline-flex items-center justify-center
                           rounded-xl border border-slate-300
                           px-5 py-2.5 text-sm font-semibold
                           text-slate-700
                           hover:bg-slate-50
                           dark:border-slate-600
                           dark:text-slate-200
                           dark:hover:bg-slate-800"
                >
                    Cancel
                </a>

                <button
                    type="submit"
                    class="inline-flex items-center justify-center gap-2
                           rounded-xl bg-emerald-600
                           px-6 py-2.5 text-sm font-semibold
                           text-white shadow-sm transition
                           hover:bg-emerald-700"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="2"
                        stroke="currentColor"
                        class="h-4 w-4"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M4.5 12.75l6 6 9-13.5"
                        />
                    </svg>

                    Save Purchase
                </button>

            </div>

        </form>

    </div>

</x-layouts.app>