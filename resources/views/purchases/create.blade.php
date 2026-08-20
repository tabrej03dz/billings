<x-layouts.app :title="__('New Purchase')">

    <form
        action="{{ route('purchases.store') }}"
        method="POST"
        enctype="multipart/form-data"
        class="space-y-5"
    >

        @csrf

        @if ($errors->any())

            <div
                class="
                    p-3
                    rounded
                    border
                    border-red-300
                    bg-red-50
                    text-red-700
                    text-sm
                "
            >

                <ul class="list-disc ml-4">

                    @foreach ($errors->all() as $e)

                        <li>
                            {{ $e }}
                        </li>

                    @endforeach

                </ul>

            </div>

        @endif


        @include(
            'purchases._form',
            [
                'purchase' => $purchase,
                'suppliers' => $suppliers,
                'items' => $items
            ]
        )


        <div class="flex justify-end gap-2 max-w-3xl mx-auto">

            <a
                href="{{ route('purchases.index') }}"
                class="
                    px-3
                    py-2
                    border
                    rounded
                "
            >
                Cancel
            </a>


            <button
                type="submit"
                class="
                    px-4
                    py-2
                    rounded
                    bg-green-600
                    text-white
                    hover:bg-green-700
                "
            >
                Save Purchase
            </button>

        </div>

    </form>


    {{-- =============================== --}}
    {{-- ADD SUPPLIER MODAL --}}
    {{-- =============================== --}}

    <div
        id="supplierModal"
        class="
            fixed
            inset-0
            z-50
            hidden
            items-center
            justify-center
            bg-black/50
            p-4
        "
    >

        <div
            class="
                w-full
                max-w-lg
                rounded-xl
                bg-white
                dark:bg-gray-900
                shadow-xl
            "
        >

            {{-- HEADER --}}

            <div
                class="
                    flex
                    items-center
                    justify-between
                    border-b
                    border-gray-200
                    dark:border-gray-700
                    px-5
                    py-4
                "
            >

                <div>

                    <h2
                        class="
                            text-lg
                            font-semibold
                            text-gray-900
                            dark:text-white
                        "
                    >
                        Add New Supplier
                    </h2>

                    <p
                        class="
                            text-xs
                            text-gray-500
                            mt-1
                        "
                    >
                        Supplier will be available immediately
                    </p>

                </div>


                <button
                    type="button"
                    onclick="closeSupplierModal()"
                    class="
                        text-gray-500
                        hover:text-gray-800
                        text-2xl
                    "
                >
                    ×
                </button>

            </div>


            {{-- BODY --}}

            <div class="p-5">

                <div
                    id="supplierError"
                    class="
                        hidden
                        mb-4
                        rounded-lg
                        border
                        border-red-300
                        bg-red-50
                        p-3
                        text-sm
                        text-red-700
                    "
                ></div>


                <div class="space-y-4">


                    {{-- Name --}}

                    <div>

                        <label
                            class="
                                block
                                mb-1
                                text-sm
                                font-medium
                            "
                        >
                            Supplier Name
                            <span class="text-red-500">
                                *
                            </span>
                        </label>

                        <input
                            type="text"
                            id="supplier_name"
                            class="
                                w-full
                                rounded-lg
                                border
                                border-gray-300
                                dark:border-gray-700
                                dark:bg-gray-800
                                px-3
                                py-2
                            "
                            placeholder="Supplier name"
                        >

                    </div>


                    {{-- Phone --}}

                    <div>

                        <label
                            class="
                                block
                                mb-1
                                text-sm
                                font-medium
                            "
                        >
                            Mobile Number
                        </label>

                        <input
                            type="text"
                            id="supplier_phone"
                            class="
                                w-full
                                rounded-lg
                                border
                                border-gray-300
                                dark:border-gray-700
                                dark:bg-gray-800
                                px-3
                                py-2
                            "
                            placeholder="Mobile number"
                        >

                    </div>


                    {{-- Email --}}

                    <div>

                        <label
                            class="
                                block
                                mb-1
                                text-sm
                                font-medium
                            "
                        >
                            Email
                        </label>

                        <input
                            type="email"
                            id="supplier_email"
                            class="
                                w-full
                                rounded-lg
                                border
                                border-gray-300
                                dark:border-gray-700
                                dark:bg-gray-800
                                px-3
                                py-2
                            "
                            placeholder="supplier@example.com"
                        >

                    </div>


                    {{-- GSTIN --}}

                    <div>

                        <label
                            class="
                                block
                                mb-1
                                text-sm
                                font-medium
                            "
                        >
                            GSTIN
                        </label>

                        <input
                            type="text"
                            id="supplier_gstin"
                            class="
                                w-full
                                rounded-lg
                                border
                                border-gray-300
                                dark:border-gray-700
                                dark:bg-gray-800
                                px-3
                                py-2
                                uppercase
                            "
                            placeholder="GSTIN"
                        >

                    </div>


                    {{-- Address --}}

                    <div>

                        <label
                            class="
                                block
                                mb-1
                                text-sm
                                font-medium
                            "
                        >
                            Address
                        </label>

                        <textarea
                            id="supplier_address"
                            rows="2"
                            class="
                                w-full
                                rounded-lg
                                border
                                border-gray-300
                                dark:border-gray-700
                                dark:bg-gray-800
                                px-3
                                py-2
                            "
                            placeholder="Supplier address"
                        ></textarea>

                    </div>

                </div>

            </div>


            {{-- FOOTER --}}

            <div
                class="
                    flex
                    justify-end
                    gap-2
                    border-t
                    border-gray-200
                    dark:border-gray-700
                    px-5
                    py-4
                "
            >

                <button
                    type="button"
                    onclick="closeSupplierModal()"
                    class="
                        rounded-lg
                        border
                        px-4
                        py-2
                    "
                >
                    Cancel
                </button>


                <button
                    type="button"
                    id="saveSupplierBtn"
                    onclick="saveSupplier()"
                    class="
                        rounded-lg
                        bg-blue-600
                        px-4
                        py-2
                        text-white
                        hover:bg-blue-700
                    "
                >
                    Save Supplier
                </button>

            </div>

        </div>

    </div>


    <script>

        function openSupplierModal()
        {
            const modal = document.getElementById('supplierModal');

            modal.classList.remove('hidden');
            modal.classList.add('flex');

            setTimeout(() => {
                document.getElementById('supplier_name').focus();
            }, 100);
        }


        function closeSupplierModal()
        {
            const modal = document.getElementById('supplierModal');

            modal.classList.add('hidden');
            modal.classList.remove('flex');

            document
                .getElementById('supplierError')
                .classList
                .add('hidden');
        }


        async function saveSupplier()
        {
            const button =
                document.getElementById('saveSupplierBtn');

            const errorBox =
                document.getElementById('supplierError');


            errorBox.classList.add('hidden');
            errorBox.innerHTML = '';


            const originalText = button.innerHTML;

            button.disabled = true;
            button.innerHTML = 'Saving...';


            try {

                const response = await fetch(
                    "{{ route('purchases.suppliers.store') }}",
                    {
                        method: "POST",

                        headers: {
                            "Content-Type": "application/json",
                            "Accept": "application/json",
                            "X-CSRF-TOKEN":
                                "{{ csrf_token() }}"
                        },

                        body: JSON.stringify({

                            name:
                                document
                                    .getElementById('supplier_name')
                                    .value,

                            phone:
                                document
                                    .getElementById('supplier_phone')
                                    .value,

                            email:
                                document
                                    .getElementById('supplier_email')
                                    .value,

                            gstin:
                                document
                                    .getElementById('supplier_gstin')
                                    .value,

                            address:
                                document
                                    .getElementById('supplier_address')
                                    .value,
                        })
                    }
                );


                const result = await response.json();


                if (!response.ok) {

                    let messages = [];


                    if (result.errors) {

                        Object.values(result.errors)
                            .forEach(errors => {

                                errors.forEach(error => {
                                    messages.push(error);
                                });

                            });

                    }
                    else {

                        messages.push(
                            result.message ??
                            'Unable to create supplier.'
                        );

                    }


                    errorBox.innerHTML =
                        messages.join('<br>');

                    errorBox.classList.remove('hidden');

                    return;
                }


                const supplier = result.supplier;


                /*
                 * Add supplier into purchase dropdown
                 */

                const select =
                    document.getElementById('supplier_id');


                const option =
                    document.createElement('option');


                option.value = supplier.id;

                option.text =
                    supplier.name +
                    (
                        supplier.phone
                            ? ' - ' + supplier.phone
                            : ''
                    );


                option.selected = true;


                select.appendChild(option);


                /*
                 * Clear form
                 */

                document.getElementById(
                    'supplier_name'
                ).value = '';

                document.getElementById(
                    'supplier_phone'
                ).value = '';

                document.getElementById(
                    'supplier_email'
                ).value = '';

                document.getElementById(
                    'supplier_gstin'
                ).value = '';

                document.getElementById(
                    'supplier_address'
                ).value = '';


                closeSupplierModal();

            }
            catch (error) {

                console.error(error);

                errorBox.innerHTML =
                    'Something went wrong while creating supplier.';

                errorBox.classList.remove('hidden');

            }
            finally {

                button.disabled = false;
                button.innerHTML = originalText;

            }
        }


        /*
         * Modal background click close
         */

        document
            .getElementById('supplierModal')
            .addEventListener('click', function (event) {

                if (event.target === this) {
                    closeSupplierModal();
                }

            });

    </script>

</x-layouts.app>