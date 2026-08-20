@props([
    'client' => null
])


@php

    $isEdit =
        filled($client?->id);


    $states = [

        ['code'=>'01','name'=>'Jammu and Kashmir'],
        ['code'=>'02','name'=>'Himachal Pradesh'],
        ['code'=>'03','name'=>'Punjab'],
        ['code'=>'04','name'=>'Chandigarh'],
        ['code'=>'05','name'=>'Uttarakhand'],
        ['code'=>'06','name'=>'Haryana'],
        ['code'=>'07','name'=>'Delhi'],
        ['code'=>'08','name'=>'Rajasthan'],
        ['code'=>'09','name'=>'Uttar Pradesh'],
        ['code'=>'10','name'=>'Bihar'],
        ['code'=>'11','name'=>'Sikkim'],
        ['code'=>'12','name'=>'Arunachal Pradesh'],
        ['code'=>'13','name'=>'Nagaland'],
        ['code'=>'14','name'=>'Manipur'],
        ['code'=>'15','name'=>'Mizoram'],
        ['code'=>'16','name'=>'Tripura'],
        ['code'=>'17','name'=>'Meghalaya'],
        ['code'=>'18','name'=>'Assam'],
        ['code'=>'19','name'=>'West Bengal'],
        ['code'=>'20','name'=>'Jharkhand'],
        ['code'=>'21','name'=>'Odisha'],
        ['code'=>'22','name'=>'Chhattisgarh'],
        ['code'=>'23','name'=>'Madhya Pradesh'],
        ['code'=>'24','name'=>'Gujarat'],

        [
            'code'=>'26',
            'name'=>'Dadra and Nagar Haveli and Daman and Diu'
        ],

        ['code'=>'27','name'=>'Maharashtra'],
        ['code'=>'29','name'=>'Karnataka'],
        ['code'=>'30','name'=>'Goa'],
        ['code'=>'31','name'=>'Lakshadweep'],
        ['code'=>'32','name'=>'Kerala'],
        ['code'=>'33','name'=>'Tamil Nadu'],
        ['code'=>'34','name'=>'Puducherry'],

        [
            'code'=>'35',
            'name'=>'Andaman and Nicobar Islands'
        ],

        ['code'=>'36','name'=>'Telangana'],
        ['code'=>'37','name'=>'Andhra Pradesh'],
        ['code'=>'38','name'=>'Ladakh'],
    ];


    /*
    |--------------------------------------------------------------------------
    | Selected State
    |--------------------------------------------------------------------------
    */

    $selectedState =
        old('state');


    if (
        !$selectedState
        && $client
    ) {

        $clientStateCode =
            trim(
                (string)
                ($client->state_code ?? '')
            );


        $clientStateName =
            trim(
                (string)
                ($client->state ?? '')
            );


        foreach ($states as $state) {

            if (
                $state['code'] === $clientStateCode
                ||
                strtolower($state['name'])
                === strtolower($clientStateName)
            ) {

                $selectedState =
                    $state['code']
                    . ','
                    . $state['name'];

                break;
            }
        }
    }


    $currentType =
        old(
            'party_type',
            $client?->party_type ?? 'client'
        );

@endphp



