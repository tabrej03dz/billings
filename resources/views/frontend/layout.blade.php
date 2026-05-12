<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MyVictory Billing - Smart Billing Software</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <script src="https://cdn.tailwindcss.com"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        mvPurple: '#A000CC',
                        mvBlue: '#2624CC',
                        mvDark: '#121333',
                        mvNavy: '#044490',
                        mvSky: '#0062EB',
                    }
                }
            }
        }
    </script>

    <style>
        html { scroll-behavior: smooth; }

        .hero-bg {
            background:
                radial-gradient(circle at 12% 12%, rgba(160,0,204,.16), transparent 34%),
                radial-gradient(circle at 90% 10%, rgba(0,98,235,.16), transparent 34%),
                linear-gradient(180deg, #ffffff 0%, #f5f7ff 48%, #ffffff 100%);
        }

        .brand-gradient {
            background: linear-gradient(135deg, #A000CC 0%, #2624CC 48%, #0062EB 100%);
        }

        .brand-text {
            background: linear-gradient(135deg, #A000CC, #2624CC, #0062EB);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .soft-card {
            box-shadow: 0 24px 70px rgba(18, 19, 51, .12);
        }

        .image-mask { border-radius: 34px; }
    </style>
</head>

<body class="bg-white text-mvDark">

<header class="sticky top-0 z-50 bg-white/90 backdrop-blur-xl border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 lg:px-8 h-20 flex items-center justify-between">
        {{-- <a href="#top" class="flex items-center gap-3">
            <img src="{{ asset('asset/img/logo.png') }}"
                 class="h-12 w-12 rounded-2xl border border-slate-200 shadow-sm"
                 alt="MyVictory">

            <div>
                <div class="font-black text-xl leading-none text-mvDark">MyVictory Billing</div>
                <div class="text-xs text-mvBlue font-semibold mt-1">Smart Billing. Simple Business.</div>
            </div>
        </a> --}}


        <a href="{{ url('/') }}" class="flex items-center">
            <img src="{{ asset('asset/img/MY VICTORY LOGO 2.png') }}"
                class="h-16 w-auto object-contain"
                alt="MyVictory Billing Logo">
        </a>

        <nav class="hidden lg:flex items-center gap-8 text-sm font-bold text-slate-600">
            <a href="#features" class="hover:text-mvBlue">Features</a>
            <a href="#benefits" class="hover:text-mvBlue">Benefits</a>
            <a href="#business" class="hover:text-mvBlue">Businesses</a>
            <a href="#pricing" class="hover:text-mvBlue">Pricing</a>
            <a href="#contact" class="hover:text-mvBlue">Demo</a>
        </nav>

        <div class="hidden md:flex items-center gap-3">
            <a href="/login" class="px-5 py-3 rounded-full border border-slate-300 text-sm font-bold hover:border-mvBlue">
                Login
            </a>

            <a href="user-register" class="px-5 py-3 rounded-full bg-mvDark text-white text-sm font-bold">
                Register
            </a>

            <a href="#contact" class="px-6 py-3 rounded-full brand-gradient text-white text-sm font-black shadow-lg shadow-blue-200">
                Book Free Demo
            </a>
        </div>

        <button id="mobileToggle" class="lg:hidden h-11 w-11 rounded-xl border border-slate-300 text-xl text-mvDark">☰</button>
    </div>

    <div id="mobileMenu" class="hidden lg:hidden bg-white border-t border-slate-200 px-4 py-4 space-y-3 text-sm font-semibold">
        <a href="#features" class="block">Features</a>
        <a href="#benefits" class="block">Benefits</a>
        <a href="#business" class="block">Businesses</a>
        <a href="#pricing" class="block">Pricing</a>
        <a href="#contact" class="block">Demo</a>
        <a href="/login" class="block text-center rounded-xl border px-4 py-3">Login</a>
        <a href="user-register" class="block text-center rounded-xl brand-gradient text-white px-4 py-3">Register</a>
    </div>
</header>

@yield('content')

<footer class="bg-white border-t border-slate-200 py-10">
    <div class="max-w-7xl mx-auto px-4 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">

            <!-- Left Column -->
            <div>
                <div class="font-black text-xl text-mvDark">MyVictory Billing</div>
                <p class="text-sm text-slate-500 mt-2">
                    © {{ date('Y') }} MyVictory Billing · Powered by <a href="https://www.realvictorygroups.com/" target="_blank" class="text-mvBlue hover:underline">Real Victory Groups</a>
                </p>

                <div class="mt-6">
                    <div class="font-black text-lg text-mvDark mb-3">Download Our App</div>

                    <div class="flex flex-col sm:flex-row gap-3">

                        <!-- App Store -->
                        <a href="https://apps.apple.com/in/app/my-victoryinvoice-billing-app/id6758130889"
                        target="_blank"
                        class="w-[245px] h-[60px] rounded-xl bg-black text-white flex items-center gap-3 px-5 hover:scale-[1.02] transition">
                            
                            <svg class="w-8 h-8 fill-white" viewBox="0 0 24 24">
                                <path d="M17.05 12.04c-.03-3.01 2.46-4.46 2.57-4.53-1.4-2.05-3.58-2.33-4.35-2.36-1.85-.19-3.61 1.09-4.55 1.09-.94 0-2.39-1.06-3.93-1.03-2.02.03-3.89 1.18-4.93 2.99-2.1 3.64-.54 9.03 1.51 11.98 1 1.45 2.2 3.08 3.77 3.02 1.51-.06 2.08-.98 3.91-.98 1.83 0 2.34.98 3.94.95 1.63-.03 2.66-1.48 3.65-2.93 1.15-1.68 1.62-3.3 1.65-3.38-.04-.02-3.18-1.22-3.21-4.82z"/>
                                <path d="M14.06 3.2c.83-1 1.39-2.39 1.24-3.78-1.2.05-2.65.8-3.51 1.8-.77.89-1.45 2.31-1.27 3.67 1.34.1 2.71-.68 3.54-1.69z"/>
                            </svg>

                            <span class="leading-tight">
                                <span class="block text-xs font-semibold">Download on the</span>
                                <span class="block text-xl font-black">App Store</span>
                            </span>
                        </a>

                        <!-- Google Play -->
                        <a href="https://play.google.com/store/apps/details?id=com.myvictory.invoicebilling&hl=en_IN"
                        target="_blank"
                        class="w-[245px] h-[60px] rounded-xl bg-black text-white flex items-center gap-3 px-5 hover:scale-[1.02] transition">

                            <svg class="w-8 h-8" viewBox="0 0 512 512">
                                <path fill="#00F076" d="M48 32l270 224L48 480z"/>
                                <path fill="#00D6FF" d="M48 32l270 224 66-66z"/>
                                <path fill="#FFD400" d="M318 256 48 480l336-158z"/>
                                <path fill="#FF3D00" d="M384 190l80 66-80 66-66-66z"/>
                            </svg>

                            <span class="leading-tight">
                                <span class="block text-xs font-semibold">Get it on</span>
                                <span class="block text-xl font-black">Google Play</span>
                            </span>
                        </a>

                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="lg:flex lg:justify-end">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-10 gap-y-3 text-sm font-semibold text-slate-600">
                    <a href="{{ url('/privacy-policy') }}" class="hover:text-mvBlue">Privacy Policy</a>
                    <a href="{{ url('/terms-conditions') }}" class="hover:text-mvBlue">Terms & Conditions</a>
                    <a href="{{ url('/refund-policy') }}" class="hover:text-mvBlue">Refund Policy</a>
                    <a href="{{ url('/shipping-delivery-policy') }}" class="hover:text-mvBlue">Shipping & Delivery Policy</a>
                    <a href="{{ url('/about-us') }}" class="hover:text-mvBlue">About Us</a>
                    <a href="{{ url('/contact-us') }}" class="hover:text-mvBlue">Contact Us</a>
                    <a href="{{ url('/pricing') }}" class="hover:text-mvBlue">Pricing Page</a>
                </div>
            </div>

        </div>
    </div>
</footer>

<script>
    const btn = document.getElementById('mobileToggle');
    const menu = document.getElementById('mobileMenu');

    if (btn && menu) {
        btn.addEventListener('click', () => {
            menu.classList.toggle('hidden');
        });
    }
</script>

</body>
</html>