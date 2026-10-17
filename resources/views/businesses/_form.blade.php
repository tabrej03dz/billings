@props(['business' => null])

@php
    $isEdit = filled($business?->id);

    $states = [
        ['code'=>'35','name'=>'Andaman and Nicobar Islands'],
        ['code'=>'37','name'=>'Andhra Pradesh'],
        ['code'=>'12','name'=>'Arunachal Pradesh'],
        ['code'=>'18','name'=>'Assam'],
        ['code'=>'10','name'=>'Bihar'],
        ['code'=>'04','name'=>'Chandigarh'],
        ['code'=>'22','name'=>'Chhattisgarh'],
        ['code'=>'26','name'=>'Dadra and Nagar Haveli and Daman and Diu'],
        ['code'=>'07','name'=>'Delhi'],
        ['code'=>'30','name'=>'Goa'],
        ['code'=>'24','name'=>'Gujarat'],
        ['code'=>'06','name'=>'Haryana'],
        ['code'=>'02','name'=>'Himachal Pradesh'],
        ['code'=>'01','name'=>'Jammu and Kashmir'],
        ['code'=>'20','name'=>'Jharkhand'],
        ['code'=>'29','name'=>'Karnataka'],
        ['code'=>'32','name'=>'Kerala'],
        ['code'=>'38','name'=>'Ladakh'],
        ['code'=>'31','name'=>'Lakshadweep'],
        ['code'=>'23','name'=>'Madhya Pradesh'],
        ['code'=>'27','name'=>'Maharashtra'],
        ['code'=>'14','name'=>'Manipur'],
        ['code'=>'17','name'=>'Meghalaya'],
        ['code'=>'15','name'=>'Mizoram'],
        ['code'=>'13','name'=>'Nagaland'],
        ['code'=>'21','name'=>'Odisha'],
        ['code'=>'34','name'=>'Puducherry'],
        ['code'=>'03','name'=>'Punjab'],
        ['code'=>'08','name'=>'Rajasthan'],
        ['code'=>'11','name'=>'Sikkim'],
        ['code'=>'33','name'=>'Tamil Nadu'],
        ['code'=>'36','name'=>'Telangana'],
        ['code'=>'16','name'=>'Tripura'],
        ['code'=>'05','name'=>'Uttarakhand'],
        ['code'=>'09','name'=>'Uttar Pradesh'],
        ['code'=>'19','name'=>'West Bengal'],
    ];

    $selectedState = old('state');

    if (
        !$selectedState &&
        !empty($business?->state_code) &&
        !empty($business?->state)
    ) {
        $selectedState =
            $business->state_code .
            ',' .
            $business->state;
    }

    $selectedUserMode =
        old('user_mode', 'new');
@endphp

