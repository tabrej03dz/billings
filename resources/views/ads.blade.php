@extends('frontend.layout')

@section('content')
<style>
    :root {
        --mv-blue: #2624CC;
        --mv-blue-dark: #1d1bb4;
        --mv-purple: #6c63ff;
        --mv-cyan: #67d9ff;
        --mv-dark: #242938;
        --mv-muted: #697386;
        --mv-soft: #f7f7ff;
        --mv-soft-blue: #f0efff;
        --mv-border: #eceef3;
    }

    html {
        scroll-behavior: smooth;
    }

    body {
        overflow-x: hidden;
    }

    .mv-container {
        width: min(1180px, calc(100% - 32px));
        margin-inline: auto;
    }

    .mv-text-gradient {
        background: linear-gradient(90deg, var(--mv-blue), var(--mv-purple));
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
    }

    .mv-gradient {
        background: linear-gradient(90deg, var(--mv-blue), var(--mv-purple));
    }

    .mv-gradient-soft {
        background:
            radial-gradient(circle at 10% 10%, rgba(103, 217, 255, .22), transparent 24%),
            radial-gradient(circle at 90% 16%, rgba(38, 36, 204, .12), transparent 27%),
            linear-gradient(180deg, #ffffff 0%, #f7f8ff 100%);
    }

    .mv-shadow {
        box-shadow: 0 18px 55px rgba(36, 41, 56, .09);
    }

    .mv-card {
        border: 1px solid var(--mv-border);
        background: #fff;
        border-radius: 22px;
        transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
    }

    .mv-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 45px rgba(36, 41, 56, .10);
        border-color: rgba(38, 36, 204, .28);
    }

    .mv-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 9px;
        border-radius: 10px;
        padding: 14px 22px;
        font-weight: 800;
        transition: all .25s ease;
    }

    .mv-button-primary {
        color: #fff;
        background: linear-gradient(90deg, var(--mv-blue), #4f4df0);
        box-shadow: 0 12px 25px rgba(38, 36, 204, .28);
    }

    .mv-button-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 16px 30px rgba(38, 36, 204, .35);
    }

    .mv-button-outline {
        color: var(--mv-dark);
        background: #fff;
        border: 1px solid #dfe2e8;
    }

    .mv-button-outline:hover {
        border-color: var(--mv-blue);
        color: var(--mv-blue);
    }

    .mv-section-title {
        color: var(--mv-dark);
        font-weight: 900;
        line-height: 1.12;
        letter-spacing: -.025em;
    }

    .mv-muted {
        color: var(--mv-muted);
    }

    .mv-icon-box {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: grid;
        place-items: center;
        background: var(--mv-soft-blue);
        color: var(--mv-blue);
        flex: 0 0 auto;
    }

    .mv-check {
        width: 21px;
        height: 21px;
        border-radius: 50%;
        display: inline-grid;
        place-items: center;
        color: #fff;
        background: var(--mv-blue);
        font-size: 12px;
        font-weight: 900;
        flex: 0 0 auto;
        margin-top: 2px;
    }

    .mv-hero-device {
        position: relative;
        min-height: 480px;
        border-radius: 34px;
        background:
            radial-gradient(circle at 90% 10%, rgba(103, 217, 255, .42), transparent 25%),
            linear-gradient(145deg, #f0f1ff, #ffffff);
        border: 1px solid #dedfff;
        overflow: hidden;
    }

    .mv-main-screen {
        position: absolute;
        width: 66%;
        left: 18%;
        top: 13%;
        border-radius: 18px;
        border: 7px solid #202532;
        box-shadow: 0 24px 45px rgba(36, 41, 56, .22);
        transform: perspective(900px) rotateY(-7deg);
        background: white;
    }

    .mv-phone-screen {
        position: absolute;
        width: 25%;
        right: 7%;
        bottom: 7%;
        border-radius: 24px;
        border: 6px solid #202532;
        box-shadow: 0 20px 38px rgba(36, 41, 56, .18);
        background: #fff;
    }

    .mv-floating-card {
        position: absolute;
        background: #fff;
        border: 1px solid #e4e4ff;
        box-shadow: 0 12px 30px rgba(36, 41, 56, .12);
        border-radius: 14px;
        padding: 12px 15px;
        z-index: 4;
    }

    .mv-marquee {
        overflow: hidden;
        mask-image: linear-gradient(to right, transparent, black 8%, black 92%, transparent);
    }

    .mv-marquee-track {
        width: max-content;
        display: flex;
        gap: 16px;
        animation: mvMarquee 34s linear infinite;
    }

    .mv-marquee:hover .mv-marquee-track {
        animation-play-state: paused;
    }

    @keyframes mvMarquee {
        from { transform: translateX(0); }
        to { transform: translateX(-50%); }
    }

    .mv-business-pill {
        white-space: nowrap;
        background: #fff;
        border: 1px solid var(--mv-border);
        border-radius: 999px;
        padding: 11px 18px;
        color: var(--mv-dark);
        font-weight: 750;
        box-shadow: 0 7px 18px rgba(36, 41, 56, .04);
    }

    .mv-plan-popular {
        border: 2px solid var(--mv-blue);
        background: linear-gradient(180deg, #f5f5ff 0%, #ffffff 100%);
        transform: translateY(-10px);
    }

    .mv-step-line::after {
        content: "";
        position: absolute;
        height: 2px;
        background: linear-gradient(90deg, var(--mv-blue), var(--mv-purple));
        top: 31px;
        left: calc(50% + 42px);
        right: calc(-50% + 42px);
        opacity: .32;
    }

    .mv-step-line:last-child::after {
        display: none;
    }

    .mv-faq-content {
        max-height: 0;
        overflow: hidden;
        transition: max-height .3s ease;
    }

    .mv-faq-item.active .mv-faq-content {
        max-height: 230px;
    }

    .mv-faq-item.active .mv-faq-arrow {
        transform: rotate(180deg);
    }

    .mv-faq-arrow {
        transition: transform .25s ease;
    }

    /* Guided tour */
    .mv-tour-overlay {
        position: fixed;
        inset: 0;
        background: rgba(20, 24, 34, .70);
        backdrop-filter: blur(2px);
        z-index: 9998;
        display: none;
    }

    .mv-tour-overlay.active {
        display: block;
    }

    .mv-tour-highlight {
        position: relative !important;
        z-index: 9999 !important;
        border-radius: 16px;
        box-shadow:
            0 0 0 5px rgba(255, 255, 255, .95),
            0 0 0 9px rgba(38, 36, 204, .95),
            0 18px 60px rgba(0, 0, 0, .30) !important;
        pointer-events: none;
    }

    .mv-tour-box {
        position: fixed;
        z-index: 10000;
        width: min(360px, calc(100vw - 28px));
        background: #fff;
        border-radius: 18px;
        padding: 20px;
        box-shadow: 0 25px 70px rgba(0, 0, 0, .28);
        display: none;
    }

    .mv-tour-box.active {
        display: block;
    }

    .mv-tour-progress {
        height: 5px;
        background: #f0f1f4;
        border-radius: 99px;
        overflow: hidden;
    }

    .mv-tour-progress span {
        display: block;
        height: 100%;
        background: linear-gradient(90deg, var(--mv-blue), var(--mv-purple));
        border-radius: 99px;
        transition: width .25s ease;
    }

    .mv-tour-restart {
        position: fixed;
        right: 18px;
        bottom: 92px;
        z-index: 90;
        width: 48px;
        height: 48px;
        border-radius: 50%;
        display: grid;
        place-items: center;
        background: #fff;
        color: var(--mv-blue);
        border: 1px solid #d8d8ff;
        box-shadow: 0 12px 28px rgba(36, 41, 56, .18);
        font-size: 21px;
    }

    .mv-sticky-cta {
        position: fixed;
        left: 50%;
        bottom: 18px;
        transform: translateX(-50%);
        z-index: 80;
        width: min(420px, calc(100% - 26px));
    }

    @media (max-width: 1023px) {
        .mv-hero-device {
            min-height: 420px;
        }

        .mv-plan-popular {
            transform: none;
        }

        .mv-step-line::after {
            display: none;
        }
    }

    @media (max-width: 639px) {
        .mv-container {
            width: min(100% - 22px, 1180px);
        }

        .mv-hero-device {
            min-height: 335px;
            border-radius: 24px;
        }

        .mv-main-screen {
            width: 74%;
            left: 10%;
            top: 15%;
            border-width: 4px;
        }

        .mv-phone-screen {
            width: 29%;
            right: 4%;
            bottom: 5%;
            border-width: 4px;
        }

        .mv-floating-card {
            padding: 8px 10px;
            font-size: 11px;
        }

        .mv-tour-restart {
            bottom: 86px;
        }
    }
</style>

<main id="top" class="bg-white text-slate-800">

    {{-- TOP PROMO BAR --}}
    <div class="mv-gradient text-white text-center text-xs sm:text-sm font-extrabold py-2 px-3">
        ✨ Ab har business banega digital! ✨
    </div>

    {{-- HERO --}}
    <section class="mv-gradient-soft overflow-hidden">
        <div class="mv-container py-14 lg:py-20">
            <div class="grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full bg-white border border-[#d8d6ff] px-4 py-2 text-xs sm:text-sm font-extrabold text-[#2624CC] shadow-sm">
                        <span class="w-2 h-2 rounded-full bg-[#2624CC]"></span>
                        Trusted billing platform for growing businesses
                    </div>

                    <h1 class="mt-6 text-4xl sm:text-5xl lg:text-[62px] mv-section-title">
                        Best GST Billing Software for
                        <span class="mv-text-gradient">Small Businesses</span>
                    </h1>

                    <p class="mt-5 text-base sm:text-lg mv-muted leading-8 max-w-xl">
                        Invoice banayein, stock manage karein, sale-purchase track karein aur professional bill
                        WhatsApp par share karein — sab kuch ek simple, fast aur secure system me.
                    </p>

                    <div class="mt-8 flex flex-wrap gap-3" id="tour-demo-button">
                        <a href="#contact" class="mv-button mv-button-primary">
                            Start 7 Days Free Trial
                            <span>→</span>
                        </a>
                        <a href="https://wa.me/917753800444" target="_blank" class="mv-button mv-button-outline">
                            WhatsApp Demo
                        </a>
                    </div>

                    <div class="mt-6 flex flex-wrap items-center gap-x-5 gap-y-2 text-xs sm:text-sm mv-muted">
                        <span class="inline-flex items-center gap-2"><span class="text-emerald-500">●</span> Free setup support</span>
                        <span class="inline-flex items-center gap-2"><span class="text-emerald-500">●</span> Secure cloud backup</span>
                        <span class="inline-flex items-center gap-2"><span class="text-emerald-500">●</span> Mobile friendly</span>
                    </div>

                    <div class="mt-8 flex items-center gap-4">
                        <div class="flex -space-x-3">
                            @foreach(['RK','AS','VG','MS'] as $avatar)
                                <div class="w-10 h-10 rounded-full bg-white border-2 border-white shadow flex items-center justify-center text-xs font-black text-[#2624CC]">
                                    {{ $avatar }}
                                </div>
                            @endforeach
                        </div>
                        <div>
                            <div class="text-yellow-400 tracking-wide">★★★★★</div>
                            <p class="text-xs mv-muted">Loved by retailers, wholesalers and service businesses</p>
                        </div>
                    </div>
                </div>

                <div class="mv-hero-device mv-shadow" id="tour-dashboard">
                    <div class="absolute top-5 left-5 w-16 h-16 rounded-full bg-[#67d9ff]/35"></div>
                    <div class="absolute bottom-10 right-16 w-24 h-24 rounded-full bg-[#2624CC]/10"></div>

                    <img
                        src="https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?auto=format&fit=crop&w=1200&q=85"
                        alt="Billing dashboard preview"
                        class="mv-main-screen h-[245px] sm:h-[310px] object-cover"
                    >

                    <img
                        src="https://images.unsplash.com/photo-1556742502-ec7c0e9f34b1?auto=format&fit=crop&w=500&q=85"
                        alt="Mobile billing preview"
                        class="mv-phone-screen h-[165px] sm:h-[215px] object-cover"
                    >

                    <div class="mv-floating-card top-[12%] right-[4%]">
                        <div class="text-[10px] text-slate-400">Invoice Created</div>
                        <div class="font-black text-green-600">✓ ₹12,840</div>
                    </div>

                    <div class="mv-floating-card left-[3%] bottom-[14%]">
                        <div class="text-[10px] text-slate-400">Today Sale</div>
                        <div class="font-black text-[#242938]">₹1,27,270</div>
                    </div>

                    <div class="mv-floating-card right-[22%] bottom-[3%] hidden sm:block">
                        <div class="text-yellow-400 text-xs">★★★★★</div>
                        <div class="font-extrabold text-xs text-[#242938]">4.8 Rating</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- QUICK TRUST ICONS --}}
    <section class="bg-white border-y border-slate-100">
        <div class="mv-container py-7 grid grid-cols-2 lg:grid-cols-4 gap-5">
            @foreach([
                ['📱','Free Mobile Access','Business ko kahi se bhi manage karein'],
                ['💻','Multi-Device Login','Desktop aur mobile par synchronized data'],
                ['👥','Multi-User Roles','Staff ke liye controlled permissions'],
                ['📦','Inventory Control','Live stock aur low-stock tracking'],
            ] as $quick)
                <div class="flex gap-3 items-center">
                    <div class="mv-icon-box text-xl">{{ $quick[0] }}</div>
                    <div>
                        <h3 class="font-extrabold text-sm text-[#242938]">{{ $quick[1] }}</h3>
                        <p class="text-xs mv-muted mt-1">{{ $quick[2] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- BUSINESS JOURNEY --}}
    <section class="py-16 lg:py-20 bg-[#fbfbff]">
        <div class="mv-container">
            <div class="text-center max-w-3xl mx-auto">
                <p class="text-[#2624CC] uppercase tracking-[.2em] text-xs font-black">Simple & Fast</p>
                <h2 class="mv-section-title text-3xl lg:text-5xl mt-3">Your business journey, simplified</h2>
                <p class="mv-muted mt-4 leading-7">
                    Daily billing se lekar business reports tak, har important process ko easy steps me manage karein.
                </p>
            </div>

            <div class="grid md:grid-cols-4 gap-8 mt-12">
                @foreach([
                    ['01','Business Setup','Shop profile, tax settings aur invoice template set karein.'],
                    ['02','Add Products','Products, services, opening stock aur prices add karein.'],
                    ['03','Create Invoice','Customer choose karke seconds me invoice generate karein.'],
                    ['04','Track Growth','Sales, profit, stock aur outstanding reports dekhein.'],
                ] as $step)
                    <div class="relative text-center mv-step-line">
                        <div class="mx-auto w-16 h-16 rounded-full mv-gradient text-white grid place-items-center font-black text-lg shadow-lg shadow-indigo-100">
                            {{ $step[0] }}
                        </div>
                        <h3 class="font-black text-[#242938] text-lg mt-5">{{ $step[1] }}</h3>
                        <p class="mv-muted text-sm leading-6 mt-2">{{ $step[2] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- REAL EXPERIENCE --}}
    <section class="py-16 lg:py-20 bg-white">
        <div class="mv-container">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div>
                    <p class="text-[#2624CC] uppercase tracking-[.2em] text-xs font-black">Real-World Experience</p>
                    <h2 class="mv-section-title text-3xl lg:text-5xl mt-3">
                        Fast billing that works the way your shop works
                    </h2>
                    <p class="mv-muted mt-5 leading-8">
                        Simple interface, shortcut-friendly workflow, smart product search aur quick customer selection
                        se counter par billing fast hoti hai aur staff ko training dena easy rehta hai.
                    </p>

                    <div class="grid sm:grid-cols-2 gap-4 mt-8">
                        @foreach([
                            ['⚡','Quick Invoice','Few clicks me professional bill'],
                            ['🔍','Smart Search','Name, mobile ya barcode se product search'],
                            ['🧮','Auto Calculation','GST, discount, round-off automatically'],
                            ['📲','Instant Sharing','PDF bill WhatsApp par directly share'],
                        ] as $item)
                            <div class="mv-card p-5 flex gap-3">
                                <div class="mv-icon-box">{{ $item[0] }}</div>
                                <div>
                                    <h3 class="font-black text-[#242938]">{{ $item[1] }}</h3>
                                    <p class="text-sm mv-muted mt-1">{{ $item[2] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="relative">
                    <img
                        src="https://images.unsplash.com/photo-1556740758-90de374c12ad?auto=format&fit=crop&w=1100&q=85"
                        alt="Retail billing experience"
                        class="rounded-[28px] w-full h-[510px] object-cover mv-shadow"
                    >
                    <div class="absolute left-4 right-4 sm:left-8 sm:right-auto bottom-5 sm:bottom-8 bg-white rounded-2xl p-5 mv-shadow max-w-sm border border-slate-100">
                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 rounded-full mv-gradient text-white grid place-items-center font-black">✓</div>
                            <div>
                                <div class="text-xs mv-muted">Invoice processed successfully</div>
                                <div class="text-xl font-black text-[#242938]">Billing completed in 28 sec</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- WHY CHOOSE --}}
    <section class="py-16 lg:py-20 bg-[#f4f5ff]">
        <div class="mv-container">
            <div class="text-center max-w-3xl mx-auto">
                <p class="text-[#2624CC] uppercase tracking-[.2em] text-xs font-black">Why Choose Us</p>
                <h2 class="mv-section-title text-3xl lg:text-5xl mt-3">Built for Indian small businesses</h2>
                <p class="mv-muted mt-4">
                    Easy learning, affordable pricing, useful features aur reliable support ka perfect combination.
                </p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-5 mt-12">
                @foreach([
                    ['80%','Less Repetitive Work','Saved customer, product aur invoice data se daily entry fast hoti hai.'],
                    ['24×7','Cloud Availability','Data ko mobile ya desktop se securely access karein.'],
                    ['100%','Business Control','Sales, stock, receivables aur business activity ek jagah.'],
                    ['Easy','Staff Training','Simple interface ki wajah se team jaldi software use kar leti hai.'],
                ] as $value)
                    <div class="mv-card p-7">
                        <div class="text-3xl font-black mv-text-gradient">{{ $value[0] }}</div>
                        <h3 class="font-black text-lg text-[#242938] mt-4">{{ $value[1] }}</h3>
                        <p class="mv-muted text-sm leading-6 mt-3">{{ $value[2] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- FEATURE GRID --}}
    <section id="features" class="py-16 lg:py-20 bg-white">
        <div class="mv-container">
            <div class="text-center max-w-3xl mx-auto">
                <p class="text-[#2624CC] uppercase tracking-[.2em] text-xs font-black">Key Features</p>
                <h2 class="mv-section-title text-3xl lg:text-5xl mt-3">
                    Everything you need to run your business better
                </h2>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5 mt-12" id="tour-features">
                @foreach([
                    ['🧾','GST & Non-GST Invoices','Tax invoice, quotation, estimate, delivery challan aur credit note.'],
                    ['📦','Inventory Management','Opening stock, stock adjustment, purchase aur sale based quantity tracking.'],
                    ['🛍️','Sale & Purchase','Daily sale, purchase, returns aur payment records ko manage karein.'],
                    ['👥','Customer & Supplier','Party ledger, outstanding, transaction history aur contact details.'],
                    ['📸','Photo Smart Entry','Product ya customer photo based quick entry workflow.'],
                    ['📊','Business Reports','Sales, purchase, profit, stock, GST aur outstanding reports.'],
                    ['🏷️','Barcode Support','Barcode scan karke product jaldi find aur bill karein.'],
                    ['📲','WhatsApp Sharing','Invoice PDF, payment reminder aur details instantly share karein.'],
                    ['🔐','User Roles','Owner, manager aur staff ke liye separate access permissions.'],
                    ['☁️','Cloud Backup','Important business records ka secure online backup.'],
                    ['🖨️','Print Templates','A4, thermal aur professional invoice layouts.'],
                    ['🌐','Multi Business','Ek login se multiple businesses manage karne ki facility.'],
                ] as $feature)
                    <article class="mv-card p-6">
                        <div class="mv-icon-box text-xl">{{ $feature[0] }}</div>
                        <h3 class="font-black text-lg text-[#242938] mt-5">{{ $feature[1] }}</h3>
                        <p class="mv-muted text-sm leading-6 mt-3">{{ $feature[2] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- PRODUCT PREVIEW --}}
    <section class="py-16 lg:py-20 bg-[#f7f8ff]">
        <div class="mv-container">
            <div class="text-center max-w-3xl mx-auto">
                <p class="text-[#2624CC] uppercase tracking-[.2em] text-xs font-black">Complete Billing Solution</p>
                <h2 class="mv-section-title text-3xl lg:text-5xl mt-3">Clear dashboard, smarter business decisions</h2>
            </div>

            <div class="grid lg:grid-cols-[1.35fr_.65fr] gap-6 mt-12">
                <div class="mv-card p-3 sm:p-5 overflow-hidden">
                    <img
                        src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&w=1400&q=85"
                        alt="Business dashboard"
                        class="rounded-2xl w-full h-[360px] sm:h-[520px] object-cover"
                    >
                </div>

                <div class="grid sm:grid-cols-2 lg:grid-cols-1 gap-5">
                    @foreach([
                        ['Invoice Management','Create, print, download and share professional invoices.'],
                        ['Stock Reports','Track available stock, low stock and item movement.'],
                        ['Party Outstanding','Know exactly who has to pay and whom you have to pay.'],
                        ['Profit Overview','Review sales performance and business growth reports.'],
                    ] as $preview)
                        <div class="mv-card p-6">
                            <div class="flex gap-3">
                                <span class="mv-check">✓</span>
                                <div>
                                    <h3 class="font-black text-[#242938]">{{ $preview[0] }}</h3>
                                    <p class="mv-muted text-sm leading-6 mt-2">{{ $preview[1] }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- SETUP STEPS --}}
    <section class="py-16 lg:py-20 bg-white">
        <div class="mv-container">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div>
                    <p class="text-[#2624CC] uppercase tracking-[.2em] text-xs font-black">Quick Setup</p>
                    <h2 class="mv-section-title text-3xl lg:text-5xl mt-3">Start billing without technical confusion</h2>

                    <div class="mt-8 space-y-4">
                        @foreach([
                            ['1','Create your account','Mobile number aur basic details se account banayein.'],
                            ['2','Complete business profile','Logo, address, GST aur invoice preferences set karein.'],
                            ['3','Add products or import data','Products manually add karein ya available import option use karein.'],
                            ['4','Create your first invoice','Customer select karke invoice generate aur share karein.'],
                        ] as $setup)
                            <div class="flex gap-4 p-5 rounded-2xl border border-slate-200 bg-white">
                                <div class="w-10 h-10 rounded-full mv-gradient text-white grid place-items-center font-black shrink-0">{{ $setup[0] }}</div>
                                <div>
                                    <h3 class="font-black text-[#242938]">{{ $setup[1] }}</h3>
                                    <p class="mv-muted text-sm mt-1">{{ $setup[2] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="rounded-[30px] p-5 sm:p-8 bg-gradient-to-br from-[#2624CC] to-[#6c63ff] mv-shadow">
                    <div class="bg-[#242938] rounded-2xl p-3">
                        <img
                            src="https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=1200&q=85"
                            alt="Billing setup screen"
                            class="rounded-xl w-full h-[410px] object-cover"
                        >
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- COUNTER --}}
    <section class="py-10 bg-[#242938] text-white">
        <div class="mv-container">
            <div class="grid sm:grid-cols-3 gap-6 text-center">
                @foreach([
                    ['2,83,326+','Invoices Created'],
                    ['6,023+','Businesses Supported'],
                    ['1.5 Cr+','Transactions Managed'],
                ] as $stat)
                    <div>
                        <div class="text-3xl lg:text-4xl font-black">{{ $stat[0] }}</div>
                        <div class="text-slate-300 text-sm mt-2">{{ $stat[1] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- LANGUAGE / SUPPORT --}}
    <section class="py-16 lg:py-20 bg-[#f7f8ff]">
        <div class="mv-container">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div>
                    <p class="text-[#2624CC] uppercase tracking-[.2em] text-xs font-black">Easy for Everyone</p>
                    <h2 class="mv-section-title text-3xl lg:text-5xl mt-3">Simple interface and local support</h2>
                    <p class="mv-muted mt-5 leading-8">
                        Business owners aur staff ke liye easy navigation, clear labels aur guided onboarding.
                        Setup se lekar daily use tak support team aapki help karti hai.
                    </p>

                    <div class="grid sm:grid-cols-2 gap-4 mt-8">
                        @foreach([
                            ['हिंदी','Hindi friendly guidance'],
                            ['English','Clear English interface'],
                            ['Support','Phone and WhatsApp assistance'],
                            ['Training','Quick product walkthrough'],
                        ] as $language)
                            <div class="mv-card p-5">
                                <div class="font-black text-[#242938]">{{ $language[0] }}</div>
                                <div class="text-sm mv-muted mt-1">{{ $language[1] }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="relative">
                    <img
                        src="https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&w=1100&q=85"
                        alt="Customer support and training"
                        class="rounded-[30px] w-full h-[500px] object-cover mv-shadow"
                    >
                    <div class="absolute right-4 bottom-5 sm:right-7 sm:bottom-7 bg-white rounded-2xl p-5 mv-shadow border border-slate-100">
                        <div class="text-yellow-400">★★★★★</div>
                        <div class="font-black text-[#242938] mt-1">Friendly onboarding support</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- BUSINESS TYPES MARQUEE --}}
    <section class="py-16 bg-white overflow-hidden">
        <div class="mv-container">
            <div class="text-center max-w-3xl mx-auto">
                <p class="text-[#2624CC] uppercase tracking-[.2em] text-xs font-black">Made for Every Business</p>
                <h2 class="mv-section-title text-3xl lg:text-5xl mt-3">One billing platform for many industries</h2>
            </div>

            @php
                $businesses = [
                    'Garment Store','Footwear Shop','Cosmetic Store','Jewellery Shop','Mobile Shop',
                    'General Store','Gift Shop','Stationery Shop','Bakery','Cafe','Wholesaler',
                    'Electronics Store','Hardware Shop','Medical Retail','Service Business','Boutique'
                ];
            @endphp

            <div class="mv-marquee mt-10">
                <div class="mv-marquee-track">
                    @foreach(array_merge($businesses, $businesses) as $business)
                        <div class="mv-business-pill">✓ {{ $business }}</div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- PRICING --}}
    <section id="pricing" class="py-16 lg:py-20 bg-[#f5f7ff]">
        <div class="mv-container">
            <div class="text-center max-w-3xl mx-auto">
                <p class="text-[#2624CC] uppercase tracking-[.2em] text-xs font-black">Plans & Pricing</p>
                <h2 class="mv-section-title text-3xl lg:text-5xl mt-3">Affordable plans for every stage</h2>
                <p class="mv-muted mt-4">Start small and upgrade as your business grows.</p>
            </div>

            @if($plans->count())
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-7 mt-12" id="tour-pricing">
                    @foreach($plans as $plan)
                        @php $isPopular = (bool) $plan->is_recommended; @endphp

                        <article class="mv-card p-7 relative {{ $isPopular ? 'mv-plan-popular' : '' }}">
                            @if($isPopular)
                                <div class="absolute -top-4 left-1/2 -translate-x-1/2 bg-[#2624CC] text-white rounded-full px-4 py-2 text-xs font-black whitespace-nowrap">
                                    Most Recommended
                                </div>
                            @endif

                            <h3 class="text-xl font-black text-[#242938]">{{ $plan->name }}</h3>
                            <p class="text-sm mv-muted mt-2 min-h-[42px]">
                                {{ $plan->subtitle ?: ($plan->description ?: 'Perfect for growing businesses') }}
                            </p>

                            <div class="mt-6">
                                <span class="text-4xl font-black text-[#2624CC]">₹{{ number_format($plan->price, 0) }}</span>
                                <span class="text-sm mv-muted">
                                    / {{ $plan->duration_days >= 365 ? 'Year' : $plan->duration_days . ' Days' }}
                                </span>
                            </div>

                            <ul class="mt-7 space-y-3">
                                @forelse($plan->planFeatures as $feature)
                                    <li class="flex gap-3 text-sm text-slate-600">
                                        <span class="mv-check">✓</span>
                                        <span>{{ $feature->title }}</span>
                                    </li>
                                @empty
                                    @foreach(['GST Billing','Stock Management','Customer & Supplier Records','Invoice Print & WhatsApp Share'] as $defaultFeature)
                                        <li class="flex gap-3 text-sm text-slate-600">
                                            <span class="mv-check">✓</span>
                                            <span>{{ $defaultFeature }}</span>
                                        </li>
                                    @endforeach
                                @endforelse
                            </ul>

                            <div class="mt-8 grid gap-3">
                                <a
                                    href="{{ route('user.register', ['plan_id' => $plan->id, 'trial' => 1]) }}"
                                    class="mv-button mv-button-primary w-full"
                                >
                                    Start Free Trial
                                </a>

                                <a
                                    href="{{ route('plan.payment', ['plan' => $plan->id, 'trial' => 0]) }}"
                                    class="mv-button mv-button-outline w-full"
                                >
                                    Buy This Plan
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="mt-10 mv-card p-10 text-center">
                    <h3 class="text-2xl font-black text-[#242938]">Plans will be available soon</h3>
                    <p class="mv-muted mt-2">Please contact our team for pricing and demo.</p>
                </div>
            @endif
        </div>
    </section>

    {{-- TESTIMONIALS --}}
    <section class="py-16 lg:py-20 bg-white">
        <div class="mv-container">
            <div class="text-center max-w-3xl mx-auto">
                <p class="text-[#2624CC] uppercase tracking-[.2em] text-xs font-black">Customer Stories</p>
                <h2 class="mv-section-title text-3xl lg:text-5xl mt-3">What business owners say</h2>
                <div class="text-yellow-400 mt-3">★★★★★ <span class="text-slate-500 text-sm">4.8/5 average feedback</span></div>
            </div>

            <div class="grid md:grid-cols-2 gap-6 mt-12">
                @foreach([
                    ['Kishan Garments','Billing pehle se kaafi fast ho gayi hai. WhatsApp invoice aur customer history daily counter work me bahut useful hai.'],
                    ['Manu Electronics','Simple interface hai, staff ko samjhane me zyada time nahi laga. Stock aur sales report clearly milti hai.'],
                    ['Keshav Gift House','Mobile aur desktop dono par business data dekhna easy hai. Invoice print professional lagta hai.'],
                    ['Abhishek Retail','Customer outstanding aur stock tracking ek jagah milne se daily management better ho gaya.'],
                ] as $review)
                    <article class="mv-card p-7">
                        <div class="text-yellow-400 tracking-wide">★★★★★</div>
                        <p class="mt-4 text-slate-600 leading-7 italic">“{{ $review[1] }}”</p>
                        <div class="flex items-center gap-3 mt-6">
                            <div class="w-11 h-11 rounded-full mv-gradient text-white grid place-items-center font-black">
                                {{ strtoupper(substr($review[0], 0, 1)) }}
                            </div>
                            <div>
                                <h3 class="font-black text-[#242938]">{{ $review[0] }}</h3>
                                <p class="text-xs mv-muted">Verified Business User</p>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- FAQ --}}
    <section class="py-16 lg:py-20 bg-[#f7f8ff]">
        <div class="mv-container max-w-4xl">
            <div class="text-center">
                <p class="text-[#2624CC] uppercase tracking-[.2em] text-xs font-black">FAQs</p>
                <h2 class="mv-section-title text-3xl lg:text-5xl mt-3">Frequently asked questions</h2>
            </div>

            <div class="mt-10 space-y-3">
                @foreach([
                    ['Can I use it on mobile and desktop?','Yes. The billing system is designed to work on modern mobile and desktop browsers. Your exact device access can depend on the selected plan.'],
                    ['Does it support GST invoices?','Yes. You can create GST and non-GST invoices, apply tax, discount and generate professional invoice PDFs.'],
                    ['Can I manage stock and purchase?','Yes. Product stock, purchase entries, sales and stock movement can be managed from the same system.'],
                    ['Can invoices be shared on WhatsApp?','Yes. Generated invoice PDF can be downloaded and shared with the customer through WhatsApp.'],
                    ['Is training provided?','Yes. The team provides onboarding guidance and basic training so you can start billing confidently.'],
                    ['What happens after the free trial?','After the trial, you can select an available paid plan. Your access and premium features will depend on the chosen plan.'],
                ] as $index => $faq)
                    <div class="mv-faq-item mv-card overflow-hidden {{ $index === 0 ? 'active' : '' }}">
                        <button type="button" class="mv-faq-button w-full text-left px-5 sm:px-6 py-5 flex justify-between gap-4 font-black text-[#242938]">
                            <span>{{ $faq[0] }}</span>
                            <span class="mv-faq-arrow">⌄</span>
                        </button>
                        <div class="mv-faq-content">
                            <p class="px-5 sm:px-6 pb-6 mv-muted leading-7">{{ $faq[1] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- CONTACT --}}
    <section id="contact" class="py-16 lg:py-20 bg-white">
        <div class="mv-container">
            <div class="rounded-[30px] overflow-hidden border border-slate-200 mv-shadow">
                <div class="grid lg:grid-cols-2">
                    <div class="p-7 sm:p-10 lg:p-12 bg-[#242938] text-white">
                        <p class="text-[#6c63ff] uppercase tracking-[.2em] text-xs font-black">Book a Demo</p>
                        <h2 class="text-3xl lg:text-5xl font-black leading-tight mt-4">See MyVictory Billing in action</h2>
                        <p class="text-slate-300 mt-5 leading-8">
                            Form submit karein. Hamari team aapko billing, inventory, customer management,
                            reports aur invoice sharing ka guided demo degi.
                        </p>

                        <div class="mt-8 space-y-4">
                            <a href="https://wa.me/917753800444" target="_blank" class="block rounded-2xl bg-white/10 border border-white/10 p-5">
                                <div class="text-xs text-slate-400">Phone / WhatsApp</div>
                                <div class="text-xl font-black mt-1">+91 77538 00444</div>
                            </a>

                            <a href="mailto:support@myvictory.in" class="block rounded-2xl bg-white/10 border border-white/10 p-5">
                                <div class="text-xs text-slate-400">Email</div>
                                <div class="font-black mt-1">support@myvictory.in</div>
                            </a>

                            <div class="rounded-2xl bg-white/10 border border-white/10 p-5">
                                <div class="text-xs text-slate-400">Office</div>
                                <div class="font-semibold mt-1">
                                    73 Basement, Ekta Enclave Society, Lakhanpur, Khyora, Kanpur, Uttar Pradesh 208024
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-7 sm:p-10 lg:p-12 bg-[#f5f7ff]" id="tour-contact-form">
                        <h3 class="text-2xl font-black text-[#242938]">Request your free demo</h3>
                        <p class="mv-muted text-sm mt-2">Fill in your details and our team will contact you.</p>

                        <form action="{{ route('demo-requests.save') }}#contact" method="POST" class="mt-7 space-y-4">
                            @csrf

                            @if(session('success'))
                                <div class="bg-green-100 border border-green-300 text-green-700 px-4 py-3 rounded-xl">
                                    {{ session('success') }}
                                </div>
                            @endif

                            @if($errors->any())
                                <div class="bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded-xl">
                                    <ul class="list-disc list-inside">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <div class="grid sm:grid-cols-2 gap-4">
                                <input
                                    type="text"
                                    name="name"
                                    value="{{ old('name') }}"
                                    required
                                    placeholder="Your name"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-4 outline-none focus:border-[#2624CC] focus:ring-2 focus:ring-indigo-100"
                                >

                                <input
                                    type="text"
                                    name="mobile"
                                    value="{{ old('mobile') }}"
                                    required
                                    maxlength="10"
                                    pattern="[6-9][0-9]{9}"
                                    inputmode="numeric"
                                    placeholder="Mobile / WhatsApp"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-4 outline-none focus:border-[#2624CC] focus:ring-2 focus:ring-indigo-100"
                                >
                            </div>

                            <div class="grid sm:grid-cols-2 gap-4">
                                <input
                                    type="text"
                                    name="city"
                                    value="{{ old('city') }}"
                                    placeholder="City"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-4 outline-none focus:border-[#2624CC] focus:ring-2 focus:ring-indigo-100"
                                >

                                <input
                                    type="text"
                                    name="business_name"
                                    value="{{ old('business_name') }}"
                                    placeholder="Business / shop name"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-4 outline-none focus:border-[#2624CC] focus:ring-2 focus:ring-indigo-100"
                                >
                            </div>

                            <select
                                name="plan"
                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-4 outline-none focus:border-[#2624CC] focus:ring-2 focus:ring-indigo-100"
                            >
                                <option value="">Select interested plan</option>
                                @foreach($plans as $formPlan)
                                    <option
                                        value="{{ $formPlan->name }} - {{ $formPlan->price }}"
                                        {{ old('plan') == ($formPlan->name . ' - ' . $formPlan->price) ? 'selected' : '' }}
                                    >
                                        {{ $formPlan->name }} - ₹{{ number_format($formPlan->price, 0) }}
                                    </option>
                                @endforeach
                            </select>

                            <textarea
                                name="message"
                                rows="4"
                                placeholder="Tell us about your billing requirement"
                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-4 outline-none focus:border-[#2624CC] focus:ring-2 focus:ring-indigo-100"
                            >{{ old('message') }}</textarea>

                            <button type="submit" class="mv-button mv-button-primary w-full">
                                Submit Demo Request
                                <span>→</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- FINAL CTA --}}
    <section class="py-12 bg-[#242938] text-white">
        <div class="mv-container text-center">
            <h2 class="text-3xl lg:text-4xl font-black">Ready to make billing faster?</h2>
            <p class="text-slate-300 mt-3">Start your free trial and digitize your daily business operations.</p>
            <a href="#pricing" class="mv-button mv-button-primary mt-7">Start 7 Days Free Trial →</a>
        </div>
    </section>
</main>

{{-- Guided Tour UI --}}
<div id="mvTourOverlay" class="mv-tour-overlay"></div>

<div id="mvTourBox" class="mv-tour-box" role="dialog" aria-modal="true" aria-labelledby="mvTourTitle">
    <div class="flex items-center justify-between gap-4">
        <div class="text-xs uppercase tracking-[.18em] font-black text-[#2624CC]">Quick Guide</div>
        <button type="button" id="mvTourClose" class="w-8 h-8 rounded-full bg-slate-100 text-slate-600 font-black">×</button>
    </div>

    <h3 id="mvTourTitle" class="text-xl font-black text-[#242938] mt-3"></h3>
    <p id="mvTourText" class="mv-muted text-sm leading-6 mt-2"></p>

    <div class="mv-tour-progress mt-5">
        <span id="mvTourProgress"></span>
    </div>

    <div class="flex items-center justify-between gap-3 mt-5">
        <button type="button" id="mvTourSkip" class="text-sm font-bold text-slate-500">Skip Guide</button>
        <div class="flex gap-2">
            <button type="button" id="mvTourPrev" class="mv-button mv-button-outline !py-2.5 !px-4 text-sm">Back</button>
            <button type="button" id="mvTourNext" class="mv-button mv-button-primary !py-2.5 !px-4 text-sm">Next</button>
        </div>
    </div>
</div>

<button type="button" id="mvTourRestart" class="mv-tour-restart" aria-label="Start page guide" title="Start page guide">
    ?
</button>

<div class="mv-sticky-cta">
    <a href="#pricing" class="mv-button mv-button-primary w-full">
        Start 7 Days Free Trial
        <span>→</span>
    </a>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    /* FAQ */
    document.querySelectorAll('.mv-faq-button').forEach(function (button) {
        button.addEventListener('click', function () {
            const item = button.closest('.mv-faq-item');
            const wasActive = item.classList.contains('active');

            document.querySelectorAll('.mv-faq-item').forEach(function (faq) {
                faq.classList.remove('active');
            });

            if (!wasActive) {
                item.classList.add('active');
            }
        });
    });

    /* Guided tour */
    const tourSteps = [
        {
            target: '#tour-demo-button',
            title: 'Start your free trial',
            text: 'Yahan se visitor free trial ya WhatsApp demo start kar sakta hai.'
        },
        {
            target: '#tour-dashboard',
            title: 'See the billing experience',
            text: 'Hero preview visitor ko dashboard, invoice aur mobile usage ka quick idea deta hai.'
        },
        {
            target: '#tour-features',
            title: 'Explore all business features',
            text: 'GST billing, inventory, barcode, reports, WhatsApp sharing aur user roles ko yahan explain kiya gaya hai.'
        },
        {
            target: '#tour-pricing',
            title: 'Choose the right plan',
            text: 'Available plans, features, free trial aur direct purchase actions is section me milenge.'
        },
        {
            target: '#tour-contact-form',
            title: 'Book a guided demo',
            text: 'Visitor apni details submit karke demo aur setup support request kar sakta hai.'
        }
    ];

    const overlay = document.getElementById('mvTourOverlay');
    const box = document.getElementById('mvTourBox');
    const title = document.getElementById('mvTourTitle');
    const text = document.getElementById('mvTourText');
    const progress = document.getElementById('mvTourProgress');
    const nextButton = document.getElementById('mvTourNext');
    const prevButton = document.getElementById('mvTourPrev');
    const closeButton = document.getElementById('mvTourClose');
    const skipButton = document.getElementById('mvTourSkip');
    const restartButton = document.getElementById('mvTourRestart');

    let currentStep = 0;
    let currentTarget = null;

    function removeHighlight() {
        if (currentTarget) {
            currentTarget.classList.remove('mv-tour-highlight');
            currentTarget = null;
        }
    }

    function positionTourBox(target) {
        const rect = target.getBoundingClientRect();
        const boxWidth = Math.min(360, window.innerWidth - 28);
        const estimatedHeight = 285;
        const gap = 18;

        let left = rect.left + (rect.width / 2) - (boxWidth / 2);
        left = Math.max(14, Math.min(left, window.innerWidth - boxWidth - 14));

        let top = rect.bottom + gap;

        if (top + estimatedHeight > window.innerHeight) {
            top = rect.top - estimatedHeight - gap;
        }

        if (top < 14) {
            top = 14;
        }

        box.style.left = left + 'px';
        box.style.top = top + 'px';
    }

    function showStep(index) {
        removeHighlight();

        const step = tourSteps[index];
        const target = document.querySelector(step.target);

        if (!target) {
            finishTour();
            return;
        }

        currentStep = index;
        currentTarget = target;

        target.scrollIntoView({
            behavior: 'smooth',
            block: 'center'
        });

        window.setTimeout(function () {
            target.classList.add('mv-tour-highlight');
            title.textContent = step.title;
            text.textContent = step.text;
            progress.style.width = (((index + 1) / tourSteps.length) * 100) + '%';
            prevButton.style.visibility = index === 0 ? 'hidden' : 'visible';
            nextButton.textContent = index === tourSteps.length - 1 ? 'Finish' : 'Next';
            positionTourBox(target);
        }, 420);
    }

    function startTour() {
        overlay.classList.add('active');
        box.classList.add('active');
        document.body.style.overflow = 'hidden';
        showStep(0);
    }

    function finishTour() {
        removeHighlight();
        overlay.classList.remove('active');
        box.classList.remove('active');
        document.body.style.overflow = '';
        localStorage.setItem('myVictoryHomepageGuideSeen', '1');
    }

    nextButton.addEventListener('click', function () {
        if (currentStep >= tourSteps.length - 1) {
            finishTour();
        } else {
            showStep(currentStep + 1);
        }
    });

    prevButton.addEventListener('click', function () {
        if (currentStep > 0) {
            showStep(currentStep - 1);
        }
    });

    closeButton.addEventListener('click', finishTour);
    skipButton.addEventListener('click', finishTour);
    overlay.addEventListener('click', finishTour);
    restartButton.addEventListener('click', startTour);

    window.addEventListener('resize', function () {
        if (box.classList.contains('active') && currentTarget) {
            positionTourBox(currentTarget);
        }
    });

    if (!localStorage.getItem('myVictoryHomepageGuideSeen')) {
        window.setTimeout(startTour, 900);
    }
});
</script>
@endsection