<div class="space-y-6">

    {{-- Edit page par type selector --}}
    @if($isEdit)

        <div>

            <label
                class="
                    block
                    text-sm
                    font-medium
                    mb-2
                "
            >
                Party Type
            </label>


            <div
                class="
                    grid
                    grid-cols-2
                    gap-3
                "
            >

                <label
                    class="
                        cursor-pointer
                        rounded-xl
                        border
                        p-4
                        transition
                        has-[:checked]:border-blue-500
                        has-[:checked]:bg-blue-50
                        dark:has-[:checked]:bg-blue-950/30
                    "
                >

                    <input
                        type="radio"
                        name="party_type"
                        value="client"
                        class="mr-2"
                        @checked(
                            $currentType === 'client'
                        )
                    >

                    <strong>
                        Client
                    </strong>

                </label>


                <label
                    class="
                        cursor-pointer
                        rounded-xl
                        border
                        p-4
                        transition
                        has-[:checked]:border-emerald-500
                        has-[:checked]:bg-emerald-50
                        dark:has-[:checked]:bg-emerald-950/30
                    "
                >

                    <input
                        type="radio"
                        name="party_type"
                        value="supplier"
                        class="mr-2"
                        @checked(
                            $currentType === 'supplier'
                        )
                    >

                    <strong>
                        Supplier
                    </strong>

                </label>

            </div>

        </div>

    @endif



    <div class="grid md:grid-cols-2 gap-4">

        {{-- Name --}}
        <div>

            <label
                class="
                    block
                    text-sm
                    font-medium
                    mb-1
                "
            >
                Name

                <span class="text-red-600">
                    *
                </span>
            </label>


            <input
                type="text"
                name="name"
                required
                value="{{ old(
                    'name',
                    $client->name ?? ''
                ) }}"
                class="
                    mt-1
                    w-full
                    border
                    rounded
                    px-3
                    py-2
                    bg-slate-200
                    dark:bg-[#242833]
                "
                placeholder="Enter name"
            >


            @error('name')

                <p class="text-red-600 text-xs mt-1">
                    {{ $message }}
                </p>

            @enderror

        </div>



        {{-- Mobile --}}
        <div>

            <label
                class="
                    block
                    text-sm
                    font-medium
                    mb-1
                "
            >
                Mobile
            </label>


            <input
                type="text"
                name="mobile"
                value="{{ old(
                    'mobile',
                    $client->mobile ?? ''
                ) }}"
                class="
                    mt-1
                    w-full
                    border
                    rounded
                    px-3
                    py-2
                    bg-slate-200
                    dark:bg-[#242833]
                "
                placeholder="Enter mobile number"
            >


            @error('mobile')

                <p class="text-red-600 text-xs mt-1">
                    {{ $message }}
                </p>

            @enderror

        </div>



        {{-- GSTIN --}}
        <div>

            <label
                class="
                    block
                    text-sm
                    font-medium
                    mb-1
                "
            >
                GSTIN
            </label>


            <input
                type="text"
                name="gstin"
                id="gstinInput"
                value="{{ old(
                    'gstin',
                    $client->gstin ?? ''
                ) }}"
                class="
                    mt-1
                    w-full
                    border
                    rounded
                    px-3
                    py-2
                    bg-slate-200
                    dark:bg-[#242833]
                    uppercase
                "
                maxlength="15"
                placeholder="22AAAAA0000A1Z5"
            >


            <p
                id="gstinMsg"
                class="text-xs mt-1 hidden"
            ></p>


            @error('gstin')

                <p class="text-red-600 text-xs mt-1">
                    {{ $message }}
                </p>

            @enderror

        </div>



        {{-- PAN --}}
        <div>

            <label
                class="
                    block
                    text-sm
                    font-medium
                    mb-1
                "
            >
                PAN
            </label>


            <input
                type="text"
                name="pan"
                maxlength="10"
                value="{{ old(
                    'pan',
                    $client->pan ?? ''
                ) }}"
                class="
                    mt-1
                    w-full
                    border
                    rounded
                    px-3
                    py-2
                    bg-slate-200
                    dark:bg-[#242833]
                    uppercase
                "
                placeholder="ABCDE1234F"
            >


            @error('pan')

                <p class="text-red-600 text-xs mt-1">
                    {{ $message }}
                </p>

            @enderror

        </div>



        {{-- State --}}
        <div class="md:col-span-2">

            <label
                class="
                    block
                    text-sm
                    font-medium
                    mb-1
                "
            >
                State (GST Code)
            </label>


            <select
                name="state"
                class="
                    mt-1
                    w-full
                    border
                    rounded
                    px-3
                    py-2
                    bg-slate-200
                    dark:bg-[#242833]
                    dark:text-white
                "
            >

                <option value="">
                    -- Select State --
                </option>


                @foreach($states as $st)

                    @php
                        $value =
                            $st['code']
                            . ','
                            . $st['name'];
                    @endphp


                    <option
                        value="{{ $value }}"
                        @selected(
                            $selectedState === $value
                        )
                    >
                        {{ $st['name'] }}
                        ({{ $st['code'] }})
                    </option>

                @endforeach

            </select>


            @error('state')

                <p class="text-red-600 text-xs mt-1">
                    {{ $message }}
                </p>

            @enderror

        </div>



        {{-- Address --}}
        <div class="md:col-span-2">

            <label
                class="
                    block
                    text-sm
                    font-medium
                    mb-1
                "
            >
                Address
            </label>


            <textarea
                name="address"
                rows="3"
                class="
                    mt-1
                    w-full
                    border
                    rounded
                    px-3
                    py-2
                    bg-slate-200
                    dark:bg-[#242833]
                "
                placeholder="Optional"
            >{{ old(
                'address',
                $client->address ?? ''
            ) }}</textarea>


            @error('address')

                <p class="text-red-600 text-xs mt-1">
                    {{ $message }}
                </p>

            @enderror

        </div>

    </div>



    {{-- Buttons --}}
    <div
        class="
            flex
            items-center
            gap-3
            pt-2
        "
    >

        <button
            type="submit"
            class="
                px-5
                py-2.5
                rounded-lg
                bg-green-600
                text-white
                font-semibold
                hover:bg-green-700
            "
        >

            <span id="partySubmitText">

                @if($isEdit)

                    Update
                    {{ $currentType === 'supplier'
                        ? 'Supplier'
                        : 'Client'
                    }}

                @else

                    Create Client

                @endif

            </span>

        </button>


        <a
            href="{{ route('clients.index') }}"
            class="
                bg-red-500
                px-5
                py-2.5
                text-white
                rounded-lg
                hover:bg-red-600
            "
        >
            Cancel
        </a>

    </div>

</div>



<script>
document.addEventListener(
    'DOMContentLoaded',
    function () {

        const gstInput =
            document.getElementById(
                'gstinInput'
            );

        const msg =
            document.getElementById(
                'gstinMsg'
            );


        if (!gstInput || !msg) {
            return;
        }


        const GST_LENGTH =
            15;


        const gstRegex =
            /^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/;



        function validateGST()
        {
            let val =
                gstInput
                    .value
                    .toUpperCase()
                    .replace(/\s+/g, '');


            gstInput.value =
                val;


            msg.classList.remove(
                'hidden'
            );


            gstInput.classList.remove(
                'border-red-500',
                'border-green-500',
                'border-orange-500'
            );


            if (!val) {

                msg.classList.add(
                    'hidden'
                );

                return;
            }


            if (val.length < GST_LENGTH) {

                msg.textContent =
                    'GSTIN must be exactly 15 characters long.';

                msg.className =
                    'text-xs mt-1 text-orange-500';

                gstInput.classList.add(
                    'border-orange-500'
                );

                return;
            }


            if (!gstRegex.test(val)) {

                msg.textContent =
                    'GSTIN format appears to be invalid.';

                msg.className =
                    'text-xs mt-1 text-red-600';

                gstInput.classList.add(
                    'border-red-500'
                );

                return;
            }


            msg.textContent =
                'GSTIN format appears to be valid.';

            msg.className =
                'text-xs mt-1 text-green-600';

            gstInput.classList.add(
                'border-green-500'
            );
        }


        gstInput.addEventListener(
            'input',
            validateGST
        );


        if (gstInput.value) {
            validateGST();
        }

    }
);
</script>