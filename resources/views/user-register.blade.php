@extends('frontend.layout')

@section('content')

<style>
    .inline-registration-card {
        overflow: hidden;
        border: 1px solid #e2e8f0;
        border-radius: 28px;
        background: #ffffff;
        box-shadow: 0 24px 70px rgba(15, 23, 42, 0.14);
    }

    .inline-registration-header {
        padding: 22px 24px;
        color: #ffffff;
        background: linear-gradient(90deg, #2624cc, #6c63ff);
    }

    .registration-field {
        width: 100%;
        padding: 14px 15px;
        border: 1px solid #d8dce8;
        border-radius: 15px;
        color: #1e293b;
        background: #ffffff;
        outline: none;
        transition: border-color .2s ease, box-shadow .2s ease, background .2s ease;
    }

    .registration-field:focus {
        border-color: #2624cc;
        box-shadow: 0 0 0 4px rgba(38, 36, 204, .10);
    }

    .registration-field[readonly],
    .registration-field:disabled {
        background: #f1f5f9;
        cursor: not-allowed;
    }

    .step-pill {
        border: 1px solid #e2e8f0;
        color: #64748b;
        background: #ffffff;
    }

    .step-pill.active {
        border-color: #2624cc !important;
        color: #ffffff !important;
        background: linear-gradient(90deg, #2624cc, #6c63ff) !important;
        box-shadow: 0 10px 24px rgba(38, 36, 204, .18);
    }

    .step-pill.active > div,
    .step-pill.active * {
        color: #ffffff !important;
    }

    .step-pill.done {
        border-color: rgba(38, 36, 204, .25) !important;
        color: #2624cc !important;
        background: #f0efff !important;
    }

    .step-pill.done > div,
    .step-pill.done * {
        color: #2624cc !important;
    }

    .registration-primary-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 48px;
        padding: 12px 24px;
        border-radius: 15px;
        color: #ffffff;
        background: linear-gradient(90deg, #2624cc, #514ff0);
        font-size: 14px;
        font-weight: 900;
        transition: .2s ease;
    }

    .registration-primary-button:hover {
        transform: translateY(-1px);
        box-shadow: 0 14px 28px rgba(38, 36, 204, .22);
    }

    .registration-primary-button:disabled {
        opacity: .6;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    .registration-secondary-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 46px;
        padding: 11px 22px;
        border: 1px solid #d8dce8;
        border-radius: 15px;
        color: #475569;
        background: #ffffff;
        font-size: 14px;
        font-weight: 800;
    }

    .registration-skip-button {
        width: 100%;
        padding: 13px 18px;
        border: 1px dashed #cbd5e1;
        border-radius: 15px;
        color: #64748b;
        background: #f8fafc;
        font-size: 13px;
        font-weight: 800;
        transition: .2s ease;
    }

    .registration-skip-button:hover {
        border-color: #2624cc;
        color: #2624cc;
        background: #f5f4ff;
    }

    .registration-otp-loader {
        display: none;
        align-items: center;
        gap: 8px;
        margin-top: 10px;
        color: #2624cc;
        font-size: 12px;
        font-weight: 800;
    }

    .registration-otp-loader.show { display: flex; }

    .registration-spinner {
        width: 16px;
        height: 16px;
        border: 2px solid #d9d8ff;
        border-top-color: #2624cc;
        border-radius: 50%;
        animation: registrationSpin .7s linear infinite;
    }

    @keyframes registrationSpin {
        to { transform: rotate(360deg); }
    }

    @media (max-width: 640px) {
        .inline-registration-card { border-radius: 22px; }
        .inline-registration-header { padding: 20px 18px; }
    }
    .plan-select-card {
        position: relative;
        overflow: hidden;
        border: 2px solid #e2e8f0;
        border-radius: 24px;
        background: #ffffff;
        transition: transform .2s ease, border-color .2s ease, box-shadow .2s ease;
    }

    .plan-select-card:hover {
        transform: translateY(-3px);
        border-color: #a5b4fc;
        box-shadow: 0 18px 45px rgba(38, 36, 204, .12);
    }

    .plan-select-card.selected {
        border-color: #2624cc;
        box-shadow: 0 20px 50px rgba(38, 36, 204, .20);
    }

    .plan-select-card.selected::after {
        content: "Selected";
        position: absolute;
        top: 14px;
        right: 14px;
        padding: 6px 10px;
        border-radius: 999px;
        color: #ffffff;
        background: linear-gradient(90deg, #2624cc, #6c63ff);
        font-size: 11px;
        font-weight: 900;
    }

    .plan-select-button {
        display: inline-flex;
        width: 100%;
        min-height: 48px;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
        color: #ffffff;
        background: linear-gradient(90deg, #2624cc, #514ff0);
        font-size: 14px;
        font-weight: 900;
        transition: opacity .2s ease, transform .2s ease;
    }

    .plan-select-button:hover {
        opacity: .92;
        transform: translateY(-1px);
    }

</style>

<main class="hero-bg relative overflow-hidden">
    <div class="absolute inset-0 pointer-events-none">
        <div class="absolute top-20 left-10 h-72 w-72 rounded-full bg-blue-200/40 blur-3xl"></div>
        <div class="absolute top-28 right-10 h-72 w-72 rounded-full bg-yellow-200/40 blur-3xl"></div>
    </div>

    <div class="relative max-w-7xl mx-auto px-4 lg:px-8 py-10 lg:py-16">

        {{-- PLAN SELECTION --}}
        <section id="pricing" class="mb-10 lg:mb-14">
            <div class="text-center max-w-3xl mx-auto mb-8">
                <p class="text-[#2624cc] font-black uppercase tracking-widest text-sm">
                    Choose Your Plan
                </p>

                <h2 class="mt-3 text-3xl sm:text-4xl lg:text-5xl font-black text-slate-950">
                    Select a plan before registration
                </h2>

                <p class="mt-3 text-slate-600">
                    Apne business ke according plan select karein. Selected plan registration form ke saath submit hoga.
                </p>
            </div>

            @php
                $availablePlans = isset($plans) ? $plans : collect();
                $selectedPlanId = (string) old('plan_id', request('plan_id', optional($selectedPlan ?? null)->id));
            @endphp

            @if($availablePlans->count())
                <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($availablePlans as $plan)
                        @php
                            $isRecommended = (bool) ($plan->is_recommended ?? false);
                            $isSelected = $selectedPlanId === (string) $plan->id;
                        @endphp

                        <article
                            class="plan-select-card {{ $isSelected ? 'selected' : '' }} p-6 sm:p-7"
                            data-plan-card="{{ $plan->id }}"
                        >
                            @if($isRecommended)
                                <div class="mb-4 inline-flex rounded-full bg-indigo-50 px-3 py-1.5 text-xs font-black text-[#2624cc]">
                                    Most Recommended
                                </div>
                            @endif

                            <h3 class="text-2xl font-black uppercase text-slate-950">
                                {{ $plan->name }}
                            </h3>

                            <p class="mt-2 min-h-[42px] text-sm leading-6 text-slate-500">
                                {{ $plan->subtitle ?: ($plan->description ?: 'Perfect for growing businesses') }}
                            </p>

                            <div class="mt-5 flex items-end gap-2">
                                <span class="text-4xl font-black text-[#2624cc]">
                                    ₹{{ number_format($plan->price, 0) }}
                                </span>

                                <span class="pb-1 text-sm font-bold text-slate-500">
                                    /
                                    {{ ($plan->duration_days ?? 365) >= 365
                                        ? 'Year'
                                        : ($plan->duration_days ?? 0) . ' Days' }}
                                </span>
                            </div>

                            <ul class="mt-6 space-y-2.5 text-sm text-slate-600">
                                @forelse($plan->planFeatures ?? [] as $feature)
                                    <li class="flex gap-2">
                                        <span class="font-black text-green-600">✓</span>
                                        <span>{{ $feature->title }}</span>
                                    </li>
                                @empty
                                    <li class="flex gap-2">
                                        <span class="font-black text-green-600">✓</span>
                                        <span>GST Billing</span>
                                    </li>
                                    <li class="flex gap-2">
                                        <span class="font-black text-green-600">✓</span>
                                        <span>Stock Management</span>
                                    </li>
                                    <li class="flex gap-2">
                                        <span class="font-black text-green-600">✓</span>
                                        <span>Invoice Print and Share</span>
                                    </li>
                                @endforelse
                            </ul>

                            <button
                                type="button"
                                class="plan-select-button mt-7"
                                data-select-plan="{{ $plan->id }}"
                                data-plan-name="{{ $plan->name }}"
                                data-plan-price="{{ number_format($plan->price, 0) }}"
                            >
                                {{ $isSelected ? 'Selected Plan' : 'Select This Plan' }}
                            </button>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="rounded-3xl border border-amber-200 bg-amber-50 px-6 py-8 text-center">
                    <h3 class="text-xl font-black text-slate-900">
                        Plans are not available
                    </h3>
                    <p class="mt-2 text-sm text-slate-600">
                        Controller se <code class="font-bold">$plans</code> variable pass hona chahiye.
                    </p>
                </div>
            @endif
        </section>

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
                <section id="registrationFormCard" class="inline-registration-card">
                    <div class="inline-registration-header">
                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                            <div>
                                <h2 class="text-2xl sm:text-3xl font-black tracking-tight text-white">
                                    Start MyVictory Billing
                                </h2>
                                <p class="mt-2 text-sm text-blue-100">
                                    Complete 3 simple steps to start using MyVictory Billing.
                                </p>
                            </div>

                            <div class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/15 px-4 py-2 text-xs font-bold text-white shadow-sm">
                                <span class="h-2 w-2 rounded-full bg-green-500"></span>
                                Secure Onboarding
                            </div>
                        </div>

                        <div class="mt-6 border-t border-white/20"></div>

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
                                <span class="text-blue-100">Step <span id="currentStepText">1</span> of 3</span>
                                <span class="text-white font-black" id="currentStepLabel">User Details</span>
                            </div>

                            <div class="h-2 w-full rounded-full bg-white/25 overflow-hidden">
                                <div id="progressBar"
                                     class="h-full rounded-full bg-white transition-all duration-300"
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
                            $step1Fields = ['name', 'phone'];
                            $step2Fields = ['business_name', 'business_email', 'mobile', 'gstin', 'business_type_id', 'address', 'state', 'state_code'];
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

                        <div id="selectedPlanSummary"
                             class="{{ $selectedPlanId ? '' : 'hidden' }} mb-5 rounded-2xl border border-indigo-200 bg-indigo-50 px-4 py-3 text-sm text-indigo-800">
                            Selected Plan:
                            <strong id="selectedPlanName">
                                {{ optional($availablePlans->firstWhere('id', (int) $selectedPlanId))->name }}
                            </strong>
                        </div>

                        <form id="multiStepForm" action="{{ route('register.store1') }}" method="POST" class="space-y-6" novalidate>
                            @csrf

                            <input type="hidden" name="payment_done" value="{{ request('payment_done', 0) }}">
                            <input type="hidden" name="plan_id" id="selectedPlanInput" value="{{ old('plan_id', request('plan_id', optional($selectedPlan ?? null)->id)) }}">
                            <input type="hidden" name="trial" value="{{ old('trial', request('trial', 0)) }}">
                            <input type="hidden" name="current_step" id="current_step" value="{{ $initialStep }}">
                            <input type="hidden" name="business_skipped" id="business_skipped" value="{{ old('business_skipped', 0) }}">
                            <input type="hidden" name="billing_skipped" id="billing_skipped" value="{{ old('billing_skipped', 0) }}">

                            {{-- STEP 1 --}}
                            <div class="form-step space-y-5" data-step="1">
                                <div class="rounded-2xl border border-indigo-100 bg-indigo-50 px-4 py-4">
                                    <div class="text-sm font-black text-slate-900">Owner account details</div>
                                    <div class="mt-1 text-xs text-slate-500">
                                        Owner name aur phone OTP se account create karein.
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-2">Full Name</label>
                                    <input type="text" name="name"
                                           value="{{ old('name', session('paid_name')) }}"
                                           required
                                           class="registration-field text-sm placeholder:text-slate-400"
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
                                               class="registration-field text-sm placeholder:text-slate-400"
                                               placeholder="Enter owner phone number">

                                        @if(!session('register_phone_verified'))
                                            <button type="button" id="sendOtpBtn"
                                                    class="registration-primary-button shrink-0">
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
                                               class="registration-field text-center text-xl font-black tracking-[0.35em]"
                                               placeholder="••••••">

                                        

                                        <button type="button" id="verifyOtpBtn" class="hidden">Verify OTP</button>
                                    </div>

                                    <input type="hidden"
                                           id="phoneVerified"
                                           value="{{ session('register_phone_verified') && session('register_phone_verified') == old('phone', session('register_phone_verified')) ? 1 : 0 }}">

                                    <p id="verifyOtpStatus" class="mt-2 text-xs text-slate-500"></p>
                                </div>

                                <div class="rounded-2xl border border-indigo-100 bg-indigo-50 px-4 py-3 text-xs text-blue-700">
                                    Phone OTP verification is required before moving to the next step.
                                </div>
                            </div>

                            {{-- STEP 2 --}}
                            <div class="form-step hidden space-y-5" data-step="2">
                                <div class="rounded-2xl border border-indigo-100 bg-indigo-50 px-4 py-4">
                                    <div class="text-sm font-black text-slate-900">Business information</div>
                                    <div class="mt-1 text-xs text-slate-500">
                                        Business mobile verified owner phone se auto fill hoga.
                                    </div>
                                </div>

                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-4">
                                    <div>
                                        <div class="text-sm font-black text-slate-900">Business details abhi nahi dena chahte?</div>
                                        <div class="mt-1 text-xs text-slate-600">Aap is step ko skip karke details baad me profile se add kar sakte hain.</div>
                                    </div>

                                    <button type="button" id="skipBusinessBtn"
                                            class="registration-secondary-button shrink-0">
                                        Skip for now
                                    </button>
                                </div>

                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-2">Business / Showroom Name <span class="text-xs font-normal text-slate-400">(Optional)</span></label>
                                    <input type="text" name="business_name" value="{{ old('business_name') }}"
                                           class="registration-field text-sm placeholder:text-slate-400"
                                           placeholder="Enter showroom name">
                                </div>

                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-2">
                                        Business Email <span class="text-xs font-normal text-slate-400">(Optional)</span>
                                    </label>

                                    <input type="email" name="business_email"
                                        value="{{ old('business_email') }}"
                                        class="registration-field text-sm placeholder:text-slate-400"
                                        placeholder="Enter business email address">
                                </div>

                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-2">Business Mobile Number</label>
                                    <input type="text" name="mobile" id="business_mobile"
                                           value="{{ old('mobile', old('phone', session('register_phone_verified'))) }}"
                                           required
                                           maxlength="10"
                                           inputmode="numeric"
                                           readonly
                                           class="registration-field text-sm placeholder:text-slate-400"
                                           placeholder="Verified phone number auto fill hoga">

                                    <p class="mt-2 text-xs text-slate-500">
                                        Ye number owner OTP verify hone ke baad automatic fill hoga.
                                    </p>
                                </div>

                                <div class="grid sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-bold text-slate-700 mb-2">GSTIN</label>
                                        <input type="text" name="gstin" id="gstin" value="{{ old('gstin') }}"
                                               class="registration-field text-sm uppercase placeholder:text-slate-400"
                                               placeholder="Optional GSTIN">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-bold text-slate-700 mb-2">Business Type <span class="text-xs font-normal text-slate-400">(Optional)</span></label>

                                        <select name="business_type_id"
                                                class="registration-field text-sm text-slate-700">
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
                                              class="registration-field text-sm placeholder:text-slate-400"
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
                                    <label class="block text-sm font-bold text-slate-700 mb-2">
                                        State (GST Code)
                                        <span id="stateOptionalText" class="text-xs font-normal text-slate-400">
                                            (Optional when GSTIN is not provided)
                                        </span>
                                    </label>

                                    <select id="state_select"
                                            class="registration-field text-sm text-slate-700">
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

                                <div class="pt-2">
                                    <button type="button" id="skipBusinessBottomBtn"
                                            class="registration-skip-button">
                                        Skip Business Details for Now
                                    </button>
                                </div>
                            </div>

                            {{-- STEP 3 --}}
                            <div class="form-step hidden space-y-5" data-step="3">
                                <div class="rounded-2xl border border-indigo-100 bg-indigo-50 px-4 py-4">
                                    <div class="text-sm font-black text-slate-900">Billing preferences</div>
                                    <div class="mt-1 text-xs text-slate-500">
                                        Choose your default invoice and rounding settings.
                                    </div>
                                </div>

                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-4">
                                    <div>
                                        <div class="text-sm font-black text-slate-900">Billing settings abhi set nahi karna chahte?</div>
                                        <div class="mt-1 text-xs text-slate-600">
                                            Default settings use hongi. Aap baad me settings page se update kar sakte hain.
                                        </div>
                                    </div>

                                    <button type="button" id="skipBillingBtn"
                                            class="registration-secondary-button shrink-0">
                                        Skip & Create Account
                                    </button>
                                </div>

                                <div class="grid sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-bold text-slate-700 mb-2">GST Enabled</label>
                                        @php
                                            $defaultGstEnabled = old('gst_enabled');

                                            if ($defaultGstEnabled === null) {
                                                $defaultGstEnabled = old('gstin') ? '1' : '0';
                                            }
                                        @endphp

                                        <select name="gst_enabled" id="gst_enabled"
                                                class="field-focus w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none focus:border-blue-500">
                                            <option value="1" {{ (string) $defaultGstEnabled === '1' ? 'selected' : '' }}>Yes</option>
                                            <option value="0" {{ (string) $defaultGstEnabled === '0' ? 'selected' : '' }}>No</option>
                                        </select>

                                        <p id="gstEnabledHelp" class="mt-2 text-xs text-slate-500">
                                            GSTIN blank hone par GST Enabled automatically No rahega.
                                        </p>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-bold text-slate-700 mb-2">Invoice Base Prefix</label>
                                        <input type="text" name="invoice_base_prefix"
                                               value="{{ old('invoice_base_prefix', 'RV/SL') }}"
                                               class="registration-field text-sm placeholder:text-slate-400"
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
                                               class="registration-field text-sm placeholder:text-slate-400"
                                               placeholder="1.00">
                                    </div>
                                </div>

                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                    <div class="text-sm font-black text-slate-900">Before submitting</div>
                                    <ul class="mt-2 space-y-1.5 text-xs text-slate-600">
                                        <li>• Verify your phone OTP</li>
                                        <li>• Business details optional hain aur baad me add ho sakti hain</li>
                                        <li>• Billing settings can be updated later</li>
                                    </ul>
                                </div>

                                <div class="pt-1">
                                    <button type="button" id="skipBillingBottomBtn"
                                            class="registration-skip-button">
                                        Skip Billing Preferences & Create Account
                                    </button>
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
                                            class="registration-secondary-button hidden">
                                        Back
                                    </button>

                                    <div class="sm:ml-auto flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                                        <button type="button" id="nextBtn"
                                                class="registration-primary-button w-full sm:w-auto">
                                            Continue
                                        </button>

                                        <button type="submit" id="submitBtn"
                                                class="registration-primary-button hidden w-full sm:w-auto">
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
    const skipBusinessBtn = document.getElementById('skipBusinessBtn');
    const skipBusinessBottomBtn = document.getElementById('skipBusinessBottomBtn');
    const businessSkippedInput = document.getElementById('business_skipped');

    const skipBillingBtn = document.getElementById('skipBillingBtn');
    const skipBillingBottomBtn = document.getElementById('skipBillingBottomBtn');
    const billingSkippedInput = document.getElementById('billing_skipped');

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
    const gstEnabledSelect = document.getElementById('gst_enabled');
    const gstEnabledHelp = document.getElementById('gstEnabledHelp');
    const stateOptionalText = document.getElementById('stateOptionalText');

    const labels = ['User Details', 'Business Details', 'Billing Setup'];
    const pills = [pillStep1, pillStep2, pillStep3];

    let currentStep = 0;
    let isSubmitting = false;
    let isVerifyingOtp = false;
    let lastAutoVerifiedOtp = '';

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

    function syncGstFields() {
        if (!gstinInput) return;

        const gstin = gstinInput.value.trim().toUpperCase();
        const hasGstin = gstin.length > 0;

        gstinInput.value = gstin;

        /*
        |--------------------------------------------------------------------------
        | GSTIN blank:
        | - State optional
        | - GST Enabled defaults to No
        |--------------------------------------------------------------------------
        */
        if (stateSelect) {
            stateSelect.required = hasGstin;
        }

        if (stateOptionalText) {
            stateOptionalText.textContent = hasGstin
                ? ''
                : '';

            stateOptionalText.className = hasGstin
                ? 'text-xs font-normal text-red-500'
                : 'text-xs font-normal text-slate-400';
        }

        if (gstEnabledSelect) {
            gstEnabledSelect.value = hasGstin ? '1' : '0';
        }

        if (gstEnabledHelp) {
            gstEnabledHelp.textContent = hasGstin
                ? ''
                : '';

            gstEnabledHelp.className = hasGstin
                ? 'mt-2 text-xs text-green-600'
                : 'mt-2 text-xs text-slate-500';
        }

        /*
        |--------------------------------------------------------------------------
        | GSTIN ke first 2 digits se State auto select
        |--------------------------------------------------------------------------
        */
        if (gstin.length < 2) {
            return;
        }

        const code = gstin.substring(0, 2);
        const stateName = gstStateMap[code];

        if (!stateName) {
            return;
        }

        if (stateCodeInput) stateCodeInput.value = code;
        if (stateInput) stateInput.value = stateName;
        if (stateSelect) stateSelect.value = code + ',' + stateName;
    }

    function updateStateFromGstin() {
        syncGstFields();
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
            document.getElementById('registrationFormCard')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
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

        if (index === 1 && businessSkippedInput?.value === '1') {
            return true;
        }

        if (index === 2 && billingSkippedInput?.value === '1') {
            return true;
        }

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

    function setBusinessFieldsDisabled(disabled) {
        if (!steps[1]) return;

        steps[1].querySelectorAll('input, select, textarea').forEach(input => {
            if (input.id === 'business_skipped') return;
            input.disabled = disabled;
            input.setCustomValidity('');
            setFieldErrorState(input, false);
        });
    }

    function skipBusinessStep() {
        if (businessSkippedInput) {
            businessSkippedInput.value = '1';
        }

        setBusinessFieldsDisabled(true);
        showStep(2);
    }

    function restoreBusinessStep() {
        if (businessSkippedInput?.value !== '1') {
            setBusinessFieldsDisabled(false);
        }
    }

    function setBillingFieldsDisabled(disabled) {
        if (!steps[2]) return;

        steps[2].querySelectorAll('input, select, textarea').forEach(input => {
            if (
                input.id === 'billing_skipped' ||
                input.id === 'terms'
            ) {
                return;
            }

            input.disabled = disabled;
            input.setCustomValidity('');
            setFieldErrorState(input, false);
        });
    }

    function applyDefaultBillingValues() {
        if (gstEnabledSelect) gstEnabledSelect.value = '0';

        const invoicePrefix = document.querySelector('[name="invoice_base_prefix"]');
        const roundingMode = document.querySelector('[name="rounding_mode"]');
        const roundingStep = document.querySelector('[name="rounding_step"]');

        if (invoicePrefix && !invoicePrefix.value.trim()) {
            invoicePrefix.value = 'RV/SL';
        }

        if (roundingMode) roundingMode.value = 'nearest';
        if (roundingStep && !roundingStep.value) roundingStep.value = '1.00';
    }

    function skipBillingAndSubmit() {
        const terms = document.getElementById('terms');

        if (!terms?.checked) {
            showError(terms, 'Account create karne ke liye Terms & Conditions accept kijiye.');
            return;
        }

        if (billingSkippedInput) {
            billingSkippedInput.value = '1';
        }

        applyDefaultBillingValues();
        setBillingFieldsDisabled(true);

        if (isSubmitting) return;

        if (!validateStep(0)) {
            showStep(0);
            return;
        }

        if (!validateStep(1)) {
            showStep(1);
            return;
        }

        isSubmitting = true;

        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Creating...';
        }

        form.submit();
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

        if (isVerifyingOtp) return;

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

        isVerifyingOtp = true;
        verifyOtpStatus.textContent = 'OTP automatic verify ho raha hai...';
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

            lastAutoVerifiedOtp = otp;
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

           
            isVerifyingOtp = false;
        }
    }

    if (stateSelect) {
        stateSelect.addEventListener('change', updateStateFields);
        updateStateFields();
    }

    if (gstinInput) {
        gstinInput.addEventListener('input', syncGstFields);
        gstinInput.addEventListener('change', syncGstFields);
        gstinInput.addEventListener('blur', syncGstFields);
        syncGstFields();
    }

    if (sendOtpBtn) {
        sendOtpBtn.addEventListener('click', sendOtp);
    }

    if (verifyOtpBtn) {
        verifyOtpBtn.addEventListener('click', verifyOtp);
    }

    if (skipBusinessBtn) {
        skipBusinessBtn.addEventListener('click', skipBusinessStep);
    }

    if (skipBusinessBottomBtn) {
        skipBusinessBottomBtn.addEventListener('click', skipBusinessStep);
    }

    if (skipBillingBtn) {
        skipBillingBtn.addEventListener('click', skipBillingAndSubmit);
    }

    if (skipBillingBottomBtn) {
        skipBillingBottomBtn.addEventListener('click', skipBillingAndSubmit);
    }

    if (phoneOtpInput) {
        phoneOtpInput.addEventListener('input', function () {
            this.value = this.value.replace(/\D/g, '').slice(0, 6);

            verifyOtpStatus.textContent = '';
            setFieldErrorState(phoneOtpInput, false);
            phoneOtpInput.setCustomValidity('');

            if (this.value.length < 6) {
                lastAutoVerifiedOtp = '';

                if (phoneVerifiedInput) {
                    phoneVerifiedInput.value = '0';
                }

               

                return;
            }

            if (this.value.length === 6 && this.value !== lastAutoVerifiedOtp && !isVerifyingOtp) {
                verifyOtp();
            }
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
            lastAutoVerifiedOtp = '';

            if (phoneOtpInput) phoneOtpInput.value = '';
          

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
            if (currentStep === 1 && businessSkippedInput?.value !== '1') {
                setBusinessFieldsDisabled(false);
            }

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

    if (businessSkippedInput?.value === '1') {
        setBusinessFieldsDisabled(true);
    } else {
        restoreBusinessStep();
    }

    if (billingSkippedInput?.value === '1') {
        applyDefaultBillingValues();
        setBillingFieldsDisabled(true);
    }

    const selectedPlanInput = document.getElementById('selectedPlanInput');
    const selectedPlanSummary = document.getElementById('selectedPlanSummary');
    const selectedPlanName = document.getElementById('selectedPlanName');
    const registrationFormCard = document.getElementById('registrationFormCard');
    const planCards = document.querySelectorAll('[data-plan-card]');
    const planButtons = document.querySelectorAll('[data-select-plan]');

    function selectPlan(button) {
        const planId = button.dataset.selectPlan || '';
        const planName = button.dataset.planName || 'Selected Plan';

        if (selectedPlanInput) {
            selectedPlanInput.value = planId;
        }

        planCards.forEach(card => {
            const isSelected = card.dataset.planCard === planId;
            card.classList.toggle('selected', isSelected);

            const cardButton = card.querySelector('[data-select-plan]');
            if (cardButton) {
                cardButton.textContent = isSelected
                    ? 'Selected Plan'
                    : 'Select This Plan';
            }
        });

        if (selectedPlanName) {
            selectedPlanName.textContent = planName;
        }

        if (selectedPlanSummary) {
            selectedPlanSummary.classList.remove('hidden');
        }

        registrationFormCard?.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
    }

    planButtons.forEach(button => {
        button.addEventListener('click', function () {
            selectPlan(this);
        });
    });

    currentStep = getInitialStep();
    showStep(currentStep, false);
</script>

@endsection