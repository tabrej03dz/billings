<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0, maximum-scale=5.0">

    <title>MyVictory Billing - Smart Billing Software</title>

    <link rel="icon"
          type="image/png"
          href="{{ asset('asset/img/favicon.ico') }}">

    {{-- Google Analytics --}}
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-YR266T6QYC"></script>

    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }

        gtag('js', new Date());
        gtag('config', 'G-YR266T6QYC');
    </script>

    {{-- Tailwind CSS --}}
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
        html {
            scroll-behavior: smooth;
            scroll-padding-top: 85px;
        }

        body {
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
            text-rendering: optimizeLegibility;
        }

        [id] {
            scroll-margin-top: 85px;
        }

        .hero-bg {
            background:
                radial-gradient(
                    circle at 12% 12%,
                    rgba(160, 0, 204, 0.16),
                    transparent 34%
                ),
                radial-gradient(
                    circle at 90% 10%,
                    rgba(0, 98, 235, 0.16),
                    transparent 34%
                ),
                linear-gradient(
                    180deg,
                    #ffffff 0%,
                    #f5f7ff 48%,
                    #ffffff 100%
                );
        }

        .brand-gradient {
            background: linear-gradient(
                135deg,
                #A000CC 0%,
                #2624CC 48%,
                #0062EB 100%
            );
        }

        .brand-text {
            background: linear-gradient(
                135deg,
                #A000CC,
                #2624CC,
                #0062EB
            );

            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .soft-card {
            box-shadow: 0 24px 70px rgba(18, 19, 51, 0.12);
        }

        .image-mask {
            border-radius: 34px;
        }

        /*
        |--------------------------------------------------------------------------
        | Mobile menu animation
        |--------------------------------------------------------------------------
        */

        #mobileMenuWrapper {
            max-height: 0;
            opacity: 0;
            overflow: hidden;
            visibility: hidden;
            transform: translateY(-8px);

            transition:
                max-height 0.35s ease,
                opacity 0.25s ease,
                transform 0.25s ease,
                visibility 0.25s ease;
        }

        #mobileMenuWrapper.mobile-menu-open {
            max-height: 650px;
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        #mobileToggleIcon {
            transition: transform 0.25s ease;
        }

        #mobileToggle[aria-expanded="true"] #mobileToggleIcon {
            transform: rotate(90deg);
        }

        /*
        |--------------------------------------------------------------------------
        | Better tap behaviour
        |--------------------------------------------------------------------------
        */

        a,
        button {
            -webkit-tap-highlight-color: transparent;
        }

        .mobile-nav-link {
            transition:
                background-color 0.2s ease,
                color 0.2s ease,
                transform 0.2s ease;
        }

        .mobile-nav-link:active {
            transform: scale(0.98);
        }

        /*
        |--------------------------------------------------------------------------
        | Mobile responsive adjustments
        |--------------------------------------------------------------------------
        */

        @media (max-width: 639px) {
            html {
                scroll-padding-top: 72px;
            }

            [id] {
                scroll-margin-top: 72px;
            }

            .soft-card {
                box-shadow: 0 15px 45px rgba(18, 19, 51, 0.1);
            }

            .image-mask {
                border-radius: 22px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            html {
                scroll-behavior: auto;
            }

            *,
            *::before,
            *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }
    </style>
</head>

<body class="min-h-screen bg-white text-mvDark flex flex-col">

    {{-- =========================================================
        HEADER
    ========================================================== --}}
    <header
        id="mainHeader"
        class="sticky top-0 z-50 bg-white/95 backdrop-blur-xl border-b border-slate-200 shadow-sm lg:shadow-none">

        <div
            class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-[68px] sm:h-[76px] lg:h-20 flex items-center justify-between gap-3">

            {{-- Logo --}}
            <a
                href="{{ url('/') }}"
                class="flex min-w-0 items-center shrink-0"
                aria-label="MyVictory Billing Home">

                <img
                    src="{{ asset('asset/img/MY VICTORY LOGO 2.png') }}"
                    class="h-11 sm:h-14 lg:h-16 w-auto max-w-[175px] sm:max-w-[220px] object-contain"
                    alt="MyVictory Billing Logo">
            </a>

            {{-- Desktop Navigation --}}
            <nav
                class="hidden lg:flex items-center justify-center gap-6 xl:gap-8 text-sm font-bold text-slate-600"
                aria-label="Primary navigation">

                <a
                    href="#features"
                    class="transition hover:text-mvBlue">
                    Features
                </a>

                <a
                    href="#benefits"
                    class="transition hover:text-mvBlue">
                    Benefits
                </a>

                <a
                    href="#business"
                    class="transition hover:text-mvBlue">
                    Businesses
                </a>

                <a
                    href="#pricing"
                    class="transition hover:text-mvBlue">
                    Pricing
                </a>

                <a
                    href="#contact"
                    class="transition hover:text-mvBlue">
                    Demo
                </a>
            </nav>

            {{-- Desktop Buttons --}}
            <div class="hidden lg:flex items-center gap-2 xl:gap-3 shrink-0">

                <a
                    href="{{ url('/login') }}"
                    class="inline-flex items-center justify-center px-4 xl:px-5 py-2.5 xl:py-3 rounded-full border border-slate-300 text-sm font-bold text-mvDark transition hover:border-mvBlue hover:text-mvBlue">

                    Login
                </a>

                <a
                    href="{{ route('ad.register.page', ['open_register' => 1]) }}"
                    class="inline-flex items-center justify-center px-4 xl:px-5 py-2.5 xl:py-3 rounded-full bg-mvDark text-white text-sm font-bold transition hover:bg-slate-800">

                    Register
                </a>

                <a
                    href="#contact"
                    class="inline-flex items-center justify-center px-5 xl:px-6 py-2.5 xl:py-3 rounded-full brand-gradient text-white text-sm font-black shadow-lg shadow-blue-200 transition hover:-translate-y-0.5">

                    Book Free Demo
                </a>
            </div>

            {{-- Tablet buttons --}}
            <div class="hidden md:flex lg:hidden items-center gap-2 ml-auto">

                <a
                    href="{{ url('/login') }}"
                    class="inline-flex items-center justify-center px-4 py-2.5 rounded-full border border-slate-300 text-sm font-bold">

                    Login
                </a>

                <a
                    href="{{ route('ad.register.page', ['open_register' => 1]) }}"
                    class="inline-flex items-center justify-center px-4 py-2.5 rounded-full bg-mvDark text-white text-sm font-bold">

                    Register
                </a>
            </div>

            {{-- Mobile menu button --}}
            <button
                id="mobileToggle"
                type="button"
                aria-label="Open navigation menu"
                aria-controls="mobileMenuWrapper"
                aria-expanded="false"
                class="lg:hidden flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-slate-300 bg-white text-mvDark shadow-sm transition hover:border-mvBlue focus:outline-none focus:ring-2 focus:ring-mvBlue/20">

                <svg
                    id="mobileToggleIcon"
                    class="h-6 w-6"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2.2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    aria-hidden="true">

                    <path id="menuIconPath1" d="M4 6h16"></path>
                    <path id="menuIconPath2" d="M4 12h16"></path>
                    <path id="menuIconPath3" d="M4 18h16"></path>
                </svg>
            </button>
        </div>

        {{-- Mobile Menu --}}
        <div
            id="mobileMenuWrapper"
            class="lg:hidden bg-white border-t border-slate-200">

            <div class="max-w-7xl mx-auto px-4 sm:px-6 py-4">

                {{-- Navigation links --}}
                <nav
                    class="grid grid-cols-1 sm:grid-cols-2 gap-2"
                    aria-label="Mobile navigation">

                    <a
                        href="#features"
                        class="mobile-menu-link mobile-nav-link flex min-h-[48px] items-center justify-between rounded-xl bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700 hover:bg-blue-50 hover:text-mvBlue">

                        <span>Features</span>
                        <span aria-hidden="true">›</span>
                    </a>

                    <a
                        href="#benefits"
                        class="mobile-menu-link mobile-nav-link flex min-h-[48px] items-center justify-between rounded-xl bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700 hover:bg-blue-50 hover:text-mvBlue">

                        <span>Benefits</span>
                        <span aria-hidden="true">›</span>
                    </a>

                    <a
                        href="#business"
                        class="mobile-menu-link mobile-nav-link flex min-h-[48px] items-center justify-between rounded-xl bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700 hover:bg-blue-50 hover:text-mvBlue">

                        <span>Businesses</span>
                        <span aria-hidden="true">›</span>
                    </a>

                    <a
                        href="#pricing"
                        class="mobile-menu-link mobile-nav-link flex min-h-[48px] items-center justify-between rounded-xl bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700 hover:bg-blue-50 hover:text-mvBlue">

                        <span>Pricing</span>
                        <span aria-hidden="true">›</span>
                    </a>

                    <a
                        href="#contact"
                        class="mobile-menu-link mobile-nav-link flex min-h-[48px] items-center justify-between rounded-xl bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700 hover:bg-blue-50 hover:text-mvBlue sm:col-span-2">

                        <span>Book Demo</span>
                        <span aria-hidden="true">›</span>
                    </a>
                </nav>

                {{-- Divider --}}
                <div class="my-4 h-px bg-slate-200"></div>

                {{-- Mobile action buttons --}}
                <div class="grid grid-cols-2 gap-3">

                    <a
                        href="{{ url('/login') }}"
                        class="mobile-menu-link flex min-h-[50px] items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-black text-mvDark transition hover:border-mvBlue hover:text-mvBlue">

                        Login
                    </a>

                    <a
                        href="{{ route('ad.register.page', ['open_register' => 1]) }}"
                        class="mobile-menu-link flex min-h-[50px] items-center justify-center rounded-xl bg-mvDark px-4 py-3 text-sm font-black text-white transition hover:bg-slate-800">

                        Register
                    </a>

                    <a
                        href="#contact"
                        class="mobile-menu-link col-span-2 flex min-h-[52px] items-center justify-center gap-2 rounded-xl brand-gradient px-4 py-3 text-sm font-black text-white shadow-lg shadow-blue-200">

                        <svg
                            class="h-5 w-5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            aria-hidden="true">

                            <path d="M8 2v4"></path>
                            <path d="M16 2v4"></path>
                            <rect
                                width="18"
                                height="18"
                                x="3"
                                y="4"
                                rx="2">
                            </rect>
                            <path d="M3 10h18"></path>
                        </svg>

                        Book Free Demo
                    </a>
                </div>
            </div>
        </div>
    </header>

    {{-- =========================================================
        PAGE CONTENT
    ========================================================== --}}
    <div class="flex-1">
        @yield('content')
    </div>

    {{-- =========================================================
        FOOTER
    ========================================================== --}}
    <footer
        id="footer"
        class="bg-white border-t border-slate-200 py-8 sm:py-10 lg:py-12">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 items-start">

                {{-- Left column --}}
                <div>

                    <div class="flex items-center gap-3">

                        <img
                            src="{{ asset('asset/img/MY VICTORY LOGO 2.png') }}"
                            class="h-12 sm:h-14 w-auto max-w-[210px] object-contain"
                            alt="MyVictory Billing">
                    </div>

                    <p class="max-w-xl text-sm leading-6 text-slate-500 mt-3">
                        Smart and easy billing software for managing invoices,
                        customers, items and business records.
                    </p>

                    <p class="text-xs sm:text-sm leading-6 text-slate-500 mt-3">
                        © {{ date('Y') }} MyVictory Billing.
                        Powered by

                        <a
                            href="https://www.realvictorygroups.com/"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="font-bold text-mvBlue hover:underline">

                            Real Victory Groups
                        </a>
                    </p>

                    {{-- App download section --}}
                    <div class="mt-7">

                        <div class="font-black text-base sm:text-lg text-mvDark mb-3">
                            Download Our App
                        </div>

                        <div
                            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2 gap-3 max-w-xl">

                            {{-- App Store --}}
                            <a
                                href="https://apps.apple.com/in/app/my-victoryinvoice-billing-app/id6758130889"
                                target="_blank"
                                rel="noopener noreferrer"
                                aria-label="Download MyVictory Billing on the App Store"
                                class="group flex w-full min-h-[62px] items-center gap-3 rounded-xl bg-black text-white px-4 sm:px-5 py-2.5 transition hover:-translate-y-0.5 hover:shadow-lg">

                                <svg
                                    class="w-8 h-8 shrink-0 fill-white"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true">

                                    <path d="M17.05 12.04c-.03-3.01 2.46-4.46 2.57-4.53-1.4-2.05-3.58-2.33-4.35-2.36-1.85-.19-3.61 1.09-4.55 1.09-.94 0-2.39-1.06-3.93-1.03-2.02.03-3.89 1.18-4.93 2.99-2.1 3.64-.54 9.03 1.51 11.98 1 1.45 2.2 3.08 3.77 3.02 1.51-.06 2.08-.98 3.91-.98 1.83 0 2.34.98 3.94.95 1.63-.03 2.66-1.48 3.65-2.93 1.15-1.68 1.62-3.3 1.65-3.38-.04-.02-3.18-1.22-3.21-4.82z"></path>
                                    <path d="M14.06 3.2c.83-1 1.39-2.39 1.24-3.78-1.2.05-2.65.8-3.51 1.8-.77.89-1.45 2.31-1.27 3.67 1.34.1 2.71-.68 3.54-1.69z"></path>
                                </svg>

                                <span class="min-w-0 leading-tight">
                                    <span class="block text-[10px] sm:text-xs font-semibold text-slate-200">
                                        Download on the
                                    </span>

                                    <span class="block text-lg sm:text-xl font-black whitespace-nowrap">
                                        App Store
                                    </span>
                                </span>
                            </a>

                            {{-- Google Play --}}
                            <a
                                href="https://play.google.com/store/apps/details?id=com.myvictory.invoicebilling&hl=en_IN"
                                target="_blank"
                                rel="noopener noreferrer"
                                aria-label="Download MyVictory Billing on Google Play"
                                class="group flex w-full min-h-[62px] items-center gap-3 rounded-xl bg-black text-white px-4 sm:px-5 py-2.5 transition hover:-translate-y-0.5 hover:shadow-lg">

                                <svg
                                    class="w-8 h-8 shrink-0"
                                    viewBox="0 0 512 512"
                                    aria-hidden="true">

                                    <path
                                        fill="#00F076"
                                        d="M48 32l270 224L48 480z">
                                    </path>

                                    <path
                                        fill="#00D6FF"
                                        d="M48 32l270 224 66-66z">
                                    </path>

                                    <path
                                        fill="#FFD400"
                                        d="M318 256 48 480l336-158z">
                                    </path>

                                    <path
                                        fill="#FF3D00"
                                        d="M384 190l80 66-80 66-66-66z">
                                    </path>
                                </svg>

                                <span class="min-w-0 leading-tight">
                                    <span class="block text-[10px] sm:text-xs font-semibold text-slate-200">
                                        Get it on
                                    </span>

                                    <span class="block text-lg sm:text-xl font-black whitespace-nowrap">
                                        Google Play
                                    </span>
                                </span>
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Right column --}}
                <div class="lg:flex lg:justify-end">

                    <div class="w-full lg:max-w-md">

                        <h3 class="text-base sm:text-lg font-black text-mvDark">
                            Important Links
                        </h3>

                        <div
                            class="mt-4 grid grid-cols-1 min-[400px]:grid-cols-2 gap-2 sm:gap-x-8 sm:gap-y-3 text-sm font-semibold text-slate-600">

                            <a
                                href="{{ url('/privacy-policy') }}"
                                class="flex min-h-[42px] items-center rounded-lg px-3 py-2 transition hover:bg-blue-50 hover:text-mvBlue">

                                Privacy Policy
                            </a>

                            <a
                                href="{{ url('/terms-conditions') }}"
                                class="flex min-h-[42px] items-center rounded-lg px-3 py-2 transition hover:bg-blue-50 hover:text-mvBlue">

                                Terms & Conditions
                            </a>

                            <a
                                href="{{ url('/refund-policy') }}"
                                class="flex min-h-[42px] items-center rounded-lg px-3 py-2 transition hover:bg-blue-50 hover:text-mvBlue">

                                Refund Policy
                            </a>

                            <a
                                href="{{ url('/shipping-delivery-policy') }}"
                                class="flex min-h-[42px] items-center rounded-lg px-3 py-2 transition hover:bg-blue-50 hover:text-mvBlue">

                                Shipping & Delivery
                            </a>

                            <a
                                href="{{ url('/about-us') }}"
                                class="flex min-h-[42px] items-center rounded-lg px-3 py-2 transition hover:bg-blue-50 hover:text-mvBlue">

                                About Us
                            </a>

                            <a
                                href="{{ url('/contact-us') }}"
                                class="flex min-h-[42px] items-center rounded-lg px-3 py-2 transition hover:bg-blue-50 hover:text-mvBlue">

                                Contact Us
                            </a>

                            <a
                                href="{{ url('/pricing') }}"
                                class="flex min-h-[42px] items-center rounded-lg px-3 py-2 transition hover:bg-blue-50 hover:text-mvBlue">

                                Pricing
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer bottom --}}
            <div
                class="mt-8 pt-5 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-3 text-center sm:text-left">

                <p class="text-xs text-slate-400">
                    Secure billing. Simple management. Better business.
                </p>

                <a
                    href="#top"
                    class="inline-flex min-h-[40px] items-center gap-2 rounded-full bg-slate-100 px-4 py-2 text-xs font-black text-slate-600 transition hover:bg-blue-50 hover:text-mvBlue">

                    Back to top
                    <span aria-hidden="true">↑</span>
                </a>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const mobileToggle = document.getElementById('mobileToggle');
            const mobileMenuWrapper = document.getElementById('mobileMenuWrapper');
            const mobileMenuLinks = document.querySelectorAll('.mobile-menu-link');

            const menuPath1 = document.getElementById('menuIconPath1');
            const menuPath2 = document.getElementById('menuIconPath2');
            const menuPath3 = document.getElementById('menuIconPath3');

            if (!mobileToggle || !mobileMenuWrapper) {
                return;
            }

            /**
             * Mobile menu icon को hamburger या close icon बनाता है।
             */
            function updateMenuIcon(isOpen) {
                if (!menuPath1 || !menuPath2 || !menuPath3) {
                    return;
                }

                if (isOpen) {
                    menuPath1.setAttribute('d', 'M6 6l12 12');
                    menuPath2.setAttribute('d', 'M12 12h0');
                    menuPath3.setAttribute('d', 'M18 6L6 18');

                    mobileToggle.setAttribute(
                        'aria-label',
                        'Close navigation menu'
                    );
                } else {
                    menuPath1.setAttribute('d', 'M4 6h16');
                    menuPath2.setAttribute('d', 'M4 12h16');
                    menuPath3.setAttribute('d', 'M4 18h16');

                    mobileToggle.setAttribute(
                        'aria-label',
                        'Open navigation menu'
                    );
                }
            }

            /**
             * Mobile menu खोलता है।
             */
            function openMobileMenu() {
                mobileMenuWrapper.classList.add('mobile-menu-open');
                mobileToggle.setAttribute('aria-expanded', 'true');

                updateMenuIcon(true);
            }

            /**
             * Mobile menu बंद करता है।
             */
            function closeMobileMenu() {
                mobileMenuWrapper.classList.remove('mobile-menu-open');
                mobileToggle.setAttribute('aria-expanded', 'false');

                updateMenuIcon(false);
            }

            /**
             * Toggle button click.
             */
            mobileToggle.addEventListener('click', function (event) {
                event.stopPropagation();

                const isOpen =
                    mobileToggle.getAttribute('aria-expanded') === 'true';

                if (isOpen) {
                    closeMobileMenu();
                } else {
                    openMobileMenu();
                }
            });

            /**
             * Menu link click होने के बाद menu बंद करें।
             */
            mobileMenuLinks.forEach(function (link) {
                link.addEventListener('click', function () {
                    closeMobileMenu();
                });
            });

            /**
             * Menu के बाहर click होने पर menu बंद करें।
             */
            document.addEventListener('click', function (event) {
                const isOpen =
                    mobileToggle.getAttribute('aria-expanded') === 'true';

                if (!isOpen) {
                    return;
                }

                const clickedInsideMenu =
                    mobileMenuWrapper.contains(event.target);

                const clickedToggle =
                    mobileToggle.contains(event.target);

                if (!clickedInsideMenu && !clickedToggle) {
                    closeMobileMenu();
                }
            });

            /**
             * Escape key से menu बंद करें।
             */
            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    closeMobileMenu();
                }
            });

            /**
             * Desktop width होने पर mobile menu reset करें।
             */
            window.addEventListener('resize', function () {
                if (window.innerWidth >= 1024) {
                    closeMobileMenu();
                }
            });
        });
    </script>

</body>
</html>