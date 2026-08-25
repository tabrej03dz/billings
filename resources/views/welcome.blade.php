@extends('frontend.layout')
@section('content')

<style>
    #typingText::after {
    content: "|";
    animation: blink 0.7s infinite;
    color: #2624CC;
}

@keyframes blink {
    50% { opacity: 0; }
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

                    {{-- <h1 class="text-4xl sm:text-5xl lg:text-7xl font-black leading-[1.05] tracking-tight text-mvDark">
                        Typing Kam,
                        <span class="brand-text">Billing Fast.</span>
                    </h1> --}}

                    <h1 class="min-h-[150px] sm:min-h-[160px] lg:min-h-[150px] text-4xl sm:text-5xl lg:text-7xl font-black leading-[1.05] tracking-tight text-mvDark">
                        <span id="typingText"></span>
                    </h1>

                    <p class="mt-6 text-lg text-slate-600 max-w-xl leading-8">
                        MyVictory Billing GST invoice, sale & purchase management, stock management,
                        customer management, WhatsApp invoice sharing aur photo based smart entry features ke saath
                        modern shops ke liye complete billing software hai.
                    </p>

                    <div class="mt-8 flex flex-wrap gap-4">
                        <a href="#contact" class="px-8 py-4 rounded-full brand-gradient text-white font-black shadow-xl shadow-blue-200 hover:opacity-90">
                            Get Free Demo
                        </a>
                        <a href="https://wa.me/917753800444" class="px-8 py-4 rounded-full bg-white border border-slate-300 font-black hover:border-mvBlue text-mvDark">
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

                {{-- HERO IMAGE COLLAGE --}}
                <div class="relative">
                    <div class="absolute -top-6 -left-5 z-20 bg-white rounded-3xl p-5 soft-card border border-slate-100 hidden sm:block">
                        <div class="text-xs text-slate-500">Today Sales</div>
                        <div class="text-3xl font-black text-mvDark">₹1,27,270</div>
                        <div class="text-xs text-mvBlue font-bold mt-1">Invoice shared on WhatsApp</div>
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
                                        <div class="text-xs text-slate-500">Invoice Status</div>
                                        <div class="font-black text-lg text-mvDark">PDF Ready</div>
                                    </div>
                                    <div class="h-12 w-12 rounded-2xl brand-gradient text-white flex items-center justify-center font-black">✓</div>
                                </div>
                                <div class="mt-4 bg-slate-100 rounded-full h-2">
                                    <div class="brand-gradient h-2 rounded-full w-[82%]"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="absolute -bottom-7 right-6 z-20 brand-gradient text-white rounded-3xl p-5 soft-card hidden sm:block">
                        <div class="text-xs text-blue-100">Smart Billing • GST Invoice</div>
                        <div class="text-2xl font-black">Retail • Wholesale • Services</div>
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
                ['Photo Based Entry', 'Customer & product smart entry'],
                ['WhatsApp Sharing', 'Invoice directly share karein'],
            ] as $item)
                <div class="rounded-3xl bg-slate-50 border border-slate-200 p-6 hover:bg-[#f1f0ff] transition">
                    <h3 class="font-black text-lg text-mvDark">✅ {{ $item[0] }}</h3>
                    <p class="text-sm text-slate-500 mt-1">{{ $item[1] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- FEATURES --}}
    <section id="features" class="py-20 bg-[#f5f7ff]">
        <div class="max-w-7xl mx-auto px-4 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-14">
                <p class="text-mvBlue font-black uppercase tracking-widest text-sm">Software Features</p>
                <h2 class="text-3xl lg:text-5xl font-black mt-3 text-mvDark">Modern shops ke liye complete billing solution</h2>
                <p class="mt-4 text-slate-600 text-lg">Smart Billing • GST Invoice • Stock Management • Easy to Use • Fast Billing</p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach([
                    ['🧾','GST Billing','Professional GST invoice, tax, discount aur PDF bill create karein.'],
                    ['🛒','Sale & Purchase','Sale entry aur purchase management ko organized rakhein.'],
                    ['📦','Stock Management','Product quantity, purchase, sale aur stock easily track karein.'],
                    ['👥','Customer Management','Customer details, history aur records ek jagah manage karein.'],
                    ['📸','Photo Based Smart Entry','Photo se customer create aur photo se product add karne ki smart facility.'],
                    ['⚡','Fast & Easy Billing','Typing kam, billing fast — daily shop work ke liye simple flow.'],
                    ['📲','WhatsApp Invoice Sharing','Invoice PDF customer ko directly WhatsApp par bhejein.'],
                    ['☁️','Cloud Based Management','Laptop, desktop ya mobile browser se kahi se bhi manage karein.'],
                ] as $feature)
                    <div class="bg-white rounded-[2rem] p-7 border border-slate-200 soft-card hover:-translate-y-1 transition">
                        <div class="h-16 w-16 rounded-2xl bg-[#f1f0ff] flex items-center justify-center text-3xl mb-6">
                            {{ $feature[0] }}
                        </div>
                        <h3 class="text-xl font-black text-mvDark">{{ $feature[1] }}</h3>
                        <p class="text-slate-600 mt-3 leading-7">{{ $feature[2] }}</p>
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
                                <div class="text-2xl font-black text-mvBlue">Fast</div>
                                <div class="text-xs text-slate-500">Billing</div>
                            </div>
                            <div>
                                <div class="text-2xl font-black text-mvBlue">Stock</div>
                                <div class="text-xs text-slate-500">Control</div>
                            </div>
                            <div>
                                <div class="text-2xl font-black text-mvBlue">Photo</div>
                                <div class="text-xs text-slate-500">Smart Entry</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <p class="text-mvBlue font-black uppercase tracking-widest text-sm">Advantages</p>
                    <h2 class="text-3xl lg:text-5xl font-black mt-3 text-mvDark">Manual billing se smart billing par shift karein</h2>
                    <p class="mt-5 text-lg text-slate-600 leading-8">
                        Manual register, calculator aur repeated customer/product entry se time waste hota hai.
                        MyVictory Billing aapko ek organized system deta hai jisse billing fast aur business control better hota hai.
                    </p>

                    <div class="mt-8 space-y-4">
                        @foreach([
                            ['Typing Kam, Billing Fast', 'Photo based smart entry aur saved data se billing jaldi hoti hai.'],
                            ['Calculation mistakes reduce hoti hain', 'GST, discount, total aur round-off system se manage hota hai.'],
                            ['Stock control better hota hai', 'Purchase aur sale ke baad stock records easily track hote hain.'],
                            ['Customer trust improve hota hai', 'Professional PDF invoice customer ko premium experience deta hai.'],
                        ] as $benefit)
                            <div class="flex gap-4 rounded-3xl bg-slate-50 border border-slate-200 p-5">
                                <div class="h-11 w-11 rounded-2xl brand-gradient text-white flex items-center justify-center font-black shrink-0">✓</div>
                                <div>
                                    <h3 class="font-black text-mvDark">{{ $benefit[0] }}</h3>
                                    <p class="text-sm text-slate-600 mt-1">{{ $benefit[1] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- BUSINESS LIST --}}
    <section id="business" class="py-20 bg-mvDark text-white overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 lg:px-8">
            <div class="text-center max-w-4xl mx-auto mb-12">
                <p class="text-blue-200 font-black uppercase tracking-widest text-sm">Perfect For</p>
                <h2 class="text-3xl lg:text-5xl font-black mt-3">
                    All Types of Small & Medium Businesses
                </h2>
                <p class="mt-5 text-slate-300 text-lg leading-8">
                    Garment, cosmetic, gift shop, boutique, artificial jewellery, mobile accessories, bakery,
                    stationery, general store aur more businesses ke liye simple billing solution.
                </p>
            </div>

            @php
                $businesses = [
                    'Garment Shops','Kids Wear Stores','Ladies Wear Shops','Footwear Shops',
                    'Cosmetic Stores','Gift Shops','Fancy Stores','Artificial Jewellery Shops',
                    'Mobile Accessories Shops','Toy Shops','Stationery Shops','Pooja Item Stores',
                    'General Stores','Home Decor Shops','Kitchenware Shops','Bakery Shops',
                    'Sweet Shops','Dry Fruit Stores','Namkeen Shops','Tea Shops','Juice Shops',
                    'Ice Cream Parlours','Small Cafes','Mobile Repair Shops','Computer Repair Shops',
                    'Printer Repair Shops','Car Accessories Shops','Bike Accessories Shops',
                    'Salons','Spa Centers','Gyms','Coaching Institutes','Tuition Centers',
                    'Cosmetic Wholesalers','Artificial Jewellery Wholesalers','Gift Item Wholesalers',
                    'Stationery Wholesalers','Mobile Accessories Distributors','Boutiques',
                    'Ladies Fashion Stores','Small Supermarkets','Mini Mart Stores',
                    'Daily Use Product Shops','Electronics Accessory Stores','Bags & Luggage Shops',
                    'Watch Shops','Sports Item Shops','Pet Shops','Furniture Decor Stores',
                    'Crockery Shops','Festival Decoration Shops','Book Stores',
                    'Hardware Light Retail Shops','Uniform Shops','Tailoring Material Shops',
                    'Baby Product Stores','Perfume & Ittar Shops','Organic Product Stores'
                ];
            @endphp

            <div class="grid sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach($businesses as $business)
                    <div class="rounded-2xl bg-white/10 border border-white/10 px-5 py-4 text-sm font-bold text-slate-100 hover:bg-mvBlue transition">
                        ✅ {{ $business }}
                    </div>
                @endforeach
            </div>

            <div class="mt-12 text-center">
                <div class="inline-flex flex-wrap justify-center gap-3 rounded-3xl bg-white text-mvDark px-8 py-5 font-black soft-card">
                    <span>Smart Billing</span>
                    <span>•</span>
                    <span>GST Invoice</span>
                    <span>•</span>
                    <span>Stock Management</span>
                    <span>•</span>
                    <span>Easy to Use</span>
                    <span>•</span>
                    <span>Fast Billing</span>
                </div>
            </div>
        </div>
    </section>

    {{-- HOW IT WORKS --}}
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-14">
                <p class="text-mvBlue font-black uppercase tracking-widest text-sm">How It Works</p>
                <h2 class="text-3xl lg:text-5xl font-black mt-3 text-mvDark">Start billing in 3 easy steps</h2>
            </div>

            <div class="grid lg:grid-cols-3 gap-8">
                @foreach([
                    ['01','Business Setup','Shop name, GST number, address, logo aur invoice settings add karein.'],
                    ['02','Create Bill Fast','Customer select karein, product/service add karein aur invoice generate karein.'],
                    ['03','Share & Track','PDF download karein ya WhatsApp par bhejein aur reports me track karein.'],
                ] as $step)
                    <div class="rounded-[2rem] bg-[#f5f7ff] border border-slate-200 p-8 hover:bg-mvBlue hover:text-white transition">
                        <div class="text-6xl font-black opacity-20">{{ $step[0] }}</div>
                        <h3 class="text-2xl font-black mt-8">{{ $step[1] }}</h3>
                        <p class="mt-4 leading-7 opacity-80">{{ $step[2] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- TESTIMONIALS --}}
    <section id="testimonials" class="py-20 bg-[#f5f7ff]">
        <div class="max-w-7xl mx-auto px-4 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-14">
                <p class="text-mvBlue font-black uppercase tracking-widest text-sm">Testimonials</p>
                <h2 class="text-3xl lg:text-5xl font-black mt-3 text-mvDark">Professional billing se customer impression better hota hai</h2>
            </div>

            <div class="grid md:grid-cols-3 gap-6">
                @foreach([
                    ['Garment Store','Billing fast ho gaya aur WhatsApp invoice feature daily use me bahut helpful hai.'],
                    ['Cosmetic Shop','Stock aur sales reports easily mil jati hain, daily work simple ho gaya.'],
                    ['Mobile Accessories Store','Invoice PDF professional lagta hai, customer ko premium feel milta hai.'],
                ] as $review)
                    <div class="bg-white rounded-[2rem] p-8 border border-slate-200 soft-card">
                        <div class="text-yellow-400 text-xl">★★★★★</div>
                        <p class="text-slate-600 leading-8 mt-5">“{{ $review[1] }}”</p>
                        <div class="mt-7 flex items-center gap-3">
                            <div class="h-12 w-12 rounded-full brand-gradient text-white flex items-center justify-center font-black">
                                {{ substr($review[0], 0, 1) }}
                            </div>
                            <div>
                                <div class="font-black text-mvDark">{{ $review[0] }}</div>
                                <div class="text-sm text-slate-500">Small Business</div>
                            </div>
                        </div>
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
                    Choose the best plan for your shop or growing business.
                </p>

            </div>


            @if($plans->count())

                <div class="grid md:grid-cols-3 gap-8">

                    @foreach($plans as $plan)

                        @php
                            $isPopular = $plan->is_recommended;
                        @endphp


                        <div class="rounded-[2rem] p-8 relative transition hover:-translate-y-1
                            {{ $isPopular
                                ? 'brand-gradient text-white shadow-2xl shadow-blue-200 scale-105'
                                : 'bg-slate-50 border border-slate-200 text-mvDark'
                            }}">


                            {{-- RECOMMENDED --}}
                            @if($isPopular)

                                <div class="absolute -top-4 right-8 bg-white text-mvBlue
                                    text-xs font-black px-4 py-2 rounded-full shadow-lg">

                                    Most Recommended ⭐

                                </div>

                            @endif


                            {{-- PLAN NAME --}}
                            <h3 class="text-2xl font-black uppercase">
                                {{ $plan->name }}
                            </h3>


                            {{-- DESCRIPTION --}}
                            <p class="mt-2 {{ $isPopular ? 'text-blue-100' : 'text-slate-500' }}">

                                {{
                                    $plan->subtitle
                                    ?: ($plan->description
                                    ?: 'Perfect for Small & Medium Businesses')
                                }}

                            </p>


                            {{-- PRICE --}}
                            <div class="mt-6">

                                <div class="flex items-end gap-2 flex-wrap">

                                    {{-- BASE PRICE --}}
                                    <div class="text-4xl font-black">

                                        ₹{{ number_format($plan->price, 0) }}

                                    </div>


                                    {{-- DURATION --}}
                                    <span class="text-sm mb-1
                                        {{ $isPopular ? 'text-blue-100' : 'text-slate-500' }}">

                                        /
                                        {{ $plan->duration_days >= 365
                                            ? 'Year'
                                            : $plan->duration_days . ' Days'
                                        }}

                                    </span>

                                </div>


                                {{-- GST PERCENTAGE ONLY --}}
                                <div class="mt-2 text-sm font-bold
                                    {{ $isPopular ? 'text-blue-100' : 'text-slate-500' }}">

                                    + {{ $plan->tax ?? '18' }}% GST

                                </div>

                            </div>


                            {{-- FEATURES --}}
                            <ul class="mt-7 space-y-3 text-sm
                                {{ $isPopular ? 'text-blue-50' : 'text-slate-600' }}">

                                @forelse($plan->planFeatures as $feature)

                                    <li>

                                        @if($feature->icon)

                                            {{ $feature->icon }}

                                        @else

                                            ✔

                                        @endif

                                        {{ $feature->title }}

                                    </li>

                                @empty

                                    <li>✔ GST Billing</li>

                                    <li>✔ Customer Management</li>

                                    <li>✔ Invoice Print / Share</li>

                                @endforelse

                            </ul>


                            {{-- BUTTONS --}}
                            <div class="mt-8 grid grid-cols-1 sm:grid-cols-2 gap-3">

                                {{-- FREE TRIAL --}}
                                <button
                                    type="button"
                                    data-plan-id="{{ $plan->id }}"
                                    data-trial="1"
                                    class="open-registration-modal block w-full text-center rounded-full py-4 px-4 font-black border
                                    {{ $isPopular
                                        ? 'bg-white/10 text-white border-white/40 hover:bg-white/20'
                                        : 'bg-white text-mvBlue border-mvBlue hover:bg-blue-50'
                                    }}"
                                >
                                    Start Free Trial
                                </button>


                                {{-- PAID PLAN --}}
                                <button
                                    type="button"
                                    data-plan-id="{{ $plan->id }}"
                                    data-trial="0"
                                    class="open-registration-modal block w-full text-center rounded-full py-4 px-4 font-black
                                    {{ $isPopular
                                        ? 'bg-white text-mvBlue hover:bg-blue-50'
                                        : 'bg-mvDark text-white hover:opacity-95'
                                    }}"
                                >
                                    Start Plan
                                </button>

                            </div>

                        </div>

                    @endforeach

                </div>


            @else

                <div class="text-center bg-slate-50 border border-slate-200 rounded-3xl p-10">

                    <h3 class="text-2xl font-black text-mvDark">
                        No active plans available
                    </h3>

                    <p class="text-slate-500 mt-2">
                        Please add active plans from admin panel.
                    </p>

                </div>

            @endif

        </div>

    </section>

    {{-- CTA --}}
    <section class="py-16 brand-gradient text-white">
        <div class="max-w-7xl mx-auto px-4 lg:px-8 text-center">
            <h2 class="text-3xl lg:text-5xl font-black">🚀 MY VICTORY BILLING SOFTWARE</h2>
            <p class="mt-4 text-xl text-blue-100">Smart Billing Software for Modern Shops 🧾</p>

            <div class="mt-8 flex flex-wrap justify-center gap-3 text-sm font-black">
                <span class="bg-white/15 border border-white/20 rounded-full px-5 py-3">GST Billing</span>
                <span class="bg-white/15 border border-white/20 rounded-full px-5 py-3">Sale & Purchase</span>
                <span class="bg-white/15 border border-white/20 rounded-full px-5 py-3">Stock Management</span>
                <span class="bg-white/15 border border-white/20 rounded-full px-5 py-3">Mobile Friendly</span>
                <span class="bg-white/15 border border-white/20 rounded-full px-5 py-3">WhatsApp Sharing</span>
                <span class="bg-white/15 border border-white/20 rounded-full px-5 py-3">Photo Smart Entry</span>
            </div>

            <a href="#contact" class="mt-9 inline-block rounded-full bg-white text-mvBlue px-8 py-4 font-black">
                Demo Available
            </a>
        </div>
    </section>

    {{-- CONTACT --}}
    <section id="contact" class="py-20 hero-bg">
        <div class="max-w-7xl mx-auto px-4 lg:px-8">
            <div class="bg-white rounded-[2.5rem] overflow-hidden border border-slate-200 soft-card">
                <div class="grid lg:grid-cols-2">
                    <div class="p-8 lg:p-12">
                        <p class="font-black uppercase tracking-widest text-mvBlue text-sm">Book Demo</p>
                        <h2 class="text-3xl lg:text-5xl font-black mt-4 text-mvDark">Ready to see MyVictory in action?</h2>
                        <p class="mt-5 text-slate-600 text-lg leading-8">
                            Form fill karein ya WhatsApp par message bhejein. Hum aapko GST billing,
                            sale purchase, stock management, photo based smart entry, WhatsApp invoice sharing
                            aur complete setup ka demo dikhaenge.
                        </p>

                        <div class="mt-8 space-y-5">
                            <div class="bg-slate-50 rounded-2xl p-5 border border-slate-200">
                                <div class="text-slate-500 text-sm">Phone / WhatsApp</div>
                                <a href="https://wa.me/917753800444" class="text-2xl font-black text-mvBlue">+91-7753800444</a>
                            </div>
                            <div class="bg-slate-50 rounded-2xl p-5 border border-slate-200">
                                <div class="text-slate-500 text-sm">Email</div>
                                <a href="mailto:support@myvictory.in" class="font-bold text-mvDark">support@myvictory.in</a>
                            </div>
                            <div class="bg-slate-50 rounded-2xl p-5 border border-slate-200">
                                <div class="text-slate-500 text-sm">Office</div>
                                <p class="font-semibold text-mvDark">73 Basement, Ekta Enclave Society, Lakhanpur, Khyora, Kanpur, Uttar Pradesh 208024</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-[#f5f7ff] p-8 lg:p-12">
                        <h3 class="text-2xl font-black mb-5 text-mvDark">Request a free demo</h3>

                        <form action="{{ route('demo-requests.save') }}#contact" method="POST" class="space-y-4">
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

                            <input type="text" name="name" value="{{ old('name') }}" required placeholder="Name"
                                class="w-full rounded-2xl border border-slate-300 px-4 py-4 outline-none focus:border-mvBlue bg-white">

                            <input type="text" name="mobile" value="{{ old('mobile') }}" maxlength="10" pattern="[6-9][0-9]{9}" inputmode="numeric" placeholder="Mobile / WhatsApp"
                                class="w-full rounded-2xl border border-slate-300 px-4 py-4 outline-none focus:border-mvBlue bg-white">

                            <input type="text" name="city" value="{{ old('city') }}" placeholder="City"
                                class="w-full rounded-2xl border border-slate-300 px-4 py-4 outline-none focus:border-mvBlue bg-white">

                            <input type="text" name="business_name" value="{{ old('business_name') }}" placeholder="Business / shop name"
                                class="w-full rounded-2xl border border-slate-300 px-4 py-4 outline-none focus:border-mvBlue bg-white">

                            <select name="plan" class="w-full rounded-2xl border border-slate-300 px-4 py-4 outline-none focus:border-mvBlue bg-white">
                                <option value="">Select Interested Plan</option>
                                <option value="Starter Plan - 999" {{ old('plan') == 'Starter Plan - 999' ? 'selected' : '' }}>Starter Plan - ₹999 / Year</option>
                                <option value="Smart Plan - 2999" {{ old('plan') == 'Smart Plan - 2999' ? 'selected' : '' }}>Smart Plan - ₹2999 / Year</option>
                                <option value="Pro Plan - 5999" {{ old('plan') == 'Pro Plan - 5999' ? 'selected' : '' }}>Pro Plan - ₹5999 / Year</option>
                            </select>

                            <textarea name="message" rows="4" placeholder="How can we help?"
                                class="w-full rounded-2xl border border-slate-300 px-4 py-4 outline-none focus:border-mvBlue bg-white">{{ old('message') }}</textarea>

                            <button type="submit" class="w-full rounded-2xl brand-gradient text-white py-4 font-black hover:opacity-90">
                                Submit Demo Request
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

</main>

<script>
    const words = [
        "Typing Kam, Billing Fast.",
        "Entry Kam, Billing Smart.",
        "Time Save, Billing Easy.",
        "Photo Entry, Invoice Ready.",
        "Smart Shop, Fast Billing.",
        "GST Bill, One Click."
    ];

    let wordIndex = 0;
    let charIndex = 0;
    let isDeleting = false;
    const typingText = document.getElementById("typingText");

    function typeEffect() {
        const currentWord = words[wordIndex];

        if (isDeleting) {
            typingText.innerHTML = currentWord.substring(0, charIndex--);
        } else {
            typingText.innerHTML = currentWord.substring(0, charIndex++);
        }

        let speed = isDeleting ? 45 : 70;

        if (!isDeleting && charIndex === currentWord.length + 1) {
            speed = 1200; // 1.2 second rukega
            isDeleting = true;
        }

        if (isDeleting && charIndex === 0) {
            isDeleting = false;
            wordIndex = (wordIndex + 1) % words.length;
            speed = 300;
        }

        setTimeout(typeEffect, speed);
    }

    typeEffect();
</script>

@endsection