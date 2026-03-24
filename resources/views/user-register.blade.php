<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register – MyVictory Billing</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen">

    <!-- Header -->
    <header class="sticky top-0 z-40 bg-slate-950/80 backdrop-blur border-b border-slate-800">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-16">

            <a href="{{ url('/') }}" class="flex items-center gap-2">
                <div class="h-9 w-9 rounded-lg bg-gradient-to-tr from-emerald-400 to-cyan-500 flex items-center justify-center text-xl font-bold overflow-hidden">
                    <img src="{{ asset('asset/img/logo.png') }}" alt="Logo" class="h-full w-full object-cover rounded">
                </div>
                <div>
                    <div class="font-semibold text-lg">MyVictory Billing</div>
                    <div class="text-xs text-slate-400">By Real Victory Groups</div>
                </div>
            </a>

            <div class="flex items-center gap-3">
                <a href="{{ url('/') }}" class="hidden sm:inline-flex text-sm px-3 py-1.5 rounded-lg border border-slate-700 hover:border-emerald-400 hover:text-emerald-400 transition">
                    Home
                </a>
                <a href="{{ route('login') }}" class="text-sm px-3 py-1.5 rounded-lg border border-slate-700 hover:border-emerald-400 hover:text-emerald-400 transition">
                    Login
                </a>
            </div>
        </div>
    </header>

    <main class="relative overflow-hidden">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(16,185,129,0.16),_transparent_60%),radial-gradient(circle_at_bottom,_rgba(56,189,248,0.10),_transparent_60%)] pointer-events-none"></div>

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10 lg:py-16 relative">
            <div class="grid lg:grid-cols-2 gap-10 items-start">

                <!-- Left Content -->
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full border border-emerald-500/40 bg-emerald-500/10 px-3 py-1 text-xs mb-4">
                        <span class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span>Start your jewellery billing journey</span>
                    </div>

                    <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold tracking-tight leading-tight">
                        Create your
                        <span class="bg-gradient-to-r from-emerald-400 via-cyan-400 to-sky-400 bg-clip-text text-transparent">
                            MyVictory Account
                        </span>
                    </h1>

                    <p class="mt-4 text-sm sm:text-base text-slate-300 max-w-xl">
                        Register your account, create your business, and setup billing preferences
                        in one simple onboarding flow.
                    </p>

                    <div class="mt-8 grid sm:grid-cols-2 gap-4">
                        <div class="bg-slate-900/70 border border-slate-800 rounded-2xl p-4">
                            <div class="h-10 w-10 rounded-lg bg-emerald-500/20 border border-emerald-500/40 flex items-center justify-center mb-3 text-emerald-300">
                                👤
                            </div>
                            <h3 class="font-semibold mb-1">User Registration</h3>
                            <p class="text-sm text-slate-400">
                                Owner account create hoga with secure login credentials.
                            </p>
                        </div>

                        <div class="bg-slate-900/70 border border-slate-800 rounded-2xl p-4">
                            <div class="h-10 w-10 rounded-lg bg-cyan-500/20 border border-cyan-500/40 flex items-center justify-center mb-3 text-cyan-300">
                                🏢
                            </div>
                            <h3 class="font-semibold mb-1">Business Creation</h3>
                            <p class="text-sm text-slate-400">
                                Business details aur billing settings same form me save hongi.
                            </p>
                        </div>
                    </div>

                    <div class="mt-8 flex flex-wrap items-center gap-4 text-xs text-slate-400">
                        <div class="flex items-center gap-2">
                            <span class="h-6 w-6 rounded-full bg-emerald-500/20 border border-emerald-400 flex items-center justify-center text-[10px]">✔</span>
                            <span>User + Business + Owner mapping</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="h-6 w-6 rounded-full bg-emerald-500/20 border border-emerald-400 flex items-center justify-center text-[10px]">₹</span>
                            <span>Billing settings at signup</span>
                        </div>
                    </div>
                </div>

                <!-- Register Form -->
                <div>
                    <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-6 sm:p-7 shadow-xl">
                        <div class="mb-5">
                            <h2 class="text-xl font-semibold">Register your account</h2>
                            <p class="text-sm text-slate-400 mt-1">
                                3 simple steps me onboarding complete karein.
                            </p>
                        </div>

                        @if (session('success'))
                            <div class="mb-4 rounded-xl border border-emerald-500/40 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="mb-4 rounded-xl border border-red-500/40 bg-red-500/10 px-4 py-3 text-sm text-red-300">
                                <ul class="list-disc ml-5 space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <!-- Progress -->
                        <div class="mb-6">
                            <div class="flex items-center justify-between mb-2">
                                <div class="text-xs text-slate-400">
                                    Step <span id="currentStepText">1</span> of 3
                                </div>
                                <div class="text-xs text-emerald-300 font-medium" id="currentStepLabel">
                                    User Details
                                </div>
                            </div>

                            <div class="w-full h-2 rounded-full bg-slate-800 overflow-hidden">
                                <div id="progressBar" class="h-full bg-gradient-to-r from-emerald-400 to-cyan-400 transition-all duration-300" style="width: 33.33%;"></div>
                            </div>
                        </div>

                        <form id="multiStepForm" action="{{ route('register.store') }}" method="POST" class="space-y-4">
                            @csrf

                            <!-- STEP 1 : USER -->
                            <div class="form-step" data-step="1">
                                <div>
                                    <label class="block text-xs text-slate-400 mb-1">Full Name</label>
                                    <input
                                        type="text"
                                        name="name"
                                        value="{{ old('name') }}"
                                        required
                                        class="w-full bg-slate-950/70 border border-slate-700 rounded-xl px-3 py-2.5 outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500"
                                        placeholder="Enter full name"
                                    >
                                </div>

                                <div class="mt-4">
                                    <label class="block text-xs text-slate-400 mb-1">Email Address</label>
                                    <input
                                        type="email"
                                        name="email"
                                        value="{{ old('email') }}"
                                        required
                                        class="w-full bg-slate-950/70 border border-slate-700 rounded-xl px-3 py-2.5 outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500"
                                        placeholder="Enter email"
                                    >
                                </div>

                                <div class="grid sm:grid-cols-2 gap-4 mt-4">
                                    <div>
                                        <label class="block text-xs text-slate-400 mb-1">Password</label>
                                        <input
                                            type="password"
                                            name="password"
                                            required
                                            class="w-full bg-slate-950/70 border border-slate-700 rounded-xl px-3 py-2.5 outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500"
                                            placeholder="Enter password"
                                        >
                                    </div>

                                    <div>
                                        <label class="block text-xs text-slate-400 mb-1">Confirm Password</label>
                                        <input
                                            type="password"
                                            name="password_confirmation"
                                            required
                                            class="w-full bg-slate-950/70 border border-slate-700 rounded-xl px-3 py-2.5 outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500"
                                            placeholder="Confirm password"
                                        >
                                    </div>
                                </div>
                            </div>

                            <!-- STEP 2 : BUSINESS -->
                            <div class="form-step hidden" data-step="2">
                                <div>
                                    <label class="block text-xs text-slate-400 mb-1">Business / Showroom Name</label>
                                    <input
                                        type="text"
                                        name="business_name"
                                        value="{{ old('business_name') }}"
                                        required
                                        class="w-full bg-slate-950/70 border border-slate-700 rounded-xl px-3 py-2.5 outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500"
                                        placeholder="Enter showroom name"
                                    >
                                </div>

                                <div class="grid sm:grid-cols-2 gap-4 mt-4">
                                    <div>
                                        <label class="block text-xs text-slate-400 mb-1">Business Email</label>
                                        <input
                                            type="email"
                                            name="business_email"
                                            value="{{ old('business_email') }}"
                                            required
                                            class="w-full bg-slate-950/70 border border-slate-700 rounded-xl px-3 py-2.5 outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500"
                                            placeholder="Business email"
                                        >
                                    </div>

                                    <div>
                                        <label class="block text-xs text-slate-400 mb-1">Mobile Number</label>
                                        <input
                                            type="text"
                                            name="mobile"
                                            value="{{ old('mobile') }}"
                                            required
                                            class="w-full bg-slate-950/70 border border-slate-700 rounded-xl px-3 py-2.5 outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500"
                                            placeholder="Business mobile number"
                                        >
                                    </div>
                                </div>

                                <div class="grid sm:grid-cols-2 gap-4 mt-4">
                                    <div>
                                        <label class="block text-xs text-slate-400 mb-1">GSTIN</label>
                                        <input
                                            type="text"
                                            name="gstin"
                                            value="{{ old('gstin') }}"
                                            class="w-full bg-slate-950/70 border border-slate-700 rounded-xl px-3 py-2.5 outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500"
                                            placeholder="Optional GSTIN"
                                        >
                                    </div>

                                    <div>
                                        <label class="block text-xs text-slate-400 mb-1">Business Type</label>
                                        <select
                                            name="type"
                                            class="w-full bg-slate-950/70 border border-slate-700 rounded-xl px-3 py-2.5 outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 text-slate-100"
                                            required
                                        >
                                            <option value="" class="bg-slate-900 text-slate-300">Select Business Type</option>
                                            <option value="Jewellery" {{ old('type') == 'Jewellery' ? 'selected' : '' }} class="bg-slate-900">Jewellery</option>
                                            <option value="Gold Jewellery" {{ old('type') == 'Gold Jewellery' ? 'selected' : '' }} class="bg-slate-900">Gold Jewellery</option>
                                            <option value="Silver Jewellery" {{ old('type') == 'Silver Jewellery' ? 'selected' : '' }} class="bg-slate-900">Silver Jewellery</option>
                                            <option value="Diamond Jewellery" {{ old('type') == 'Diamond Jewellery' ? 'selected' : '' }} class="bg-slate-900">Diamond Jewellery</option>
                                            <option value="Retail" {{ old('type') == 'Retail' ? 'selected' : '' }} class="bg-slate-900">Retail</option>
                                            <option value="Wholesale" {{ old('type') == 'Wholesale' ? 'selected' : '' }} class="bg-slate-900">Wholesale</option>
                                            <option value="Manufacturer" {{ old('type') == 'Manufacturer' ? 'selected' : '' }} class="bg-slate-900">Manufacturer</option>
                                            <option value="Other" {{ old('type') == 'Other' ? 'selected' : '' }} class="bg-slate-900">Other</option>
                                        </select>
                                    </div>
                                                                    </div>

                                <div class="mt-4">
                                    <label class="block text-xs text-slate-400 mb-1">Address</label>
                                    <textarea
                                        name="address"
                                        rows="3"
                                        class="w-full bg-slate-950/70 border border-slate-700 rounded-xl px-3 py-2.5 outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500"
                                        placeholder="Enter business address"
                                    >{{ old('address') }}</textarea>
                                </div>

                                <div class="grid sm:grid-cols-2 gap-4 mt-4">
                                    <div>
                                        <label class="block text-xs text-slate-400 mb-1">State</label>
                                        <input
                                            type="text"
                                            name="state"
                                            value="{{ old('state') }}"
                                            class="w-full bg-slate-950/70 border border-slate-700 rounded-xl px-3 py-2.5 outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500"
                                            placeholder="Enter state"
                                        >
                                    </div>

                                    <div>
                                        <label class="block text-xs text-slate-400 mb-1">State Code</label>
                                        <input
                                            type="text"
                                            name="state_code"
                                            value="{{ old('state_code') }}"
                                            class="w-full bg-slate-950/70 border border-slate-700 rounded-xl px-3 py-2.5 outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500"
                                            placeholder="Enter state code"
                                        >
                                    </div>
                                </div>
                            </div>

                            <!-- STEP 3 : BILLING -->
                            <div class="form-step hidden" data-step="3">
                                <div class="grid sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs text-slate-400 mb-1">GST Enabled</label>
                                        <select
                                            name="gst_enabled"
                                            class="w-full bg-slate-950/70 border border-slate-700 rounded-xl px-3 py-2.5 outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500"
                                        >
                                            <option value="1" {{ old('gst_enabled', '1') == '1' ? 'selected' : '' }}>Yes</option>
                                            <option value="0" {{ old('gst_enabled') == '0' ? 'selected' : '' }}>No</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-xs text-slate-400 mb-1">Invoice Base Prefix</label>
                                        <input
                                            type="text"
                                            name="invoice_base_prefix"
                                            value="{{ old('invoice_base_prefix', 'RV/SL') }}"
                                            class="w-full bg-slate-950/70 border border-slate-700 rounded-xl px-3 py-2.5 outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500"
                                            placeholder="RV/SL"
                                        >
                                    </div>
                                </div>

                                <div class="grid sm:grid-cols-2 gap-4 mt-4">
                                    <div>
                                        <label class="block text-xs text-slate-400 mb-1">Rounding Mode</label>
                                        <select
                                            name="rounding_mode"
                                            class="w-full bg-slate-950/70 border border-slate-700 rounded-xl px-3 py-2.5 outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500"
                                        >
                                            <option value="none" {{ old('rounding_mode') == 'none' ? 'selected' : '' }}>None</option>
                                            <option value="nearest" {{ old('rounding_mode', 'nearest') == 'nearest' ? 'selected' : '' }}>Nearest</option>
                                            <option value="up" {{ old('rounding_mode') == 'up' ? 'selected' : '' }}>Up</option>
                                            <option value="down" {{ old('rounding_mode') == 'down' ? 'selected' : '' }}>Down</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-xs text-slate-400 mb-1">Rounding Step</label>
                                        <input
                                            type="number"
                                            step="0.01"
                                            name="rounding_step"
                                            value="{{ old('rounding_step', '1.00') }}"
                                            class="w-full bg-slate-950/70 border border-slate-700 rounded-xl px-3 py-2.5 outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500"
                                            placeholder="1.00"
                                        >
                                    </div>
                                </div>

                                <div class="flex items-start gap-3 pt-4">
                                    <input
                                        id="terms"
                                        type="checkbox"
                                        name="terms"
                                        value="1"
                                        required
                                        class="mt-1 rounded border-slate-700 bg-slate-950 text-emerald-500 focus:ring-emerald-500"
                                    >
                                    <label for="terms" class="text-xs text-slate-400 leading-5">
                                        I agree to the
                                        <a href="#" class="text-emerald-400 hover:text-emerald-300">Terms & Conditions</a>
                                        and
                                        <a href="#" class="text-emerald-400 hover:text-emerald-300">Privacy Policy</a>.
                                    </label>
                                </div>
                            </div>

                            <!-- Buttons -->
                            <div class="flex items-center justify-between gap-3 pt-2">
                                <button
                                    type="button"
                                    id="prevBtn"
                                    class="hidden px-5 py-2.5 rounded-xl border border-slate-700 hover:border-emerald-400 hover:text-emerald-300 transition"
                                >
                                    Back
                                </button>

                                <div class="ml-auto flex items-center gap-3">
                                    <button
                                        type="button"
                                        id="nextBtn"
                                        class="px-5 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-900 font-semibold text-sm shadow-lg shadow-emerald-500/20 transition"
                                    >
                                        Next
                                    </button>

                                    <button
                                        type="submit"
                                        id="submitBtn"
                                        class="hidden px-5 py-2.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-900 font-semibold text-sm shadow-lg shadow-cyan-500/20 transition"
                                    >
                                        Create Account
                                    </button>
                                </div>
                            </div>

                            <p class="text-sm text-slate-400 text-center pt-2">
                                Already have an account?
                                <a href="{{ route('login') }}" class="text-cyan-300 hover:text-cyan-200 font-medium">
                                    Login here
                                </a>
                            </p>
                        </form>
                    </div>

                    <div class="mt-4 text-center text-xs text-slate-500">
                        Need help? Call or WhatsApp:
                        <a href="https://wa.me/917753800444" target="_blank" class="text-emerald-400 hover:text-emerald-300">
                            +91-7753800444
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="border-t border-slate-800 bg-slate-950 py-5">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center justify-between gap-3 text-xs text-slate-500">
            <div>
                © {{ date('Y') }} MyVictory Billing · Powered by Real Victory Groups
            </div>
            <div class="flex flex-wrap gap-4">
                <a href="#" class="hover:text-emerald-400">Terms & Conditions</a>
                <a href="#" class="hover:text-emerald-400">Privacy Policy</a>
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

        let currentStep = 0;

        const labels = [
            'User Details',
            'Business Details',
            'Billing Setup'
        ];

        function showStep(index) {
            steps.forEach((step, i) => {
                step.classList.toggle('hidden', i !== index);
            });

            currentStepText.textContent = index + 1;
            currentStepLabel.textContent = labels[index];
            progressBar.style.width = ((index + 1) / steps.length * 100) + '%';

            if (index === 0) {
                prevBtn.classList.add('hidden');
            } else {
                prevBtn.classList.remove('hidden');
            }

            if (index === steps.length - 1) {
                nextBtn.classList.add('hidden');
                submitBtn.classList.remove('hidden');
            } else {
                nextBtn.classList.remove('hidden');
                submitBtn.classList.add('hidden');
            }
        }

        function validateStep(index) {
            const currentInputs = steps[index].querySelectorAll('input, select, textarea');

            for (let input of currentInputs) {
                if (!input.checkValidity()) {
                    input.reportValidity();
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

            return true;
        }

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
    </script>

</body>
</html>