<x-layouts.app :title="__('Create Client / Supplier')">

    @php
        $activeType =
            old(
                'party_type',
                $type ?? request('type', 'client')
            );

        if (
            !in_array(
                $activeType,
                ['client', 'supplier'],
                true
            )
        ) {
            $activeType = 'client';
        }
    @endphp


    <div
        class="
            max-w-3xl
            mx-auto
            bg-[#BFE0E0]
            dark:bg-[#354A54]
            p-6
            my-2
            rounded-t-xl
        "
    >

        <h1
            id="partyPageTitle"
            class="
                text-center
                text-xl
                font-bold
                text-slate-900
                dark:text-white
            "
        >
            {{ $activeType === 'supplier'
                ? 'Create Supplier'
                : 'Create Client'
            }}
        </h1>

    </div>


    <div
        class="
            max-w-3xl
            mx-auto
            bg-[#F3F4F6]
            dark:bg-[#1A1D23]
            rounded-b-xl
            shadow
            overflow-hidden
        "
    >

        {{-- ========================================= --}}
        {{-- CLIENT / SUPPLIER TABS --}}
        {{-- ========================================= --}}

        <div
            class="
                grid
                grid-cols-2
                border-b
                border-slate-300
                dark:border-slate-700
                bg-white
                dark:bg-neutral-900
            "
        >

            <button
                type="button"
                id="clientTab"
                onclick="selectPartyType('client')"
                class="
                    px-5
                    py-4
                    text-sm
                    font-bold
                    border-b-2
                    transition
                "
            >
                Client
            </button>


            <button
                type="button"
                id="supplierTab"
                onclick="selectPartyType('supplier')"
                class="
                    px-5
                    py-4
                    text-sm
                    font-bold
                    border-b-2
                    transition
                "
            >
                Supplier
            </button>

        </div>


        <div class="p-6">

            @if ($errors->any())

                <div
                    class="
                        mb-5
                        rounded-lg
                        border
                        border-red-300
                        bg-red-50
                        p-4
                        text-sm
                        text-red-700
                    "
                >

                    <ul class="list-disc pl-5">

                        @foreach ($errors->all() as $error)

                            <li>
                                {{ $error }}
                            </li>

                        @endforeach

                    </ul>

                </div>

            @endif


            <form
                action="{{ route('clients.store') }}"
                method="POST"
                class="space-y-6"
            >

                @csrf


                <input
                    type="hidden"
                    name="party_type"
                    id="party_type"
                    value="{{ $activeType }}"
                >


                @include(
                    'clients._form',
                    [
                        'client' => null
                    ]
                )

            </form>

        </div>

    </div>



    <script>
        function selectPartyType(type)
        {
            const partyTypeInput =
                document.getElementById('party_type');

            const clientTab =
                document.getElementById('clientTab');

            const supplierTab =
                document.getElementById('supplierTab');

            const title =
                document.getElementById('partyPageTitle');

            const submitText =
                document.getElementById('partySubmitText');


            partyTypeInput.value =
                type;


            /*
            |--------------------------------------------------------------------------
            | Reset Tabs
            |--------------------------------------------------------------------------
            */

            [
                clientTab,
                supplierTab
            ].forEach(function (button) {

                button.classList.remove(
                    'border-blue-600',
                    'text-blue-700',
                    'bg-blue-50',

                    'border-emerald-600',
                    'text-emerald-700',
                    'bg-emerald-50',

                    'dark:bg-blue-950/30',
                    'dark:bg-emerald-950/30'
                );

                button.classList.add(
                    'border-transparent',
                    'text-slate-500'
                );

            });


            /*
            |--------------------------------------------------------------------------
            | Client Active
            |--------------------------------------------------------------------------
            */

            if (type === 'client') {

                clientTab.classList.remove(
                    'border-transparent',
                    'text-slate-500'
                );

                clientTab.classList.add(
                    'border-blue-600',
                    'text-blue-700',
                    'bg-blue-50',
                    'dark:bg-blue-950/30'
                );


                title.textContent =
                    'Create Client';


                if (submitText) {
                    submitText.textContent =
                        'Create Client';
                }

            }


            /*
            |--------------------------------------------------------------------------
            | Supplier Active
            |--------------------------------------------------------------------------
            */

            else {

                supplierTab.classList.remove(
                    'border-transparent',
                    'text-slate-500'
                );

                supplierTab.classList.add(
                    'border-emerald-600',
                    'text-emerald-700',
                    'bg-emerald-50',
                    'dark:bg-emerald-950/30'
                );


                title.textContent =
                    'Create Supplier';


                if (submitText) {
                    submitText.textContent =
                        'Create Supplier';
                }

            }
        }


        document.addEventListener(
            'DOMContentLoaded',
            function () {

                selectPartyType(
                    document
                        .getElementById('party_type')
                        .value
                );

            }
        );
    </script>

</x-layouts.app>