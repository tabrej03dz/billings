<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register – MyVictory Billing</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Create your MyVictory Billing account and onboard your jewellery business in minutes.">

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">

    <style>
        html {
            scroll-behavior: smooth;
        }

        body {
            background:
                radial-gradient(circle at top left, rgba(16,185,129,0.08), transparent 24%),
                radial-gradient(circle at top right, rgba(6,182,212,0.08), transparent 26%),
                linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
        }

        .main-card {
            background: rgba(255,255,255,0.94);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            box-shadow:
                0 10px 30px rgba(15, 23, 42, 0.06),
                0 1px 2px rgba(15, 23, 42, 0.04);
        }

        .step-pill.active {
            background: #ecfdf5;
            color: #065f46;
            border-color: #a7f3d0;
        }

        .step-pill.done {
            background: #f0fdf4;
            color: #166534;
            border-color: #bbf7d0;
        }

        .field-focus:focus {
            box-shadow: 0 0 0 4px rgba(16,185,129,0.10);
        }

        .soft-divider {
            background: linear-gradient(90deg, transparent, rgba(148,163,184,0.35), transparent);
            height: 1px;
        }
    </style>
</head>
<body class="min-h-screen text-slate-800">

    <!-- Header -->
    <header class="sticky top-0 z-50 border-b border-slate-200/80 bg-white/80 backdrop-blur-xl">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="{{ url('/') }}" class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-xl overflow-hidden border border-slate-200 bg-white shadow-sm">
                    <img src="{{ asset('asset/img/logo.png') }}" alt="MyVictory Billing Logo" class="h-full w-full object-cover">
                </div>
                <div class="leading-tight">
                    <div class="font-semibold text-base text-slate-900">MyVictory Billing</div>
                    <div class="text-[11px] text-slate-500">By Real Victory Groups</div>
                </div>
            </a>

            <div class="flex items-center gap-2 sm:gap-3">
                <a href="{{ url('/') }}"
                   class="hidden sm:inline-flex items-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm text-slate-700 hover:border-slate-400 transition">
                    Home
                </a>
                <a href="{{ route('login') }}"
                   class="inline-flex items-center rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800 transition">
                    Login
                </a>
            </div>
        </div>
    </header>

    <main class="relative overflow-hidden">
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute top-10 left-10 h-56 w-56 rounded-full bg-emerald-200/30 blur-3xl"></div>
            <div class="absolute top-24 right-10 h-56 w-56 rounded-full bg-cyan-200/30 blur-3xl"></div>
        </div>

        <div class="relative max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10 lg:py-14">
            <!-- Top intro -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-medium text-emerald-700">
                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                    Secure onboarding for your billing account
                </div>

                <h1 class="mt-5 text-3xl sm:text-4xl font-bold tracking-tight text-slate-900">
                    Create your MyVictory account
                </h1>

                <p class="mt-3 text-sm sm:text-base leading-7 text-slate-600 max-w-2xl mx-auto">
                    Set up your owner account, business details, and billing preferences in a clean 3-step onboarding flow.
                </p>
            </div>

            <!-- Main card -->
            <section class="rounded-[28px] border border-white/70 main-card overflow-hidden">
                <!-- top section -->
                <div class="px-5 sm:px-8 pt-6 sm:pt-7 pb-5 bg-gradient-to-b from-white to-slate-50/80">
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                        <div>
                            <h2 class="text-xl sm:text-2xl font-semibold tracking-tight text-slate-900">
                                Register your account
                            </h2>
                            <p class="mt-1 text-sm text-slate-500">
                                Complete the steps below to start using MyVictory Billing.
                            </p>
                        </div>

                        <div class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-600 shadow-sm">
                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                            Professional onboarding
                        </div>
                    </div>

                    <div class="mt-5 soft-divider"></div>

                    <!-- step pills -->
                    <div class="mt-5 grid grid-cols-3 gap-2">
                        <div id="pillStep1" class="step-pill active rounded-2xl border border-slate-200 bg-white px-3 py-3 text-center transition">
                            <div class="text-[11px] sm:text-xs text-slate-500">Step 1</div>
                            <div class="mt-0.5 text-xs sm:text-sm font-semibold">User Details</div>
                        </div>
                        <div id="pillStep2" class="step-pill rounded-2xl border border-slate-200 bg-white px-3 py-3 text-center transition">
                            <div class="text-[11px] sm:text-xs text-slate-500">Step 2</div>
                            <div class="mt-0.5 text-xs sm:text-sm font-semibold">Business Info</div>
                        </div>
                        <div id="pillStep3" class="step-pill rounded-2xl border border-slate-200 bg-white px-3 py-3 text-center transition">
                            <div class="text-[11px] sm:text-xs text-slate-500">Step 3</div>
                            <div class="mt-0.5 text-xs sm:text-sm font-semibold">Billing Setup</div>
                        </div>
                    </div>

                    <!-- progress -->
                    <div class="mt-5">
                        <div class="flex items-center justify-between text-xs mb-2">
                            <span class="text-slate-500">Step <span id="currentStepText">1</span> of 3</span>
                            <span class="text-emerald-600 font-medium" id="currentStepLabel">User Details</span>
                        </div>

                        <div class="h-2 w-full rounded-full bg-slate-200 overflow-hidden">
                            <div id="progressBar"
                                 class="h-full rounded-full bg-gradient-to-r from-emerald-500 via-cyan-500 to-sky-500 transition-all duration-300"
                                 style="width: 33.33%;"></div>
                        </div>
                    </div>
                </div>

                <!-- form section -->
                <div class="px-5 sm:px-8 py-6 sm:py-8">
                    @if (session('success'))
                        <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
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

                    <form id="multiStepForm" action="{{ route('register.store1') }}" method="POST" class="space-y-6">
                        @csrf
                        <input type="hidden" name="current_step" id="current_step" value="{{ old('current_step', 1) }}">
                        <!-- STEP 1 -->
                        <div class="form-step space-y-5" data-step="1">
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                                <div class="text-sm font-semibold text-slate-900">Owner account details</div>
                                <div class="mt-1 text-xs text-slate-500">Enter your login credentials to create the primary account.</div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">Full Name</label>
                                <input
                                    type="text"
                                    name="name"
                                    value="{{ old('name') }}"
                                    required
                                    class="field-focus w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none placeholder:text-slate-400 focus:border-emerald-500"
                                    placeholder="Enter your full name"
                                >
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">Email Address</label>
                                <div class="flex flex-col sm:flex-row gap-3">
                                    <input
                                        type="email"
                                        name="email"
                                        id="email"
                                        value="{{ old('email') }}"
                                        required
                                        class="field-focus w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none placeholder:text-slate-400 focus:border-emerald-500"
                                        placeholder="Enter your email address"
                                    >

                                    <button
                                        type="button"
                                        id="sendOtpBtn"
                                        class="shrink-0 rounded-2xl border border-amber-200 bg-amber-400 px-5 py-3 text-sm font-semibold text-slate-900 hover:bg-amber-300 transition"
                                    >
                                        Send OTP
                                    </button>
                                </div>
                                <p id="otpStatus" class="mt-2 text-xs text-slate-500"></p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">Email OTP</label>
                                <div class="flex flex-col sm:flex-row gap-3">
                                    <input
                                        type="text"
                                        id="emailOtp"
                                        maxlength="6"
                                        class="field-focus w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none placeholder:text-slate-400 focus:border-emerald-500"
                                        placeholder="Enter 6 digit OTP"
                                    >

                                    <button
                                        type="button"
                                        id="verifyOtpBtn"
                                        class="shrink-0 rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white hover:bg-slate-800 transition"
                                    >
                                        Verify OTP
                                    </button>
                                </div>

                                <input type="hidden" id="emailVerified" value="0">
                                <p id="verifyOtpStatus" class="mt-2 text-xs text-slate-500"></p>
                            </div>

                            <div class="grid sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">Password</label>
                                    <input
                                        type="password"
                                        name="password"
                                        required
                                        class="field-focus w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none placeholder:text-slate-400 focus:border-emerald-500"
                                        placeholder="Create a password"
                                    >
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">Confirm Password</label>
                                    <input
                                        type="password"
                                        name="password_confirmation"
                                        required
                                        class="field-focus w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none placeholder:text-slate-400 focus:border-emerald-500"
                                        placeholder="Confirm your password"
                                    >
                                </div>
                            </div>

                            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs text-emerald-700">
                                Email verification is required before moving to the next step.
                            </div>
                        </div>

                        <!-- STEP 2 -->
                        <div class="form-step hidden space-y-5" data-step="2">
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                                <div class="text-sm font-semibold text-slate-900">Business information</div>
                                <div class="mt-1 text-xs text-slate-500">Add showroom and contact details for your billing profile.</div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">Business / Showroom Name</label>
                                <input
                                    type="text"
                                    name="business_name"
                                    value="{{ old('business_name') }}"
                                    required
                                    class="field-focus w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none placeholder:text-slate-400 focus:border-emerald-500"
                                    placeholder="Enter showroom name"
                                >
                            </div>

                            <div class="grid sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">Business Email</label>
                                    <input
                                        type="email"
                                        name="business_email"
                                        value="{{ old('business_email') }}"
                                        required
                                        class="field-focus w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none placeholder:text-slate-400 focus:border-emerald-500"
                                        placeholder="Business email"
                                    >
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">Mobile Number</label>
                                    <input
                                        type="text"
                                        name="mobile"
                                        value="{{ old('mobile') }}"
                                        required
                                        class="field-focus w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none placeholder:text-slate-400 focus:border-emerald-500"
                                        placeholder="Business mobile number"
                                    >
                                </div>
                            </div>

                            <div class="grid sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">GSTIN</label>
                                    <input
                                        type="text"
                                        name="gstin"
                                        value="{{ old('gstin') }}"
                                        class="field-focus w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm uppercase outline-none placeholder:text-slate-400 focus:border-emerald-500"
                                        placeholder="Optional GSTIN"
                                    >
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">Business Type</label>
                                    <select
                                        name="type"
                                        required
                                        class="field-focus w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-700 outline-none focus:border-emerald-500"
                                    >
                                        <option value="">-- Select Business Type --</option>
                                        <option value="Jewellery" {{ old('type') == 'Jewellery' ? 'selected' : '' }}>Jewellery</option>
                                        <option value="Retail" {{ old('type') == 'Retail' ? 'selected' : '' }}>Retail</option>
                                        <option value="Wholesale" {{ old('type') == 'Wholesale' ? 'selected' : '' }}>Wholesale</option>
                                        <option value="Manufacturer" {{ old('type') == 'Manufacturer' ? 'selected' : '' }}>Manufacturer</option>
                                        <option value="Service Provider" {{ old('type') == 'Service Provider' ? 'selected' : '' }}>Service Provider</option>
                                        <option value="Trading" {{ old('type') == 'Trading' ? 'selected' : '' }}>Trading</option>
                                        <option value="E-Commerce" {{ old('type') == 'E-Commerce' ? 'selected' : '' }}>E-Commerce</option>
                                        <option value="Agency" {{ old('type') == 'Agency' ? 'selected' : '' }}>Agency</option>
                                        <option value="Other" {{ old('type') == 'Other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">Address</label>
                                <textarea
                                    name="address"
                                    rows="4"
                                    class="field-focus w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none placeholder:text-slate-400 focus:border-emerald-500"
                                    placeholder="Enter business address"
                                >{{ old('address') }}</textarea>
                            </div>

                            <div class="grid sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">State</label>
                                    <input
                                        type="text"
                                        name="state"
                                        value="{{ old('state') }}"
                                        class="field-focus w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none placeholder:text-slate-400 focus:border-emerald-500"
                                        placeholder="Enter state"
                                    >
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">State Code</label>
                                    <input
                                        type="text"
                                        name="state_code"
                                        value="{{ old('state_code') }}"
                                        class="field-focus w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none placeholder:text-slate-400 focus:border-emerald-500"
                                        placeholder="Enter state code"
                                    >
                                </div>
                            </div>
                        </div>

                        <!-- STEP 3 -->
                        <div class="form-step hidden space-y-5" data-step="3">
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                                <div class="text-sm font-semibold text-slate-900">Billing preferences</div>
                                <div class="mt-1 text-xs text-slate-500">Choose your default invoice and rounding settings.</div>
                            </div>

                            <div class="grid sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">GST Enabled</label>
                                    <select
                                        name="gst_enabled"
                                        class="field-focus w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none focus:border-emerald-500"
                                    >
                                        <option value="1" {{ old('gst_enabled', '1') == '1' ? 'selected' : '' }}>Yes</option>
                                        <option value="0" {{ old('gst_enabled') == '0' ? 'selected' : '' }}>No</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">Invoice Base Prefix</label>
                                    <input
                                        type="text"
                                        name="invoice_base_prefix"
                                        value="{{ old('invoice_base_prefix', 'RV/SL') }}"
                                        class="field-focus w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none placeholder:text-slate-400 focus:border-emerald-500"
                                        placeholder="RV/SL"
                                    >
                                </div>
                            </div>

                            <div class="grid sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">Rounding Mode</label>
                                    <select
                                        name="rounding_mode"
                                        class="field-focus w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none focus:border-emerald-500"
                                    >
                                        <option value="none" {{ old('rounding_mode') == 'none' ? 'selected' : '' }}>None</option>
                                        <option value="nearest" {{ old('rounding_mode', 'nearest') == 'nearest' ? 'selected' : '' }}>Nearest</option>
                                        <option value="up" {{ old('rounding_mode') == 'up' ? 'selected' : '' }}>Up</option>
                                        <option value="down" {{ old('rounding_mode') == 'down' ? 'selected' : '' }}>Down</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">Rounding Step</label>
                                    <input
                                        type="number"
                                        step="0.01"
                                        name="rounding_step"
                                        value="{{ old('rounding_step', '1.00') }}"
                                        class="field-focus w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none placeholder:text-slate-400 focus:border-emerald-500"
                                        placeholder="1.00"
                                    >
                                </div>
                            </div>

                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <div class="text-sm font-semibold text-slate-900">Before submitting</div>
                                <ul class="mt-2 space-y-1.5 text-xs text-slate-600">
                                    <li>• Verify your email OTP</li>
                                    <li>• Check showroom details carefully</li>
                                    <li>• Billing settings can be updated later</li>
                                </ul>
                            </div>

                            <div class="flex items-start gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-4">
                                <input
                                    id="terms"
                                    type="checkbox"
                                    name="terms"
                                    value="1"
                                    required
                                    class="mt-1 rounded border-slate-300 bg-white text-emerald-500 focus:ring-emerald-500"
                                >
                                <label for="terms" class="text-xs sm:text-sm text-slate-600 leading-6">
                                    I agree to the
                                    <a href="#" class="font-medium text-slate-900 hover:text-emerald-600">Terms & Conditions</a>
                                    and
                                    <a href="#" class="font-medium text-slate-900 hover:text-emerald-600">Privacy Policy</a>.
                                </label>
                            </div>
                        </div>

                        <!-- buttons -->
                        <div class="pt-2">
                            <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-3">
                                <button
                                    type="button"
                                    id="prevBtn"
                                    class="hidden rounded-2xl border border-slate-300 bg-white px-5 py-3 text-sm font-medium text-slate-700 hover:border-slate-400 transition"
                                >
                                    Back
                                </button>

                                <div class="sm:ml-auto flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                                    <button
                                        type="button"
                                        id="nextBtn"
                                        class="w-full sm:w-auto rounded-2xl bg-slate-900 px-6 py-3 text-sm font-semibold text-white hover:bg-slate-800 transition"
                                    >
                                        Continue
                                    </button>

                                    <button
                                        type="submit"
                                        id="submitBtn"
                                        class="hidden w-full sm:w-auto rounded-2xl bg-emerald-600 px-6 py-3 text-sm font-semibold text-white hover:bg-emerald-700 transition"
                                    >
                                        Create Account
                                    </button>
                                </div>
                            </div>

                            <p class="text-sm text-slate-500 text-center pt-5">
                                Already have an account?
                                <a href="{{ route('login') }}" class="font-medium text-slate-900 hover:text-emerald-600">
                                    Login here
                                </a>
                            </p>
                        </div>
                    </form>
                </div>
            </section>

            <div class="mt-5 text-center text-xs text-slate-500">
                Need help? Contact support:
                <a href="https://wa.me/917753800444" target="_blank" class="font-medium text-slate-900 hover:text-emerald-600">
                    +91-7753800444
                </a>
            </div>
        </div>
    </main>

    <footer class="border-t border-slate-200 bg-white/70 backdrop-blur py-5 mt-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center justify-between gap-3 text-xs text-slate-500">
            <div>
                © {{ date('Y') }} MyVictory Billing · Powered by Real Victory Groups
            </div>
            <div class="flex flex-wrap gap-4">
                <a href="#" class="hover:text-slate-700 transition">Terms & Conditions</a>
                <a href="#" class="hover:text-slate-700 transition">Privacy Policy</a>
            </div>
        </div>
    </footer>

    {{-- <script>
        const steps = document.querySelectorAll('.form-step');
        const nextBtn = document.getElementById('nextBtn');
        const prevBtn = document.getElementById('prevBtn');
        const submitBtn = document.getElementById('submitBtn');
        const progressBar = document.getElementById('progressBar');
        const currentStepText = document.getElementById('currentStepText');
        const currentStepLabel = document.getElementById('currentStepLabel');

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

        let currentStep = 0;

        const labels = ['User Details', 'Business Details', 'Billing Setup'];
        const pills = [pillStep1, pillStep2, pillStep3];

        function updateStepPills(index) {
            pills.forEach((pill, i) => {
                pill.classList.remove('active', 'done');

                if (i < index) {
                    pill.classList.add('done');
                } else if (i === index) {
                    pill.classList.add('active');
                }
            });
        }

        function showStep(index) {
            steps.forEach((step, i) => {
                step.classList.toggle('hidden', i !== index);
            });

            currentStepText.textContent = index + 1;
            currentStepLabel.textContent = labels[index];
            progressBar.style.width = ((index + 1) / steps.length * 100) + '%';

            prevBtn.classList.toggle('hidden', index === 0);
            nextBtn.classList.toggle('hidden', index === steps.length - 1);
            submitBtn.classList.toggle('hidden', index !== steps.length - 1);

            updateStepPills(index);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function validateStep(index) {
            const currentInputs = steps[index].querySelectorAll('input, select, textarea');

            for (let input of currentInputs) {
                if (input.type === 'hidden') continue;

                if (!input.checkValidity()) {
                    input.reportValidity();
                    input.focus();
                    return false;
                }
            }

            const password = document.querySelector('input[name="password"]');
            const confirmPassword = document.querySelector('input[name="password_confirmation"]');

            if (index === 0 && password && confirmPassword) {
                if (password.value !== confirmPassword.value) {
                    confirmPassword.setCustomValidity('Passwords do not match');
                    confirmPassword.reportValidity();
                    confirmPassword.focus();
                    return false;
                } else {
                    confirmPassword.setCustomValidity('');
                }
            }

            if (index === 0 && emailVerifiedInput.value !== '1') {
                verifyOtpStatus.textContent = 'Pehle email OTP verify kijiye.';
                verifyOtpStatus.className = 'mt-2 text-xs text-red-500';
                emailOtpInput.focus();
                return false;
            }

            return true;
        }

        sendOtpBtn.addEventListener('click', async function () {
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

                emailVerifiedInput.value = '0';
                verifyOtpStatus.textContent = '';
                otpStatus.textContent = data.message || 'OTP bhej diya gaya hai.';
                otpStatus.className = 'mt-2 text-xs text-emerald-600';
            } catch (error) {
                otpStatus.textContent = error?.message || 'OTP bhejne me problem aayi.';
                otpStatus.className = 'mt-2 text-xs text-red-500';
            }
        });

        verifyOtpBtn.addEventListener('click', async function () {
            const email = emailInput.value.trim();
            const otp = emailOtpInput.value.trim();

            if (!email) {
                emailInput.reportValidity();
                emailInput.focus();
                return;
            }

            if (!otp || otp.length !== 6) {
                emailOtpInput.focus();
                verifyOtpStatus.textContent = 'Valid 6 digit OTP daliyega.';
                verifyOtpStatus.className = 'mt-2 text-xs text-red-500';
                return;
            }

            verifyOtpStatus.textContent = 'OTP verify ho raha hai...';
            verifyOtpStatus.className = 'mt-2 text-xs text-cyan-600';

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

                emailVerifiedInput.value = '1';
                verifyOtpStatus.textContent = data.message || 'OTP verify ho gaya.';
                verifyOtpStatus.className = 'mt-2 text-xs text-emerald-600';
            } catch (error) {
                emailVerifiedInput.value = '0';
                verifyOtpStatus.textContent = error?.message || 'OTP verify nahi hua.';
                verifyOtpStatus.className = 'mt-2 text-xs text-red-500';
            }
        });

        emailInput.addEventListener('input', function () {
            emailVerifiedInput.value = '0';
            verifyOtpStatus.textContent = '';
        });

        nextBtn.addEventListener('click', function () {
            if (!validateStep(currentStep)) return;

            if (currentStep < steps.length - 1) {
                currentStep++;
                showStep(currentStep);
            }
        });

        prevBtn.addEventListener('click', function () {
            if (currentStep > 0) {
                currentStep--;
                showStep(currentStep);
            }
        });

        showStep(currentStep);
    </script> --}}

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

    const labels = ['User Details', 'Business Details', 'Billing Setup'];
    const pills = [pillStep1, pillStep2, pillStep3];

    let currentStep = 0;

    function getInitialStep() {
        const oldStep = parseInt(currentStepInput?.value || '1', 10);
        if (!isNaN(oldStep) && oldStep >= 1 && oldStep <= steps.length) {
            return oldStep - 1;
        }
        return 0;
    }

    function updateStepPills(index) {
        pills.forEach((pill, i) => {
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

        input.classList.remove('border-red-500', 'focus:border-red-500', 'ring-1', 'ring-red-200');

        if (hasError) {
            input.classList.add('border-red-500', 'focus:border-red-500', 'ring-1', 'ring-red-200');
        }
    }

    function clearStepErrors(index) {
        const currentInputs = steps[index].querySelectorAll('input, select, textarea');
        currentInputs.forEach(input => setFieldErrorState(input, false));
    }

    function validateStep(index) {
        clearStepErrors(index);

        const currentInputs = steps[index].querySelectorAll('input, select, textarea');

        for (let input of currentInputs) {
            if (input.type === 'hidden' || input.type === 'button' || input.type === 'submit') continue;

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
            } else {
                confirmPassword.setCustomValidity('');
            }
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
            otpStatus.className = 'mt-2 text-xs text-emerald-600';
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
        verifyOtpStatus.className = 'mt-2 text-xs text-cyan-600';

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
            verifyOtpStatus.className = 'mt-2 text-xs text-emerald-600';
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

    nextBtn.addEventListener('click', function () {
        if (!validateStep(currentStep)) return;

        if (currentStep < steps.length - 1) {
            showStep(currentStep + 1);
        }
    });

    prevBtn.addEventListener('click', function () {
        if (currentStep > 0) {
            showStep(currentStep - 1);
        }
    });

    if (form) {
        form.addEventListener('submit', function () {
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