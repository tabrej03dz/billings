@php
    $invoice = $ewayBill->invoice;
@endphp

<x-layouts.app :title="'Edit E-Way Bill'">

    <div class="max-w-6xl mx-auto px-4 py-6">


        {{-- HEADER --}}
        <div class="mb-5 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">

            <div>

                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    Edit E-Way Bill
                </h1>

                <div class="mt-1 text-sm text-gray-500 dark:text-neutral-400">
                    Invoice #{{ $invoice->invoice_number }}
                </div>

            </div>


            <a
                href="{{ route(
                    'eway-bills.show',
                    $ewayBill->id
                ) }}"
                class="inline-flex items-center justify-center rounded-lg
                       border border-gray-300 bg-white px-4 py-2
                       text-sm font-medium text-gray-700
                       hover:bg-gray-50
                       dark:border-neutral-700 dark:bg-neutral-900
                       dark:text-neutral-200 dark:hover:bg-neutral-800"
            >
                ← Back
            </a>

        </div>


        {{-- VALIDATION --}}
        @if($errors->any())

            <div
                class="mb-5 rounded-xl border border-red-300
                       bg-red-50 p-4 text-red-700"
            >

                <div class="font-bold">
                    Please fix these fields:
                </div>

                <ul class="mt-2 list-disc pl-5 text-sm">

                    @foreach($errors->all() as $error)

                        <li>
                            {{ $error }}
                        </li>

                    @endforeach

                </ul>

            </div>

        @endif


        <form
            method="POST"
            action="{{ route(
                'eway-bills.update',
                $ewayBill->id
            ) }}"
            class="space-y-5"
        >

            @csrf
            @method('PUT')


            {{-- DOCUMENT DETAILS --}}
            <div
                class="rounded-xl border border-gray-200 bg-white
                       p-5 shadow-sm dark:border-neutral-700
                       dark:bg-neutral-900"
            >

                <h2 class="mb-4 text-lg font-bold">
                    Document Details
                </h2>


                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">


                    <div>

                        <label class="mb-1 block text-sm font-medium">
                            Supply Type
                        </label>

                        <select
                            name="supply_type"
                            class="w-full rounded-lg border-gray-300
                                   dark:border-neutral-700
                                   dark:bg-neutral-800"
                            required
                        >

                            <option
                                value="Outward"
                                @selected(
                                    old(
                                        'supply_type',
                                        $ewayBill->supply_type
                                    ) === 'Outward'
                                )
                            >
                                Outward
                            </option>

                            <option
                                value="Inward"
                                @selected(
                                    old(
                                        'supply_type',
                                        $ewayBill->supply_type
                                    ) === 'Inward'
                                )
                            >
                                Inward
                            </option>

                        </select>

                    </div>


                    <div>

                        <label class="mb-1 block text-sm font-medium">
                            Sub Supply Type
                        </label>

                        <input
                            type="text"
                            name="sub_supply_type"
                            value="{{ old(
                                'sub_supply_type',
                                $ewayBill->sub_supply_type
                            ) }}"
                            class="w-full rounded-lg border-gray-300
                                   dark:border-neutral-700
                                   dark:bg-neutral-800"
                            required
                        >

                    </div>


                    <div>

                        <label class="mb-1 block text-sm font-medium">
                            Document Type
                        </label>

                        <input
                            type="text"
                            name="document_type"
                            value="{{ old(
                                'document_type',
                                $ewayBill->document_type
                            ) }}"
                            class="w-full rounded-lg border-gray-300
                                   dark:border-neutral-700
                                   dark:bg-neutral-800"
                            required
                        >

                    </div>


                    <div>

                        <label class="mb-1 block text-sm font-medium">
                            Document No.
                        </label>

                        <input
                            type="text"
                            name="document_no"
                            value="{{ old(
                                'document_no',
                                $ewayBill->document_no
                            ) }}"
                            class="w-full rounded-lg border-gray-300
                                   dark:border-neutral-700
                                   dark:bg-neutral-800"
                            required
                        >

                    </div>


                    <div>

                        <label class="mb-1 block text-sm font-medium">
                            Document Date
                        </label>

                        <input
                            type="date"
                            name="document_date"
                            value="{{ old(
                                'document_date',
                                optional(
                                    $ewayBill->document_date
                                )->format('Y-m-d')
                            ) }}"
                            class="w-full rounded-lg border-gray-300
                                   dark:border-neutral-700
                                   dark:bg-neutral-800"
                            required
                        >

                    </div>


                    <div>

                        <label class="mb-1 block text-sm font-medium">
                            E-Way Bill No.
                        </label>

                        <input
                            type="text"
                            name="eway_bill_no"
                            value="{{ old(
                                'eway_bill_no',
                                $ewayBill->eway_bill_no
                            ) }}"
                            class="w-full rounded-lg border-gray-300
                                   dark:border-neutral-700
                                   dark:bg-neutral-800"
                        >

                    </div>


                    <div>

                        <label class="mb-1 block text-sm font-medium">
                            E-Way Bill Date
                        </label>

                        <input
                            type="date"
                            name="eway_bill_date"
                            value="{{ old(
                                'eway_bill_date',
                                optional(
                                    $ewayBill->eway_bill_date
                                )->format('Y-m-d')
                            ) }}"
                            class="w-full rounded-lg border-gray-300
                                   dark:border-neutral-700
                                   dark:bg-neutral-800"
                        >

                    </div>


                    <div>

                        <label class="mb-1 block text-sm font-medium">
                            Valid Upto
                        </label>

                        <input
                            type="date"
                            name="valid_upto"
                            value="{{ old(
                                'valid_upto',
                                optional(
                                    $ewayBill->valid_upto
                                )->format('Y-m-d')
                            ) }}"
                            class="w-full rounded-lg border-gray-300
                                   dark:border-neutral-700
                                   dark:bg-neutral-800"
                        >

                    </div>


                    <div>

                        <label class="mb-1 block text-sm font-medium">
                            Status
                        </label>

                        <select
                            name="status"
                            class="w-full rounded-lg border-gray-300
                                   dark:border-neutral-700
                                   dark:bg-neutral-800"
                        >

                            @foreach([
                                'draft',
                                'generated',
                                'cancelled',
                                'failed'
                            ] as $status)

                                <option
                                    value="{{ $status }}"
                                    @selected(
                                        old(
                                            'status',
                                            $ewayBill->status
                                        ) === $status
                                    )
                                >
                                    {{ ucfirst($status) }}
                                </option>

                            @endforeach

                        </select>

                    </div>

                </div>

            </div>


            {{-- FROM DETAILS --}}
            <div
                class="rounded-xl border border-gray-200 bg-white
                       p-5 shadow-sm dark:border-neutral-700
                       dark:bg-neutral-900"
            >

                <h2 class="mb-4 text-lg font-bold">
                    From / Supplier Details
                </h2>


                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">


                    <div>

                        <label class="mb-1 block text-sm font-medium">
                            Name
                        </label>

                        <input
                            type="text"
                            name="from_name"
                            value="{{ old(
                                'from_name',
                                $ewayBill->from_name
                            ) }}"
                            class="w-full rounded-lg border-gray-300
                                   dark:border-neutral-700
                                   dark:bg-neutral-800"
                            required
                        >

                    </div>


                    <div>

                        <label class="mb-1 block text-sm font-medium">
                            GSTIN
                        </label>

                        <input
                            type="text"
                            name="from_gstin"
                            value="{{ old(
                                'from_gstin',
                                $ewayBill->from_gstin
                            ) }}"
                            class="w-full rounded-lg border-gray-300
                                   dark:border-neutral-700
                                   dark:bg-neutral-800"
                        >

                    </div>


                    <div class="md:col-span-2">

                        <label class="mb-1 block text-sm font-medium">
                            Address
                        </label>

                        <textarea
                            name="from_address"
                            rows="3"
                            class="w-full rounded-lg border-gray-300
                                   dark:border-neutral-700
                                   dark:bg-neutral-800"
                            required
                        >{{ old(
                            'from_address',
                            $ewayBill->from_address
                        ) }}</textarea>

                    </div>


                    <div>

                        <label class="mb-1 block text-sm font-medium">
                            Place / City
                        </label>

                        <input
                            type="text"
                            name="from_place"
                            value="{{ old(
                                'from_place',
                                $ewayBill->from_place
                            ) }}"
                            class="w-full rounded-lg border-gray-300
                                   dark:border-neutral-700
                                   dark:bg-neutral-800"
                            required
                        >

                    </div>


                    <div>

                        <label class="mb-1 block text-sm font-medium">
                            Pincode
                        </label>

                        <input
                            type="text"
                            maxlength="6"
                            name="from_pincode"
                            value="{{ old(
                                'from_pincode',
                                $ewayBill->from_pincode
                            ) }}"
                            class="w-full rounded-lg border-gray-300
                                   dark:border-neutral-700
                                   dark:bg-neutral-800"
                            required
                        >

                    </div>


                    <div>

                        <label class="mb-1 block text-sm font-medium">
                            State
                        </label>

                        <input
                            type="text"
                            name="from_state"
                            value="{{ old(
                                'from_state',
                                $ewayBill->from_state
                            ) }}"
                            class="w-full rounded-lg border-gray-300
                                   dark:border-neutral-700
                                   dark:bg-neutral-800"
                            required
                        >

                    </div>


                    <div>

                        <label class="mb-1 block text-sm font-medium">
                            State Code
                        </label>

                        <input
                            type="text"
                            name="from_state_code"
                            value="{{ old(
                                'from_state_code',
                                $ewayBill->from_state_code
                            ) }}"
                            class="w-full rounded-lg border-gray-300
                                   dark:border-neutral-700
                                   dark:bg-neutral-800"
                        >

                    </div>

                </div>

            </div>


            {{-- TO DETAILS --}}
            <div
                class="rounded-xl border border-gray-200 bg-white
                       p-5 shadow-sm dark:border-neutral-700
                       dark:bg-neutral-900"
            >

                <h2 class="mb-4 text-lg font-bold">
                    To / Customer Details
                </h2>


                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">


                    <div>

                        <label class="mb-1 block text-sm font-medium">
                            Name
                        </label>

                        <input
                            type="text"
                            name="to_name"
                            value="{{ old(
                                'to_name',
                                $ewayBill->to_name
                            ) }}"
                            class="w-full rounded-lg border-gray-300
                                   dark:border-neutral-700
                                   dark:bg-neutral-800"
                            required
                        >

                    </div>


                    <div>

                        <label class="mb-1 block text-sm font-medium">
                            GSTIN
                        </label>

                        <input
                            type="text"
                            name="to_gstin"
                            value="{{ old(
                                'to_gstin',
                                $ewayBill->to_gstin
                            ) }}"
                            class="w-full rounded-lg border-gray-300
                                   dark:border-neutral-700
                                   dark:bg-neutral-800"
                        >

                    </div>


                    <div class="md:col-span-2">

                        <label class="mb-1 block text-sm font-medium">
                            Address
                        </label>

                        <textarea
                            name="to_address"
                            rows="3"
                            class="w-full rounded-lg border-gray-300
                                   dark:border-neutral-700
                                   dark:bg-neutral-800"
                            required
                        >{{ old(
                            'to_address',
                            $ewayBill->to_address
                        ) }}</textarea>

                    </div>


                    <div>

                        <label class="mb-1 block text-sm font-medium">
                            Place / City
                        </label>

                        <input
                            type="text"
                            name="to_place"
                            value="{{ old(
                                'to_place',
                                $ewayBill->to_place
                            ) }}"
                            class="w-full rounded-lg border-gray-300
                                   dark:border-neutral-700
                                   dark:bg-neutral-800"
                            required
                        >

                    </div>


                    <div>

                        <label class="mb-1 block text-sm font-medium">
                            Pincode
                        </label>

                        <input
                            type="text"
                            maxlength="6"
                            name="to_pincode"
                            value="{{ old(
                                'to_pincode',
                                $ewayBill->to_pincode
                            ) }}"
                            class="w-full rounded-lg border-gray-300
                                   dark:border-neutral-700
                                   dark:bg-neutral-800"
                            required
                        >

                    </div>


                    <div>

                        <label class="mb-1 block text-sm font-medium">
                            State
                        </label>

                        <input
                            type="text"
                            name="to_state"
                            value="{{ old(
                                'to_state',
                                $ewayBill->to_state
                            ) }}"
                            class="w-full rounded-lg border-gray-300
                                   dark:border-neutral-700
                                   dark:bg-neutral-800"
                            required
                        >

                    </div>


                    <div>

                        <label class="mb-1 block text-sm font-medium">
                            State Code
                        </label>

                        <input
                            type="text"
                            name="to_state_code"
                            value="{{ old(
                                'to_state_code',
                                $ewayBill->to_state_code
                            ) }}"
                            class="w-full rounded-lg border-gray-300
                                   dark:border-neutral-700
                                   dark:bg-neutral-800"
                        >

                    </div>

                </div>

            </div>


            {{-- TRANSPORT --}}
            <div
                class="rounded-xl border border-gray-200 bg-white
                       p-5 shadow-sm dark:border-neutral-700
                       dark:bg-neutral-900"
            >

                <h2 class="mb-4 text-lg font-bold">
                    Transportation Details
                </h2>


                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">


                    <div>

                        <label class="mb-1 block text-sm font-medium">
                            Transport Mode
                        </label>

                        <select
                            name="transport_mode"
                            class="w-full rounded-lg border-gray-300
                                   dark:border-neutral-700
                                   dark:bg-neutral-800"
                            required
                        >

                            @foreach([
                                'Road',
                                'Rail',
                                'Air',
                                'Ship'
                            ] as $mode)

                                <option
                                    value="{{ $mode }}"
                                    @selected(
                                        old(
                                            'transport_mode',
                                            $ewayBill->transport_mode
                                        ) === $mode
                                    )
                                >
                                    {{ $mode }}
                                </option>

                            @endforeach

                        </select>

                    </div>


                    <div>

                        <label class="mb-1 block text-sm font-medium">
                            Distance KM
                        </label>

                        <input
                            type="number"
                            min="1"
                            name="distance"
                            value="{{ old(
                                'distance',
                                $ewayBill->distance
                            ) }}"
                            class="w-full rounded-lg border-gray-300
                                   dark:border-neutral-700
                                   dark:bg-neutral-800"
                            required
                        >

                    </div>


                    <div>

                        <label class="mb-1 block text-sm font-medium">
                            Transporter Name
                        </label>

                        <input
                            type="text"
                            name="transporter_name"
                            value="{{ old(
                                'transporter_name',
                                $ewayBill->transporter_name
                            ) }}"
                            class="w-full rounded-lg border-gray-300
                                   dark:border-neutral-700
                                   dark:bg-neutral-800"
                        >

                    </div>


                    <div>

                        <label class="mb-1 block text-sm font-medium">
                            Transporter ID / GSTIN
                        </label>

                        <input
                            type="text"
                            name="transporter_id"
                            value="{{ old(
                                'transporter_id',
                                $ewayBill->transporter_id
                            ) }}"
                            class="w-full rounded-lg border-gray-300
                                   dark:border-neutral-700
                                   dark:bg-neutral-800"
                        >

                    </div>


                    <div>

                        <label class="mb-1 block text-sm font-medium">
                            Vehicle No.
                        </label>

                        <input
                            type="text"
                            name="vehicle_no"
                            value="{{ old(
                                'vehicle_no',
                                $ewayBill->vehicle_no
                            ) }}"
                            class="w-full rounded-lg border-gray-300
                                   uppercase
                                   dark:border-neutral-700
                                   dark:bg-neutral-800"
                        >

                    </div>


                    <div>

                        <label class="mb-1 block text-sm font-medium">
                            Vehicle Type
                        </label>

                        <select
                            name="vehicle_type"
                            class="w-full rounded-lg border-gray-300
                                   dark:border-neutral-700
                                   dark:bg-neutral-800"
                        >

                            <option
                                value="Regular"
                                @selected(
                                    old(
                                        'vehicle_type',
                                        $ewayBill->vehicle_type
                                    ) === 'Regular'
                                )
                            >
                                Regular
                            </option>

                            <option
                                value="Over Dimensional Cargo"
                                @selected(
                                    old(
                                        'vehicle_type',
                                        $ewayBill->vehicle_type
                                    ) === 'Over Dimensional Cargo'
                                )
                            >
                                Over Dimensional Cargo
                            </option>

                        </select>

                    </div>


                    <div>

                        <label class="mb-1 block text-sm font-medium">
                            Transport Doc No.
                        </label>

                        <input
                            type="text"
                            name="transport_doc_no"
                            value="{{ old(
                                'transport_doc_no',
                                $ewayBill->transport_doc_no
                            ) }}"
                            class="w-full rounded-lg border-gray-300
                                   dark:border-neutral-700
                                   dark:bg-neutral-800"
                        >

                    </div>


                    <div>

                        <label class="mb-1 block text-sm font-medium">
                            Transport Doc Date
                        </label>

                        <input
                            type="date"
                            name="transport_doc_date"
                            value="{{ old(
                                'transport_doc_date',
                                optional(
                                    $ewayBill->transport_doc_date
                                )->format('Y-m-d')
                            ) }}"
                            class="w-full rounded-lg border-gray-300
                                   dark:border-neutral-700
                                   dark:bg-neutral-800"
                        >

                    </div>

                </div>

            </div>


            {{-- BUTTONS --}}
            <div class="flex flex-wrap justify-end gap-3">

                <a
                    href="{{ route(
                        'eway-bills.index'
                    ) }}"
                    class="rounded-lg border border-gray-300
                           bg-white px-5 py-2.5 text-sm
                           font-medium text-gray-700
                           hover:bg-gray-50
                           dark:border-neutral-700
                           dark:bg-neutral-900
                           dark:text-neutral-200"
                >
                    Cancel
                </a>


                <button
                    type="submit"
                    class="rounded-lg bg-gray-800
                           px-6 py-2.5 text-sm
                           font-semibold text-white
                           hover:bg-gray-900"
                >
                    Update E-Way Bill
                </button>

            </div>

        </form>

    </div>

</x-layouts.app>