<div class="space-y-8">

    {{-- ====================================================== --}}
    {{-- VALIDATION ERRORS --}}
    {{-- ====================================================== --}}

    @if ($errors->any())

        <div class="rounded-xl border border-red-300 bg-red-50 p-4 text-left dark:border-red-700 dark:bg-red-950/40">

            <p class="mb-2 font-semibold text-red-700 dark:text-red-300">
                Please fix the following errors:
            </p>

            <ul class="list-disc space-y-1 pl-5 text-sm text-red-600 dark:text-red-300">

                @foreach ($errors->all() as $error)

                    <li>
                        {{ $error }}
                    </li>

                @endforeach

            </ul>

        </div>

    @endif

    @if(session('error'))

        <div class="rounded-xl border border-red-300 bg-red-50 p-4 text-red-700 dark:border-red-700 dark:bg-red-950/40 dark:text-red-300">
            {{ session('error') }}
        </div>

    @endif

    {{-- ====================================================== --}}
    {{-- BUSINESS DETAILS --}}
    {{-- ====================================================== --}}

    <div class="rounded-xl border border-gray-300 dark:border-gray-700 overflow-hidden">

        <div class="bg-cyan-700 text-white px-5 py-3">

            <h2 class="font-bold text-lg">
                1. Business Details
            </h2>

        </div>

        <div class="p-5 space-y-5">

            <div class="grid md:grid-cols-2 gap-4">

                {{-- BUSINESS NAME --}}

                <div>

                    <label class="block text-sm font-medium mb-1">

                        Business Name

                        <span class="text-red-600">
                            *
                        </span>

                    </label>

                    <input
                        type="text"
                        name="name"
                        value="{{ old('name', $business->name ?? '') }}"
                        class="mt-1 w-full border rounded px-3 py-2 bg-slate-200 dark:bg-[#242833]"
                        required
                    >

                    @error('name')

                        <p class="text-red-600 text-xs mt-1">
                            {{ $message }}
                        </p>

                    @enderror

                </div>

                {{-- SLUG --}}

                <div>

                    <label class="block text-sm font-medium mb-1">
                        Slug (optional)
                    </label>

                    <input
                        type="text"
                        name="slug"
                        value="{{ old('slug', $business->slug ?? '') }}"
                        placeholder="auto-from-name if left blank"
                        class="mt-1 w-full border rounded px-3 py-2 bg-slate-200 dark:bg-[#242833]"
                    >

                    @error('slug')

                        <p class="text-red-600 text-xs mt-1">
                            {{ $message }}
                        </p>

                    @enderror

                </div>

                {{-- INVOICE PREFIX --}}

                <div>

                    <label class="block text-sm font-medium mb-1">
                        Invoice Base Prefix
                    </label>

                    <input
                        type="text"
                        name="invoice_base_prefix"
                        value="{{ old('invoice_base_prefix', $business->invoice_base_prefix ?? '') }}"
                        placeholder="RV/SL"
                        class="mt-1 w-full border rounded px-3 py-2 bg-slate-200 dark:bg-[#242833]"
                    >

                    @error('invoice_base_prefix')

                        <p class="text-red-600 text-xs mt-1">
                            {{ $message }}
                        </p>

                    @enderror

                </div>

                {{-- BUSINESS EMAIL --}}

                <div>

                    <label class="block text-sm font-medium mb-1">

                        Business Email

                        <span class="text-red-600">
                            *
                        </span>

                    </label>

                    <input
                        type="email"
                        name="email"
                        value="{{ old('email', $business->email ?? '') }}"
                        class="mt-1 w-full border rounded px-3 py-2 bg-slate-200 dark:bg-[#242833]"
                        required
                    >

                    @error('email')

                        <p class="text-red-600 text-xs mt-1">
                            {{ $message }}
                        </p>

                    @enderror

                </div>

                {{-- BUSINESS MOBILE --}}

                <div>

                    <label class="block text-sm font-medium mb-1">
                        Business Mobile
                    </label>

                    <input
                        type="text"
                        name="mobile"
                        value="{{ old('mobile', $business->mobile ?? '') }}"
                        class="mt-1 w-full border rounded px-3 py-2 bg-slate-200 dark:bg-[#242833]"
                    >

                    <p
                        id="business-mobile-help"
                        class="text-xs text-gray-500 mt-1 hidden"
                    >
                        Existing user ke case me same business mobile allowed hai.
                    </p>

                    @error('mobile')

                        <p class="text-red-600 text-xs mt-1">
                            {{ $message }}
                        </p>

                    @enderror

                </div>

                {{-- GSTIN --}}

                <div>

                    <label class="block text-sm font-medium mb-1">
                        GSTIN
                    </label>

                    <input
                        type="text"
                        name="gstin"
                        value="{{ old('gstin', $business->gstin ?? '') }}"
                        class="mt-1 w-full border rounded px-3 py-2 bg-slate-200 dark:bg-[#242833]"
                    >

                    @error('gstin')

                        <p class="text-red-600 text-xs mt-1">
                            {{ $message }}
                        </p>

                    @enderror

                </div>

                {{-- BUSINESS TYPE --}}

                <div>

                    <label class="block text-sm font-medium mb-1">

                        Business Type

                        <span class="text-red-600">
                            *
                        </span>

                    </label>

                    <select
                        name="type"
                        required
                        class="mt-1 w-full border rounded px-3 py-2 bg-gray-300 dark:bg-[#242833] text-gray-700 dark:text-gray-300"
                    >

                        <option value="">
                            -- Select Business Type --
                        </option>

                        @foreach($businessTypes as $businessType)

                            <option
                                value="{{ $businessType->id }}"
                                {{
                                    (string) old(
                                        'type',
                                        $business?->type ?? ''
                                    )
                                    ===
                                    (string) $businessType->id
                                    ? 'selected'
                                    : ''
                                }}
                            >
                                {{ $businessType->name }}
                            </option>

                        @endforeach

                    </select>

                    @error('type')

                        <p class="text-red-600 text-xs mt-1">
                            {{ $message }}
                        </p>

                    @enderror

                </div>

                {{-- STATE --}}

                <div>

                    <label class="block text-sm font-medium mb-1">

                        State (GST Code)

                        <span class="text-red-600">
                            *
                        </span>

                    </label>

                    <select
                        name="state"
                        class="mt-1 w-full border rounded px-3 py-2 bg-gray-300 dark:bg-[#242833]"
                        required
                    >

                        <option value="">
                            -- Select State --
                        </option>

                        @foreach($states as $st)

                            @php
                                $value =
                                    $st['code'] .
                                    ',' .
                                    $st['name'];
                            @endphp

                            <option
                                value="{{ $value }}"
                                {{
                                    $selectedState === $value
                                    ? 'selected'
                                    : ''
                                }}
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

                {{-- BILL TEMPLATE --}}

                @if(
                    isset($billTemplates) &&
                    $billTemplates->count()
                )

                    <div class="md:col-span-2">

                        <label class="block text-sm font-semibold mb-1">

                            PDF Bill Template

                            <span class="text-red-600">
                                *
                            </span>

                        </label>

                        <select
                            name="pdf_template_id"
                            required
                            class="w-full rounded-xl border border-gray-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-gray-900 dark:text-white px-4 py-2"
                        >

                            <option value="">
                                Select Template
                            </option>

                            @foreach($billTemplates as $template)
                                <option
                                    value="{{ $template->id }}"
                                    {{
                                        old(
                                            'pdf_template_id',
                                            $business->pdf_template_id ?? ''
                                        )
                                        ==
                                        $template->id
                                        ? 'selected'
                                        : ''
                                    }}
                                >

                                    {{
                                        $template->name
                                        ??
                                        'Template ' .
                                        $template->id
                                    }}

                                </option>

                            @endforeach

                        </select>

                        @error('pdf_template_id')

                            <p class="text-red-600 text-xs mt-1">
                                {{ $message }}
                            </p>

                        @enderror

                    </div>

                @endif

                {{-- ADDRESS --}}

                <div class="md:col-span-2">

                    <label class="block text-sm font-medium mb-1">
                        Address
                    </label>

                    <textarea
                        name="address"
                        rows="3"
                        class="mt-1 w-full border rounded px-3 py-2 bg-slate-200 dark:bg-[#242833]"
                        placeholder="Optional"
                    >{{ old('address', $business->address ?? '') }}</textarea>

                    @error('address')

                        <p class="text-red-600 text-xs mt-1">
                            {{ $message }}
                        </p>

                    @enderror

                </div>

            </div>

            {{-- ====================================================== --}}
            {{-- FILES --}}
            {{-- ====================================================== --}}

            <div class="grid md:grid-cols-3 gap-4">

                {{-- LOGO --}}

                <div>

                    <label class="block text-sm font-medium mb-1">
                        Logo
                    </label>

                    <input
                        type="file"
                        name="logo"
                        accept="image/*"
                        class="mt-1 w-full border rounded px-3 py-2 bg-slate-200 dark:bg-[#242833]"
                    >

                    @error('logo')

                        <p class="text-red-600 text-xs mt-1">
                            {{ $message }}
                        </p>

                    @enderror

                    @if(
                        $isEdit &&
                        $business->logo
                    )

                        <div class="mt-3 flex items-center gap-3">

                            <img
                                src="{{ asset('storage/'.$business->logo) }}"
                                class="w-14 h-14 rounded object-cover"
                            >

                            <label class="text-sm">

                                <input
                                    type="checkbox"
                                    name="remove_logo"
                                    value="1"
                                >

                                Remove

                            </label>

                        </div>

                    @endif

                </div>

                {{-- SIGNATURE --}}

                <div>

                    <label class="block text-sm font-medium mb-1">
                        Signature
                    </label>

                    <input
                        type="file"
                        name="signature"
                        accept="image/*"
                        class="mt-1 w-full border rounded px-3 py-2 bg-slate-200 dark:bg-[#242833]"
                    >

                    @error('signature')

                        <p class="text-red-600 text-xs mt-1">
                            {{ $message }}
                        </p>

                    @enderror

                    @if(
                        $isEdit &&
                        $business->signature
                    )

                        <div class="mt-3 flex items-center gap-3">

                            <img
                                src="{{ asset('storage/'.$business->signature) }}"
                                class="w-14 h-14 rounded object-cover"
                            >

                            <label class="text-sm">

                                <input
                                    type="checkbox"
                                    name="remove_signature"
                                    value="1"
                                >

                                Remove

                            </label>

                        </div>

                    @endif

                </div>

                {{-- LETTER HEAD --}}

                <div>

                    <label class="block text-sm font-medium mb-1">
                        Letter Head
                    </label>

                    <input
                        type="file"
                        name="letter_head"
                        accept="image/*"
                        class="mt-1 w-full border rounded px-3 py-2 bg-slate-200 dark:bg-[#242833]"
                    >

                    @error('letter_head')

                        <p class="text-red-600 text-xs mt-1">
                            {{ $message }}
                        </p>

                    @enderror

                    @if(
                        $isEdit &&
                        $business->letter_head
                    )

                        <div class="mt-3 flex items-center gap-3">

                            <img
                                src="{{ asset('storage/'.$business->letter_head) }}"
                                class="w-14 h-14 rounded object-cover"
                            >

                            <label class="text-sm">

                                <input
                                    type="checkbox"
                                    name="remove_letter_head"
                                    value="1"
                                >

                                Remove

                            </label>

                        </div>

                    @endif

                </div>

            </div>

            {{-- TERMS --}}

            <div>

                <label class="block text-sm font-medium mb-1">
                    Terms & Conditions
                </label>

                <textarea
                    name="terms"
                    rows="3"
                    class="mt-1 w-full border rounded px-3 py-2 bg-slate-200 dark:bg-[#242833]"
                    placeholder="Optional"
                >{{ old('terms', $business->terms ?? '') }}</textarea>

                @error('terms')

                    <p class="text-red-600 text-xs mt-1">
                        {{ $message }}
                    </p>

                @enderror

            </div>

        </div>

    </div>

    {{-- ====================================================== --}}
    {{-- CREATE PAGE USER SECTION --}}
    {{-- ====================================================== --}}

    @if(!$isEdit)

        <div class="rounded-xl border border-gray-300 dark:border-gray-700 overflow-hidden">

            <div class="bg-purple-700 text-white px-5 py-3">

                <h2 class="font-bold text-lg">
                    2. Business User
                </h2>

                <p class="text-xs mt-1 text-purple-100">
                    Naya user create karein ya existing user ko is business se attach karein.
                </p>

            </div>

            <div class="p-5 space-y-6">

                {{-- ====================================================== --}}
                {{-- USER MODE --}}
                {{-- ====================================================== --}}

                <div>

                    <label class="block text-sm font-semibold mb-2">

                        User Option

                        <span class="text-red-600">
                            *
                        </span>

                    </label>

                    <div class="grid sm:grid-cols-2 gap-4">

                        {{-- CREATE NEW USER --}}

                        <label class="flex items-start gap-3 border rounded-xl p-4 bg-white dark:bg-[#242833] cursor-pointer">

                            <input
                                type="radio"
                                name="user_mode"
                                value="new"
                                class="mt-1"
                                {{
                                    $selectedUserMode === 'new'
                                    ? 'checked'
                                    : ''
                                }}
                            >

                            <div>

                                <div class="font-semibold">
                                    Create New User
                                </div>

                                <div class="text-xs text-gray-500 mt-1">
                                    Naya login user create hoga.
                                </div>

                            </div>

                        </label>

                        {{-- EXISTING USER --}}

                        <label class="flex items-start gap-3 border rounded-xl p-4 bg-white dark:bg-[#242833] cursor-pointer">

                            <input
                                type="radio"
                                name="user_mode"
                                value="existing"
                                class="mt-1"
                                {{
                                    $selectedUserMode === 'existing'
                                    ? 'checked'
                                    : ''
                                }}
                            >

                            <div>

                                <div class="font-semibold">
                                    Select Existing User
                                </div>

                                <div class="text-xs text-gray-500 mt-1">
                                    Purana user naye business se attach hoga.
                                </div>

                            </div>

                        </label>

                    </div>

                    @error('user_mode')

                        <p class="text-red-600 text-xs mt-1">
                            {{ $message }}
                        </p>

                    @enderror

                </div>

                {{-- ====================================================== --}}
                {{-- EXISTING USER --}}
                {{-- ====================================================== --}}

                <div
                    id="existing-user-section"
                    class="{{
                        $selectedUserMode === 'existing'
                        ? ''
                        : 'hidden'
                    }}"
                >

                    <label class="block text-sm font-medium mb-1">

                        Existing User

                        <span class="text-red-600">
                            *
                        </span>

                    </label>

                    <select
                        name="existing_user_id"
                        id="existing_user_id"
                        class="w-full border rounded px-3 py-2 bg-slate-200 dark:bg-[#242833]"
                    >

                        <option value="">
                            -- Select Existing User --
                        </option>

                        @foreach($users ?? [] as $existingUser)

                            <option
                                value="{{ $existingUser->id }}"
                                {{
                                    old('existing_user_id')
                                    ==
                                    $existingUser->id
                                    ? 'selected'
                                    : ''
                                }}
                            >

                                {{ $existingUser->name }}

                                @if($existingUser->phone)

                                    | {{ $existingUser->phone }}

                                @endif

                                @if($existingUser->email)

                                    | {{ $existingUser->email }}

                                @endif

                            </option>

                        @endforeach

                    </select>

                    @error('existing_user_id')

                        <p class="text-red-600 text-xs mt-1">
                            {{ $message }}
                        </p>

                    @enderror

                    <div class="mt-3 rounded-lg bg-blue-50 dark:bg-blue-950/30 p-3 text-sm text-blue-700 dark:text-blue-300">

                        Existing user ka password, email, phone aur role change nahi hoga.
                        Uske purane businesses bhi connected rahenge.

                    </div>

                </div>

                {{-- ====================================================== --}}
                {{-- NEW USER --}}
                {{-- ====================================================== --}}

                <div
                    id="new-user-section"
                    class="{{
                        $selectedUserMode === 'existing'
                        ? 'hidden'
                        : ''
                    }}"
                >

                    <div class="grid md:grid-cols-2 gap-4">

                        {{-- USER NAME --}}

                        <div>

                            <label class="block text-sm font-medium mb-1">

                                User Name

                                <span class="text-red-600">
                                    *
                                </span>

                            </label>

                            <input
                                type="text"
                                name="user_name"
                                value="{{ old('user_name') }}"
                                class="new-user-field new-user-required w-full border rounded px-3 py-2 bg-slate-200 dark:bg-[#242833]"
                            >

                            @error('user_name')

                                <p class="text-red-600 text-xs mt-1">
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>

                        {{-- USER PHONE --}}

                        <div>

                            <label class="block text-sm font-medium mb-1">

                                User Phone

                                <span class="text-red-600">
                                    *
                                </span>

                            </label>

                            <input
                                type="text"
                                name="user_phone"
                                value="{{ old('user_phone') }}"
                                class="new-user-field new-user-required w-full border rounded px-3 py-2 bg-slate-200 dark:bg-[#242833]"
                            >

                            @error('user_phone')

                                <p class="text-red-600 text-xs mt-1">
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>

                        {{-- LOGIN EMAIL --}}

                        <div>

                            <label class="block text-sm font-medium mb-1">

                                Login Email

                                <span class="text-red-600">
                                    *
                                </span>

                            </label>

                            <input
                                type="email"
                                name="user_email"
                                value="{{ old('user_email') }}"
                                class="new-user-field new-user-required w-full border rounded px-3 py-2 bg-slate-200 dark:bg-[#242833]"
                            >

                            @error('user_email')

                                <p class="text-red-600 text-xs mt-1">
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>

                        {{-- GOOGLE DRIVE --}}

                        <div>

                            <label class="block text-sm font-medium mb-1">
                                Google Drive Folder ID
                            </label>

                            <input
                                type="text"
                                name="user_google_drive_folder_id"
                                value="{{ old('user_google_drive_folder_id') }}"
                                class="new-user-field w-full border rounded px-3 py-2 bg-slate-200 dark:bg-[#242833]"
                                placeholder="Optional"
                            >

                            @error('user_google_drive_folder_id')

                                <p class="text-red-600 text-xs mt-1">
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>



                        {{-- ROLES --}}

                        <div class="md:col-span-2">

                            <label class="block text-sm font-medium mb-2">

                                User Role

                                <span class="text-red-600">
                                    *
                                </span>

                            </label>

                            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3">

                                @forelse( $roles ?? [] as $role)

                                    <label class="flex items-center gap-2 border rounded-lg px-3 py-2 bg-white dark:bg-[#242833] cursor-pointer">

                                        <input
                                            type="checkbox"
                                            name="roles[]"
                                            value="{{ $role->name }}"
                                            class="new-user-field role-checkbox"
                                            {{
                                                in_array(
                                                    $role->name,
                                                    old(
                                                        'roles',
                                                        []
                                                    )
                                                )
                                                ? 'checked'
                                                : ''
                                            }}
                                        >

                                        <span>
                                            {{
                                                ucwords(
                                                    $role->name
                                                )
                                            }}
                                        </span>

                                    </label>

                                @empty

                                    <p class="text-red-600 text-sm">
                                        No roles found.
                                    </p>

                                @endforelse

                            </div>

                            @error('roles')

                                <p class="text-red-600 text-xs mt-1">
                                    {{ $message }}
                                </p>

                            @enderror

                            @error('roles.*')

                                <p class="text-red-600 text-xs mt-1">
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>

                    </div>

                </div>

            </div>

        </div>

        {{-- ====================================================== --}}
        {{-- PLAN DETAILS --}}
        {{-- ====================================================== --}}

        <div class="rounded-xl border border-gray-300 dark:border-gray-700 overflow-hidden">

            <div class="bg-green-700 text-white px-5 py-3">

                <h2 class="font-bold text-lg">
                    3. Assign Plan
                </h2>

                <p class="text-xs mt-1 text-green-100">
                    Selected plan ki permissions selected user ko assign hongi.
                </p>

            </div>

            <div class="p-5">

                <div class="grid md:grid-cols-2 gap-4">

                    {{-- PLAN --}}

                    <div class="md:col-span-2">

                        <label class="block text-sm font-medium mb-1">

                            Select Plan

                            <span class="text-red-600">
                                *
                            </span>

                        </label>

                        <select
                            name="plan_id"
                            class="w-full border rounded px-3 py-2 bg-slate-200 dark:bg-[#242833]"
                            required
                        >

                            <option value="">
                                -- Select Plan --
                            </option>

                            @foreach($plans ?? [] as $plan)

                                <option
                                    value="{{ $plan->id }}"
                                    {{
                                        old('plan_id')
                                        ==
                                        $plan->id
                                        ? 'selected'
                                        : ''
                                    }}
                                >

                                    {{ $plan->name }}

                                    @if(
                                        isset(
                                            $plan->price
                                        )
                                    )

                                        -
                                        ₹{{
                                            number_format(
                                                $plan->price,
                                                2
                                            )
                                        }}

                                    @endif

                                </option>

                            @endforeach

                        </select>

                        @error('plan_id')

                            <p class="text-red-600 text-xs mt-1">
                                {{ $message }}
                            </p>

                        @enderror

                    </div>

                    {{-- NUMBER OF OFFICES --}}

                    <div>

                        <label class="block text-sm font-medium mb-1">

                            Number of Offices

                            <span class="text-red-600">
                                *
                            </span>

                        </label>

                        <input
                            type="number"
                            name="number_of_office"
                            min="1"
                            value="{{ old('number_of_office', 1) }}"
                            class="w-full border rounded px-3 py-2 bg-slate-200 dark:bg-[#242833]"
                            required
                        >

                        @error('number_of_office')

                            <p class="text-red-600 text-xs mt-1">
                                {{ $message }}
                            </p>

                        @enderror

                    </div>

                    {{-- NUMBER OF USERS --}}

                    <div>

                        <label class="block text-sm font-medium mb-1">

                            Number of Users

                            <span class="text-red-600">
                                *
                            </span>

                        </label>

                        <input
                            type="number"
                            name="number_of_user"
                            min="1"
                            value="{{ old('number_of_user', 1) }}"
                            class="w-full border rounded px-3 py-2 bg-slate-200 dark:bg-[#242833]"
                            required
                        >

                        @error('number_of_user')

                            <p class="text-red-600 text-xs mt-1">
                                {{ $message }}
                            </p>

                        @enderror

                    </div>

                    {{-- START DATE --}}

                    <div>

                        <label class="block text-sm font-medium mb-1">
                            Start Date
                        </label>

                        <input
                            type="date"
                            name="start_date"
                            value="{{ old('start_date', now()->format('Y-m-d')) }}"
                            class="w-full border rounded px-3 py-2 bg-slate-200 dark:bg-[#242833]"
                        >

                        @error('start_date')

                            <p class="text-red-600 text-xs mt-1">
                                {{ $message }}
                            </p>

                        @enderror

                    </div>

                    {{-- EXPIRY DATE --}}

                    <div>

                        <label class="block text-sm font-medium mb-1">
                            Expiry Date
                        </label>

                        <input
                            type="date"
                            name="expiry_date"
                            value="{{ old('expiry_date') }}"
                            class="w-full border rounded px-3 py-2 bg-slate-200 dark:bg-[#242833]"
                        >

                        <p class="text-xs text-gray-500 mt-1">
                            Blank chhodne par plan duration ke according automatically calculate hoga.
                        </p>

                        @error('expiry_date')

                            <p class="text-red-600 text-xs mt-1">
                                {{ $message }}
                            </p>

                        @enderror

                    </div>

                    {{-- PLAN STATUS --}}

                    <div class="md:col-span-2">

                        <label class="inline-flex items-center gap-2">

                            <input
                                type="checkbox"
                                name="plan_status"
                                value="1"
                                {{
                                    old(
                                        'plan_status',
                                        1
                                    )
                                    ? 'checked'
                                    : ''
                                }}
                            >

                            <span class="font-medium">
                                Plan Active
                            </span>

                        </label>

                    </div>

                </div>

            </div>

        </div>

    @endif

    {{-- ====================================================== --}}
    {{-- BUTTONS --}}
    {{-- ====================================================== --}}

    <div class="flex items-center justify-end gap-3">

        <a
            href="{{ route('businesses.index') }}"
            class="bg-gray-500 px-5 py-2 rounded-lg text-white hover:bg-gray-600"
        >
            Cancel
        </a>

        <button
            type="submit"
            class="px-6 py-2 rounded-lg bg-green-600 text-white hover:bg-green-700 font-semibold"
        >

            {{
                $isEdit
                ? 'Update Business'
                : 'Create Business + User + Plan'
            }}

        </button>

    </div>

