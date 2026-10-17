<x-layouts.app :title="__('Import Items')">

    <div class="min-h-screen bg-slate-100 py-5 dark:bg-[#0f1419] sm:py-8">

        <div class="mx-auto max-w-6xl px-3 sm:px-6 lg:px-8">

            {{-- Header --}}
            <div
                class="relative mb-6 overflow-hidden rounded-2xl
                    bg-gradient-to-r from-cyan-700 via-teal-600
                    to-emerald-600 px-5 py-6 shadow-lg
                    sm:px-8 sm:py-8"
            >

                <div
                    class="absolute -right-14 -top-14
                        h-40 w-40 rounded-full bg-white/10"
                ></div>

                <div
                    class="absolute -bottom-20 -left-10
                        h-44 w-44 rounded-full bg-white/10"
                ></div>

                <div
                    class="relative flex flex-col gap-5
                        sm:flex-row sm:items-center
                        sm:justify-between"
                >

                    <div>

                        <div class="mb-2 flex items-center gap-2">

                            <span
                                class="inline-flex h-10 w-10
                                    items-center justify-center
                                    rounded-xl bg-white/20"
                            >
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
                                        d="M12 3v12m0 0 4-4m-4 4-4-4M5 21h14"
                                    />
                                </svg>
                            </span>

                            <span
                                class="rounded-full bg-white/15
                                    px-3 py-1 text-xs
                                    font-semibold text-white"
                            >
                                Bulk Inventory
                            </span>

                        </div>

                        <h1
                            class="text-2xl font-bold
                                text-white sm:text-3xl"
                        >
                            Import Items From Excel
                        </h1>

                        <p
                            class="mt-2 max-w-2xl
                                text-sm text-cyan-50
                                sm:text-base"
                        >
                            XLSX, XLS ya CSV file se multiple
                            items ek saath add karein.
                        </p>

                    </div>

                    <div class="flex flex-wrap gap-2">

                        <a
                            href="{{ route('items.import.template') }}"
                            class="inline-flex items-center
                                justify-center gap-2 rounded-xl
                                bg-white px-4 py-2.5
                                text-sm font-bold text-teal-700
                                shadow transition
                                hover:bg-teal-50"
                        >
                            ↓ Download Template
                        </a>

                        <a
                            href="{{ route('items.index') }}"
                            class="inline-flex items-center
                                justify-center gap-2 rounded-xl
                                border border-white/30
                                bg-white/15 px-4 py-2.5
                                text-sm font-semibold
                                text-white backdrop-blur-sm
                                transition hover:bg-white
                                hover:text-teal-700"
                        >
                            ← Back to Items
                        </a>

                    </div>

                </div>

            </div>


            {{-- Success --}}
            @if(session('success'))

                <div
                    class="mb-5 rounded-xl border
                        border-emerald-200
                        bg-emerald-50 p-4
                        text-emerald-800
                        dark:border-emerald-900
                        dark:bg-emerald-950/30
                        dark:text-emerald-300"
                >
                    {{ session('success') }}
                </div>

            @endif


            @if(session()->has('success'))

                <div
                    class="mb-5 rounded-xl border border-emerald-300
                        bg-emerald-50 p-4 text-emerald-800
                        shadow-sm
                        dark:border-emerald-800
                        dark:bg-emerald-950/30
                        dark:text-emerald-300"
                >
                    <div class="flex items-start gap-3">

                        <div
                            class="flex h-9 w-9 shrink-0
                                items-center justify-center
                                rounded-full bg-emerald-600
                                font-bold text-white"
                        >
                            ✓
                        </div>

                        <div>
                            <p class="font-bold">
                                Import Successful
                            </p>

                            <p class="mt-1 text-sm">
                                {{ session('success') }}
                            </p>
                        </div>

                    </div>
                </div>

            @endif


            {{-- Errors --}}
            @if($errors->any())

                <div
                    class="mb-5 rounded-xl
                        border border-red-200
                        bg-red-50 p-4
                        dark:border-red-900
                        dark:bg-red-950/30"
                >

                    <p
                        class="font-bold text-red-800
                            dark:text-red-300"
                    >
                        Import Error
                    </p>

                    <ul
                        class="mt-2 list-disc space-y-1
                            pl-5 text-sm text-red-700
                            dark:text-red-400"
                    >

                        @foreach($errors->all() as $error)

                            <li>
                                {{ $error }}
                            </li>

                        @endforeach

                    </ul>

                </div>

            @endif


            {{-- Summary --}}
            @if(session('import_summary'))

                @php
                    $summary =
                        session('import_summary');
                @endphp

                <div
                    class="mb-5 grid grid-cols-2
                        gap-3 sm:max-w-md"
                >

                    <div
                        class="rounded-xl border
                            border-emerald-200
                            bg-white p-4 shadow-sm
                            dark:border-slate-700
                            dark:bg-slate-800"
                    >

                        <p
                            class="text-xs font-semibold
                                uppercase text-slate-500"
                        >
                            Imported
                        </p>

                        <p
                            class="mt-1 text-2xl font-bold
                                text-emerald-600"
                        >
                            {{ $summary['imported'] ?? 0 }}
                        </p>

                    </div>

                    <div
                        class="rounded-xl border
                            border-orange-200
                            bg-white p-4 shadow-sm
                            dark:border-slate-700
                            dark:bg-slate-800"
                    >

                        <p
                            class="text-xs font-semibold
                                uppercase text-slate-500"
                        >
                            Skipped
                        </p>

                        <p
                            class="mt-1 text-2xl font-bold
                                text-orange-600"
                        >
                            {{ $summary['skipped'] ?? 0 }}
                        </p>

                    </div>

                </div>

            @endif


            {{-- Row Errors --}}
            @if(session('import_errors'))

                @php
                    $importErrors =
                        session('import_errors', []);
                @endphp

                @if(count($importErrors))

                    <div
                        class="mb-6 overflow-hidden
                            rounded-2xl border
                            border-red-200 bg-white
                            shadow-sm
                            dark:border-red-900
                            dark:bg-[#171c22]"
                    >

                        <div
                            class="border-b border-red-100
                                bg-red-50 px-5 py-4
                                dark:border-red-900
                                dark:bg-red-950/30"
                        >

                            <h2
                                class="font-bold text-red-800
                                    dark:text-red-300"
                            >
                                Skipped Rows
                            </h2>

                            <p
                                class="mt-1 text-xs
                                    text-red-600
                                    dark:text-red-400"
                            >
                                In rows ko correct karke
                                dobara import kar sakte hain.
                            </p>

                        </div>

                        <div class="max-h-80 overflow-auto">

                            <table
                                class="w-full text-left text-sm"
                            >

                                <thead
                                    class="sticky top-0
                                        bg-slate-100
                                        dark:bg-slate-800"
                                >

                                    <tr>

                                        <th class="px-4 py-3">
                                            Excel Row
                                        </th>

                                        <th class="px-4 py-3">
                                            Error
                                        </th>

                                    </tr>

                                </thead>

                                <tbody>

                                    @foreach($importErrors as $importError)

                                        <tr
                                            class="border-t
                                                border-slate-100
                                                dark:border-slate-700"
                                        >

                                            <td
                                                class="whitespace-nowrap
                                                    px-4 py-3
                                                    font-bold
                                                    text-red-600"
                                            >
                                                {{ $importError['row'] ?? '-' }}
                                            </td>

                                            <td
                                                class="px-4 py-3
                                                    text-slate-700
                                                    dark:text-slate-300"
                                            >
                                                {{ $importError['message'] ?? '' }}
                                            </td>

                                        </tr>

                                    @endforeach

                                </tbody>

                            </table>

                        </div>

                    </div>

                @endif

            @endif


            <div
                class="grid gap-6 lg:grid-cols-[420px_1fr]"
            >

                {{-- Upload Box --}}
                <div>

                    <div
                        class="rounded-2xl border
                            border-slate-200 bg-white
                            p-5 shadow-sm
                            dark:border-slate-700
                            dark:bg-[#171c22]
                            sm:p-6"
                    >

                        <div class="mb-5">

                            <h2
                                class="text-lg font-bold
                                    text-slate-900
                                    dark:text-white"
                            >
                                Upload Excel File
                            </h2>

                            <p
                                class="mt-1 text-sm
                                    text-slate-500
                                    dark:text-slate-400"
                            >
                                Maximum file size 10 MB.
                            </p>

                        </div>

                        <form
                            method="POST"
                            action="{{ route('items.import.store') }}"
                            enctype="multipart/form-data"
                        >

                            @csrf

                            <label
                                for="import_file"
                                class="group flex min-h-[220px]
                                    cursor-pointer flex-col
                                    items-center justify-center
                                    rounded-2xl border-2
                                    border-dashed
                                    border-slate-300
                                    bg-slate-50 p-6
                                    text-center
                                    transition
                                    hover:border-teal-500
                                    hover:bg-teal-50/50
                                    dark:border-slate-600
                                    dark:bg-slate-800/50
                                    dark:hover:border-teal-500"
                            >

                                <span
                                    class="flex h-16 w-16
                                        items-center justify-center
                                        rounded-2xl
                                        bg-emerald-100
                                        text-3xl
                                        text-emerald-700
                                        dark:bg-emerald-900/40
                                        dark:text-emerald-300"
                                >
                                    ↑
                                </span>

                                <span
                                    class="mt-4 font-bold
                                        text-slate-800
                                        dark:text-white"
                                >
                                    Select Excel File
                                </span>

                                <span
                                    class="mt-1 text-xs
                                        text-slate-500
                                        dark:text-slate-400"
                                >
                                    XLSX, XLS or CSV
                                </span>

                                <span
                                    id="selectedFileName"
                                    class="mt-3 hidden
                                        rounded-lg
                                        bg-emerald-100
                                        px-3 py-1.5
                                        text-xs font-bold
                                        text-emerald-700
                                        dark:bg-emerald-900/40
                                        dark:text-emerald-300"
                                ></span>

                            </label>

                            <input
                                id="import_file"
                                type="file"
                                name="import_file"
                                accept=".xlsx,.xls,.csv"
                                class="hidden"
                                required
                            >

                            @error('import_file')

                                <p
                                    class="mt-2 text-sm
                                        font-medium text-red-600"
                                >
                                    {{ $message }}
                                </p>

                            @enderror


                            <button
                                type="submit"
                                class="mt-5 inline-flex
                                    h-12 w-full
                                    items-center justify-center
                                    gap-2 rounded-xl
                                    bg-gradient-to-r
                                    from-teal-600
                                    to-emerald-600
                                    px-5 text-sm
                                    font-bold text-white
                                    shadow-lg
                                    shadow-teal-600/20
                                    transition
                                    hover:from-teal-700
                                    hover:to-emerald-700"
                            >
                                Import Items
                            </button>

                        </form>

                    </div>


                    {{-- Important --}}
                    <div
                        class="mt-4 rounded-xl
                            border border-amber-200
                            bg-amber-50 p-4
                            text-sm text-amber-800
                            dark:border-amber-900
                            dark:bg-amber-950/30
                            dark:text-amber-300"
                    >

                        <p class="font-bold">
                            Important
                        </p>

                        <ul
                            class="mt-2 list-disc space-y-1
                                pl-5 text-xs leading-5"
                        >

                            <li>
                                First row me column headings honi chahiye.
                            </li>

                            <li>
                                Headings change na karein.
                            </li>

                            <li>
                                Category ID nahi, category name dalein.
                            </li>

                            <li>
                                Category pehle system me available honi chahiye.
                            </li>

                            <li>
                                Barcode blank rehne par automatically generate hoga.
                            </li>

                            <li>
                                Duplicate SKU, HUID ya Barcode row skip ho jayegi.
                            </li>

                            <li>
                                Extra Excel columns ignore kar diye jayenge.
                            </li>

                        </ul>

                    </div>

                </div>


                {{-- Allowed Fields --}}
                <div
                    class="overflow-hidden
                        rounded-2xl border
                        border-slate-200
                        bg-white shadow-sm
                        dark:border-slate-700
                        dark:bg-[#171c22]"
                >

                    <div
                        class="border-b border-slate-200
                            bg-slate-50 px-5 py-4
                            dark:border-slate-700
                            dark:bg-slate-800"
                    >

                        <div
                            class="flex flex-col gap-2
                                sm:flex-row sm:items-center
                                sm:justify-between"
                        >

                            <div>

                                <h2
                                    class="text-lg font-bold
                                        text-slate-900
                                        dark:text-white"
                                >
                                    Allowed Excel Columns
                                </h2>

                                <p
                                    class="mt-1 text-xs
                                        text-slate-500
                                        dark:text-slate-400"
                                >
                                    Ye columns current business ke
                                    item configuration ke hisaab se hain.
                                </p>

                            </div>

                            <a
                                href="{{ route('items.import.template') }}"
                                class="inline-flex
                                    items-center justify-center
                                    rounded-lg
                                    bg-emerald-600
                                    px-3 py-2
                                    text-xs font-bold
                                    text-white
                                    hover:bg-emerald-700"
                            >
                                Download Ready Template
                            </a>

                        </div>

                    </div>


                    <div class="overflow-x-auto">

                        <table
                            class="w-full text-left
                                text-sm"
                        >

                            <thead
                                class="bg-slate-100
                                    text-xs uppercase
                                    text-slate-600
                                    dark:bg-slate-800
                                    dark:text-slate-300"
                            >

                                <tr>

                                    <th class="px-4 py-3">
                                        Column
                                    </th>

                                    <th class="px-4 py-3">
                                        Required
                                    </th>

                                    <th class="px-4 py-3">
                                        Format / Example
                                    </th>

                                </tr>

                            </thead>

                            <tbody>

                                @foreach($columnDetails as $field)

                                    <tr
                                        class="border-t
                                            border-slate-100
                                            dark:border-slate-700"
                                    >

                                        <td
                                            class="px-4 py-3"
                                        >

                                            <code
                                                class="rounded
                                                    bg-slate-100
                                                    px-2 py-1
                                                    text-xs font-bold
                                                    text-teal-700
                                                    dark:bg-slate-800
                                                    dark:text-teal-300"
                                            >
                                                {{ $field['column'] }}
                                            </code>

                                        </td>

                                        <td class="px-4 py-3">

                                            @if($field['required'])

                                                <span
                                                    class="rounded-full
                                                        bg-red-100
                                                        px-2.5 py-1
                                                        text-[11px]
                                                        font-bold
                                                        text-red-700
                                                        dark:bg-red-900/40
                                                        dark:text-red-300"
                                                >
                                                    Required
                                                </span>

                                            @else

                                                <span
                                                    class="rounded-full
                                                        bg-slate-100
                                                        px-2.5 py-1
                                                        text-[11px]
                                                        font-semibold
                                                        text-slate-600
                                                        dark:bg-slate-800
                                                        dark:text-slate-300"
                                                >
                                                    Optional
                                                </span>

                                            @endif

                                        </td>

                                        <td
                                            class="px-4 py-3
                                                text-xs
                                                text-slate-600
                                                dark:text-slate-300"
                                        >
                                            {{ $field['format'] }}
                                        </td>

                                    </tr>

                                @endforeach

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>


            {{-- Existing Categories --}}
            @if(
                in_array(
                    'category_id',
                    $allowedFields,
                    true
                )
            )

                <div
                    class="mt-6 rounded-2xl
                        border border-slate-200
                        bg-white p-5 shadow-sm
                        dark:border-slate-700
                        dark:bg-[#171c22]"
                >

                    <h2
                        class="font-bold text-slate-900
                            dark:text-white"
                    >
                        Available Categories
                    </h2>

                    <p
                        class="mt-1 text-xs
                            text-slate-500
                            dark:text-slate-400"
                    >
                        Excel ke <code>category</code>
                        column me inhi names ka use karein.
                    </p>

                    <div
                        class="mt-4 flex flex-wrap gap-2"
                    >

                        @forelse($categories as $category)

                            <span
                                class="rounded-lg
                                    border border-teal-200
                                    bg-teal-50
                                    px-3 py-1.5
                                    text-xs font-semibold
                                    text-teal-700
                                    dark:border-teal-900
                                    dark:bg-teal-950/30
                                    dark:text-teal-300"
                            >
                                {{ $category->name }}
                            </span>

                        @empty

                            <span
                                class="text-sm
                                    font-medium text-orange-600"
                            >
                                Abhi koi category available nahi hai.
                            </span>

                        @endforelse

                    </div>

                </div>

            @endif


            {{-- Units --}}
            @if(
                in_array(
                    'unit',
                    $allowedFields,
                    true
                )
            )

                <div
                    class="mt-4 rounded-2xl
                        border border-slate-200
                        bg-white p-5 shadow-sm
                        dark:border-slate-700
                        dark:bg-[#171c22]"
                >

                    <h2
                        class="font-bold text-slate-900
                            dark:text-white"
                    >
                        Available Units
                    </h2>

                    <div
                        class="mt-3 flex flex-wrap gap-2"
                    >

                        @forelse($units as $unit)

                            <span
                                class="rounded-lg
                                    bg-slate-100
                                    px-3 py-1.5
                                    text-xs font-semibold
                                    text-slate-700
                                    dark:bg-slate-800
                                    dark:text-slate-300"
                            >
                                {{ $unit->name }}
                            </span>

                        @empty

                            <span
                                class="text-xs text-slate-500"
                            >
                                No predefined units available.
                            </span>

                        @endforelse

                    </div>

                </div>

            @endif

        </div>

    </div>


    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const input =
                document.getElementById('import_file');

            const nameBox =
                document.getElementById(
                    'selectedFileName'
                );

            input?.addEventListener(
                'change',
                function () {

                    const file =
                        this.files?.[0];

                    if (!file || !nameBox) {
                        return;
                    }

                    nameBox.textContent =
                        file.name;

                    nameBox.classList.remove(
                        'hidden'
                    );
                }
            );

        });
    </script>

</x-layouts.app>