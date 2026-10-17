<x-layouts.app :title="__('Edit Purchase #'.($purchase->invoice_no ?? $purchase->id))">

    <div class="mx-auto max-w-7xl space-y-5">

        {{-- ========================================================= --}}
        {{-- HEADER --}}
        {{-- ========================================================= --}}
        <div
            class="flex flex-col gap-4 rounded-2xl
                   border border-slate-200 bg-white
                   p-5 shadow-sm
                   dark:border-slate-700 dark:bg-[#1b2128]
                   sm:flex-row sm:items-center sm:justify-between"
        >

            <div class="flex items-center gap-3">

                <div
                    class="flex h-11 w-11 items-center justify-center
                           rounded-xl bg-blue-100 text-blue-700
                           dark:bg-blue-500/10 dark:text-blue-400"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.8"
                        stroke="currentColor"
                        class="h-5 w-5"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M16.862 3.487a2.25 2.25 0 013.182 3.182L8.25 18.462l-4.5 1.125 1.125-4.5L16.862 3.487z"
                        />
                    </svg>
                </div>

                <div>

                    <h1
                        class="text-xl font-bold text-slate-900
                               dark:text-white"
                    >
                        Edit Purchase
                    </h1>

                    <div
                        class="mt-1 flex flex-wrap gap-x-4 gap-y-1
                               text-sm text-slate-500
                               dark:text-slate-400"
                    >

                        <span>
                            Invoice:
                            <strong
                                class="text-slate-700
                                       dark:text-slate-200"
                            >
                                {{ $purchase->invoice_no ?: 'PUR-'.$purchase->id }}
                            </strong>
                        </span>

                        @if($purchase->invoice_date)

                            <span>
                                {{ \Carbon\Carbon::parse($purchase->invoice_date)->format('d M Y') }}
                            </span>

                        @endif

                    </div>

                </div>

            </div>


            <div class="flex gap-2">

                <a
                    href="{{ route('purchases.show', $purchase) }}"
                    class="inline-flex items-center justify-center
                           rounded-xl border border-blue-200
                           bg-blue-50 px-4 py-2.5
                           text-sm font-semibold text-blue-700
                           hover:bg-blue-100
                           dark:border-blue-900
                           dark:bg-blue-500/10
                           dark:text-blue-400"
                >
                    View
                </a>

                <a
                    href="{{ route('purchases.index') }}"
                    class="inline-flex items-center justify-center
                           rounded-xl border border-slate-300
                           px-4 py-2.5 text-sm font-semibold
                           text-slate-700 hover:bg-slate-50
                           dark:border-slate-600
                           dark:text-slate-200
                           dark:hover:bg-slate-800"
                >
                    ← Back
                </a>

            </div>

        </div>


        <form
            action="{{ route('purchases.update', $purchase) }}"
            method="POST"
            enctype="multipart/form-data"
            class="space-y-5"
        >

            @csrf
            @method('PUT')


            @if ($errors->any())

                <div
                    class="rounded-xl border border-red-200
                           bg-red-50 p-4 text-sm
                           text-red-700
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
                class="sticky bottom-0 z-20
                       flex flex-col-reverse gap-3
                       rounded-2xl border border-slate-200
                       bg-white/95 p-4 shadow-lg
                       backdrop-blur
                       dark:border-slate-700
                       dark:bg-[#1b2128]/95
                       sm:flex-row sm:items-center
                       sm:justify-end"
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
                    class="inline-flex items-center justify-center
                           gap-2 rounded-xl bg-blue-600
                           px-6 py-2.5 text-sm font-semibold
                           text-white shadow-sm
                           transition hover:bg-blue-700"
                >
                    Update Purchase
                </button>

            </div>

        </form>

    </div>

</x-layouts.app>