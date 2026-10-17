<x-layouts.app :title="__('Edit Business')">

    <div class="max-w-4xl mx-auto bg-[#BFE0E0] dark:bg-[#354A54] p-6 text-center text-xl font-bold my-2 rounded-xl">
        Edit Business + User + Plan
    </div>

    <div class="max-w-4xl mx-auto p-6 bg-[#F3F4F6] dark:bg-[#1A1D23] rounded-xl shadow">

        @if(session('error'))
            <div class="mb-5 rounded-lg border border-red-300 bg-red-50 p-4 text-red-700">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-5 rounded-lg border border-red-300 bg-red-50 p-4 text-red-700">
                <p class="font-semibold mb-2">
                    Please fix the following errors:
                </p>

                <ul class="list-disc pl-5 text-sm space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif


        <form
            action="{{ route('businesses.update', $business->id) }}"
            method="POST"
            enctype="multipart/form-data"
            class="space-y-8"
        >
            @csrf
            @method('PUT')


            {{-- ====================================================== --}}
            {{-- 1. BUSINESS DETAILS --}}
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
                                <span class="text-red-600">*</span>
                            </label>

                            <input
                                type="text"
                                name="name"
                                value="{{ old('name', $business->name) }}"
                                required
                                class="w-full border rounded px-3 py-2 bg-slate-200 dark:bg-[#242833]"
                            >

                            @error('name')
                                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>


                        {{-- SLUG --}}
                        <div>
                            <label class="block text-sm font-medium mb-1">
                                Slug
                            </label>

                            <input
                                type="text"
                                name="slug"
                                value="{{ old('slug', $business->slug) }}"
                                placeholder="Auto generated from business name"
                                class="w-full border rounded px-3 py-2 bg-slate-200 dark:bg-[#242833]"
                            >

                            @error('slug')
                                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>


                        {{-- EMAIL --}}
                        <div>
                            <label class="block text-sm font-medium mb-1">
                                Business Email
                                <span class="text-red-600">*</span>
                            </label>

                            <input
                                type="email"
                                name="email"
                                value="{{ old('email', $business->email) }}"
                                required
                                class="w-full border rounded px-3 py-2 bg-slate-200 dark:bg-[#242833]"
                            >

                            @error('email')
                                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>


                        {{-- MOBILE --}}
                        <div>
                            <label class="block text-sm font-medium mb-1">
                                Business Mobile
                            </label>

                            <input
                                type="text"
                                name="mobile"
                                value="{{ old('mobile', $business->mobile) }}"
                                class="w-full border rounded px-3 py-2 bg-slate-200 dark:bg-[#242833]"
                            >

                            @error('mobile')
                                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>


                        {{-- BUSINESS TYPE --}}
                        <div>
                            <label class="block text-sm font-medium mb-1">
                                Business Type
                                <span class="text-red-600">*</span>
                            </label>

                            <select
                                name="type"
                                required
                                class="w-full border rounded px-3 py-2 bg-slate-200 dark:bg-[#242833]"
                            >

                                <option value="">-- Select Business Type --</option>

                                @foreach($businessTypes as $businessType)
                                    <option
                                        value="{{ $businessType->id }}"
                                        {{
                                            (string) old('type', $business->type)
                                            === (string) $businessType->id
                                                ? 'selected'
                                                : ''
                                        }}
                                    >
                                        {{ $businessType->name }}
                                    </option>
                                @endforeach

                            </select>

                            @error('type')
                                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
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
                                value="{{ old('gstin', $business->gstin) }}"
                                class="w-full border rounded px-3 py-2 bg-slate-200 dark:bg-[#242833]"
                            >

                            @error('gstin')
                                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>


                        {{-- STATE --}}
                        <div>
                            <label class="block text-sm font-medium mb-1">
                                State
                                <span class="text-red-600">*</span>
                            </label>

                            @php
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

                                $businessState =
                                    $business->state_code . ',' . $business->state;

                                $selectedState =
                                    old('state', $businessState);
                            @endphp

                            <select
                                name="state"
                                required
                                class="w-full border rounded px-3 py-2 bg-slate-200 dark:bg-[#242833]"
                            >

                                <option value="">-- Select State --</option>

                                @foreach($states as $state)
                                    @php
                                        $stateValue =
                                            $state['code'] . ',' . $state['name'];
                                    @endphp

                                    <option
                                        value="{{ $stateValue }}"
                                        {{ $selectedState === $stateValue ? 'selected' : '' }}
                                    >
                                        {{ $state['name'] }} ({{ $state['code'] }})
                                    </option>
                                @endforeach

                            </select>

                            @error('state')
                                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>


                        {{-- PDF TEMPLATE --}}
                        <div>
                            <label class="block text-sm font-medium mb-1">
                                PDF Bill Template
                                <span class="text-red-600">*</span>
                            </label>

                            <select
                                name="pdf_template_id"
                                required
                                class="w-full border rounded px-3 py-2 bg-slate-200 dark:bg-[#242833]"
                            >

                                <option value="">-- Select Template --</option>

                                @foreach($billTemplates as $template)
                                    <option
                                        value="{{ $template->id }}"
                                        {{
                                            old(
                                                'pdf_template_id',
                                                $business->pdf_template_id
                                            ) == $template->id
                                                ? 'selected'
                                                : ''
                                        }}
                                    >
                                        {{ $template->name ?? 'Template '.$template->id }}
                                    </option>
                                @endforeach

                            </select>

                            @error('pdf_template_id')
                                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>


                        {{-- PREFIX --}}
                        <div>
                            <label class="block text-sm font-medium mb-1">
                                Invoice Base Prefix
                            </label>

                            <input
                                type="text"
                                name="invoice_base_prefix"
                                value="{{ old('invoice_base_prefix', $business->invoice_base_prefix) }}"
                                class="w-full border rounded px-3 py-2 bg-slate-200 dark:bg-[#242833]"
                            >

                            @error('invoice_base_prefix')
                                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>


                        {{-- ADDRESS --}}
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium mb-1">
                                Address
                            </label>

                            <textarea
                                name="address"
                                rows="3"
                                class="w-full border rounded px-3 py-2 bg-slate-200 dark:bg-[#242833]"
                            >{{ old('address', $business->address) }}</textarea>

                            @error('address')
                                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                    </div>


                    {{-- FILES --}}
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
                                class="w-full border rounded px-3 py-2 bg-slate-200 dark:bg-[#242833]"
                            >

                            @if($business->logo)
                                <div class="mt-3">
                                    <img
                                        src="{{ asset('storage/'.$business->logo) }}"
                                        class="w-16 h-16 rounded object-cover"
                                    >

                                    <label class="mt-2 inline-flex items-center gap-2 text-sm">
                                        <input
                                            type="checkbox"
                                            name="remove_logo"
                                            value="1"
                                        >
                                        Remove Logo
                                    </label>
                                </div>
                            @endif

                            @error('logo')
                                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                            @enderror
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
                                class="w-full border rounded px-3 py-2 bg-slate-200 dark:bg-[#242833]"
                            >

                            @if($business->signature)
                                <div class="mt-3">
                                    <img
                                        src="{{ asset('storage/'.$business->signature) }}"
                                        class="w-16 h-16 rounded object-cover"
                                    >

                                    <label class="mt-2 inline-flex items-center gap-2 text-sm">
                                        <input
                                            type="checkbox"
                                            name="remove_signature"
                                            value="1"
                                        >
                                        Remove Signature
                                    </label>
                                </div>
                            @endif

                            @error('signature')
                                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                            @enderror
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
                                class="w-full border rounded px-3 py-2 bg-slate-200 dark:bg-[#242833]"
                            >

                            @if($business->letter_head)
                                <div class="mt-3">
                                    <img
                                        src="{{ asset('storage/'.$business->letter_head) }}"
                                        class="w-16 h-16 rounded object-cover"
                                    >

                                    <label class="mt-2 inline-flex items-center gap-2 text-sm">
                                        <input
                                            type="checkbox"
                                            name="remove_letter_head"
                                            value="1"
                                        >
                                        Remove Letter Head
                                    </label>
                                </div>
                            @endif

                            @error('letter_head')
                                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                            @enderror
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
                            class="w-full border rounded px-3 py-2 bg-slate-200 dark:bg-[#242833]"
                        >{{ old('terms', $business->terms) }}</textarea>

                        @error('terms')
                            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                </div>

            </div>


            {{-- ====================================================== --}}
            {{-- 2. USER DETAILS --}}
            {{-- ====================================================== --}}

            <div class="rounded-xl border border-gray-300 dark:border-gray-700 overflow-hidden">

                <div class="bg-purple-700 text-white px-5 py-3">
                    <h2 class="font-bold text-lg">
                        2. Business User Details
                    </h2>

                    <p class="text-xs mt-1 text-purple-100">
                        Business ke existing user ko yahan se update kar sakte hain.
                    </p>
                </div>


                <div class="p-5">

                    <div class="grid md:grid-cols-2 gap-4">

                        {{-- USER NAME --}}
                        <div>
                            <label class="block text-sm font-medium mb-1">
                                User Name
                                <span class="text-red-600">*</span>
                            </label>

                            <input
                                type="text"
                                name="user_name"
                                value="{{ old('user_name', $businessUser->name) }}"
                                required
                                class="w-full border rounded px-3 py-2 bg-slate-200 dark:bg-[#242833]"
                            >

                            @error('user_name')
                                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>


                        {{-- USER PHONE --}}
                        <div>
                            <label class="block text-sm font-medium mb-1">
                                User Phone
                                <span class="text-red-600">*</span>
                            </label>

                            <input
                                type="text"
                                name="user_phone"
                                value="{{ old('user_phone', $businessUser->phone) }}"
                                required
                                class="w-full border rounded px-3 py-2 bg-slate-200 dark:bg-[#242833]"
                            >

                            @error('user_phone')
                                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>


                        {{-- USER EMAIL --}}
                        <div>
                            <label class="block text-sm font-medium mb-1">
                                Login Email
                                <span class="text-red-600">*</span>
                            </label>

                            <input
                                type="email"
                                name="user_email"
                                value="{{ old('user_email', $businessUser->email) }}"
                                required
                                class="w-full border rounded px-3 py-2 bg-slate-200 dark:bg-[#242833]"
                            >

                            @error('user_email')
                                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
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
                                value="{{ old(
                                    'user_google_drive_folder_id',
                                    $businessUser->google_drive_folder_id
                                ) }}"
                                class="w-full border rounded px-3 py-2 bg-slate-200 dark:bg-[#242833]"
                            >

                            @error('user_google_drive_folder_id')
                                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>


                        {{-- PASSWORD --}}
                        <div>
                            <label class="block text-sm font-medium mb-1">
                                New Password
                            </label>

                            <input
                                type="password"
                                name="user_password"
                                autocomplete="new-password"
                                class="w-full border rounded px-3 py-2 bg-slate-200 dark:bg-[#242833]"
                            >

                            <p class="text-xs text-gray-500 mt-1">
                                Password change nahi karna ho to blank chhodiye.
                            </p>

                            @error('user_password')
                                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>


                        {{-- CONFIRM PASSWORD --}}
                        <div>
                            <label class="block text-sm font-medium mb-1">
                                Confirm New Password
                            </label>

                            <input
                                type="password"
                                name="user_password_confirmation"
                                autocomplete="new-password"
                                class="w-full border rounded px-3 py-2 bg-slate-200 dark:bg-[#242833]"
                            >
                        </div>


                        {{-- ROLES --}}
                        <div class="md:col-span-2">

                            <label class="block text-sm font-medium mb-2">
                                User Roles
                                <span class="text-red-600">*</span>
                            </label>

                            @php
                                $currentRoles = old(
                                    'roles',
                                    $businessUser->roles->pluck('name')->toArray()
                                );
                            @endphp

                            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3">

                                @foreach($roles as $role)

                                    <label
                                        class="flex items-center gap-2 border rounded-lg px-3 py-2
                                               bg-white dark:bg-[#242833] cursor-pointer"
                                    >

                                        <input
                                            type="checkbox"
                                            name="roles[]"
                                            value="{{ $role->name }}"
                                            {{
                                                in_array(
                                                    $role->name,
                                                    $currentRoles
                                                )
                                                    ? 'checked'
                                                    : ''
                                            }}
                                        >

                                        <span>
                                            {{ ucwords($role->name) }}
                                        </span>

                                    </label>

                                @endforeach

                            </div>

                            @error('roles')
                                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                            @enderror

                            @error('roles.*')
                                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                            @enderror

                        </div>

                    </div>

                </div>

            </div>


            {{-- ====================================================== --}}
            {{-- 3. PLAN DETAILS --}}
            {{-- ====================================================== --}}

            <div class="rounded-xl border border-gray-300 dark:border-gray-700 overflow-hidden">

                <div class="bg-green-700 text-white px-5 py-3">

                    <h2 class="font-bold text-lg">
                        3. User Plan
                    </h2>

                    <p class="text-xs mt-1 text-green-100">
                        Plan change karne par permissions bhi automatically update hongi.
                    </p>

                </div>


                <div class="p-5">

                    <div class="grid md:grid-cols-2 gap-4">


                        {{-- PLAN --}}
                        <div class="md:col-span-2">

                            <label class="block text-sm font-medium mb-1">
                                Select Plan
                                <span class="text-red-600">*</span>
                            </label>

                            <select
                                name="plan_id"
                                required
                                class="w-full border rounded px-3 py-2 bg-slate-200 dark:bg-[#242833]"
                            >

                                <option value="">-- Select Plan --</option>

                                @foreach($plans as $plan)

                                    <option
                                        value="{{ $plan->id }}"
                                        {{
                                            old(
                                                'plan_id',
                                                $userPlan->plan_id ?? ''
                                            ) == $plan->id
                                                ? 'selected'
                                                : ''
                                        }}
                                    >

                                        {{ $plan->name }}

                                        @if(isset($plan->price))
                                            - ₹{{ number_format($plan->price, 2) }}
                                        @endif

                                    </option>

                                @endforeach

                            </select>

                            @error('plan_id')
                                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                            @enderror

                        </div>


                        {{-- NUMBER OF OFFICES --}}
                        <div>

                            <label class="block text-sm font-medium mb-1">
                                Number of Offices
                                <span class="text-red-600">*</span>
                            </label>

                            <input
                                type="number"
                                name="number_of_office"
                                min="1"
                                value="{{ old(
                                    'number_of_office',
                                    $userPlan->number_of_office ?? 1
                                ) }}"
                                required
                                class="w-full border rounded px-3 py-2 bg-slate-200 dark:bg-[#242833]"
                            >

                            @error('number_of_office')
                                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                            @enderror

                        </div>


                        {{-- NUMBER OF USERS --}}
                        <div>

                            <label class="block text-sm font-medium mb-1">
                                Number of Users
                                <span class="text-red-600">*</span>
                            </label>

                            <input
                                type="number"
                                name="number_of_user"
                                min="1"
                                value="{{ old(
                                    'number_of_user',
                                    $userPlan->number_of_user ?? 1
                                ) }}"
                                required
                                class="w-full border rounded px-3 py-2 bg-slate-200 dark:bg-[#242833]"
                            >

                            @error('number_of_user')
                                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
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
                                value="{{ old(
                                    'start_date',
                                    $userPlan?->start_date
                                        ? \Carbon\Carbon::parse($userPlan->start_date)->format('Y-m-d')
                                        : now()->format('Y-m-d')
                                ) }}"
                                class="w-full border rounded px-3 py-2 bg-slate-200 dark:bg-[#242833]"
                            >

                            @error('start_date')
                                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
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
                                value="{{ old(
                                    'expiry_date',
                                    $userPlan?->expiry_date
                                        ? \Carbon\Carbon::parse($userPlan->expiry_date)->format('Y-m-d')
                                        : ''
                                ) }}"
                                class="w-full border rounded px-3 py-2 bg-slate-200 dark:bg-[#242833]"
                            >

                            <p class="text-xs text-gray-500 mt-1">
                                Blank karne par selected plan ke duration se automatically calculate hoga.
                            </p>

                            @error('expiry_date')
                                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                            @enderror

                        </div>


                        {{-- STATUS --}}
                        <div class="md:col-span-2">

                            <input
                                type="hidden"
                                name="plan_status"
                                value="0"
                            >

                            <label class="inline-flex items-center gap-2">

                                <input
                                    type="checkbox"
                                    name="plan_status"
                                    value="1"
                                    {{
                                        old(
                                            'plan_status',
                                            $userPlan?->status ?? 1
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
                    Update Business + User + Plan
                </button>

            </div>

        </form>

    </div>


    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const nameInput = document.querySelector('input[name="name"]');
            const slugInput = document.querySelector('input[name="slug"]');

            if (!nameInput || !slugInput) {
                return;
            }

            let slugEdited = slugInput.value.length > 0;

            slugInput.addEventListener('input', function () {
                slugEdited = slugInput.value.length > 0;
            });

            nameInput.addEventListener('input', function () {

                if (slugEdited) {
                    return;
                }

                slugInput.value = nameInput.value
                    .trim()
                    .toLowerCase()
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/(^-|-$)/g, '');
            });

        });
    </script>

</x-layouts.app>