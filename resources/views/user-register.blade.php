@extends('frontend.layout')

@section('content')

<main class="hero-bg relative overflow-hidden">
    <div class="absolute inset-0 pointer-events-none">
        <div class="absolute top-20 left-10 h-72 w-72 rounded-full bg-blue-200/40 blur-3xl"></div>
        <div class="absolute top-28 right-10 h-72 w-72 rounded-full bg-yellow-200/40 blur-3xl"></div>
    </div>

    <div class="relative max-w-7xl mx-auto px-4 lg:px-8 py-10 lg:py-16">
        <div class="grid lg:grid-cols-12 gap-10 items-start">

            <div class="lg:col-span-5 lg:sticky lg:top-28">
                <div class="inline-flex items-center gap-2 rounded-full bg-white border border-blue-100 shadow-sm px-5 py-2 text-sm font-black text-blue-700 mb-6">
                    <span class="h-2.5 w-2.5 rounded-full bg-green-500"></span>
                    Smart Billing Software for Modern Shops
                </div>

                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black leading-[1.05] tracking-tight">
                    Create Account,
                    <span class="text-blue-600">Start Billing Fast.</span>
                </h1>

                <p class="mt-6 text-lg text-slate-600 leading-8">
                    Owner details, business information aur billing preferences add karke
                    apna MyVictory Billing account few minutes me ready karein.
                </p>

                <div class="mt-8 grid grid-cols-3 gap-4">
                    <div class="bg-white rounded-3xl p-5 border border-slate-200 soft-card">
                        <div class="text-2xl font-black text-blue-600">GST</div>
                        <p class="text-xs text-slate-500 mt-1">Billing Ready</p>
                    </div>

                    <div class="bg-white rounded-3xl p-5 border border-slate-200 soft-card">
                        <div class="text-2xl font-black text-blue-600">Photo</div>
                        <p class="text-xs text-slate-500 mt-1">Smart Entry</p>
                    </div>

                    <div class="bg-white rounded-3xl p-5 border border-slate-200 soft-card">
                        <div class="text-2xl font-black text-blue-600">Cloud</div>
                        <p class="text-xs text-slate-500 mt-1">Any Device</p>
                    </div>
                </div>

                <div class="mt-8 rounded-[2rem] bg-slate-950 text-white p-6 soft-card">
                    <div class="text-sm text-blue-200">After registration</div>
                    <div class="text-2xl font-black mt-1">Login → Choose Plan → Start Billing</div>
                </div>

                <div class="mt-6 rounded-[2rem] bg-white border border-slate-200 p-6 soft-card">
                    <div class="font-black text-slate-950">Need support?</div>
                    <p class="text-sm text-slate-500 mt-1">Registration ya setup me help chahiye to WhatsApp karein.</p>
                    <a href="https://wa.me/917753800444"
                       class="mt-4 inline-flex rounded-full bg-blue-600 px-5 py-3 text-white text-sm font-black">
                        WhatsApp Now
                    </a>
                </div>
            </div>

            <div class="lg:col-span-7">
                <section class="rounded-[2.5rem] border border-white/70 main-card overflow-hidden">
                    <div class="px-5 sm:px-8 pt-6 sm:pt-8 pb-6 bg-gradient-to-b from-white to-blue-50/50">
                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                            <div>
                                <h2 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900">
                                    Register your account
                                </h2>
                                <p class="mt-2 text-sm text-slate-500">
                                    Complete 3 simple steps to start using MyVictory Billing.
                                </p>
                            </div>

                            <div class="inline-flex items-center gap-2 rounded-full border border-blue-100 bg-white px-4 py-2 text-xs font-bold text-blue-700 shadow-sm">
                                <span class="h-2 w-2 rounded-full bg-green-500"></span>
                                Secure Onboarding
                            </div>
                        </div>

                        <div class="mt-6 soft-divider"></div>

                        <div class="mt-6 grid grid-cols-3 gap-2">
                            <div id="pillStep1" class="step-pill active rounded-2xl border border-slate-200 bg-white px-3 py-3 text-center transition">
                                <div class="text-[11px] sm:text-xs text-slate-500">Step 1</div>
                                <div class="mt-0.5 text-xs sm:text-sm font-black">User Details</div>
                            </div>

                            <div id="pillStep2" class="step-pill rounded-2xl border border-slate-200 bg-white px-3 py-3 text-center transition">
                                <div class="text-[11px] sm:text-xs text-slate-500">Step 2</div>
                                <div class="mt-0.5 text-xs sm:text-sm font-black">Business Info</div>
                            </div>

                            <div id="pillStep3" class="step-pill rounded-2xl border border-slate-200 bg-white px-3 py-3 text-center transition">
                                <div class="text-[11px] sm:text-xs text-slate-500">Step 3</div>
                                <div class="mt-0.5 text-xs sm:text-sm font-black">Billing Setup</div>
                            </div>
                        </div>

                        <div class="mt-6">
                            <div class="flex items-center justify-between text-xs mb-2">
                                <span class="text-slate-500">Step <span id="currentStepText">1</span> of 3</span>
                                <span class="text-blue-600 font-black" id="currentStepLabel">User Details</span>
                            </div>

                            <div class="h-2 w-full rounded-full bg-slate-200 overflow-hidden">
                                <div id="progressBar"
                                     class="h-full rounded-full bg-blue-600 transition-all duration-300"
                                     style="width: 33.33%;"></div>
                            </div>
                        </div>
                    </div>

                    <div class="px-5 sm:px-8 py-6 sm:py-8">
                        @if (session('success'))
                            <div class="mb-5 rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                                <ul class="list-disc ml-5 space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if(!empty($selectedPlan))
                            <div class="mb-5 rounded-2xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-700">
                                Selected Plan: <strong>{{ $selectedPlan->name }}</strong> -
                                ₹{{ number_format($selectedPlan->price, 0) }}
                            </div>
                        @endif

                        @php
                            $step1Fields = ['name', 'phone', 'password', 'password_confirmation'];
                            $step2Fields = ['business_name', 'mobile', 'gstin', 'business_type_id', 'address', 'state', 'state_code'];
                            $step3Fields = ['gst_enabled', 'invoice_base_prefix', 'rounding_mode', 'rounding_step', 'terms'];

                            $initialStep = old('current_step', 1);

                            if ($errors->hasAny($step1Fields)) {
                                $initialStep = 1;
                            } elseif ($errors->hasAny($step2Fields)) {
                                $initialStep = 2;
                            } elseif ($errors->hasAny($step3Fields)) {
                                $initialStep = 3;
                            }
                        @endphp

                        <form id="multiStepForm" action="{{ route('register.store1') }}" method="POST" class="space-y-6" novalidate>
                            @csrf

                            <input type="hidden" name="payment_done" value="{{ request('payment_done', 0) }}">
                            <input type="hidden" name="plan_id" value="{{ old('plan_id', request('plan_id')) }}">
                            <input type="hidden" name="trial" value="{{ old('trial', request('trial', 0)) }}">
                            <input type="hidden" name="current_step" id="current_step" value="{{ $initialStep }}">

                            {{-- STEP 1 --}}
                            <div class="form-step space-y-5" data-step="1">
                                <div class="rounded-2xl border border-blue-100 bg-blue-50 px-4 py-4">
                                    <div class="text-sm font-black text-slate-900">Owner account details</div>
                                    <div class="mt-1 text-xs text-slate-500">
                                        Owner name, phone OTP aur password se account create karein.
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-2">Full Name</label>
                                    <input type="text" name="name"
                                           value="{{ old('name', session('paid_name')) }}"
                                           required
                                           class="field-focus w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none placeholder:text-slate-400 focus:border-blue-500"
                                           placeholder="Enter your full name">
                                </div>

                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-2">Owner Phone Number</label>

                                    <div class="flex flex-col sm:flex-row gap-3">
                                        <input type="text" name="phone" id="owner_phone"
                                               value="{{ old('phone', session('register_phone_verified')) }}"
                                               required
                                               maxlength="10"
                                               inputmode="numeric"
                                               class="field-focus w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none placeholder:text-slate-400 focus:border-blue-500"
                                               placeholder="Enter owner phone number">

                                        @if(!session('register_phone_verified'))
                                            <button type="button" id="sendOtpBtn"
                                                    class="shrink-0 rounded-2xl bg-yellow-400 px-5 py-3 text-sm font-black text-slate-950 hover:bg-yellow-300 transition">
                                                Send OTP
                                            </button>
                                        @else
                                            <div class="rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 font-bold">
                                                Phone already verified.
                                            </div>
                                        @endif
                                    </div>

                                    <p id="otpStatus" class="mt-2 text-xs text-slate-500"></p>
                                </div>

                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-2">Phone OTP</label>

                                    <div class="flex flex-col sm:flex-row gap-3">
                                        <input type="text" id="phoneOtp"
                                               maxlength="6"
                                               inputmode="numeric"
                                               class="field-focus w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none placeholder:text-slate-400 focus:border-blue-500"
                                               placeholder="Enter 6 digit OTP">

                                        <button type="button" id="verifyOtpBtn"
                                                class="shrink-0 rounded-2xl bg-slate-950 px-5 py-3 text-sm font-black text-white hover:bg-slate-800 transition">
                                            Verify OTP
                                        </button>
                                    </div>

                                    <input type="hidden"
                                           id="phoneVerified"
                                           value="{{ session('register_phone_verified') && session('register_phone_verified') == old('phone', session('register_phone_verified')) ? 1 : 0 }}">

                                    <p id="verifyOtpStatus" class="mt-2 text-xs text-slate-500"></p>
                                </div>

                                <div class="grid sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-bold text-slate-700 mb-2">Password</label>
                                        <input type="password" name="password"
                                               class="field-focus w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none placeholder:text-slate-400 focus:border-blue-500"
                                               placeholder="Create a password">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-bold text-slate-700 mb-2">Confirm Password</label>
                                        <input type="password" name="password_confirmation"
                                               class="field-focus w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none placeholder:text-slate-400 focus:border-blue-500"
                                               placeholder="Confirm your password">
                                    </div>
                                </div>

                                <div class="rounded-2xl border border-blue-100 bg-blue-50 px-4 py-3 text-xs text-blue-700">
                                    Phone OTP verification is required before moving to the next step.
                                </div>
                            </div>

                            {{-- STEP 2 --}}
                            <div class="form-step hidden space-y-5" data-step="2">
                                <div class="rounded-2xl border border-blue-100 bg-blue-50 px-4 py-4">
                                    <div class="text-sm font-black text-slate-900">Business information</div>
                                    <div class="mt-1 text-xs text-slate-500">
                                        Business mobile verified owner phone se auto fill hoga.
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-2">Business / Showroom Name</label>
                                    <input type="text" name="business_name" value="{{ old('business_name') }}" required
                                           class="field-focus w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none placeholder:text-slate-400 focus:border-blue-500"
                                           placeholder="Enter showroom name">
                                </div>

                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-2">Business Mobile Number</label>
                                    <input type="text" name="mobile" id="business_mobile"
                                           value="{{ old('mobile', old('phone', session('register_phone_verified'))) }}"
                                           required
                                           maxlength="10"
                                           inputmode="numeric"
                                           readonly
                                           class="field-focus w-full rounded-2xl border border-slate-300 bg-slate-100 px-4 py-3 text-sm outline-none placeholder:text-slate-400 focus:border-blue-500 cursor-not-allowed"
                                           placeholder="Verified phone number auto fill hoga">

                                    <p class="mt-2 text-xs text-slate-500">
                                        Ye number owner OTP verify hone ke baad automatic fill hoga.
                                    </p>
                                </div>

                                <div class="grid sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-bold text-slate-700 mb-2">GSTIN</label>
                                        <input type="text" name="gstin" id="gstin" value="{{ old('gstin') }}"
                                               class="field-focus w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm uppercase outline-none placeholder:text-slate-400 focus:border-blue-500"
                                               placeholder="Optional GSTIN">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-bold text-slate-700 mb-2">Business Type</label>

                                        <select name="business_type_id" required
                                                class="field-focus w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-700 outline-none focus:border-blue-500">
                                            <option value="">-- Select Business Type --</option>

                                            @foreach($businessTypes as $businessType)
                                                <option value="{{ $businessType->id }}"
                                                    {{ old('business_type_id') == $businessType->id ? 'selected' : '' }}>
                                                    {{ $businessType->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-2">Address</label>
                                    <textarea name="address" rows="4"
                                              class="field-focus w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none placeholder:text-slate-400 focus:border-blue-500"
                                              placeholder="Enter business address">{{ old('address') }}</textarea>
                                </div>

                                @php
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
                                        ['code'=>'26','name'=>'Dadra and Nagar Haveli and Daman and Diu'],
                                        ['code'=>'27','name'=>'Maharashtra'],
                                        ['code'=>'29','name'=>'Karnataka'],
                                        ['code'=>'30','name'=>'Goa'],
                                        ['code'=>'31','name'=>'Lakshadweep'],
                                        ['code'=>'32','name'=>'Kerala'],
                                        ['code'=>'33','name'=>'Tamil Nadu'],
                                        ['code'=>'34','name'=>'Puducherry'],
                                        ['code'=>'35','name'=>'Andaman and Nicobar Islands'],
                                        ['code'=>'36','name'=>'Telangana'],
                                        ['code'=>'37','name'=>'Andhra Pradesh'],
                                        ['code'=>'38','name'=>'Ladakh'],
                                    ];

                                    $selectedStateValue = old('state_code') && old('state')
                                        ? old('state_code') . ',' . old('state')
                                        : '';
                                @endphp

                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-2">State (GST Code)</label>

                                    <select id="state_select" required
                                            class="field-focus w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-700 outline-none focus:border-blue-500">
                                        <option value="">-- Select State --</option>

                                        @foreach($states as $st)
                                            @php $value = $st['code'] . ',' . $st['name']; @endphp
                                            <option value="{{ $value }}" {{ $selectedStateValue === $value ? 'selected' : '' }}>
                                                {{ $st['name'] }} ({{ $st['code'] }})
                                            </option>
                                        @endforeach
                                    </select>

                                    <input type="hidden" name="state" id="state" value="{{ old('state') }}">
                                    <input type="hidden" name="state_code" id="state_code" value="{{ old('state_code') }}">
                                </div>
                            </div>

                            {{-- STEP 3 --}}
                            <div class="form-step hidden space-y-5" data-step="3">
                                <div class="rounded-2xl border border-blue-100 bg-blue-50 px-4 py-4">
                                    <div class="text-sm font-black text-slate-900">Billing preferences</div>
                                    <div class="mt-1 text-xs text-slate-500">
                                        Choose your default invoice and rounding settings.
                                    </div>
                                </div>

                                <div class="grid sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-bold text-slate-700 mb-2">GST Enabled</label>
                                        <select name="gst_enabled"
                                                class="field-focus w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none focus:border-blue-500">
                                            <option value="1" {{ old('gst_enabled', '1') == '1' ? 'selected' : '' }}>Yes</option>
                                            <option value="0" {{ old('gst_enabled') == '0' ? 'selected' : '' }}>No</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-bold text-slate-700 mb-2">Invoice Base Prefix</label>
                                        <input type="text" name="invoice_base_prefix"
                                               value="{{ old('invoice_base_prefix', 'RV/SL') }}"
                                               class="field-focus w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none placeholder:text-slate-400 focus:border-blue-500"
                                               placeholder="RV/SL">
                                    </div>
                                </div>

                                <div class="grid sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-bold text-slate-700 mb-2">Rounding Mode</label>
                                        <select name="rounding_mode"
                                                class="field-focus w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none focus:border-blue-500">
                                            <option value="none" {{ old('rounding_mode') == 'none' ? 'selected' : '' }}>None</option>
                                            <option value="nearest" {{ old('rounding_mode', 'nearest') == 'nearest' ? 'selected' : '' }}>Nearest</option>
                                            <option value="up" {{ old('rounding_mode') == 'up' ? 'selected' : '' }}>Up</option>
                                            <option value="down" {{ old('rounding_mode') == 'down' ? 'selected' : '' }}>Down</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-bold text-slate-700 mb-2">Rounding Step</label>
                                        <input type="number" step="0.01" name="rounding_step"
                                               value="{{ old('rounding_step', '1.00') }}"
                                               class="field-focus w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none placeholder:text-slate-400 focus:border-blue-500"
                                               placeholder="1.00">
                                    </div>
                                </div>

                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                    <div class="text-sm font-black text-slate-900">Before submitting</div>
                                    <ul class="mt-2 space-y-1.5 text-xs text-slate-600">
                                        <li>• Verify your phone OTP</li>
                                        <li>• Check showroom details carefully</li>
                                        <li>• Billing settings can be updated later</li>
                                    </ul>
                                </div>

                                <div class="flex items-start gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-4">
                                    <input id="terms" type="checkbox" name="terms" value="1" required
                                           {{ old('terms') ? 'checked' : '' }}
                                           class="mt-1 rounded border-slate-300 bg-white text-blue-600 focus:ring-blue-500">

                                    <label for="terms" class="text-xs sm:text-sm text-slate-600 leading-6">
                                        I agree to the
                                        <a href="{{ route('terms-conditions')}}" class="font-bold text-slate-900 hover:text-blue-600">Terms & Conditions</a>
                                        and
                                        <a href="{{ route('privacy-policy') }}" class="font-bold text-slate-900 hover:text-blue-600">Privacy Policy</a>.
                                    </label>
                                </div>
                            </div>

                            <div class="pt-2">
                                <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-3">
                                    <button type="button" id="prevBtn"
                                            class="hidden rounded-full border border-slate-300 bg-white px-6 py-3 text-sm font-bold text-slate-700 hover:border-blue-500 transition">
                                        Back
                                    </button>

                                    <div class="sm:ml-auto flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                                        <button type="button" id="nextBtn"
                                                class="w-full sm:w-auto rounded-full bg-blue-600 px-8 py-4 text-sm font-black text-white shadow-lg shadow-blue-200 hover:bg-blue-700 transition">
                                            Continue
                                        </button>

                                        <button type="submit" id="submitBtn"
                                                class="hidden w-full sm:w-auto rounded-full bg-slate-950 px-8 py-4 text-sm font-black text-white hover:bg-slate-800 transition">
                                            Create Account
                                        </button>
                                    </div>
                                </div>

                                <p class="text-sm text-slate-500 text-center pt-5">
                                    Already have an account?
                                    <a href="{{ route('login') }}" class="font-black text-blue-600 hover:text-blue-700">
                                        Login here
                                    </a>
                                </p>
                            </div>
                        </form>
                    </div>
                </section>

                <div class="mt-5 text-center text-xs text-slate-500">
                    Need help? Contact support:
                    <a href="https://wa.me/917753800444" target="_blank" class="font-black text-blue-600 hover:text-blue-700">
                        +91-7753800444
                    </a>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    const steps = document.querySelectorAll('.form-step');
    const nextBtn = document.getElementById('nextBtn');
    const prevBtn = document.getElementById('prevBtn');
    const submitBtn = document.getElementById('submitBtn');
    const progressBar = document.getElementById('progressBar');
    const currentStepText = document.getElementById('currentStepText');
    const currentStepLabel = document.getElementById('currentStepLabel');
    const currentStepInput = document.getElementById('current_step');

    const pillStep1 = document.getElementById('pillStep1');
    const pillStep2 = document.getElementById('pillStep2');
    const pillStep3 = document.getElementById('pillStep3');

    const sendOtpBtn = document.getElementById('sendOtpBtn');
    const verifyOtpBtn = document.getElementById('verifyOtpBtn');

    const ownerPhoneInput = document.getElementById('owner_phone');
    const businessMobileInput = document.getElementById('business_mobile');

    const phoneOtpInput = document.getElementById('phoneOtp');
    const phoneVerifiedInput = document.getElementById('phoneVerified');

    const otpStatus = document.getElementById('otpStatus');
    const verifyOtpStatus = document.getElementById('verifyOtpStatus');

    const form = document.getElementById('multiStepForm');

    const gstinInput = document.getElementById('gstin');
    const stateSelect = document.getElementById('state_select');
    const stateInput = document.getElementById('state');
    const stateCodeInput = document.getElementById('state_code');

    const labels = ['User Details', 'Business Details', 'Billing Setup'];
    const pills = [pillStep1, pillStep2, pillStep3];

    let currentStep = 0;
    let isSubmitting = false;

    const gstStateMap = {
        '01': 'Jammu and Kashmir',
        '02': 'Himachal Pradesh',
        '03': 'Punjab',
        '04': 'Chandigarh',
        '05': 'Uttarakhand',
        '06': 'Haryana',
        '07': 'Delhi',
        '08': 'Rajasthan',
        '09': 'Uttar Pradesh',
        '10': 'Bihar',
        '11': 'Sikkim',
        '12': 'Arunachal Pradesh',
        '13': 'Nagaland',
        '14': 'Manipur',
        '15': 'Mizoram',
        '16': 'Tripura',
        '17': 'Meghalaya',
        '18': 'Assam',
        '19': 'West Bengal',
        '20': 'Jharkhand',
        '21': 'Odisha',
        '22': 'Chhattisgarh',
        '23': 'Madhya Pradesh',
        '24': 'Gujarat',
        '26': 'Dadra and Nagar Haveli and Daman and Diu',
        '27': 'Maharashtra',
        '29': 'Karnataka',
        '30': 'Goa',
        '31': 'Lakshadweep',
        '32': 'Kerala',
        '33': 'Tamil Nadu',
        '34': 'Puducherry',
        '35': 'Andaman and Nicobar Islands',
        '36': 'Telangana',
        '37': 'Andhra Pradesh',
        '38': 'Ladakh',
    };

    function updateStateFields() {
        if (!stateSelect || !stateInput || !stateCodeInput) return;

        const value = stateSelect.value;

        if (!value) {
            stateInput.value = '';
            stateCodeInput.value = '';
            return;
        }

        const parts = value.split(',');

        stateCodeInput.value = parts[0] || '';
        stateInput.value = parts.slice(1).join(',') || '';
    }

    function updateStateFromGstin() {
        if (!gstinInput || !stateSelect || !stateInput || !stateCodeInput) return;

        const gstin = gstinInput.value.trim().toUpperCase();
        gstinInput.value = gstin;

        if (gstin.length < 2) return;

        const code = gstin.substring(0, 2);
        const stateName = gstStateMap[code];

        if (!stateName) return;

        stateCodeInput.value = code;
        stateInput.value = stateName;
        stateSelect.value = code + ',' + stateName;
    }

    function syncBusinessMobileFromOwner() {
        if (!ownerPhoneInput || !businessMobileInput) return;

        const phone = ownerPhoneInput.value.trim();

        if (phoneVerifiedInput && phoneVerifiedInput.value === '1' && phone.length === 10) {
            businessMobileInput.value = phone;
        }
    }

    function getInitialStep() {
        const oldStep = parseInt(currentStepInput?.value || '1', 10);

        if (!isNaN(oldStep) && oldStep >= 1 && oldStep <= steps.length) {
            return oldStep - 1;
        }

        return 0;
    }

    function updateStepPills(index) {
        pills.forEach((pill, i) => {
            if (!pill) return;

            pill.classList.remove('active', 'done');

            if (i < index) {
                pill.classList.add('done');
            } else if (i === index) {
                pill.classList.add('active');
            }
        });
    }

    function updateStepMeta(index) {
        if (currentStepText) currentStepText.textContent = index + 1;
        if (currentStepLabel) currentStepLabel.textContent = labels[index];
        if (progressBar) progressBar.style.width = (((index + 1) / steps.length) * 100) + '%';

        if (prevBtn) prevBtn.classList.toggle('hidden', index === 0);
        if (nextBtn) nextBtn.classList.toggle('hidden', index === steps.length - 1);
        if (submitBtn) submitBtn.classList.toggle('hidden', index !== steps.length - 1);

        if (currentStepInput) {
            currentStepInput.value = index + 1;
        }

        updateStepPills(index);
    }

    function showStep(index, shouldScroll = true) {
        currentStep = index;

        steps.forEach((step, i) => {
            step.classList.toggle('hidden', i !== index);
        });

        syncBusinessMobileFromOwner();
        updateStepMeta(index);

        if (shouldScroll) {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    }

    function setFieldErrorState(input, hasError) {
        if (!input) return;

        input.classList.remove(
            'border-red-500',
            'focus:border-red-500',
            'ring-1',
            'ring-red-200'
        );

        if (hasError) {
            input.classList.add(
                'border-red-500',
                'focus:border-red-500',
                'ring-1',
                'ring-red-200'
            );
        }
    }

    function clearStepErrors(index) {
        if (!steps[index]) return;

        const currentInputs = steps[index].querySelectorAll('input, select, textarea');

        currentInputs.forEach(input => {
            setFieldErrorState(input, false);
            input.setCustomValidity('');
        });
    }

    function showError(input, message) {
        if (!input) return false;

        input.setCustomValidity(message);
        setFieldErrorState(input, true);

        const stepBox = input.closest('.form-step');

        if (stepBox) {
            const stepIndex = Array.from(steps).indexOf(stepBox);

            if (stepIndex !== -1 && stepIndex !== currentStep) {
                showStep(stepIndex);
            }
        }

        setTimeout(() => {
            input.reportValidity();
            input.focus();
        }, 150);

        return false;
    }

    function validateStep(index) {
        clearStepErrors(index);

        updateStateFromGstin();
        updateStateFields();
        syncBusinessMobileFromOwner();

        const currentInputs = steps[index].querySelectorAll('input, select, textarea');

        for (let input of currentInputs) {
            if (
                input.type === 'hidden' ||
                input.type === 'button' ||
                input.type === 'submit' ||
                input.id === 'phoneOtp'
            ) {
                continue;
            }

            input.setCustomValidity('');

            if (input.name === 'password' || input.name === 'password_confirmation') {
                continue;
            }

            if (input.required) {
                if (input.type === 'checkbox' && !input.checked) {
                    return showError(input, 'Ye field required hai.');
                }

                if (input.type !== 'checkbox' && !input.value.trim()) {
                    return showError(input, 'Ye field required hai.');
                }
            }

            if (!input.checkValidity()) {
                return showError(input, 'Valid value daliyega.');
            }
        }

        const password = document.querySelector('input[name="password"]');
        const confirmPassword = document.querySelector('input[name="password_confirmation"]');

        if (index === 0 && password && confirmPassword) {
            password.setCustomValidity('');
            confirmPassword.setCustomValidity('');

            if (!password.value.trim()) {
                return showError(password, 'Password required hai.');
            }

            if (!confirmPassword.value.trim()) {
                return showError(confirmPassword, 'Confirm password required hai.');
            }

            if (password.value.length < 6) {
                return showError(password, 'Password minimum 6 characters hona chahiye.');
            }

            if (password.value !== confirmPassword.value) {
                return showError(confirmPassword, 'Passwords match nahi ho rahe.');
            }
        }

        if (index === 0 && phoneVerifiedInput && phoneVerifiedInput.value !== '1') {
            verifyOtpStatus.textContent = 'Pehle phone OTP verify kijiye.';
            verifyOtpStatus.className = 'mt-2 text-xs text-red-500';

            setFieldErrorState(phoneOtpInput, true);
            phoneOtpInput.focus();

            return false;
        }

        if (index === 1) {
            const ownerPhone = ownerPhoneInput.value.trim();
            const businessPhone = businessMobileInput.value.trim();

            if (phoneVerifiedInput.value !== '1') {
                showStep(0);
                verifyOtpStatus.textContent = 'Pehle phone OTP verify kijiye.';
                verifyOtpStatus.className = 'mt-2 text-xs text-red-500';
                phoneOtpInput.focus();
                return false;
            }

            if (businessPhone !== ownerPhone) {
                businessMobileInput.value = ownerPhone;
            }

            if (businessMobileInput.value.length !== 10) {
                return showError(businessMobileInput, 'Business mobile required hai.');
            }
        }

        return true;
    }

    function validateAllStepsBeforeSubmit() {
        for (let i = 0; i < steps.length; i++) {
            if (!validateStep(i)) {
                showStep(i);
                return false;
            }
        }

        return true;
    }

    pills.forEach((pill, index) => {
        if (!pill) return;

        pill.style.cursor = 'pointer';

        pill.addEventListener('click', function () {
            if (index > currentStep) {
                if (!validateStep(currentStep)) return;
            }

            showStep(index);
        });
    });

    function bindFieldListeners() {
        document.querySelectorAll('input, select, textarea').forEach(input => {
            input.addEventListener('input', function () {
                setFieldErrorState(this, false);
                this.setCustomValidity('');
            });

            input.addEventListener('change', function () {
                setFieldErrorState(this, false);
                this.setCustomValidity('');
            });
        });
    }

    async function sendOtp() {
        const phone = ownerPhoneInput.value.trim();

        if (!phone || phone.length !== 10) {
            otpStatus.textContent = 'Valid 10 digit phone number daliyega.';
            otpStatus.className = 'mt-2 text-xs text-red-500';
            setFieldErrorState(ownerPhoneInput, true);
            ownerPhoneInput.focus();
            return;
        }

        otpStatus.textContent = 'OTP bheja ja raha hai...';
        otpStatus.className = 'mt-2 text-xs text-amber-600';

        if (sendOtpBtn) {
            sendOtpBtn.disabled = true;
            sendOtpBtn.textContent = 'Sending...';
        }

        try {
            const response = await fetch("{{ route('register.sendOtp') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': "{{ csrf_token() }}",
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ phone })
            });

            const data = await response.json();

            if (!response.ok) {
                throw data;
            }

            if (phoneVerifiedInput) {
                phoneVerifiedInput.value = '0';
            }

            if (businessMobileInput) {
                businessMobileInput.value = '';
            }

            verifyOtpStatus.textContent = '';
            otpStatus.textContent = data.message || 'OTP phone number par bhej diya gaya hai.';
            otpStatus.className = 'mt-2 text-xs text-green-600';

            setFieldErrorState(ownerPhoneInput, false);
            setFieldErrorState(phoneOtpInput, false);
        } catch (error) {
            otpStatus.textContent = error?.message || 'OTP bhejne me problem aayi.';
            otpStatus.className = 'mt-2 text-xs text-red-500';
        } finally {
            if (sendOtpBtn) {
                sendOtpBtn.disabled = false;
                sendOtpBtn.textContent = 'Send OTP';
            }
        }
    }

    async function verifyOtp() {
        const phone = ownerPhoneInput.value.trim();
        const otp = phoneOtpInput.value.trim();

        if (!phone || phone.length !== 10) {
            verifyOtpStatus.textContent = 'Valid 10 digit phone number daliyega.';
            verifyOtpStatus.className = 'mt-2 text-xs text-red-500';
            setFieldErrorState(ownerPhoneInput, true);
            ownerPhoneInput.focus();
            return;
        }

        if (!otp || otp.length !== 6) {
            verifyOtpStatus.textContent = 'Valid 6 digit OTP daliyega.';
            verifyOtpStatus.className = 'mt-2 text-xs text-red-500';
            setFieldErrorState(phoneOtpInput, true);
            phoneOtpInput.focus();
            return;
        }

        verifyOtpStatus.textContent = 'OTP verify ho raha hai...';
        verifyOtpStatus.className = 'mt-2 text-xs text-blue-600';

        if (verifyOtpBtn) {
            verifyOtpBtn.disabled = true;
            verifyOtpBtn.textContent = 'Verifying...';
        }

        try {
            const response = await fetch("{{ route('register.verifyOtp') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': "{{ csrf_token() }}",
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ phone, otp })
            });

            const data = await response.json();

            if (!response.ok) {
                throw data;
            }

            if (phoneVerifiedInput) {
                phoneVerifiedInput.value = '1';
            }

            if (businessMobileInput) {
                businessMobileInput.value = phone;
            }

            verifyOtpStatus.textContent = data.message || 'Phone OTP verify ho gaya. Business mobile auto fill kar diya gaya.';
            verifyOtpStatus.className = 'mt-2 text-xs text-green-600';

            setFieldErrorState(phoneOtpInput, false);
            setFieldErrorState(ownerPhoneInput, false);
            setFieldErrorState(businessMobileInput, false);
        } catch (error) {
            if (phoneVerifiedInput) {
                phoneVerifiedInput.value = '0';
            }

            if (businessMobileInput) {
                businessMobileInput.value = '';
            }

            verifyOtpStatus.textContent = error?.message || 'OTP verify nahi hua.';
            verifyOtpStatus.className = 'mt-2 text-xs text-red-500';

            setFieldErrorState(phoneOtpInput, true);
        } finally {
            if (verifyOtpBtn) {
                verifyOtpBtn.disabled = false;
                verifyOtpBtn.textContent = 'Verify OTP';
            }
        }
    }

    if (stateSelect) {
        stateSelect.addEventListener('change', updateStateFields);
        updateStateFields();
    }

    if (gstinInput) {
        gstinInput.addEventListener('input', updateStateFromGstin);
        gstinInput.addEventListener('blur', updateStateFromGstin);
        updateStateFromGstin();
    }

    if (sendOtpBtn) {
        sendOtpBtn.addEventListener('click', sendOtp);
    }

    if (verifyOtpBtn) {
        verifyOtpBtn.addEventListener('click', verifyOtp);
    }

    if (phoneOtpInput) {
        phoneOtpInput.addEventListener('input', function () {
            this.value = this.value.replace(/\D/g, '').slice(0, 6);

            verifyOtpStatus.textContent = '';
            setFieldErrorState(phoneOtpInput, false);
            phoneOtpInput.setCustomValidity('');
        });
    }

    if (ownerPhoneInput) {
        ownerPhoneInput.addEventListener('input', function () {
            this.value = this.value.replace(/\D/g, '').slice(0, 10);

            if (phoneVerifiedInput) {
                phoneVerifiedInput.value = '0';
            }

            if (businessMobileInput) {
                businessMobileInput.value = '';
            }

            otpStatus.textContent = '';
            verifyOtpStatus.textContent = '';

            setFieldErrorState(ownerPhoneInput, false);
            setFieldErrorState(phoneOtpInput, false);
            setFieldErrorState(businessMobileInput, false);

            ownerPhoneInput.setCustomValidity('');
            phoneOtpInput.setCustomValidity('');
        });
    }

    if (businessMobileInput) {
        businessMobileInput.addEventListener('input', function () {
            this.value = this.value.replace(/\D/g, '').slice(0, 10);
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', function () {
            if (!validateStep(currentStep)) return;

            if (currentStep < steps.length - 1) {
                showStep(currentStep + 1);
            }
        });
    }

    if (prevBtn) {
        prevBtn.addEventListener('click', function () {
            if (currentStep > 0) {
                showStep(currentStep - 1);
            }
        });
    }

    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            if (isSubmitting) return;

            updateStateFromGstin();
            updateStateFields();
            syncBusinessMobileFromOwner();

            if (!validateAllStepsBeforeSubmit()) {
                return;
            }

            if (currentStepInput) {
                currentStepInput.value = currentStep + 1;
            }

            isSubmitting = true;

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Creating...';
            }

            form.submit();
        });
    }

    bindFieldListeners();

    currentStep = getInitialStep();
    showStep(currentStep, false);
</script>

@endsection