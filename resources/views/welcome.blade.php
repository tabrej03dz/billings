<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MyVictory Billing - GST Billing Software</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- Tailwind CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        html { scroll-behavior: smooth; }
        .soft-grid {
            background-image:
                linear-gradient(rgba(15,23,42,.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(15,23,42,.04) 1px, transparent 1px);
            background-size: 36px 36px;
        }
    </style>
</head>

<body class="bg-white text-slate-900">

{{-- HEADER --}}
<header class="sticky top-0 z-50 bg-white/90 backdrop-blur-xl border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 lg:px-8 h-16 flex items-center justify-between">
        <a href="#top" class="flex items-center gap-3">
            <img src="{{ asset('asset/img/logo.png') }}" class="h-10 w-10 rounded-xl border border-slate-200" alt="MyVictory">
            <div>
                <div class="font-extrabold text-lg leading-none">MyVictory Billing</div>
                <div class="text-xs text-slate-500 mt-1">By Real Victory Groups</div>
            </div>
        </a>

        <nav class="hidden lg:flex items-center gap-8 text-sm font-semibold text-slate-700">
            <a href="#features" class="hover:text-blue-600">Features</a>
            <a href="#software" class="hover:text-blue-600">Software</a>
            <a href="#pricing" class="hover:text-blue-600">Pricing</a>
            <a href="#faq" class="hover:text-blue-600">FAQ</a>
            <a href="#contact" class="hover:text-blue-600">Contact</a>
        </nav>

        <div class="hidden md:flex items-center gap-3">
            <a href="/login" class="px-4 py-2 rounded-xl border border-slate-300 text-sm font-semibold hover:border-blue-500">Login</a>
            <a href="user-register" class="px-4 py-2 rounded-xl bg-slate-900 text-white text-sm font-semibold hover:bg-blue-600">Register</a>
            <a href="#contact" class="px-5 py-2 rounded-xl bg-blue-600 text-white text-sm font-bold hover:bg-blue-700">Book Demo</a>
        </div>

        <button id="mobileToggle" class="lg:hidden h-10 w-10 rounded-xl border border-slate-300 text-xl">☰</button>
    </div>

    <div id="mobileMenu" class="hidden lg:hidden bg-white border-t border-slate-200 px-4 py-4 space-y-3 text-sm font-semibold">
        <a href="#features" class="block">Features</a>
        <a href="#software" class="block">Software</a>
        <a href="#pricing" class="block">Pricing</a>
        <a href="#faq" class="block">FAQ</a>
        <a href="#contact" class="block">Contact</a>
        <a href="/login" class="block text-center rounded-xl border px-4 py-2">Login</a>
        <a href="user-register" class="block text-center rounded-xl bg-blue-600 text-white px-4 py-2">Register</a>
    </div>
</header>

<main id="top">

{{-- HERO --}}
<section class="relative overflow-hidden bg-[#f7fbff] soft-grid">
    <div class="absolute top-0 right-0 w-[520px] h-[520px] bg-blue-200/40 blur-3xl rounded-full"></div>
    <div class="absolute bottom-0 left-0 w-[420px] h-[420px] bg-cyan-200/50 blur-3xl rounded-full"></div>

    <div class="relative max-w-7xl mx-auto px-4 lg:px-8 py-16 lg:py-24">
        <div class="grid lg:grid-cols-2 gap-14 items-center">

            <div>
                <div class="inline-flex items-center gap-2 bg-white border border-blue-100 shadow-sm rounded-full px-4 py-2 text-sm font-bold text-blue-700 mb-6">
                    <span class="h-2.5 w-2.5 rounded-full bg-green-500"></span>
                    GST Billing Software for Jewellery Showrooms
                </div>

                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black leading-tight tracking-tight">
                    Create GST invoices.
                    <span class="block text-blue-600">Send on WhatsApp.</span>
                    Grow faster.
                </h1>

                <p class="mt-6 text-lg text-slate-600 max-w-xl leading-8">
                    MyVictory Billing helps jewellery businesses manage customers, items,
                    GST invoices, reports, WhatsApp sharing and showroom billing in one simple cloud software.
                </p>

                <div class="mt-8 flex flex-wrap gap-4">
                    <a href="#contact" class="px-7 py-4 rounded-2xl bg-blue-600 text-white font-bold shadow-xl shadow-blue-200 hover:bg-blue-700">
                        Get Free Demo
                    </a>
                    <a href="#features" class="px-7 py-4 rounded-2xl bg-white border border-slate-300 font-bold hover:border-blue-500 hover:text-blue-600">
                        Explore Features
                    </a>
                </div>

                <div class="mt-9 flex flex-wrap gap-6 text-sm text-slate-600">
                    <div class="flex items-center gap-2"><span class="text-green-600 font-black">✓</span> No installation</div>
                    <div class="flex items-center gap-2"><span class="text-green-600 font-black">✓</span> Cloud based</div>
                    <div class="flex items-center gap-2"><span class="text-green-600 font-black">✓</span> Jewellery GST ready</div>
                </div>
            </div>

            {{-- DASHBOARD MOCKUP --}}
            <div class="relative">
                <div class="absolute -top-8 -left-8 bg-white rounded-3xl shadow-xl p-5 border border-slate-100 hidden sm:block">
                    <div class="text-xs text-slate-500">Today Sales</div>
                    <div class="text-2xl font-black text-slate-900">₹1,27,270</div>
                    <div class="text-xs text-green-600 font-bold mt-1">+ WhatsApp invoice sent</div>
                </div>

                <div class="absolute -bottom-8 -right-6 bg-white rounded-3xl shadow-xl p-5 border border-slate-100 hidden sm:block">
                    <div class="text-xs text-slate-500">GST Auto</div>
                    <div class="text-2xl font-black text-blue-600">3%</div>
                    <div class="text-xs text-slate-500 mt-1">Gold jewellery billing</div>
                </div>

                <div class="bg-slate-950 rounded-[2rem] shadow-2xl overflow-hidden border-[10px] border-white">
                    <div class="h-12 bg-slate-900 flex items-center px-5 gap-2">
                        <span class="h-3 w-3 rounded-full bg-red-400"></span>
                        <span class="h-3 w-3 rounded-full bg-yellow-400"></span>
                        <span class="h-3 w-3 rounded-full bg-green-400"></span>
                        <div class="ml-4 text-xs text-slate-400">myvictory.in/dashboard</div>
                    </div>

                    <div class="bg-white p-6">
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h3 class="font-black text-xl">Billing Dashboard</h3>
                                <p class="text-sm text-slate-500">Jewellery showroom overview</p>
                            </div>
                            <button class="bg-blue-600 text-white text-xs font-bold px-4 py-2 rounded-xl">New Invoice</button>
                        </div>

                        <div class="grid grid-cols-3 gap-3 mb-5">
                            <div class="rounded-2xl bg-blue-50 p-4">
                                <div class="text-xs text-slate-500">Invoices</div>
                                <div class="font-black text-2xl">128</div>
                            </div>
                            <div class="rounded-2xl bg-green-50 p-4">
                                <div class="text-xs text-slate-500">Customers</div>
                                <div class="font-black text-2xl">540</div>
                            </div>
                            <div class="rounded-2xl bg-orange-50 p-4">
                                <div class="text-xs text-slate-500">Reports</div>
                                <div class="font-black text-2xl">24</div>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-slate-200 overflow-hidden">
                            <div class="grid grid-cols-4 bg-slate-50 text-xs font-bold text-slate-500">
                                <div class="p-3">Item</div>
                                <div class="p-3 text-right">Weight</div>
                                <div class="p-3 text-right">GST</div>
                                <div class="p-3 text-right">Amount</div>
                            </div>
                            <div class="grid grid-cols-4 border-t text-sm">
                                <div class="p-3 font-semibold">22K Necklace</div>
                                <div class="p-3 text-right">18.750g</div>
                                <div class="p-3 text-right">₹3,707</div>
                                <div class="p-3 text-right font-bold">₹1,27,270</div>
                            </div>
                            <div class="grid grid-cols-4 border-t text-sm">
                                <div class="p-3 font-semibold">Making Charge</div>
                                <div class="p-3 text-right">-</div>
                                <div class="p-3 text-right">Auto</div>
                                <div class="p-3 text-right font-bold">₹12,000</div>
                            </div>
                        </div>

                        <div class="mt-5 rounded-2xl bg-slate-900 text-white p-4 flex items-center justify-between">
                            <div>
                                <div class="font-bold">Invoice Ready</div>
                                <div class="text-xs text-slate-400">Send PDF on WhatsApp & Email</div>
                            </div>
                            <div class="bg-green-500 text-white rounded-xl px-4 py-2 text-xs font-bold">Send Now</div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

{{-- STRIP --}}
<section class="bg-white border-y border-slate-200">
    <div class="max-w-7xl mx-auto px-4 lg:px-8 py-8 grid md:grid-cols-4 gap-6">
        <div>
            <div class="text-3xl font-black text-blue-600">1 Click</div>
            <p class="text-sm text-slate-500">Invoice sharing</p>
        </div>
        <div>
            <div class="text-3xl font-black text-blue-600">100%</div>
            <p class="text-sm text-slate-500">Browser based</p>
        </div>
        <div>
            <div class="text-3xl font-black text-blue-600">GST</div>
            <p class="text-sm text-slate-500">Auto calculation</p>
        </div>
        <div>
            <div class="text-3xl font-black text-blue-600">Cloud</div>
            <p class="text-sm text-slate-500">Secure data access</p>
        </div>
    </div>
</section>

{{-- FEATURES --}}
<section id="features" class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 lg:px-8">
        <div class="max-w-3xl mb-12">
            <p class="text-blue-600 font-black uppercase tracking-widest text-sm">Features</p>
            <h2 class="text-3xl lg:text-5xl font-black mt-3">Everything your jewellery showroom needs for billing</h2>
            <p class="mt-4 text-slate-600 text-lg">Simple screens, powerful billing logic and fast invoice sharing.</p>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            @php
                $features = [
                    ['🧾','GST Invoice Creation','Create GST bills with customer details, item details, tax, discount and round-off.'],
                    ['💍','Jewellery Item Structure','Manage gold, silver, diamond, purity, gross weight, making charges and wastage.'],
                    ['📲','WhatsApp & Email Share','Send invoice PDF directly to customer WhatsApp and email from your system.'],
                    ['📊','Sales & GST Reports','Track day-wise sales, customer history, invoice reports and GST summary.'],
                    ['👥','Role Based Access','Give separate access to owner, accountant and billing staff.'],
                    ['☁️','Cloud Software','Use from laptop, desktop or mobile browser without installation.'],
                ];
            @endphp

            @foreach($features as $feature)
                <div class="group rounded-[2rem] border border-slate-200 p-7 hover:border-blue-200 hover:shadow-2xl hover:shadow-blue-100 transition bg-white">
                    <div class="h-16 w-16 rounded-2xl bg-slate-100 group-hover:bg-blue-600 group-hover:text-white flex items-center justify-center text-3xl transition">
                        {{ $feature[0] }}
                    </div>
                    <h3 class="text-xl font-black mt-6">{{ $feature[1] }}</h3>
                    <p class="text-slate-600 mt-3 leading-7">{{ $feature[2] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- SOFTWARE MODULES --}}
<section id="software" class="py-20 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <div>
                <p class="text-blue-600 font-black uppercase tracking-widest text-sm">Software Modules</p>
                <h2 class="text-3xl lg:text-5xl font-black mt-3">One software, multiple business controls</h2>
                <p class="mt-5 text-lg text-slate-600">
                    MyVictory Billing is not only invoice software. It helps you control your daily billing,
                    customers, reports and team access from one dashboard.
                </p>

                <div class="mt-8 space-y-4">
                    <div class="flex gap-4 bg-white p-5 rounded-2xl border border-slate-200">
                        <div class="text-2xl">📦</div>
                        <div>
                            <h3 class="font-black">Item & Customer Master</h3>
                            <p class="text-sm text-slate-500">Save customer and jewellery item data for faster billing.</p>
                        </div>
                    </div>
                    <div class="flex gap-4 bg-white p-5 rounded-2xl border border-slate-200">
                        <div class="text-2xl">📄</div>
                        <div>
                            <h3 class="font-black">Invoice PDF Template</h3>
                            <p class="text-sm text-slate-500">Professional invoice format for jewellery showroom billing.</p>
                        </div>
                    </div>
                    <div class="flex gap-4 bg-white p-5 rounded-2xl border border-slate-200">
                        <div class="text-2xl">🔐</div>
                        <div>
                            <h3 class="font-black">Staff Permission</h3>
                            <p class="text-sm text-slate-500">Control what your staff can view, create or manage.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-[2rem] border border-slate-200 shadow-xl p-6">
                <div class="grid sm:grid-cols-2 gap-4">
                    @foreach([
                        ['Customers','Add & manage buyers','👤'],
                        ['Invoices','GST billing records','🧾'],
                        ['Reports','Sales & tax summary','📊'],
                        ['Items','Jewellery item master','💍'],
                        ['WhatsApp','Instant invoice share','📲'],
                        ['Staff','Role based access','🔑'],
                    ] as $module)
                        <div class="rounded-3xl bg-slate-50 border border-slate-100 p-5 hover:bg-blue-600 hover:text-white transition">
                            <div class="text-3xl mb-4">{{ $module[2] }}</div>
                            <div class="font-black text-lg">{{ $module[0] }}</div>
                            <div class="text-sm opacity-70">{{ $module[1] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

{{-- STEPS --}}
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-14">
            <p class="text-blue-600 font-black uppercase tracking-widest text-sm">How it works</p>
            <h2 class="text-3xl lg:text-5xl font-black mt-3">Start billing in 3 simple steps</h2>
        </div>

        <div class="grid lg:grid-cols-3 gap-8">
            <div class="rounded-[2rem] bg-blue-600 text-white p-8">
                <div class="text-6xl font-black opacity-20">01</div>
                <h3 class="text-2xl font-black mt-8">Create Business Setup</h3>
                <p class="mt-4 text-blue-100 leading-7">Add showroom name, GST, address, invoice settings and basic business details.</p>
            </div>

            <div class="rounded-[2rem] bg-slate-950 text-white p-8">
                <div class="text-6xl font-black opacity-20">02</div>
                <h3 class="text-2xl font-black mt-8">Generate Jewellery Bill</h3>
                <p class="mt-4 text-slate-300 leading-7">Select customer, add item, weight, making charge, GST and generate invoice.</p>
            </div>

            <div class="rounded-[2rem] bg-cyan-600 text-white p-8">
                <div class="text-6xl font-black opacity-20">03</div>
                <h3 class="text-2xl font-black mt-8">Share with Customer</h3>
                <p class="mt-4 text-cyan-100 leading-7">Download PDF or send invoice directly on WhatsApp and Email.</p>
            </div>
        </div>
    </div>
</section>

{{-- PRICING --}}
<section id="pricing" class="py-20 bg-slate-950 text-white">
    <div class="max-w-7xl mx-auto px-4 lg:px-8">
        <div class="text-center mb-14">
            <p class="text-blue-400 font-black uppercase tracking-widest text-sm">Pricing</p>
            <h2 class="text-3xl lg:text-5xl font-black mt-3">Simple subscription pricing</h2>
            <p class="mt-4 text-slate-400">Start small, grow anytime.</p>
        </div>

        <div class="grid md:grid-cols-3 gap-8">
            <div class="rounded-[2rem] bg-white text-slate-900 p-8">
                <h3 class="text-2xl font-black">Starter</h3>
                <p class="text-slate-500 mt-2">Single jewellery shop</p>
                <div class="text-4xl font-black mt-6">₹999 <span class="text-sm text-slate-500">/ year</span></div>
                <ul class="mt-7 space-y-3 text-sm text-slate-600">
                    <li>✓ 1 showroom/location</li>
                    <li>✓ Unlimited invoices</li>
                    <li>✓ Customer master</li>
                    <li>✓ PDF invoice download</li>
                </ul>
                <a href="https://api.whatsapp.com/send/?phone=917753800444" class="mt-8 block text-center rounded-2xl bg-slate-900 text-white py-3 font-bold">Enquiry</a>
            </div>

            <div class="rounded-[2rem] bg-blue-600 p-8 relative shadow-2xl shadow-blue-900/40">
                <div class="absolute -top-4 right-8 bg-yellow-400 text-slate-950 text-xs font-black px-4 py-2 rounded-full">Most Popular</div>
                <h3 class="text-2xl font-black">Growth</h3>
                <p class="text-blue-100 mt-2">WhatsApp + reports</p>
                <div class="text-4xl font-black mt-6">₹1,999 <span class="text-sm text-blue-100">/ year</span></div>
                <ul class="mt-7 space-y-3 text-sm text-blue-50">
                    <li>✓ Up to 3 showrooms</li>
                    <li>✓ WhatsApp & Email invoice</li>
                    <li>✓ Customer & sales reports</li>
                    <li>✓ Basic stock tracking</li>
                    <li>✓ Priority support</li>
                </ul>
                <a href="https://api.whatsapp.com/send/?phone=917753800444" class="mt-8 block text-center rounded-2xl bg-white text-blue-700 py-3 font-black">Enquiry</a>
            </div>

            <div class="rounded-[2rem] bg-white text-slate-900 p-8">
                <h3 class="text-2xl font-black">Enterprise</h3>
                <p class="text-slate-500 mt-2">Multi showroom control</p>
                <div class="text-4xl font-black mt-6">Custom</div>
                <ul class="mt-7 space-y-3 text-sm text-slate-600">
                    <li>✓ Unlimited showrooms</li>
                    <li>✓ Advanced roles</li>
                    <li>✓ Custom reports</li>
                    <li>✓ Integration support</li>
                    <li>✓ Dedicated manager</li>
                </ul>
                <a href="tel:917753800444" class="mt-8 block text-center rounded-2xl bg-slate-900 text-white py-3 font-bold">Call Now</a>
            </div>
        </div>
    </div>
</section>

{{-- FAQ --}}
<section id="faq" class="py-20 bg-slate-50">
    <div class="max-w-4xl mx-auto px-4 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl lg:text-5xl font-black">Frequently Asked Questions</h2>
        </div>

        <div class="space-y-4">
            <details class="bg-white rounded-2xl border border-slate-200 p-6">
                <summary class="font-black cursor-pointer">Kya MyVictory sirf jewellery business ke liye hai?</summary>
                <p class="text-slate-600 mt-3">Haan, ye gold, silver, diamond aur jewellery showrooms ke billing workflow ke liye design hai.</p>
            </details>

            <details class="bg-white rounded-2xl border border-slate-200 p-6">
                <summary class="font-black cursor-pointer">Software install karna padega?</summary>
                <p class="text-slate-600 mt-3">Nahi, MyVictory cloud based software hai. Browser me chalega.</p>
            </details>

            <details class="bg-white rounded-2xl border border-slate-200 p-6">
                <summary class="font-black cursor-pointer">WhatsApp invoice kaise send hoga?</summary>
                <p class="text-slate-600 mt-3">Invoice generate hone ke baad customer ke WhatsApp number par PDF invoice share kar sakte hain.</p>
            </details>

            <details class="bg-white rounded-2xl border border-slate-200 p-6">
                <summary class="font-black cursor-pointer">Data safe rahega?</summary>
                <p class="text-slate-600 mt-3">Data secure cloud server par store hota hai aur role based access ke saath protected rahta hai.</p>
            </details>
        </div>
    </div>
</section>

{{-- CONTACT --}}
<section id="contact" class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 lg:px-8">
        <div class="rounded-[2.5rem] bg-gradient-to-br from-blue-600 to-slate-950 overflow-hidden">
            <div class="grid lg:grid-cols-2 gap-10 p-8 lg:p-12 text-white">
                <div>
                    <p class="font-black uppercase tracking-widest text-blue-200 text-sm">Book Demo</p>
                    <h2 class="text-3xl lg:text-5xl font-black mt-4">Ready to see MyVictory in action?</h2>
                    <p class="mt-5 text-blue-100 text-lg leading-8">
                        Form fill karein ya WhatsApp par message bhejein. Hum aapko billing,
                        reports, WhatsApp invoices aur complete setup ka demo dikhaenge.
                    </p>

                    <div class="mt-8 space-y-5">
                        <div>
                            <div class="text-blue-200 text-sm">Phone / WhatsApp</div>
                            <a href="https://wa.me/917753800444" class="text-2xl font-black">+91-7753800444</a>
                        </div>
                        <div>
                            <div class="text-blue-200 text-sm">Email</div>
                            <a href="mailto:support@myvictory.in" class="font-bold">support@myvictory.in</a>
                        </div>
                        <div>
                            <div class="text-blue-200 text-sm">Office</div>
                            <p class="font-semibold">73 Basement, Ekta Enclave Society, Lakhanpur, Khyora, Kanpur, Uttar Pradesh 208024</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white text-slate-900 rounded-[2rem] p-7">
                    <h3 class="text-2xl font-black mb-5">Request a free demo</h3>

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
                               class="w-full rounded-2xl border border-slate-300 px-4 py-3 outline-none focus:border-blue-600">

                        <input type="text" name="mobile" value="{{ old('mobile') }}" maxlength="10" pattern="[6-9][0-9]{9}" inputmode="numeric" placeholder="Mobile / WhatsApp"
                               class="w-full rounded-2xl border border-slate-300 px-4 py-3 outline-none focus:border-blue-600">

                        <input type="text" name="city" value="{{ old('city') }}" placeholder="City"
                               class="w-full rounded-2xl border border-slate-300 px-4 py-3 outline-none focus:border-blue-600">

                        <input type="text" name="business_name" value="{{ old('business_name') }}" placeholder="Jewellery business name"
                               class="w-full rounded-2xl border border-slate-300 px-4 py-3 outline-none focus:border-blue-600">

                        <textarea name="message" rows="4" placeholder="How can we help?"
                                  class="w-full rounded-2xl border border-slate-300 px-4 py-3 outline-none focus:border-blue-600">{{ old('message') }}</textarea>

                        <button type="submit" class="w-full rounded-2xl bg-blue-600 text-white py-4 font-black hover:bg-blue-700">
                            Submit Request
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

</main>

<footer class="bg-white border-t border-slate-200 py-7">
    <div class="max-w-7xl mx-auto px-4 lg:px-8 flex flex-col md:flex-row justify-between gap-4 text-sm text-slate-500">
        <div>© {{ date('Y') }} MyVictory Billing · Powered by Real Victory Groups</div>
        <div class="flex gap-5">
            <a href="#" class="hover:text-blue-600">Terms & Conditions</a>
            <a href="#" class="hover:text-blue-600">Privacy Policy</a>
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