</div>

{{-- ====================================================== --}}
{{-- JAVASCRIPT --}}
{{-- ====================================================== --}}

<script>
document.addEventListener('DOMContentLoaded', function () {

    /*
    |--------------------------------------------------------------------------
    | AUTO SLUG
    |--------------------------------------------------------------------------
    */

    const nameInput =
        document.querySelector(
            'input[name="name"]'
        );

    const slugInput =
        document.querySelector(
            'input[name="slug"]'
        );

    if (
        nameInput &&
        slugInput
    ) {
        let slugEdited =
            slugInput.value.length > 0;

        slugInput.addEventListener(
            'input',
            function () {
                slugEdited =
                    slugInput.value.length > 0;
            }
        );

        nameInput.addEventListener(
            'input',
            function () {

                if (slugEdited) {
                    return;
                }

                slugInput.value =
                    nameInput.value
                        .trim()
                        .toLowerCase()
                        .replace(
                            /[^a-z0-9]+/g,
                            '-'
                        )
                        .replace(
                            /(^-|-$)/g,
                            ''
                        );
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE / EXISTING USER SWITCH
    |--------------------------------------------------------------------------
    */

    const userModeInputs =
        document.querySelectorAll(
            'input[name="user_mode"]'
        );

    const newUserSection =
        document.getElementById(
            'new-user-section'
        );

    const existingUserSection =
        document.getElementById(
            'existing-user-section'
        );

    const existingUserSelect =
        document.getElementById(
            'existing_user_id'
        );

    const businessMobileHelp =
        document.getElementById(
            'business-mobile-help'
        );

    /*
    |--------------------------------------------------------------------------
    | UPDATE USER MODE
    |--------------------------------------------------------------------------
    */

    function updateUserMode() {

        const checked =
            document.querySelector(
                'input[name="user_mode"]:checked'
            );

        if (!checked) {
            return;
        }

        const mode =
            checked.value;

        const newUserFields =
            newUserSection
                ? newUserSection.querySelectorAll(
                    'input, select, textarea'
                )
                : [];

        /*
        |--------------------------------------------------------------------------
        | EXISTING USER
        |--------------------------------------------------------------------------
        */

        if (mode === 'existing') {

            if (newUserSection) {
                newUserSection.classList.add(
                    'hidden'
                );
            }

            if (existingUserSection) {
                existingUserSection.classList.remove(
                    'hidden'
                );
            }

            /*
            | Disable all new user fields.
            |
            | Isse:
            | - password submit nahi hoga
            | - confirm password submit nahi hoga
            | - roles submit nahi honge
            | - new user email/phone submit nahi hoga
            */

            newUserFields.forEach(
                function (field) {
                    field.disabled = true;
                    field.required = false;
                }
            );

            /*
            | Existing user required
            */

            if (existingUserSelect) {
                existingUserSelect.disabled = false;
                existingUserSelect.required = true;
            }

            /*
            | Mobile help show
            */

            if (businessMobileHelp) {
                businessMobileHelp.classList.remove(
                    'hidden'
                );
            }

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | NEW USER
        |--------------------------------------------------------------------------
        */

        if (newUserSection) {
            newUserSection.classList.remove(
                'hidden'
            );
        }

        if (existingUserSection) {
            existingUserSection.classList.add(
                'hidden'
            );
        }

        /*
        | Enable new user fields
        */

        newUserFields.forEach(
            function (field) {
                field.disabled = false;
            }
        );

        /*
        | Required fields
        */

        const requiredNames = [
            'user_name',
            'user_phone',
            'user_email',
        ];

        newUserFields.forEach(
            function (field) {

                if (
                    requiredNames.includes(
                        field.name
                    )
                ) {
                    field.required = true;
                }
            }
        );

        /*
        | Existing user disabled
        */

        if (existingUserSelect) {
            existingUserSelect.disabled = true;
            existingUserSelect.required = false;
        }

        /*
        | Mobile help hide
        */

        if (businessMobileHelp) {
            businessMobileHelp.classList.add(
                'hidden'
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | EVENT
    |--------------------------------------------------------------------------
    */

    userModeInputs.forEach(
        function (input) {

            input.addEventListener(
                'change',
                updateUserMode
            );
        }
    );

    /*
    |--------------------------------------------------------------------------
    | INITIAL STATE
    |--------------------------------------------------------------------------
    */

    updateUserMode();

});
</script>