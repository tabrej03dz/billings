<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register – MyVictory Billing</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Create your MyVictory Billing account and onboard your business in minutes.">

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">

    <style>
        html { scroll-behavior: smooth; }

        body {
            background:
                radial-gradient(circle at 15% 10%, rgba(37,99,235,.14), transparent 32%),
                radial-gradient(circle at 85% 15%, rgba(245,158,11,.18), transparent 32%),
                linear-gradient(180deg, #fffaf0 0%, #ffffff 45%, #f8fbff 100%);
        }

        .hero-bg {
            background:
                radial-gradient(circle at 15% 10%, rgba(37,99,235,.14), transparent 32%),
                radial-gradient(circle at 85% 15%, rgba(245,158,11,.18), transparent 32%),
                linear-gradient(180deg, #fffaf0 0%, #ffffff 45%, #f8fbff 100%);
        }

        .soft-card {
            box-shadow: 0 24px 70px rgba(15, 23, 42, .10);
        }

        .main-card {
            background: rgba(255,255,255,0.96);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            box-shadow: 0 24px 70px rgba(15, 23, 42, .10);
        }

        .step-pill.active {
            background: #eff6ff;
            color: #1d4ed8;
            border-color: #bfdbfe;
        }

        .step-pill.done {
            background: #f0fdf4;
            color: #166534;
            border-color: #bbf7d0;
        }

        .field-focus:focus {
            box-shadow: 0 0 0 4px rgba(37,99,235,0.12);
        }

        .soft-divider {
            background: linear-gradient(90deg, transparent, rgba(148,163,184,0.35), transparent);
            height: 1px;
        }
    </style>
</head>

<body class="min-h-screen text-slate-900">

<header class="sticky top-0 z-50 bg-white/90 backdrop-blur-xl border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 lg:px-8 h-20 flex items-center justify-between">
        <a href="{{ url('/') }}" class="flex items-center gap-3">
            <img src="{{ asset('asset/img/logo.png') }}"
                 class="h-12 w-12 rounded-2xl border border-slate-200 shadow-sm"
                 alt="MyVictory">

            <div>
                <div class="font-black text-xl leading-none">MyVictory Billing</div>
                <div class="text-xs text-slate-500 mt-1">By Real Victory Groups</div>
            </div>
        </a>

        <div class="flex items-center gap-3">
            <a href="{{ url('/') }}"
               class="hidden sm:inline-flex px-5 py-3 rounded-full border border-slate-300 text-sm font-bold hover:border-blue-500">
                Home
            </a>

            <a href="{{ route('login') }}"
               class="px-5 py-3 rounded-full bg-slate-950 text-white text-sm font-bold">
                Login
            </a>

            <a href="https://wa.me/917753800444"
               class="hidden md:inline-flex px-6 py-3 rounded-full bg-blue-600 text-white text-sm font-black shadow-lg shadow-blue-200">
                Need Help?
            </a>
        </div>
    </div>
</header>

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

                        <form id="multiStepForm" action="{{ route('register.store1') }}" method="POST" class="space-y-6">
                            @csrf
                            <input type="hidden" name="payment_done" value="{{ request('payment_done', 0) }}">
                            <input type="hidden" name="plan_id" value="{{ old('plan_id', request('plan_id')) }}">
                            <input type="hidden" name="trial" value="{{ old('trial', request('trial', 0)) }}">
                            <input type="hidden" name="current_step" id="current_step" value="{{ old('current_step', 1) }}">

                            <div class="form-step space-y-5" data-step="1">
                                <div class="rounded-2xl border border-blue-100 bg-blue-50 px-4 py-4">
                                    <div class="text-sm font-black text-slate-900">Owner account details</div>
                                    <div class="mt-1 text-xs text-slate-500">
                                        Enter login credentials to create the primary account.
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-2">Full Name</label>
                                    <input type="text" name="name" value="{{ old('name') }}" required
                                           class="field-focus w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none placeholder:text-slate-400 focus:border-blue-500"
                                           placeholder="Enter your full name">
                                </div>

                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-2">Email Address</label>

                                    <div class="flex flex-col sm:flex-row gap-3">
                                        <input type="email" name="email" id="email" value="{{ old('email') }}" required
                                               class="field-focus w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none placeholder:text-slate-400 focus:border-blue-500"
                                               placeholder="Enter your email address">

                                        <button type="button" id="sendOtpBtn"
                                                class="shrink-0 rounded-2xl bg-yellow-400 px-5 py-3 text-sm font-black text-slate-950 hover:bg-yellow-300 transition">
                                            Send OTP
                                        </button>
                                    </div>

                                    <p id="otpStatus" class="mt-2 text-xs text-slate-500"></p>
                                </div>

                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-2">Email OTP</label>

                                    <div class="flex flex-col sm:flex-row gap-3">
                                        <input type="text" id="emailOtp" maxlength="6"
                                               class="field-focus w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none placeholder:text-slate-400 focus:border-blue-500"
                                               placeholder="Enter 6 digit OTP">

                                        <button type="button" id="verifyOtpBtn"
                                                class="shrink-0 rounded-2xl bg-slate-950 px-5 py-3 text-sm font-black text-white hover:bg-slate-800 transition">
                                            Verify OTP
                                        </button>
                                    </div>

                                    <input type="hidden" id="emailVerified" value="0">
                                    <p id="verifyOtpStatus" class="mt-2 text-xs text-slate-500"></p>
                                </div>

                                <div class="grid sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-bold text-slate-700 mb-2">Password</label>
                                        <input type="password" name="password" required
                                               class="field-focus w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none placeholder:text-slate-400 focus:border-blue-500"
                                               placeholder="Create a password">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-bold text-slate-700 mb-2">Confirm Password</label>
                                        <input type="password" name="password_confirmation" required
                                               class="field-focus w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none placeholder:text-slate-400 focus:border-blue-500"
                                               placeholder="Confirm your password">
                                    </div>
                                </div>

                                <div class="rounded-2xl border border-blue-100 bg-blue-50 px-4 py-3 text-xs text-blue-700">
                                    Email verification is required before moving to the next step.
                                </div>
                            </div>

                            <div class="form-step hidden space-y-5" data-step="2">
                                <div class="rounded-2xl border border-blue-100 bg-blue-50 px-4 py-4">
                                    <div class="text-sm font-black text-slate-900">Business information</div>
                                    <div class="mt-1 text-xs text-slate-500">
                                        Add showroom and contact details for your billing profile.
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-2">Business / Showroom Name</label>
                                    <input type="text" name="business_name" value="{{ old('business_name') }}" required
                                           class="field-focus w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none placeholder:text-slate-400 focus:border-blue-500"
                                           placeholder="Enter showroom name">
                                </div>

                                <div class="grid sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-bold text-slate-700 mb-2">Business Email</label>
                                        <input type="email" name="business_email" value="{{ old('business_email') }}" required
                                               class="field-focus w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none placeholder:text-slate-400 focus:border-blue-500"
                                               placeholder="Business email">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-bold text-slate-700 mb-2">Mobile Number</label>
                                        <input type="text" name="mobile" value="{{ old('mobile') }}" required
                                               class="field-focus w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none placeholder:text-slate-400 focus:border-blue-500"
                                               placeholder="Business mobile number">
                                    </div>
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
                                        <li>• Verify your email OTP</li>
                                        <li>• Check showroom details carefully</li>
                                        <li>• Billing settings can be updated later</li>
                                    </ul>
                                </div>

                                <div class="flex items-start gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-4">
                                    <input id="terms" type="checkbox" name="terms" value="1" required
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

<footer class="border-t border-slate-200 bg-white/80 backdrop-blur py-5">
    <div class="max-w-7xl mx-auto px-4 lg:px-8 flex flex-col md:flex-row items-center justify-between gap-3 text-xs text-slate-500">
        <div>
            © {{ date('Y') }} MyVictory Billing · Powered by Real Victory Groups
        </div>

        <div class="flex flex-wrap gap-4">
            <a href="#" class="hover:text-blue-600 transition">Terms & Conditions</a>
            <a href="#" class="hover:text-blue-600 transition">Privacy Policy</a>
        </div>
    </div>
</footer>

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
    const emailInput = document.getElementById('email');
    const emailOtpInput = document.getElementById('emailOtp');
    const otpStatus = document.getElementById('otpStatus');
    const verifyOtpStatus = document.getElementById('verifyOtpStatus');
    const emailVerifiedInput = document.getElementById('emailVerified');
    const form = document.getElementById('multiStepForm');

    const gstinInput = document.getElementById('gstin');
    const stateSelect = document.getElementById('state_select');
    const stateInput = document.getElementById('state');
    const stateCodeInput = document.getElementById('state_code');

    const labels = ['User Details', 'Business Details', 'Billing Setup'];
    const pills = [pillStep1, pillStep2, pillStep3];

    let currentStep = 0;

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

    if (stateSelect) {
        stateSelect.addEventListener('change', updateStateFields);
        updateStateFields();
    }

    if (gstinInput) {
        gstinInput.addEventListener('input', updateStateFromGstin);
        gstinInput.addEventListener('blur', updateStateFromGstin);
        updateStateFromGstin();
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
        currentStepText.textContent = index + 1;
        currentStepLabel.textContent = labels[index];
        progressBar.style.width = (((index + 1) / steps.length) * 100) + '%';

        prevBtn.classList.toggle('hidden', index === 0);
        nextBtn.classList.toggle('hidden', index === steps.length - 1);
        submitBtn.classList.toggle('hidden', index !== steps.length - 1);

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
        const currentInputs = steps[index].querySelectorAll('input, select, textarea');

        currentInputs.forEach(input => {
            setFieldErrorState(input, false);
        });
    }

    function validateStep(index) {
        clearStepErrors(index);

        updateStateFromGstin();
        updateStateFields();

        const currentInputs = steps[index].querySelectorAll('input, select, textarea');

        for (let input of currentInputs) {
            if (
                input.type === 'hidden' ||
                input.type === 'button' ||
                input.type === 'submit'
            ) {
                continue;
            }

            input.setCustomValidity('');

            if (!input.checkValidity()) {
                setFieldErrorState(input, true);
                input.reportValidity();
                input.focus();
                return false;
            }
        }

        const password = document.querySelector('input[name="password"]');
        const confirmPassword = document.querySelector('input[name="password_confirmation"]');

        if (index === 0 && password && confirmPassword) {
            confirmPassword.setCustomValidity('');

            if (password.value !== confirmPassword.value) {
                confirmPassword.setCustomValidity('Passwords do not match');
                setFieldErrorState(confirmPassword, true);
                confirmPassword.reportValidity();
                confirmPassword.focus();
                return false;
            }

            confirmPassword.setCustomValidity('');
        }

        if (index === 0 && emailVerifiedInput && emailVerifiedInput.value !== '1') {
            verifyOtpStatus.textContent = 'Pehle email OTP verify kijiye.';
            verifyOtpStatus.className = 'mt-2 text-xs text-red-500';
            setFieldErrorState(emailOtpInput, true);
            emailOtpInput.focus();
            return false;
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

                if (this.name === 'password_confirmation') {
                    this.setCustomValidity('');
                }
            });

            input.addEventListener('change', function () {
                setFieldErrorState(this, false);
            });
        });
    }

    async function sendOtp() {
        const email = emailInput.value.trim();

        if (!email) {
            emailInput.reportValidity();
            emailInput.focus();
            return;
        }

        otpStatus.textContent = 'OTP bheja ja raha hai...';
        otpStatus.className = 'mt-2 text-xs text-amber-600';

        try {
            const response = await fetch("{{ route('register.sendOtp') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': "{{ csrf_token() }}",
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ email })
            });

            const data = await response.json();

            if (!response.ok) {
                throw data;
            }

            if (emailVerifiedInput) {
                emailVerifiedInput.value = '0';
            }

            verifyOtpStatus.textContent = '';
            otpStatus.textContent = data.message || 'OTP bhej diya gaya hai.';
            otpStatus.className = 'mt-2 text-xs text-green-600';

            setFieldErrorState(emailInput, false);
            setFieldErrorState(emailOtpInput, false);
        } catch (error) {
            otpStatus.textContent = error?.message || 'OTP bhejne me problem aayi.';
            otpStatus.className = 'mt-2 text-xs text-red-500';
        }
    }

    async function verifyOtp() {
        const email = emailInput.value.trim();
        const otp = emailOtpInput.value.trim();

        if (!email) {
            emailInput.reportValidity();
            emailInput.focus();
            return;
        }

        if (!otp || otp.length !== 6) {
            verifyOtpStatus.textContent = 'Valid 6 digit OTP daliyega.';
            verifyOtpStatus.className = 'mt-2 text-xs text-red-500';
            setFieldErrorState(emailOtpInput, true);
            emailOtpInput.focus();
            return;
        }

        verifyOtpStatus.textContent = 'OTP verify ho raha hai...';
        verifyOtpStatus.className = 'mt-2 text-xs text-blue-600';

        try {
            const response = await fetch("{{ route('register.verifyOtp') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': "{{ csrf_token() }}",
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ email, otp })
            });

            const data = await response.json();

            if (!response.ok) {
                throw data;
            }

            if (emailVerifiedInput) {
                emailVerifiedInput.value = '1';
            }

            verifyOtpStatus.textContent = data.message || 'OTP verify ho gaya.';
            verifyOtpStatus.className = 'mt-2 text-xs text-green-600';

            setFieldErrorState(emailOtpInput, false);
            setFieldErrorState(emailInput, false);
        } catch (error) {
            if (emailVerifiedInput) {
                emailVerifiedInput.value = '0';
            }

            verifyOtpStatus.textContent = error?.message || 'OTP verify nahi hua.';
            verifyOtpStatus.className = 'mt-2 text-xs text-red-500';

            setFieldErrorState(emailOtpInput, true);
        }
    }

    if (sendOtpBtn) {
        sendOtpBtn.addEventListener('click', sendOtp);
    }

    if (verifyOtpBtn) {
        verifyOtpBtn.addEventListener('click', verifyOtp);
    }

    if (emailInput) {
        emailInput.addEventListener('input', function () {
            if (emailVerifiedInput) {
                emailVerifiedInput.value = '0';
            }

            verifyOtpStatus.textContent = '';
            setFieldErrorState(emailInput, false);
            setFieldErrorState(emailOtpInput, false);
        });
    }

    if (emailOtpInput) {
        emailOtpInput.addEventListener('input', function () {
            verifyOtpStatus.textContent = '';
            setFieldErrorState(emailOtpInput, false);
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
            updateStateFromGstin();
            updateStateFields();

            if (!validateStep(currentStep)) {
                e.preventDefault();
                return;
            }

            if (currentStepInput) {
                currentStepInput.value = currentStep + 1;
            }
        });
    }

    bindFieldListeners();

    currentStep = getInitialStep();
    showStep(currentStep, false);
</script>

</body>
</html>