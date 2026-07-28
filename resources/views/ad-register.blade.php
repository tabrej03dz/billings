@extends('frontend.layout')

@section('content')

<style>
    #typingText::after {
        content: "|";
        animation: blink 0.7s infinite;
        color: #2624CC;
    }

    @keyframes blink {
        50% {
            opacity: 0;
        }
    }

    body.registration-modal-open {
        overflow: hidden;
    }

    .registration-modal {
        position: fixed;
        inset: 0;
        z-index: 99999;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 16px;
    }

    .registration-modal.show {
        display: flex;
    }

    .registration-modal-backdrop {
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, 0.72);
        backdrop-filter: blur(5px);
    }

    .registration-modal-dialog {
        position: relative;
        z-index: 2;
        width: min(720px, 100%);
        max-height: calc(100vh - 32px);
        overflow-y: auto;
        border-radius: 28px;
        background: #ffffff;
        border: 1px solid rgba(255, 255, 255, 0.7);
        box-shadow: 0 30px 100px rgba(15, 23, 42, 0.35);
    }

    .registration-modal-header {
        position: sticky;
        top: 0;
        z-index: 15;
        padding: 20px 22px;
        color: #ffffff;
        background: linear-gradient(90deg, #2624cc, #6c63ff);
    }

    .registration-modal-close {
        position: absolute;
        top: 16px;
        right: 16px;
        width: 38px;
        height: 38px;
        display: grid;
        place-items: center;
        border-radius: 50%;
        color: #ffffff;
        background: rgba(255, 255, 255, 0.16);
        font-size: 22px;
        font-weight: 900;
        transition: 0.2s;
    }

    .registration-modal-close:hover {
        background: rgba(255, 255, 255, 0.28);
    }

    .registration-field {
        width: 100%;
        padding: 14px 15px;
        border: 1px solid #d8dce8;
        border-radius: 15px;
        color: #1e293b;
        background: #ffffff;
        outline: none;
        transition: 0.2s;
    }

    .registration-field:focus {
        border-color: #2624cc;
        box-shadow: 0 0 0 4px rgba(38, 36, 204, 0.1);
    }

    .registration-field[readonly],
    .registration-field:disabled {
        background: #f1f5f9;
        cursor: not-allowed;
    }

    .registration-step {
        display: none;
    }

    .registration-step.active {
        display: block;
    }

    .registration-step-pill {
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 10px 6px;
        color: #64748b;
        background: #ffffff;
        text-align: center;
        transition: 0.2s;
    }

    .registration-step-pill.active {
        border-color: #2624cc;
        color: #ffffff;
        background: linear-gradient(90deg, #2624cc, #6c63ff);
    }

    .registration-step-pill.done {
        border-color: rgba(38, 36, 204, 0.25);
        color: #2624cc;
        background: #f0efff;
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
        transition: 0.2s;
    }

    .registration-primary-button:hover {
        transform: translateY(-1px);
        box-shadow: 0 14px 28px rgba(38, 36, 204, 0.22);
    }

    .registration-primary-button:disabled {
        opacity: 0.6;
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
    }

    .registration-skip-button:hover {
        border-color: #2624cc;
        color: #2624cc;
        background: #f5f4ff;
    }

    .registration-message {
        display: none;
        margin-bottom: 18px;
        padding: 12px 15px;
        border-radius: 14px;
        font-size: 13px;
    }

    .registration-message.show {
        display: block;
    }

    .registration-message.success {
        color: #166534;
        border: 1px solid #bbf7d0;
        background: #f0fdf4;
    }

    .registration-message.error {
        color: #b91c1c;
        border: 1px solid #fecaca;
        background: #fef2f2;
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

    .registration-otp-loader.show {
        display: flex;
    }

    .registration-spinner {
        width: 16px;
        height: 16px;
        border: 2px solid #d9d8ff;
        border-top-color: #2624cc;
        border-radius: 50%;
        animation: registrationSpin 0.7s linear infinite;
    }

    @keyframes registrationSpin {
        to {
            transform: rotate(360deg);
        }
    }

    @media (max-width: 640px) {
        .registration-modal {
            align-items: flex-end;
            padding: 0;
        }

        .registration-modal-dialog {
            width: 100%;
            max-height: 94vh;
            border-radius: 25px 25px 0 0;
        }

        .registration-modal-header {
            padding: 18px;
        }
    }
</style>

<main id="top">

    {{-- HERO --}}
    <section class="hero-bg relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 lg:px-8 py-16 lg:py-24">
            <div class="grid lg:grid-cols-2 gap-14 items-center">

                <div>
                    <div class="inline-flex items-center gap-2 rounded-full bg-white border border-[#d8d6ff] shadow-sm px-5 py-2 text-sm font-black text-mvBlue mb-6">
                        <span class="h-2.5 w-2.5 rounded-full brand-gradient"></span>
                        Smart Billing Software for Modern Shops
                    </div>

                    <h1 class="min-h-[150px] sm:min-h-[160px] lg:min-h-[150px] text-4xl sm:text-5xl lg:text-7xl font-black leading-[1.05] tracking-tight text-mvDark">
                        <span id="typingText"></span>
                    </h1>

                    <p class="mt-6 text-lg text-slate-600 max-w-xl leading-8">
                        MyVictory Billing GST invoice, sale and purchase management,
                        stock management, customer management, WhatsApp invoice sharing
                        aur photo-based smart entry features ke saath modern shops ke
                        liye complete billing software hai.
                    </p>

                    <div class="mt-8 flex flex-wrap gap-4">
                        <button type="button"
                                class="open-registration-modal px-8 py-4 rounded-full brand-gradient text-white font-black shadow-xl shadow-blue-200 hover:opacity-90">
                            Create Free Account
                        </button>

                        <a href="https://wa.me/917753800444"
                           target="_blank"
                           class="px-8 py-4 rounded-full bg-white border border-slate-300 font-black hover:border-mvBlue text-mvDark">
                            WhatsApp Now
                        </a>
                    </div>

                    <div class="mt-10 grid grid-cols-3 gap-4 max-w-xl">
                        <div class="bg-white rounded-3xl p-5 border border-slate-200 soft-card">
                            <div class="text-2xl font-black text-mvBlue">₹999</div>
                            <p class="text-xs text-slate-500 mt-1">Starting / Year</p>
                        </div>

                        <div class="bg-white rounded-3xl p-5 border border-slate-200 soft-card">
                            <div class="text-2xl font-black text-mvBlue">Photo</div>
                            <p class="text-xs text-slate-500 mt-1">Smart Entry</p>
                        </div>

                        <div class="bg-white rounded-3xl p-5 border border-slate-200 soft-card">
                            <div class="text-2xl font-black text-mvBlue">Cloud</div>
                            <p class="text-xs text-slate-500 mt-1">Any Device</p>
                        </div>
                    </div>
                </div>

                <div class="relative">
                    <div class="absolute -top-6 -left-5 z-20 bg-white rounded-3xl p-5 soft-card border border-slate-100 hidden sm:block">
                        <div class="text-xs text-slate-500">Today Sales</div>
                        <div class="text-3xl font-black text-mvDark">₹1,27,270</div>
                        <div class="text-xs text-mvBlue font-bold mt-1">
                            Invoice shared on WhatsApp
                        </div>
                    </div>

                    <div class="grid grid-cols-12 gap-4">
                        <div class="col-span-7">
                            <img src="https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?auto=format&fit=crop&w=900&q=80"
                                 class="image-mask w-full h-[420px] object-cover soft-card"
                                 alt="Small business billing software">
                        </div>

                        <div class="col-span-5 space-y-4 pt-10">
                            <img src="https://images.unsplash.com/photo-1556742502-ec7c0e9f34b1?auto=format&fit=crop&w=700&q=80"
                                 class="rounded-[2rem] w-full h-[190px] object-cover soft-card"
                                 alt="Retail shop billing">

                            <div class="bg-white rounded-[2rem] p-5 border border-slate-200 soft-card">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-xs text-slate-500">
                                            Invoice Status
                                        </div>

                                        <div class="font-black text-lg text-mvDark">
                                            PDF Ready
                                        </div>
                                    </div>

                                    <div class="h-12 w-12 rounded-2xl brand-gradient text-white flex items-center justify-center font-black">
                                        ✓
                                    </div>
                                </div>

                                <div class="mt-4 bg-slate-100 rounded-full h-2">
                                    <div class="brand-gradient h-2 rounded-full w-[82%]"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="absolute -bottom-7 right-6 z-20 brand-gradient text-white rounded-3xl p-5 soft-card hidden sm:block">
                        <div class="text-xs text-blue-100">
                            Smart Billing • GST Invoice
                        </div>

                        <div class="text-2xl font-black">
                            Retail • Wholesale • Services
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- QUICK FEATURES --}}
    <section class="bg-white border-y border-slate-200">
        <div class="max-w-7xl mx-auto px-4 lg:px-8 py-8 grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
            @foreach([
                ['GST Invoice Billing', 'Professional tax invoice'],
                ['Stock Management', 'Product stock tracking'],
                ['Photo Based Entry', 'Customer and product smart entry'],
                ['WhatsApp Sharing', 'Invoice directly share karein'],
            ] as $item)
                <div class="rounded-3xl bg-slate-50 border border-slate-200 p-6 hover:bg-[#f1f0ff] transition">
                    <h3 class="font-black text-lg text-mvDark">
                        ✅ {{ $item[0] }}
                    </h3>

                    <p class="text-sm text-slate-500 mt-1">
                        {{ $item[1] }}
                    </p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- FEATURES --}}
    <section id="features" class="py-20 bg-[#f5f7ff]">
        <div class="max-w-7xl mx-auto px-4 lg:px-8">

            <div class="text-center max-w-3xl mx-auto mb-14">
                <p class="text-mvBlue font-black uppercase tracking-widest text-sm">
                    Software Features
                </p>

                <h2 class="text-3xl lg:text-5xl font-black mt-3 text-mvDark">
                    Modern shops ke liye complete billing solution
                </h2>

                <p class="mt-4 text-slate-600 text-lg">
                    Smart Billing • GST Invoice • Stock Management • Easy to Use
                </p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach([
                    ['🧾','GST Billing','Professional GST invoice, tax, discount aur PDF bill create karein.'],
                    ['🛒','Sale & Purchase','Sale entry aur purchase management ko organized rakhein.'],
                    ['📦','Stock Management','Product quantity, purchase, sale aur stock easily track karein.'],
                    ['👥','Customer Management','Customer details, history aur records ek jagah manage karein.'],
                    ['📸','Photo Smart Entry','Photo se customer aur product add karne ki smart facility.'],
                    ['⚡','Fast Billing','Typing kam aur daily billing ka simple flow.'],
                    ['📲','WhatsApp Sharing','Invoice PDF directly WhatsApp par bhejein.'],
                    ['☁️','Cloud Management','Mobile, laptop ya desktop se business manage karein.'],
                ] as $feature)
                    <div class="bg-white rounded-[2rem] p-7 border border-slate-200 soft-card hover:-translate-y-1 transition">
                        <div class="h-16 w-16 rounded-2xl bg-[#f1f0ff] flex items-center justify-center text-3xl mb-6">
                            {{ $feature[0] }}
                        </div>

                        <h3 class="text-xl font-black text-mvDark">
                            {{ $feature[1] }}
                        </h3>

                        <p class="text-slate-600 mt-3 leading-7">
                            {{ $feature[2] }}
                        </p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- BENEFITS --}}
    <section id="benefits" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-14 items-center">

                <div class="relative">
                    <img src="https://images.unsplash.com/photo-1520607162513-77705c0f0d4a?auto=format&fit=crop&w=1000&q=80"
                         class="rounded-[2.5rem] w-full h-[540px] object-cover soft-card"
                         alt="Business billing software">

                    <div class="absolute bottom-6 left-6 right-6 bg-white/90 backdrop-blur-xl rounded-3xl p-6 border border-white soft-card">
                        <div class="grid grid-cols-3 gap-4 text-center">
                            <div>
                                <div class="text-2xl font-black text-mvBlue">
                                    Fast
                                </div>
                                <div class="text-xs text-slate-500">
                                    Billing
                                </div>
                            </div>

                            <div>
                                <div class="text-2xl font-black text-mvBlue">
                                    Stock
                                </div>
                                <div class="text-xs text-slate-500">
                                    Control
                                </div>
                            </div>

                            <div>
                                <div class="text-2xl font-black text-mvBlue">
                                    Photo
                                </div>
                                <div class="text-xs text-slate-500">
                                    Smart Entry
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <p class="text-mvBlue font-black uppercase tracking-widest text-sm">
                        Advantages
                    </p>

                    <h2 class="text-3xl lg:text-5xl font-black mt-3 text-mvDark">
                        Manual billing se smart billing par shift karein
                    </h2>

                    <p class="mt-5 text-lg text-slate-600 leading-8">
                        Manual register, calculator aur repeated entry me time waste
                        hota hai. MyVictory Billing se billing fast aur business
                        control better hota hai.
                    </p>

                    <div class="mt-8 space-y-4">
                        @foreach([
                            ['Typing Kam, Billing Fast', 'Saved data aur smart entry se billing jaldi hoti hai.'],
                            ['Calculation mistakes reduce', 'GST, discount, total aur round-off automatically manage hota hai.'],
                            ['Stock control better', 'Purchase aur sale ke baad stock easily track hota hai.'],
                            ['Professional invoice', 'PDF invoice customer ko premium experience deta hai.'],
                        ] as $benefit)
                            <div class="flex gap-4 rounded-3xl bg-slate-50 border border-slate-200 p-5">
                                <div class="h-11 w-11 rounded-2xl brand-gradient text-white flex items-center justify-center font-black shrink-0">
                                    ✓
                                </div>

                                <div>
                                    <h3 class="font-black text-mvDark">
                                        {{ $benefit[0] }}
                                    </h3>

                                    <p class="text-sm text-slate-600 mt-1">
                                        {{ $benefit[1] }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- BUSINESS TYPES --}}
    <section id="business" class="py-20 bg-mvDark text-white overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 lg:px-8">

            <div class="text-center max-w-4xl mx-auto mb-12">
                <p class="text-blue-200 font-black uppercase tracking-widest text-sm">
                    Perfect For
                </p>

                <h2 class="text-3xl lg:text-5xl font-black mt-3">
                    All Types of Small and Medium Businesses
                </h2>

                <p class="mt-5 text-slate-300 text-lg leading-8">
                    Retail, wholesale aur service businesses ke liye simple
                    billing solution.
                </p>
            </div>

            @php
                $businesses = [
                    'Garment Shops',
                    'Footwear Shops',
                    'Cosmetic Stores',
                    'Gift Shops',
                    'Artificial Jewellery Shops',
                    'Mobile Accessories Shops',
                    'Stationery Shops',
                    'General Stores',
                    'Bakery Shops',
                    'Sweet Shops',
                    'Small Cafes',
                    'Mobile Repair Shops',
                    'Computer Repair Shops',
                    'Salons',
                    'Gyms',
                    'Coaching Institutes',
                    'Boutiques',
                    'Small Supermarkets',
                    'Mini Mart Stores',
                    'Book Stores',
                    'Watch Shops',
                    'Sports Item Shops',
                    'Pet Shops',
                    'Home Decor Shops',
                ];
            @endphp

            <div class="grid sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach($businesses as $business)
                    <div class="rounded-2xl bg-white/10 border border-white/10 px-5 py-4 text-sm font-bold text-slate-100 hover:bg-mvBlue transition">
                        ✅ {{ $business }}
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- HOW IT WORKS --}}
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 lg:px-8">

            <div class="text-center max-w-3xl mx-auto mb-14">
                <p class="text-mvBlue font-black uppercase tracking-widest text-sm">
                    How It Works
                </p>

                <h2 class="text-3xl lg:text-5xl font-black mt-3 text-mvDark">
                    Start billing in 3 easy steps
                </h2>
            </div>

            <div class="grid lg:grid-cols-3 gap-8">
                @foreach([
                    ['01','Create Account','Mobile number OTP se account create karein.'],
                    ['02','Complete Setup','Business aur billing details abhi ya baad me add karein.'],
                    ['03','Start Billing','Invoice banayein, share karein aur reports track karein.'],
                ] as $step)
                    <div class="rounded-[2rem] bg-[#f5f7ff] border border-slate-200 p-8 hover:bg-mvBlue hover:text-white transition">
                        <div class="text-6xl font-black opacity-20">
                            {{ $step[0] }}
                        </div>

                        <h3 class="text-2xl font-black mt-8">
                            {{ $step[1] }}
                        </h3>

                        <p class="mt-4 leading-7 opacity-80">
                            {{ $step[2] }}
                        </p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- PRICING --}}
    <section id="pricing" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 lg:px-8">

            <div class="text-center mb-14">
                <p class="text-mvBlue font-black uppercase tracking-widest text-sm">
                    Pricing Plans
                </p>

                <h2 class="text-3xl lg:text-5xl font-black mt-3 text-mvDark">
                    Plans Starting From ₹999 / Year
                </h2>

                <p class="mt-4 text-slate-600">
                    Choose the best plan for your business.
                </p>
            </div>

            @if($plans->count())
                <div class="grid md:grid-cols-3 gap-8">
                    @foreach($plans as $plan)
                        @php
                            $isPopular = (bool) $plan->is_recommended;
                        @endphp

                        <div class="rounded-[2rem] p-8 relative transition hover:-translate-y-1
                            {{ $isPopular
                                ? 'brand-gradient text-white shadow-2xl shadow-blue-200 scale-105'
                                : 'bg-slate-50 border border-slate-200 text-mvDark' }}">

                            @if($isPopular)
                                <div class="absolute -top-4 right-8 bg-white text-mvBlue text-xs font-black px-4 py-2 rounded-full shadow-lg">
                                    Most Recommended
                                </div>
                            @endif

                            <h3 class="text-2xl font-black uppercase">
                                {{ $plan->name }}
                            </h3>

                            <p class="mt-2 {{ $isPopular ? 'text-blue-100' : 'text-slate-500' }}">
                                {{ $plan->subtitle ?: ($plan->description ?: 'Perfect for growing businesses') }}
                            </p>

                            <div class="text-4xl font-black mt-6">
                                ₹{{ number_format($plan->price, 0) }}

                                <span class="text-sm {{ $isPopular ? 'text-blue-100' : 'text-slate-500' }}">
                                    /
                                    {{ $plan->duration_days >= 365
                                        ? 'Year'
                                        : $plan->duration_days . ' Days' }}
                                </span>
                            </div>

                            <ul class="mt-7 space-y-3 text-sm {{ $isPopular ? 'text-blue-50' : 'text-slate-600' }}">
                                @forelse($plan->planFeatures as $feature)
                                    <li>
                                        ✔ {{ $feature->title }}
                                    </li>
                                @empty
                                    <li>✔ GST Billing</li>
                                    <li>✔ Stock Management</li>
                                    <li>✔ Invoice Print and Share</li>
                                @endforelse
                            </ul>

                            <button type="button"
                                    data-plan-id="{{ $plan->id }}"
                                    class="open-registration-modal mt-8 block w-full text-center rounded-full py-4 font-black
                                    {{ $isPopular
                                        ? 'bg-white text-mvBlue'
                                        : 'bg-mvDark text-white' }}">
                                Start Free Registration
                            </button>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center bg-slate-50 border border-slate-200 rounded-3xl p-10">
                    <h3 class="text-2xl font-black text-mvDark">
                        Plans will be available soon
                    </h3>
                </div>
            @endif
        </div>
    </section>

    {{-- CTA --}}
    <section class="py-16 brand-gradient text-white">
        <div class="max-w-7xl mx-auto px-4 lg:px-8 text-center">

            <h2 class="text-3xl lg:text-5xl font-black">
                MyVictory Billing Software
            </h2>

            <p class="mt-4 text-xl text-blue-100">
                Smart Billing Software for Modern Shops
            </p>

            <button type="button"
                    class="open-registration-modal mt-9 inline-block rounded-full bg-white text-mvBlue px-8 py-4 font-black">
                Create Free Account
            </button>
        </div>
    </section>

    {{-- CONTACT INFORMATION --}}
    <section id="contact" class="py-20 hero-bg">
        <div class="max-w-5xl mx-auto px-4 lg:px-8">

            <div class="bg-white rounded-[2.5rem] border border-slate-200 soft-card p-8 lg:p-12 text-center">
                <p class="font-black uppercase tracking-widest text-mvBlue text-sm">
                    Need Help?
                </p>

                <h2 class="text-3xl lg:text-5xl font-black mt-4 text-mvDark">
                    Talk to our support team
                </h2>

                <p class="mt-5 text-slate-600 text-lg leading-8">
                    Registration, business setup ya billing software ke use me
                    help ke liye WhatsApp karein.
                </p>

                <div class="mt-8 flex flex-wrap justify-center gap-4">
                    <a href="https://wa.me/917753800444"
                       target="_blank"
                       class="rounded-full brand-gradient text-white px-8 py-4 font-black">
                        WhatsApp: +91-7753800444
                    </a>

                    <button type="button"
                            class="open-registration-modal rounded-full bg-white border border-mvBlue text-mvBlue px-8 py-4 font-black">
                        Register Now
                    </button>
                </div>
            </div>
        </div>
    </section>

