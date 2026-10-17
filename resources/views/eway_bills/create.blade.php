@php
    $business = $invoice->business;
    $client = $invoice->client;
@endphp

<x-layouts.app :title="'Create E-Way Bill'">

    <div class="max-w-6xl mx-auto py-6">

        <div class="mb-6">
            <h1 class="text-2xl font-bold">
                Create E-Way Bill
            </h1>

            <p class="text-sm text-gray-500 mt-1">
                Invoice #{{ $invoice->invoice_number }}
            </p>
        </div>

        @if ($errors->any())
            <div class="mb-5 p-4 bg-red-50 border border-red-200 rounded-lg">
                <strong>Please check following fields:</strong>

                <ul class="mt-2 list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li class="text-red-600">
                            {{ $error }}
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif


        <form
            method="POST"
            action="{{ route('eway-bills.store', $invoice->id) }}"
            class="space-y-6"
        >

            @csrf


            {{-- ============================== --}}
            {{-- DOCUMENT DETAILS --}}
            {{-- ============================== --}}

            <div class="bg-white dark:bg-neutral-900 rounded-xl shadow p-5">

                <h2 class="font-semibold text-lg mb-4">
                    Document Details
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                    <div>
                        <label class="block text-sm mb-1">
                            Supply Type
                        </label>

                        <select
                            name="supply_type"
                            class="w-full border rounded-lg px-3 py-2"
                            required
                        >
                            <option value="Outward"
                                @selected(old('supply_type', 'Outward') === 'Outward')
                            >
                                Outward
                            </option>

                            <option value="Inward"
                                @selected(old('supply_type') === 'Inward')
                            >
                                Inward
                            </option>
                        </select>
                    </div>


                    <div>
                        <label class="block text-sm mb-1">
                            Sub Supply Type
                        </label>

                        <select
                            name="sub_supply_type"
                            class="w-full border rounded-lg px-3 py-2"
                            required
                        >
                            <option value="Supply">
                                Supply
                            </option>

                            <option value="Export">
                                Export
                            </option>

                            <option value="Job Work">
                                Job Work
                            </option>

                            <option value="Others">
                                Others
                            </option>
                        </select>
                    </div>


                    <div>
                        <label class="block text-sm mb-1">
                            Document Type
                        </label>

                        <select
                            name="document_type"
                            class="w-full border rounded-lg px-3 py-2"
                            required
                        >
                            <option value="Tax Invoice">
                                Tax Invoice
                            </option>

                            <option value="Bill of Supply">
                                Bill of Supply
                            </option>

                            <option value="Delivery Challan">
                                Delivery Challan
                            </option>
                        </select>
                    </div>


                    <div>
                        <label class="block text-sm mb-1">
                            Document No.
                        </label>

                        <input
                            type="text"
                            name="document_no"
                            value="{{ old('document_no', $invoice->invoice_number) }}"
                            class="w-full border rounded-lg px-3 py-2 bg-gray-100"
                            required
                        >
                    </div>


                    <div>
                        <label class="block text-sm mb-1">
                            Document Date
                        </label>

                        <input
                            type="date"
                            name="document_date"
                            value="{{ old(
                                'document_date',
                                optional($invoice->invoice_date)->format('Y-m-d')
                            ) }}"
                            class="w-full border rounded-lg px-3 py-2"
                            required
                        >
                    </div>

                </div>
            </div>


            {{-- ============================== --}}
            {{-- FROM DETAILS --}}
            {{-- ============================== --}}

            <div class="bg-white dark:bg-neutral-900 rounded-xl shadow p-5">

                <h2 class="font-semibold text-lg mb-4">
                    From / Supplier Details
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <div>
                        <label class="block text-sm mb-1">
                            Business Name
                        </label>

                        <input
                            type="text"
                            name="from_name"
                            value="{{ old('from_name', $business->name) }}"
                            class="w-full border rounded-lg px-3 py-2"
                            required
                        >
                    </div>


                    <div>
                        <label class="block text-sm mb-1">
                            GSTIN
                        </label>

                        <input
                            type="text"
                            name="from_gstin"
                            value="{{ old('from_gstin', $business->gstin) }}"
                            class="w-full border rounded-lg px-3 py-2"
                        >
                    </div>


                    <div class="md:col-span-2">
                        <label class="block text-sm mb-1">
                            Address
                        </label>

                        <textarea
                            name="from_address"
                            class="w-full border rounded-lg px-3 py-2"
                            required
                        >{{ old('from_address', $business->address) }}</textarea>
                    </div>


                    <div>
                        <label class="block text-sm mb-1">
                            Place / City
                        </label>

                        <input
                            type="text"
                            name="from_place"
                            value="{{ old('from_place', $business->city ?? '') }}"
                            class="w-full border rounded-lg px-3 py-2"
                            required
                        >
                    </div>


                    <div>
                        <label class="block text-sm mb-1">
                            Pincode
                        </label>

                        <input
                            type="text"
                            maxlength="6"
                            name="from_pincode"
                            value="{{ old('from_pincode', $business->pincode ?? '') }}"
                            class="w-full border rounded-lg px-3 py-2"
                            required
                        >
                    </div>


                    <div>
                        <label class="block text-sm mb-1">
                            State
                        </label>

                        <input
                            type="text"
                            name="from_state"
                            value="{{ old('from_state', $business->state) }}"
                            class="w-full border rounded-lg px-3 py-2"
                            required
                        >
                    </div>


                    <div>
                        <label class="block text-sm mb-1">
                            State Code
                        </label>

                        <input
                            type="text"
                            name="from_state_code"
                            value="{{ old('from_state_code', $business->state_code) }}"
                            class="w-full border rounded-lg px-3 py-2"
                        >
                    </div>

                </div>
            </div>


            {{-- ============================== --}}
            {{-- TO DETAILS --}}
            {{-- ============================== --}}

            <div class="bg-white dark:bg-neutral-900 rounded-xl shadow p-5">

                <h2 class="font-semibold text-lg mb-4">
                    To / Customer Details
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <div>
                        <label class="block text-sm mb-1">
                            Customer Name
                        </label>

                        <input
                            type="text"
                            name="to_name"
                            value="{{ old('to_name', $client->name) }}"
                            class="w-full border rounded-lg px-3 py-2"
                            required
                        >
                    </div>


                    <div>
                        <label class="block text-sm mb-1">
                            GSTIN
                        </label>

                        <input
                            type="text"
                            name="to_gstin"
                            value="{{ old('to_gstin', $client->gstin) }}"
                            class="w-full border rounded-lg px-3 py-2"
                        >
                    </div>


                    <div class="md:col-span-2">
                        <label class="block text-sm mb-1">
                            Address
                        </label>

                        <textarea
                            name="to_address"
                            class="w-full border rounded-lg px-3 py-2"
                            required
                        >{{ old('to_address', $client->address) }}</textarea>
                    </div>


                    <div>
                        <label class="block text-sm mb-1">
                            Place / City
                        </label>

                        <input
                            type="text"
                            name="to_place"
                            value="{{ old('to_place', $client->city ?? '') }}"
                            class="w-full border rounded-lg px-3 py-2"
                            required
                        >
                    </div>


                    <div>
                        <label class="block text-sm mb-1">
                            Pincode
                        </label>

                        <input
                            type="text"
                            maxlength="6"
                            name="to_pincode"
                            value="{{ old('to_pincode', $client->pincode ?? '') }}"
                            class="w-full border rounded-lg px-3 py-2"
                            required
                        >
                    </div>


                    <div>
                        <label class="block text-sm mb-1">
                            State
                        </label>

                        <input
                            type="text"
                            name="to_state"
                            value="{{ old('to_state', $client->state) }}"
                            class="w-full border rounded-lg px-3 py-2"
                            required
                        >
                    </div>


                    <div>
                        <label class="block text-sm mb-1">
                            State Code
                        </label>

                        <input
                            type="text"
                            name="to_state_code"
                            value="{{ old('to_state_code', $client->state_code) }}"
                            class="w-full border rounded-lg px-3 py-2"
                        >
                    </div>

                </div>
            </div>


            {{-- ============================== --}}
            {{-- INVOICE ITEMS --}}
            {{-- ============================== --}}

            <div class="bg-white dark:bg-neutral-900 rounded-xl shadow p-5">

                <h2 class="font-semibold text-lg mb-4">
                    Invoice Items
                </h2>

                <div class="overflow-x-auto">

                    <table class="w-full border-collapse">

                        <thead>
                        <tr>
                            <th class="border p-2">Item</th>
                            <th class="border p-2">HSN/SAC</th>
                            <th class="border p-2">Qty</th>
                            <th class="border p-2">Rate</th>
                            <th class="border p-2">Tax</th>
                            <th class="border p-2">Amount</th>
                        </tr>
                        </thead>

                        <tbody>

                        @foreach($invoice->items as $item)

                            <tr>
                                <td class="border p-2">
                                    {{ $item->description }}
                                </td>

                                <td class="border p-2">
                                    {{ $item->sac_code }}
                                </td>

                                <td class="border p-2 text-center">
                                    {{ $item->quantity }}
                                </td>

                                <td class="border p-2 text-right">
                                    ₹{{ number_format($item->rate ?? 0, 2) }}
                                </td>

                                <td class="border p-2 text-center">
                                    {{ $item->tax_percent ?? 0 }}%
                                </td>

                                <td class="border p-2 text-right">
                                    ₹{{ number_format($item->amount ?? 0, 2) }}
                                </td>
                            </tr>

                        @endforeach

                        </tbody>

                    </table>

                </div>

                <div class="mt-4 text-right text-lg font-semibold">
                    Invoice Total:
                    ₹{{ number_format($invoice->total ?? 0, 2) }}
                </div>

            </div>


            {{-- ============================== --}}
            {{-- TRANSPORT --}}
            {{-- ============================== --}}

            <div class="bg-white dark:bg-neutral-900 rounded-xl shadow p-5">

                <h2 class="font-semibold text-lg mb-4">
                    Transportation Details
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                    <div>
                        <label class="block text-sm mb-1">
                            Transport Mode
                        </label>

                        <select
                            name="transport_mode"
                            class="w-full border rounded-lg px-3 py-2"
                            required
                        >
                            <option value="">
                                Select
                            </option>

                            <option value="Road">
                                Road
                            </option>

                            <option value="Rail">
                                Rail
                            </option>

                            <option value="Air">
                                Air
                            </option>

                            <option value="Ship">
                                Ship
                            </option>
                        </select>
                    </div>


                    <div>
                        <label class="block text-sm mb-1">
                            Approx Distance (KM)
                        </label>

                        <input
                            type="number"
                            name="distance"
                            value="{{ old('distance') }}"
                            class="w-full border rounded-lg px-3 py-2"
                            required
                        >
                    </div>


                    <div>
                        <label class="block text-sm mb-1">
                            Transporter Name
                        </label>

                        <input
                            type="text"
                            name="transporter_name"
                            value="{{ old('transporter_name') }}"
                            class="w-full border rounded-lg px-3 py-2"
                        >
                    </div>


                    <div>
                        <label class="block text-sm mb-1">
                            Transporter ID / GSTIN
                        </label>

                        <input
                            type="text"
                            name="transporter_id"
                            value="{{ old('transporter_id') }}"
                            class="w-full border rounded-lg px-3 py-2"
                        >
                    </div>


                    <div>
                        <label class="block text-sm mb-1">
                            Vehicle No.
                        </label>

                        <input
                            type="text"
                            name="vehicle_no"
                            value="{{ old('vehicle_no') }}"
                            placeholder="UP15AB1234"
                            class="w-full border rounded-lg px-3 py-2 uppercase"
                        >
                    </div>


                    <div>
                        <label class="block text-sm mb-1">
                            Vehicle Type
                        </label>

                        <select
                            name="vehicle_type"
                            class="w-full border rounded-lg px-3 py-2"
                        >
                            <option value="Regular">
                                Regular
                            </option>

                            <option value="Over Dimensional Cargo">
                                Over Dimensional Cargo
                            </option>
                        </select>
                    </div>


                    <div>
                        <label class="block text-sm mb-1">
                            Transport Document No.
                        </label>

                        <input
                            type="text"
                            name="transport_doc_no"
                            value="{{ old('transport_doc_no') }}"
                            class="w-full border rounded-lg px-3 py-2"
                        >
                    </div>


                    <div>
                        <label class="block text-sm mb-1">
                            Transport Document Date
                        </label>

                        <input
                            type="date"
                            name="transport_doc_date"
                            value="{{ old('transport_doc_date') }}"
                            class="w-full border rounded-lg px-3 py-2"
                        >
                    </div>

                </div>

            </div>


            {{-- ============================================ --}}
            {{-- TEMPORARY MANUAL EWB DETAILS --}}
            {{-- ============================================ --}}

            <div class="bg-white dark:bg-neutral-900 rounded-xl shadow p-5">

                <h2 class="font-semibold text-lg mb-2">
                    E-Way Bill Details
                </h2>

                <p class="text-sm text-gray-500 mb-4">
                    Abhi API connect nahi hai, isliye ye details manual rakhi gayi hain.
                    API integration ke baad ye automatically fill hongi.
                </p>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                    <div>
                        <label class="block text-sm mb-1">
                            E-Way Bill No.
                        </label>

                        <input
                            type="text"
                            name="eway_bill_no"
                            value="{{ old('eway_bill_no') }}"
                            class="w-full border rounded-lg px-3 py-2"
                        >
                    </div>

                    <div>
                        <label class="block text-sm mb-1">
                            E-Way Bill Date
                        </label>

                        <input
                            type="date"
                            name="eway_bill_date"
                            value="{{ old('eway_bill_date') }}"
                            class="w-full border rounded-lg px-3 py-2"
                        >
                    </div>

                    <div>
                        <label class="block text-sm mb-1">
                            Valid Upto
                        </label>

                        <input
                            type="date"
                            name="valid_upto"
                            value="{{ old('valid_upto') }}"
                            class="w-full border rounded-lg px-3 py-2"
                        >
                    </div>

                </div>

            </div>


            <div class="flex justify-end gap-3">

                <a
                    href="{{ route('invoices.preview', $invoice->id) }}"
                    class="px-5 py-2 border rounded-lg"
                >
                    Cancel
                </a>

                <button
                    type="submit"
                    class="px-6 py-2 bg-blue-600 text-white rounded-lg"
                >
                    Save E-Way Bill
                </button>

            </div>

        </form>

    </div>

</x-layouts.app>