</main>

{{-- REGISTRATION POPUP --}}
<div id="registrationModal"
     class="registration-modal"
     aria-hidden="true">

    <div class="registration-modal-backdrop"
         data-close-registration-modal>
    </div>

    <div class="registration-modal-dialog"
         role="dialog"
         aria-modal="true"
         aria-labelledby="registrationModalTitle">

        <div class="registration-modal-header">
            <button type="button"
                    class="registration-modal-close"
                    data-close-registration-modal
                    aria-label="Close registration">
                ×
            </button>

            <div class="pr-12">
                <div class="text-xs font-bold text-blue-100">
                    Free account registration
                </div>

                <h2 id="registrationModalTitle"
                    class="mt-1 text-2xl font-black">
                    Start MyVictory Billing
                </h2>

                <p class="mt-1 text-sm text-blue-100">
                    Name aur mobile verify hote hi account create ho jayega.
                </p>
            </div>
        </div>

        {{-- STEP PROGRESS --}}
        <div class="border-b border-slate-100 bg-indigo-50/50 px-5 py-4 sm:px-7">

            <div class="grid grid-cols-3 gap-2">
                <div id="popupStepPill1"
                     class="registration-step-pill active">
                    <div class="text-[10px]">
                        Step 1
                    </div>

                    <div class="mt-1 text-xs font-black">
                        Mobile
                    </div>
                </div>

                <div id="popupStepPill2"
                     class="registration-step-pill">
                    <div class="text-[10px]">
                        Step 2
                    </div>

                    <div class="mt-1 text-xs font-black">
                        Business
                    </div>
                </div>

                <div id="popupStepPill3"
                     class="registration-step-pill">
                    <div class="text-[10px]">
                        Step 3
                    </div>

                    <div class="mt-1 text-xs font-black">
                        Billing
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <div class="mb-2 flex items-center justify-between text-xs">
                    <span class="text-slate-500">
                        Step
                        <span id="popupCurrentStepNumber">1</span>
                        of 3
                    </span>

                    <span id="popupCurrentStepTitle"
                          class="font-black text-[#2624cc]">
                        Mobile Verification
                    </span>
                </div>

                <div class="h-2 overflow-hidden rounded-full bg-slate-200">
                    <div id="popupProgressBar"
                         class="h-full rounded-full bg-gradient-to-r from-[#2624cc] to-[#6c63ff] transition-all duration-300"
                         style="width: 33.333%">
                    </div>
                </div>
            </div>
        </div>

        <div class="p-5 sm:p-7">

            <div id="registrationMessage"
                 class="registration-message {{ $errors->any() ? 'show error' : '' }}">
                @if($errors->any())
                    <div class="font-black mb-2">
                        Registration complete nahi hua:
                    </div>

                    <ul class="list-disc pl-5 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <form id="popupRegistrationForm"
                  method="POST"
                  action="{{ route('ad.register.complete') }}"
                  novalidate>
                @csrf

                <input type="hidden"
                       name="current_step"
                       id="popupCurrentStep"
                       value="1">

                <input type="hidden"
                       name="business_skipped"
                       id="popupBusinessSkipped"
                       value="0">

                <input type="hidden"
                       name="billing_skipped"
                       id="popupBillingSkipped"
                       value="0">

                <input type="hidden"
                       name="plan_id"
                       id="popupPlanId"
                       value="{{ request('plan_id') }}">

                {{-- STEP 1 --}}
                <div class="registration-step active space-y-5"
                     data-registration-step="1">

                    <div>
                        <label for="popupOwnerName"
                               class="mb-2 block text-sm font-bold text-slate-700">
                            Full Name
                        </label>

                        <input type="text"
                               name="name"
                               id="popupOwnerName"
                               value="{{ old('name') }}"
                               class="registration-field"
                               autocomplete="name"
                               placeholder="Enter your full name"
                               required>
                    </div>

                    <div>
                        <label for="popupOwnerPhone"
                               class="mb-2 block text-sm font-bold text-slate-700">
                            Mobile Number
                        </label>

                        <div class="flex flex-col gap-3 sm:flex-row">
                            <input type="text"
                                   name="phone"
                                   id="popupOwnerPhone"
                                   value="{{ old('phone', session('register_phone_verified')) }}"
                                   maxlength="10"
                                   inputmode="numeric"
                                   autocomplete="tel"
                                   class="registration-field"
                                   placeholder="Enter 10 digit mobile number">

                            <button type="button"
                                    id="popupSendOtpBtn"
                                    class="registration-primary-button shrink-0">
                                Send OTP
                            </button>
                        </div>

                        <p id="popupOtpSendStatus"
                           class="mt-2 text-xs text-slate-500">
                        </p>
                    </div>

                    <div id="popupOtpSection"
                         class="{{ session('register_phone_verified') ? '' : 'hidden' }}">

                        <label for="popupPhoneOtp"
                               class="mb-2 block text-sm font-bold text-slate-700">
                            Enter OTP
                        </label>

                        <input type="text"
                               id="popupPhoneOtp"
                               maxlength="6"
                               inputmode="numeric"
                               autocomplete="one-time-code"
                               class="registration-field text-center text-xl font-black tracking-[0.45em]"
                               placeholder="••••••">

                        <input type="hidden"
                               id="popupPhoneVerified"
                               value="{{ session('register_phone_verified') ? 1 : 0 }}">

                        <div id="popupOtpLoader"
                             class="registration-otp-loader">
                            <span class="registration-spinner"></span>
                            OTP verify ho raha hai...
                        </div>

                        <p id="popupOtpVerifyStatus"
                           class="mt-2 text-xs text-slate-500">
                        </p>

                        <p class="mt-2 text-xs text-slate-400">
                            6 digit OTP enter karte hi automatically verify ho jayega.
                        </p>
                    </div>
                </div>

                {{-- STEP 2 --}}
                <div class="registration-step space-y-5"
                     data-registration-step="2">

                    <div>
                        <label class="mb-2 block text-sm font-bold text-slate-700">
                            Business Name
                            <span class="font-normal text-slate-400">
                                Optional
                            </span>
                        </label>

                        <input type="text"
                               name="business_name"
                               value="{{ old('business_name') }}"
                               class="registration-field"
                               placeholder="Enter business or shop name">
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-bold text-slate-700">
                                Business Mobile
                            </label>

                            <input type="text"
                                   name="mobile"
                                   id="popupBusinessMobile"
                                   value="{{ old('mobile', session('register_phone_verified')) }}"
                                   maxlength="10"
                                   readonly
                                   class="registration-field"
                                   placeholder="Verified mobile">
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-bold text-slate-700">
                                Business Email
                                <span class="font-normal text-slate-400">
                                    Optional
                                </span>
                            </label>

                            <input type="email"
                                   name="business_email"
                                   value="{{ old('business_email') }}"
                                   class="registration-field"
                                   placeholder="Enter email">
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-bold text-slate-700">
                                Business Type
                                <span class="font-normal text-slate-400">
                                    Optional
                                </span>
                            </label>

                            <select name="type"
                                    class="registration-field">
                                <option value="">
                                    Select business type
                                </option>

                                @foreach($businessTypes as $businessType)
                                    <option value="{{ $businessType->id }}"
                                        @selected(old('type') == $businessType->id)>
                                        {{ $businessType->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-bold text-slate-700">
                                GSTIN
                                <span class="font-normal text-slate-400">
                                    Optional
                                </span>
                            </label>

                            <input type="text"
                                   name="gstin"
                                   id="popupGstin"
                                   value="{{ old('gstin') }}"
                                   maxlength="15"
                                   class="registration-field uppercase"
                                   placeholder="Enter GSTIN">
                        </div>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-bold text-slate-700">
                            Business Address
                            <span class="font-normal text-slate-400">
                                Optional
                            </span>
                        </label>

                        <textarea name="address"
                                  rows="3"
                                  class="registration-field"
                                  placeholder="Enter business address">{{ old('address') }}</textarea>
                    </div>

                    @php
                        $states = [
                            ['35', 'Andaman and Nicobar Islands'],
                            ['37', 'Andhra Pradesh'],
                            ['12', 'Arunachal Pradesh'],
                            ['18', 'Assam'],
                            ['10', 'Bihar'],
                            ['04', 'Chandigarh'],
                            ['22', 'Chhattisgarh'],
                            ['26', 'Dadra and Nagar Haveli and Daman and Diu'],
                            ['07', 'Delhi'],
                            ['30', 'Goa'],
                            ['24', 'Gujarat'],
                            ['06', 'Haryana'],
                            ['02', 'Himachal Pradesh'],
                            ['01', 'Jammu and Kashmir'],
                            ['20', 'Jharkhand'],
                            ['29', 'Karnataka'],
                            ['32', 'Kerala'],
                            ['38', 'Ladakh'],
                            ['31', 'Lakshadweep'],
                            ['23', 'Madhya Pradesh'],
                            ['27', 'Maharashtra'],
                            ['14', 'Manipur'],
                            ['17', 'Meghalaya'],
                            ['15', 'Mizoram'],
                            ['13', 'Nagaland'],
                            ['21', 'Odisha'],
                            ['34', 'Puducherry'],
                            ['03', 'Punjab'],
                            ['08', 'Rajasthan'],
                            ['11', 'Sikkim'],
                            ['33', 'Tamil Nadu'],
                            ['36', 'Telangana'],
                            ['16', 'Tripura'],
                            ['05', 'Uttarakhand'],
                            ['09', 'Uttar Pradesh'],
                            ['19', 'West Bengal'],
                        ];

                        $selectedState = old('state_code') && old('state')
                            ? old('state_code') . ',' . old('state')
                            : '';
                    @endphp

                    <div>
                        <label class="mb-2 block text-sm font-bold text-slate-700">
                            State
                            <span class="font-normal text-slate-400">
                                Optional
                            </span>
                        </label>

                        <select id="popupStateSelect"
                                class="registration-field">

                            <option value="">
                                Select state
                            </option>

                            @foreach($states as $state)
                                @php
                                    $stateValue = $state[0] . ',' . $state[1];
                                @endphp

                                <option value="{{ $stateValue }}"
                                    @selected($selectedState === $stateValue)>
                                    {{ $state[1] }}
                                </option>
                            @endforeach
                        </select>
                        <input type="hidden"
                               name="state"
                               id="popupState"
                               value="{{ old('state') }}">

                        <input type="hidden"
                               name="state_code"
                               id="popupStateCode"
                               value="{{ old('state_code') }}">
                    </div>

                    <button type="button"
                            id="popupSkipBusinessBtn"
                            class="registration-skip-button">
                        Skip business details for now
                    </button>
                </div>

                {{-- STEP 3 --}}
                <div class="registration-step space-y-5"
                     data-registration-step="3">

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-bold text-slate-700">
                                GST Billing
                            </label>

                            <select name="gst_enabled"
                                    id="popupGstEnabled"
                                    class="registration-field">
                                <option value="0"
                                    @selected(old('gst_enabled', '0') == '0')>
                                    Disabled
                                </option>

                                <option value="1"
                                    @selected(old('gst_enabled') == '1')>
                                    Enabled
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-bold text-slate-700">
                                Invoice Prefix
                            </label>

                            <input type="text"
                                   name="invoice_base_prefix"
                                   value="{{ old('invoice_base_prefix', 'INV') }}"
                                   class="registration-field"
                                   placeholder="INV">
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-bold text-slate-700">
                                Amount Rounding
                            </label>

                            <select name="rounding_mode"
                                    class="registration-field">
                                <option value="none">
                                    No rounding
                                </option>

                                <option value="nearest"
                                    @selected(old('rounding_mode', 'nearest') == 'nearest')>
                                    Nearest amount
                                </option>

                                <option value="up"
                                    @selected(old('rounding_mode') == 'up')>
                                    Round up
                                </option>

                                <option value="down"
                                    @selected(old('rounding_mode') == 'down')>
                                    Round down
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-bold text-slate-700">
                                Rounding Value
                            </label>

                            <input type="number"
                                   name="rounding_step"
                                   value="{{ old('rounding_step', '1.00') }}"
                                   min="0"
                                   step="0.01"
                                   class="registration-field"
                                   placeholder="1.00">
                        </div>
                    </div>

                    <button type="button"
                            id="popupSkipBillingBtn"
                            class="registration-skip-button">
                        Use default settings and continue
                    </button>
                </div>

                {{-- NAVIGATION --}}
                <div class="mt-7 flex flex-col-reverse gap-3 border-t border-slate-100 pt-6 sm:flex-row sm:items-center sm:justify-between">

                    <button type="button"
                            id="popupPrevBtn"
                            class="registration-secondary-button hidden">
                        Back
                    </button>

                    <div class="flex w-full flex-col gap-3 sm:ml-auto sm:w-auto sm:flex-row">
                        {{-- <button type="button"
                                id="popupNextBtn"
                                class="registration-primary-button hidden w-full sm:w-auto">
                            Continue
                        </button> --}}

                        <button type="button"
                                id="popupSubmitBtn"
                                class="registration-primary-button hidden w-full sm:w-auto">
                            Continue
                        </button>
                    </div>
                </div>

                <p class="mt-5 text-center text-sm text-slate-500">
                    Already registered?

                    <a href="{{ route('login') }}"
                       class="font-black text-[#2624cc]">
                        Login
                    </a>
                </p>
            </form>
        </div>
    </div>
</div>


<script>
    document.addEventListener('DOMContentLoaded', function () {
        const words = [
            'Typing Kam, Billing Fast.',
            'Entry Kam, Billing Smart.',
            'Time Save, Billing Easy.',
            'Photo Entry, Invoice Ready.',
            'Smart Shop, Fast Billing.',
            'GST Bill, One Click.'
        ];

        let wordIndex = 0;
        let charIndex = 0;
        let isDeleting = false;

        const typingText = document.getElementById('typingText');

        function typeEffect() {
            if (!typingText) {
                return;
            }

            const currentWord = words[wordIndex];

            if (isDeleting) {
                typingText.textContent =
                    currentWord.substring(0, charIndex--);
            } else {
                typingText.textContent =
                    currentWord.substring(0, charIndex++);
            }

            let speed = isDeleting ? 45 : 70;

            if (
                !isDeleting &&
                charIndex === currentWord.length + 1
            ) {
                speed = 1200;
                isDeleting = true;
            }

            if (
                isDeleting &&
                charIndex === 0
            ) {
                isDeleting = false;
                wordIndex =
                    (wordIndex + 1) % words.length;

                speed = 300;
            }

            setTimeout(typeEffect, speed);
        }

        typeEffect();

        const registrationModal =
            document.getElementById('registrationModal');

        const openRegistrationButtons =
            document.querySelectorAll(
                '.open-registration-modal'
            );

        const closeRegistrationButtons =
            document.querySelectorAll(
                '[data-close-registration-modal]'
            );

        const popupPlanId =
            document.getElementById('popupPlanId');

        const INITIAL_POPUP_DELAY = 800;
        const REOPEN_POPUP_DELAY = 10000;

        const REGISTRATION_COMPLETED_KEY =
            'myvictory_registration_completed';

        let registrationPopupTimer = null;

        let registrationCompleted = false;

        function clearRegistrationPopupTimer() {
            if (!registrationPopupTimer) {
                return;
            }

            clearTimeout(registrationPopupTimer);
            registrationPopupTimer = null;
        }


        function canOpenRegistrationModal(ignoreCompletedStatus = false) {
            if (!registrationModal) {
                return false;
            }

            /*
            * Automatic popup completed registration ke baad nahi khulega.
            * Manual Register button click par forceOpen=true hone se popup
            * hamesha khul sakta hai.
            */
            return !registrationModal.classList.contains('show');
        }

        function openRegistrationModal(planId = '', forceOpen = false) {
            clearRegistrationPopupTimer();

            if (!canOpenRegistrationModal(forceOpen)) {
                return;
            }

            if (popupPlanId) {
                popupPlanId.value = planId || '';
            }

            registrationModal.classList.add('show');
            registrationModal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('registration-modal-open');

            setTimeout(function () {
                const currentPhoneVerified =
                    document.getElementById('popupPhoneVerified');

                const currentOwnerName =
                    document.getElementById('popupOwnerName');

                if (
                    currentPhoneVerified &&
                    currentPhoneVerified.value !== '1' &&
                    currentOwnerName
                ) {
                    currentOwnerName.focus();
                }
            }, 200);
        }

        function scheduleRegistrationPopup(delay = REOPEN_POPUP_DELAY) {
            clearRegistrationPopupTimer();

            registrationPopupTimer = setTimeout(function () {
                openRegistrationModal('', false);
            }, delay);
        }

        function closeRegistrationModal(
            reopenLater = true
        ) {
            if (!registrationModal) {
                return;
            }

            registrationModal.classList.remove('show');

            registrationModal.setAttribute(
                'aria-hidden',
                'true'
            );

            document.body.classList.remove(
                'registration-modal-open'
            );

            if (reopenLater) {
                scheduleRegistrationPopup(
                    REOPEN_POPUP_DELAY
                );
            }
        }

        function markRegistrationCompleted() {
            registrationCompleted = true;

            localStorage.setItem(
                REGISTRATION_COMPLETED_KEY,
                '1'
            );

            clearRegistrationPopupTimer();
        }

        openRegistrationButtons.forEach(function (button) {
            button.addEventListener('click', function (event) {
                event.preventDefault();
                event.stopPropagation();

                const planId =
                    button.getAttribute('data-plan-id') || '';

                /*
                * Manual click par completed localStorage flag popup ko
                * block nahi karega.
                */
                openRegistrationModal(planId, true);
            });
        });

        closeRegistrationButtons.forEach(
            function (button) {
                button.addEventListener(
                    'click',
                    function () {
                        closeRegistrationModal(true);
                    }
                );
            }
        );

        document.addEventListener(
            'keydown',
            function (event) {
                if (
                    event.key === 'Escape' &&
                    registrationModal &&
                    registrationModal.classList.contains(
                        'show'
                    )
                ) {
                    closeRegistrationModal(true);
                }
            }
        );

        @guest
            scheduleRegistrationPopup(
                INITIAL_POPUP_DELAY
            );
        @endguest

        const form =
            document.getElementById(
                'popupRegistrationForm'
            );

        if (!form) {
            return;
        }

        const steps = Array.from(
            document.querySelectorAll(
                '[data-registration-step]'
            )
        );

        const pills = [
            document.getElementById('popupStepPill1'),
            document.getElementById('popupStepPill2'),
            document.getElementById('popupStepPill3')
        ];

        const stepTitles = [
            'Name & Mobile Verification',
            'Business Information',
            'Billing Setup'
        ];

        const currentStepInput =
            document.getElementById(
                'popupCurrentStep'
            );

        const currentStepNumber =
            document.getElementById(
                'popupCurrentStepNumber'
            );

        const currentStepTitle =
            document.getElementById(
                'popupCurrentStepTitle'
            );

        const progressBar =
            document.getElementById(
                'popupProgressBar'
            );

        const prevBtn =
            document.getElementById(
                'popupPrevBtn'
            );

        /*
        * popupNextBtn HTML me nahi hai.
        * Step 2 par popupSubmitBtn ko hi Continue
        * aur Step 3 par final Continue banaya gaya hai.
        */
        const submitBtn =
            document.getElementById(
                'popupSubmitBtn'
            );

        const sendOtpBtn =
            document.getElementById(
                'popupSendOtpBtn'
            );

        const ownerPhone =
            document.getElementById(
                'popupOwnerPhone'
            );

        const ownerName =
            document.getElementById(
                'popupOwnerName'
            );

        const businessMobile =
            document.getElementById(
                'popupBusinessMobile'
            );

        const phoneOtp =
            document.getElementById(
                'popupPhoneOtp'
            );

        const phoneVerified =
            document.getElementById(
                'popupPhoneVerified'
            );

        const otpSection =
            document.getElementById(
                'popupOtpSection'
            );

        const otpLoader =
            document.getElementById(
                'popupOtpLoader'
            );

        const otpSendStatus =
            document.getElementById(
                'popupOtpSendStatus'
            );

        const otpVerifyStatus =
            document.getElementById(
                'popupOtpVerifyStatus'
            );

        const skipBusinessBtn =
            document.getElementById(
                'popupSkipBusinessBtn'
            );

        const skipBillingBtn =
            document.getElementById(
                'popupSkipBillingBtn'
            );

        const businessSkipped =
            document.getElementById(
                'popupBusinessSkipped'
            );

        const billingSkipped =
            document.getElementById(
                'popupBillingSkipped'
            );

        const stateSelect =
            document.getElementById(
                'popupStateSelect'
            );

        const stateInput =
            document.getElementById(
                'popupState'
            );

        const stateCodeInput =
            document.getElementById(
                'popupStateCode'
            );

        const gstinInput =
            document.getElementById(
                'popupGstin'
            );

        const gstEnabled =
            document.getElementById(
                'popupGstEnabled'
            );

        const messageBox =
            document.getElementById(
                'registrationMessage'
            );

        const dialog =
            registrationModal?.querySelector(
                '.registration-modal-dialog'
            );

        let currentStep = 0;
        let requestRunning = false;
        let otpVerificationRunning = false;
        let finalSubmissionRunning = false;
        let lastVerifiedOtp = '';

        const csrfToken =
            document.querySelector(
                'meta[name="csrf-token"]'
            )?.getAttribute('content')
            || '{{ csrf_token() }}';

        const serverValidationErrors =
            @json($errors->all());

        const serverValidationFields =
            @json($errors->keys());

        function cleanPhone(value) {
            return String(value || '')
                .replace(/\D/g, '')
                .slice(0, 10);
        }

        function cleanOtp(value) {
            return String(value || '')
                .replace(/\D/g, '')
                .slice(0, 6);
        }

        function validPhone(phone) {
            return /^[6-9][0-9]{9}$/.test(phone);
        }

        function showMessage(
            message,
            type = 'success'
        ) {
            if (!messageBox) {
                return;
            }

            messageBox.textContent = message;

            messageBox.className =
                'registration-message show ' + type;

            dialog?.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        function hideMessage() {
            if (!messageBox) {
                return;
            }

            messageBox.textContent = '';

            messageBox.className =
                'registration-message';
        }

        function setButtonLoading(
            button,
            loading,
            loadingText = 'Please wait...'
        ) {
            if (!button) {
                return;
            }

            if (loading) {
                if (!button.dataset.originalText) {
                    button.dataset.originalText =
                        button.textContent.trim();
                }

                button.disabled = true;
                button.textContent = loadingText;

                button.classList.add(
                    'opacity-60',
                    'cursor-not-allowed'
                );

                return;
            }

            button.disabled = false;

            button.textContent =
                button.dataset.originalText
                || button.textContent;

            button.classList.remove(
                'opacity-60',
                'cursor-not-allowed'
            );
        }

        function setOtpLoading(loading) {
            if (otpLoader) {
                otpLoader.classList.toggle(
                    'show',
                    loading
                );
            }

            if (phoneOtp) {
                phoneOtp.disabled = loading;
            }
        }

        function syncStateFields() {
            if (
                !stateSelect ||
                !stateInput ||
                !stateCodeInput
            ) {
                return;
            }

            const value = stateSelect.value;

            if (!value) {
                stateInput.value = '';
                stateCodeInput.value = '';
                return;
            }

            const parts = value.split(',');

            stateCodeInput.value =
                parts.shift() || '';

            stateInput.value =
                parts.join(',') || '';
        }

        function showStep(index) {
            if (
                index < 0 ||
                index >= steps.length
            ) {
                return;
            }

            currentStep = index;

            steps.forEach(
                function (step, stepIndex) {
                    step.classList.toggle(
                        'active',
                        stepIndex === index
                    );
                }
            );

            pills.forEach(
                function (pill, pillIndex) {
                    if (!pill) {
                        return;
                    }

                    pill.classList.remove(
                        'active',
                        'done'
                    );

                    if (pillIndex === index) {
                        pill.classList.add('active');
                    } else if (pillIndex < index) {
                        pill.classList.add('done');
                    }
                }
            );

            if (currentStepInput) {
                currentStepInput.value =
                    String(index + 1);
            }

            if (currentStepNumber) {
                currentStepNumber.textContent =
                    String(index + 1);
            }

            if (currentStepTitle) {
                currentStepTitle.textContent =
                    stepTitles[index];
            }

            if (progressBar) {
                progressBar.style.width =
                    (
                        ((index + 1) / steps.length)
                        * 100
                    ) + '%';
            }

            if (prevBtn) {
                prevBtn.classList.toggle(
                    'hidden',
                    index === 0
                );
            }

            /*
            * Step 1 par button hidden rahega.
            * OTP verify hone par automatic Step 2 khulega.
            *
            * Step 2 par button Business Continue hoga.
            * Step 3 par button final Continue hoga.
            */
            if (submitBtn) {
                submitBtn.classList.toggle(
                    'hidden',
                    index === 0
                );

                submitBtn.disabled = false;

                if (index === 1) {
                    submitBtn.textContent = 'Continue';
                }

                if (index === 2) {
                    submitBtn.textContent = 'Continue';
                }

                submitBtn.dataset.originalText =
                    'Continue';
            }

            hideMessage();

            setTimeout(function () {
                dialog?.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            }, 50);
        }

        async function parseResponse(response) {
            const contentType =
                response.headers.get(
                    'content-type'
                ) || '';

            let data = {};

            if (
                contentType.includes(
                    'application/json'
                )
            ) {
                data = await response
                    .json()
                    .catch(function () {
                        return {};
                    });
            } else {
                const rawText =
                    await response.text();

                data = {
                    message:
                        response.status === 419
                            ? 'Page expire ho gaya hai. Page refresh karke dobara try kijiye.'
                            : (
                                rawText
                                    ? 'Server se valid response nahi mila.'
                                    : 'Request failed.'
                            )
                };
            }

            if (!response.ok) {
                let message =
                    data.message
                    || 'Request failed.';

                if (data.errors) {
                    const firstError =
                        Object.values(
                            data.errors
                        )[0];

                    if (
                        Array.isArray(firstError)
                        && firstError.length
                    ) {
                        message = firstError[0];
                    }
                }

                throw new Error(message);
            }

            return data;
        }

        async function saveCurrentStep(
            stepNumber
        ) {
            if (
                !phoneVerified ||
                phoneVerified.value !== '1'
            ) {
                showMessage(
                    'Pehle mobile number verify kijiye.',
                    'error'
                );

                showStep(0);
                return false;
            }

            syncStateFields();

            const formData =
                new FormData(form);

            formData.set(
                'step',
                String(stepNumber)
            );

            try {
                const response = await fetch(
                    "{{ route('ad.register.save-step') }}",
                    {
                        method: 'POST',

                        headers: {
                            'X-CSRF-TOKEN':
                                csrfToken,

                            'Accept':
                                'application/json',

                            'X-Requested-With':
                                'XMLHttpRequest'
                        },

                        body: formData
                    }
                );

                const result =
                    await parseResponse(response);

                if (result.success !== true) {
                    throw new Error(
                        result.message
                        || 'Details save nahi ho payi.'
                    );
                }

                return true;
            } catch (error) {
                showMessage(
                    error.message
                    || 'Details save nahi ho payi.',
                    'error'
                );

                return false;
            }
        }

        async function verifyOtpAutomatically() {
            if (
                otpVerificationRunning ||
                !phoneVerified ||
                phoneVerified.value === '1'
            ) {
                return;
            }

            const name =
                ownerName
                    ? ownerName.value.trim()
                    : '';

            const phone =
                cleanPhone(
                    ownerPhone?.value
                );

            const otp =
                cleanOtp(
                    phoneOtp?.value
                );

            if (!name) {
                showMessage(
                    'Pehle apna naam enter kijiye.',
                    'error'
                );

                ownerName?.focus();
                return;
            }

            if (ownerPhone) {
                ownerPhone.value = phone;
            }

            if (phoneOtp) {
                phoneOtp.value = otp;
            }

            if (
                !validPhone(phone) ||
                otp.length !== 6
            ) {
                return;
            }

            if (lastVerifiedOtp === otp) {
                return;
            }

            otpVerificationRunning = true;
            lastVerifiedOtp = otp;

            if (otpVerifyStatus) {
                otpVerifyStatus.textContent = '';
            }

            setOtpLoading(true);

            try {
                const response = await fetch(
                    "{{ route('register.verifyOtp') }}",
                    {
                        method: 'POST',

                        headers: {
                            'Content-Type':
                                'application/json',

                            'X-CSRF-TOKEN':
                                csrfToken,

                            'Accept':
                                'application/json',

                            'X-Requested-With':
                                'XMLHttpRequest'
                        },

                        body: JSON.stringify({
                            name: name,
                            phone: phone,
                            otp: otp
                        })
                    }
                );

                const result =
                    await parseResponse(response);

                phoneVerified.value = '1';

                if (ownerPhone) {
                    ownerPhone.readOnly = true;
                }

                if (businessMobile) {
                    businessMobile.value = phone;
                }

                sendOtpBtn?.classList.add(
                    'hidden'
                );

                if (otpVerifyStatus) {
                    otpVerifyStatus.textContent =
                        result.message
                        || 'Mobile verified successfully.';

                    otpVerifyStatus.className =
                        'mt-2 text-xs font-bold text-green-600';
                }

                setOtpLoading(false);

                const firstStepSaved =
                    await saveCurrentStep(1);

                if (!firstStepSaved) {
                    /*
                    * OTP server par verify ho chuka hai.
                    * Isliye phoneVerified ko dobara 0 nahi karna.
                    * User retry kar sakta hai.
                    */
                    showMessage(
                        'Mobile verify ho gaya hai, lekin Step 1 database me save nahi hua. Continue dobara try kijiye.',
                        'error'
                    );

                    return;
                }

                showStep(1);

                setTimeout(function () {
                    form
                        .querySelector(
                            '[name="business_name"]'
                        )
                        ?.focus();
                }, 200);

            } catch (error) {
                lastVerifiedOtp = '';

                if (otpVerifyStatus) {
                    otpVerifyStatus.textContent =
                        error.message;

                    otpVerifyStatus.className =
                        'mt-2 text-xs font-bold text-red-600';
                }

                if (phoneOtp) {
                    phoneOtp.value = '';
                    phoneOtp.focus();
                }

                setOtpLoading(false);

            } finally {
                otpVerificationRunning = false;
            }
        }

        sendOtpBtn?.addEventListener(
            'click',
            async function () {
                if (requestRunning) {
                    return;
                }

                hideMessage();

                const name =
                    ownerName
                        ? ownerName.value.trim()
                        : '';

                const phone =
                    cleanPhone(
                        ownerPhone?.value
                    );

                if (ownerPhone) {
                    ownerPhone.value = phone;
                }

                if (!name) {
                    if (otpSendStatus) {
                        otpSendStatus.textContent =
                            'Pehle apna naam enter kijiye.';

                        otpSendStatus.className =
                            'mt-2 text-xs font-bold text-red-600';
                    }

                    ownerName?.focus();
                    return;
                }

                if (!validPhone(phone)) {
                    if (otpSendStatus) {
                        otpSendStatus.textContent =
                            'Valid 10 digit mobile number enter kijiye.';

                        otpSendStatus.className =
                            'mt-2 text-xs font-bold text-red-600';
                    }

                    ownerPhone?.focus();
                    return;
                }

                requestRunning = true;

                setButtonLoading(
                    sendOtpBtn,
                    true,
                    'Sending...'
                );

                if (otpSendStatus) {
                    otpSendStatus.textContent = '';
                }

                try {
                    const response = await fetch(
                        "{{ route('register.sendOtp') }}",
                        {
                            method: 'POST',

                            headers: {
                                'Content-Type':
                                    'application/json',

                                'X-CSRF-TOKEN':
                                    csrfToken,

                                'Accept':
                                    'application/json',

                                'X-Requested-With':
                                    'XMLHttpRequest'
                            },

                            body: JSON.stringify({
                                name: name,
                                phone: phone
                            })
                        }
                    );

                    const result =
                        await parseResponse(response);

                    otpSection?.classList.remove(
                        'hidden'
                    );

                    if (otpSendStatus) {
                        otpSendStatus.textContent =
                            result.message
                            || 'OTP sent successfully.';

                        if (result.debug_otp) {
                            otpSendStatus.textContent +=
                                ' Testing OTP: '
                                + result.debug_otp;
                        }

                        otpSendStatus.className =
                            'mt-2 text-xs font-bold text-green-600';
                    }

                    if (ownerPhone) {
                        ownerPhone.readOnly = true;
                    }

                    sendOtpBtn.dataset.originalText =
                        'Resend OTP';

                    if (phoneOtp) {
                        phoneOtp.value = '';
                    }

                    lastVerifiedOtp = '';

                    phoneOtp?.focus();

                } catch (error) {
                    if (otpSendStatus) {
                        otpSendStatus.textContent =
                            error.message;

                        otpSendStatus.className =
                            'mt-2 text-xs font-bold text-red-600';
                    }

                    if (ownerPhone) {
                        ownerPhone.readOnly = false;
                    }

                } finally {
                    requestRunning = false;

                    setButtonLoading(
                        sendOtpBtn,
                        false
                    );
                }
            }
        );

        phoneOtp?.addEventListener(
            'input',
            function () {
                phoneOtp.value =
                    cleanOtp(phoneOtp.value);

                if (otpVerifyStatus) {
                    otpVerifyStatus.textContent = '';
                }

                if (
                    phoneOtp.value.length === 6
                ) {
                    verifyOtpAutomatically();
                }
            }
        );

        if (
            'OTPCredential' in window &&
            navigator.credentials &&
            window.AbortController
        ) {
            const otpAbortController =
                new AbortController();

            navigator.credentials.get({
                otp: {
                    transport: ['sms']
                },

                signal:
                    otpAbortController.signal

            }).then(function (otpCredential) {
                if (!otpCredential?.code) {
                    return;
                }

                if (phoneOtp) {
                    phoneOtp.value =
                        cleanOtp(
                            otpCredential.code
                        );
                }

                verifyOtpAutomatically();

            }).catch(function () {
            });
        }

        ownerPhone?.addEventListener(
            'input',
            function () {
                if (
                    phoneVerified &&
                    phoneVerified.value === '1'
                ) {
                    return;
                }

                ownerPhone.value =
                    cleanPhone(ownerPhone.value);

                if (businessMobile) {
                    businessMobile.value =
                        ownerPhone.value;
                }

                otpSection?.classList.add(
                    'hidden'
                );

                if (phoneOtp) {
                    phoneOtp.value = '';
                }

                lastVerifiedOtp = '';
            }
        );

        ownerPhone?.addEventListener(
            'keydown',
            function (event) {
                if (
                    event.key === 'Enter' &&
                    validPhone(
                        cleanPhone(
                            ownerPhone.value
                        )
                    )
                ) {
                    event.preventDefault();
                    sendOtpBtn?.click();
                }
            }
        );

        prevBtn?.addEventListener(
            'click',
            function () {
                if (
                    requestRunning ||
                    finalSubmissionRunning
                ) {
                    return;
                }

                if (currentStep > 0) {
                    showStep(currentStep - 1);
                }
            }
        );

        skipBusinessBtn?.addEventListener(
            'click',
            async function () {
                if (requestRunning) {
                    return;
                }

                requestRunning = true;

                setButtonLoading(
                    skipBusinessBtn,
                    true,
                    'Saving...'
                );

                businessSkipped.value = '1';

                [
                    'business_name',
                    'business_email',
                    'business_type_id',
                    'gstin',
                    'address',
                    'state',
                    'state_code'
                ].forEach(function (fieldName) {
                    const field =
                        form.querySelector(
                            '[name="' +
                            fieldName +
                            '"]'
                        );

                    if (field) {
                        field.value = '';
                    }
                });

                if (stateSelect) {
                    stateSelect.value = '';
                }

                syncStateFields();

                const saved =
                    await saveCurrentStep(2);

                requestRunning = false;

                setButtonLoading(
                    skipBusinessBtn,
                    false
                );

                if (saved) {
                    showStep(2);
                }
            }
        );

        async function submitFinalRegistration() {
            if (
                finalSubmissionRunning ||
                requestRunning
            ) {
                return;
            }

            hideMessage();

            if (
                !phoneVerified ||
                phoneVerified.value !== '1'
            ) {
                showMessage(
                    'Pehle mobile number verify kijiye.',
                    'error'
                );

                showStep(0);
                return;
            }

            finalSubmissionRunning = true;

            setButtonLoading(
                submitBtn,
                true,
                'Creating Account...'
            );

            if (skipBillingBtn) {
                skipBillingBtn.disabled = true;
            }

            try {
                syncStateFields();

                const businessNameField =
                    form.querySelector(
                        '[name="business_name"]'
                    );

                const businessTypeField =
                    form.querySelector(
                        '[name="business_type_id"]'
                    );

                if (
                    !businessNameField?.value.trim() ||
                    !businessTypeField?.value
                ) {
                    businessSkipped.value = '1';
                }

                const saved =
                    await saveCurrentStep(3);

                if (!saved) {
                    throw new Error(
                        'Last step database me save nahi hua.'
                    );
                }

                /*
                * Backend complete route successful redirect karega.
                * Validation fail hone se pehle localStorage ko completed
                * mark nahi karna hai.
                *
                * Native form submit se submit event dobara trigger nahi hoga.
                */
                HTMLFormElement.prototype.submit.call(
                    form
                );

            } catch (error) {
                finalSubmissionRunning = false;

                if (skipBillingBtn) {
                    skipBillingBtn.disabled = false;
                }

                setButtonLoading(
                    submitBtn,
                    false
                );

                showMessage(
                    error.message
                    || 'Registration complete nahi ho paya.',
                    'error'
                );
            }
        }

        submitBtn?.addEventListener(
            'click',
            async function () {
                if (
                    requestRunning ||
                    finalSubmissionRunning
                ) {
                    return;
                }

                /*
                * Step 2 par ye button next step kholega.
                */
                if (currentStep === 1) {
                    requestRunning = true;

                    setButtonLoading(
                        submitBtn,
                        true,
                        'Saving...'
                    );

                    businessSkipped.value = '0';

                    const saved =
                        await saveCurrentStep(2);

                    requestRunning = false;

                    setButtonLoading(
                        submitBtn,
                        false
                    );

                    if (saved) {
                        showStep(2);
                    }

                    return;
                }

                /*
                * Step 3 par final account create hoga.
                */
                if (currentStep === 2) {
                    billingSkipped.value = '0';

                    await submitFinalRegistration();
                }
            }
        );

        skipBillingBtn?.addEventListener(
            'click',
            async function () {
                if (
                    requestRunning ||
                    finalSubmissionRunning
                ) {
                    return;
                }

                billingSkipped.value = '1';

                const gstField =
                    form.querySelector(
                        '[name="gst_enabled"]'
                    );

                const prefixField =
                    form.querySelector(
                        '[name="invoice_base_prefix"]'
                    );

                const roundingModeField =
                    form.querySelector(
                        '[name="rounding_mode"]'
                    );

                const roundingStepField =
                    form.querySelector(
                        '[name="rounding_step"]'
                    );

                if (gstField) {
                    gstField.value = '0';
                }

                if (prefixField) {
                    prefixField.value = 'RV/SL';
                }

                if (roundingModeField) {
                    roundingModeField.value =
                        'nearest';
                }

                if (roundingStepField) {
                    roundingStepField.value =
                        '1.00';
                }

                await submitFinalRegistration();
            }
        );

        form.addEventListener(
            'submit',
            function (event) {
                event.preventDefault();

                if (
                    currentStep === 2 &&
                    !requestRunning &&
                    !finalSubmissionRunning
                ) {
                    submitFinalRegistration();
                }
            }
        );

        stateSelect?.addEventListener(
            'change',
            syncStateFields
        );

        if (stateSelect?.value) {
            syncStateFields();
        }

        gstinInput?.addEventListener(
            'input',
            function () {
                const gstin =
                    gstinInput.value
                        .toUpperCase()
                        .replace(/\s/g, '')
                        .slice(0, 15);

                gstinInput.value = gstin;

                if (gstEnabled) {
                    gstEnabled.value =
                        gstin ? '1' : '0';
                }

                if (gstin.length < 2) {
                    return;
                }

                const gstStateCode =
                    gstin.substring(0, 2);

                const matchingOption =
                    Array.from(
                        stateSelect?.options || []
                    ).find(function (option) {
                        return option.value.startsWith(
                            gstStateCode + ','
                        );
                    });

                if (matchingOption && stateSelect) {
                    stateSelect.value =
                        matchingOption.value;

                    syncStateFields();
                }
            }
        );

        if (
            phoneVerified &&
            phoneVerified.value === '1'
        ) {
            if (ownerPhone) {
                ownerPhone.readOnly = true;
            }

            if (businessMobile) {
                businessMobile.value =
                    cleanPhone(ownerPhone?.value);
            }

            sendOtpBtn?.classList.add(
                'hidden'
            );

            otpSection?.classList.remove(
                'hidden'
            );

            phoneOtp?.classList.add(
                'hidden'
            );

            if (otpVerifyStatus) {
                otpVerifyStatus.textContent =
                    'Mobile number already verified hai.';

                otpVerifyStatus.className =
                    'mt-2 text-xs font-bold text-green-600';
            }

            showStep(1);

        } else {
            showStep(0);
        }

        if (
            Array.isArray(
                serverValidationErrors
            ) &&
            serverValidationErrors.length > 0
        ) {
            registrationCompleted = false;

            localStorage.removeItem(
                REGISTRATION_COMPLETED_KEY
            );

            const stepOneFields = [
                'name',
                'phone'
            ];

            const stepTwoFields = [
                'business_name',
                'business_email',
                'mobile',
                'business_type_id',
                'gstin',
                'address',
                'state',
                'state_code'
            ];

            const hasStepOneError =
                serverValidationFields.some(
                    function (field) {
                        return stepOneFields.includes(
                            field
                        );
                    }
                );

            const hasStepTwoError =
                serverValidationFields.some(
                    function (field) {
                        return stepTwoFields.includes(
                            field
                        );
                    }
                );

            if (hasStepOneError) {
                showStep(0);
            } else if (hasStepTwoError) {
                showStep(1);
            } else {
                showStep(2);
            }

            registrationModal?.classList.add(
                'show'
            );

            registrationModal?.setAttribute(
                'aria-hidden',
                'false'
            );

            document.body.classList.add(
                'registration-modal-open'
            );

            if (messageBox) {
                messageBox.innerHTML =
                    '<div class="font-black mb-2">'
                    + 'Registration complete nahi hua:'
                    + '</div>'
                    + '<ul class="list-disc pl-5 space-y-1">'
                    + serverValidationErrors
                        .map(function (error) {
                            return '<li>'
                                + String(error)
                                    .replace(
                                        /&/g,
                                        '&amp;'
                                    )
                                    .replace(
                                        /</g,
                                        '&lt;'
                                    )
                                    .replace(
                                        />/g,
                                        '&gt;'
                                    )
                                    .replace(
                                        /"/g,
                                        '&quot;'
                                    )
                                    .replace(
                                        /'/g,
                                        '&#039;'
                                    )
                                + '</li>';
                        })
                        .join('')
                    + '</ul>';

                messageBox.className =
                    'registration-message show error';
            }

            finalSubmissionRunning = false;
            requestRunning = false;

            setButtonLoading(
                submitBtn,
                false
            );

            if (skipBillingBtn) {
                skipBillingBtn.disabled = false;
            }

            dialog?.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }
    });
</script>
@endsection