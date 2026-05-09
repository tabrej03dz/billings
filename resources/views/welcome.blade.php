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
        .hero-bg {
            background:
                radial-gradient(circle at 15% 10%, rgba(37,99,235,.14), transparent 32%),
                radial-gradient(circle at 85% 15%, rgba(245,158,11,.18), transparent 32%),
                linear-gradient(180deg, #fffaf0 0%, #ffffff 45%, #f8fbff 100%);
        }
        .soft-card {
            box-shadow: 0 24px 70px rgba(15, 23, 42, .10);
        }
        .image-mask {
            border-radius: 34px;
        }
    </style>
</head>

<body class="bg-white text-slate-900">

{{-- HEADER --}}
<header class="sticky top-0 z-50 bg-white/90 backdrop-blur-xl border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 lg:px-8 h-20 flex items-center justify-between">
        <a href="#top" class="flex items-center gap-3">
            <img src="{{ asset('asset/img/logo.png') }}" class="h-12 w-12 rounded-2xl border border-slate-200 shadow-sm" alt="MyVictory">
            <div>
                <div class="font-black text-xl leading-none">MyVictory Billing</div>
                <div class="text-xs text-slate-500 mt-1">By Real Victory Groups</div>
            </div>
        </a>

        <nav class="hidden lg:flex items-center gap-8 text-sm font-bold text-slate-600">
            <a href="#features" class="hover:text-blue-600">Features</a>
            <a href="#benefits" class="hover:text-blue-600">Benefits</a>
            <a href="#business" class="hover:text-blue-600">For Business</a>
            <a href="#pricing" class="hover:text-blue-600">Pricing</a>
            <a href="#contact" class="hover:text-blue-600">Demo</a>
        </nav>

        <div class="hidden md:flex items-center gap-3">
            <a href="/login" class="px-5 py-3 rounded-full border border-slate-300 text-sm font-bold hover:border-blue-500">
                Login
            </a>
            <a href="user-register" class="px-5 py-3 rounded-full bg-slate-950 text-white text-sm font-bold">
                Register
            </a>
            <a href="#contact" class="px-6 py-3 rounded-full bg-blue-600 text-white text-sm font-black shadow-lg shadow-blue-200">
                Book Free Demo
            </a>
        </div>

        <button id="mobileToggle" class="lg:hidden h-11 w-11 rounded-xl border border-slate-300 text-xl">☰</button>
    </div>

    <div id="mobileMenu" class="hidden lg:hidden bg-white border-t border-slate-200 px-4 py-4 space-y-3 text-sm font-semibold">
        <a href="#features" class="block">Features</a>
        <a href="#benefits" class="block">Benefits</a>
        <a href="#business" class="block">For Business</a>
        <a href="#pricing" class="block">Pricing</a>
        <a href="#contact" class="block">Demo</a>
        <a href="/login" class="block text-center rounded-xl border px-4 py-3">Login</a>
        <a href="user-register" class="block text-center rounded-xl bg-blue-600 text-white px-4 py-3">Register</a>
    </div>
</header>

<main id="top">

{{-- HERO --}}
<section class="hero-bg relative overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 lg:px-8 py-16 lg:py-24">
        <div class="grid lg:grid-cols-2 gap-14 items-center">

            <div>
                <div class="inline-flex items-center gap-2 rounded-full bg-white border border-blue-100 shadow-sm px-5 py-2 text-sm font-black text-blue-700 mb-6">
                    <span class="h-2.5 w-2.5 rounded-full bg-green-500"></span>
                    Smart GST Billing Software for Jewellery Showrooms
                </div>

                <h1 class="text-4xl sm:text-5xl lg:text-7xl font-black leading-[1.05] tracking-tight">
                    Jewellery billing अब बनेगी
                    <span class="text-blue-600">fast, premium & error-free.</span>
                </h1>

                <p class="mt-6 text-lg text-slate-600 max-w-xl leading-8">
                    MyVictory Billing jewellery showrooms ke liye ek smart cloud billing software hai jisme GST invoice,
                    customer management, item billing, WhatsApp invoice sharing, staff permissions aur business reports sab ek jagah milte hain.
                </p>

                <div class="mt-8 flex flex-wrap gap-4">
                    <a href="#contact" class="px-8 py-4 rounded-full bg-blue-600 text-white font-black shadow-xl shadow-blue-200 hover:bg-blue-700">
                        Get Free Demo
                    </a>
                    <a href="https://wa.me/917753800444" class="px-8 py-4 rounded-full bg-white border border-slate-300 font-black hover:border-blue-500">
                        WhatsApp Now
                    </a>
                </div>

                <div class="mt-10 grid grid-cols-3 gap-4 max-w-xl">
                    <div class="bg-white rounded-3xl p-5 border border-slate-200 soft-card">
                        <div class="text-2xl font-black text-blue-600">3%</div>
                        <p class="text-xs text-slate-500 mt-1">GST Auto</p>
                    </div>
                    <div class="bg-white rounded-3xl p-5 border border-slate-200 soft-card">
                        <div class="text-2xl font-black text-blue-600">1 Click</div>
                        <p class="text-xs text-slate-500 mt-1">Bill Share</p>
                    </div>
                    <div class="bg-white rounded-3xl p-5 border border-slate-200 soft-card">
                        <div class="text-2xl font-black text-blue-600">Cloud</div>
                        <p class="text-xs text-slate-500 mt-1">Any Device</p>
                    </div>
                </div>
            </div>

            {{-- HERO IMAGE COLLAGE --}}
            <div class="relative">
                <div class="absolute -top-6 -left-5 z-20 bg-white rounded-3xl p-5 soft-card border border-slate-100 hidden sm:block">
                    <div class="text-xs text-slate-500">Today Sales</div>
                    <div class="text-3xl font-black text-slate-950">₹1,27,270</div>
                    <div class="text-xs text-green-600 font-bold mt-1">Invoice shared on WhatsApp</div>
                </div>

                <div class="grid grid-cols-12 gap-4">
                    <div class="col-span-7">
                        <img src="https://images.unsplash.com/photo-1606760227091-3dd870d97f1d?auto=format&fit=crop&w=900&q=80"
                             class="image-mask w-full h-[420px] object-cover soft-card"
                             alt="Jewellery showroom billing">
                    </div>
                    <div class="col-span-5 space-y-4 pt-10">
                        <img src="https://images.unsplash.com/photo-1617038260897-41a1f14a8ca0?auto=format&fit=crop&w=700&q=80"
                             class="rounded-[2rem] w-full h-[190px] object-cover soft-card"
                             alt="Jewellery products">
                        <div class="bg-white rounded-[2rem] p-5 border border-slate-200 soft-card">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-xs text-slate-500">Invoice Status</div>
                                    <div class="font-black text-lg">PDF Ready</div>
                                </div>
                                <div class="h-12 w-12 rounded-2xl bg-green-100 text-green-600 flex items-center justify-center font-black">✓</div>
                            </div>
                            <div class="mt-4 bg-slate-100 rounded-full h-2">
                                <div class="bg-blue-600 h-2 rounded-full w-[82%]"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="absolute -bottom-7 right-6 z-20 bg-blue-600 text-white rounded-3xl p-5 soft-card hidden sm:block">
                    <div class="text-xs text-blue-100">GST Jewellery Billing</div>
                    <div class="text-2xl font-black">Gold • Silver • Diamond</div>
                </div>
            </div>

        </div>
    </div>
</section>

{{-- TRUST BAR --}}
<section class="bg-white border-y border-slate-200">
    <div class="max-w-7xl mx-auto px-4 lg:px-8 py-8 grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
        @foreach([
            ['No Installation', 'Direct browser me use karein'],
            ['Smart Invoice', 'Professional GST PDF bill'],
            ['WhatsApp Ready', 'Customer ko bill instantly bhejein'],
            ['Staff Control', 'Owner, accountant aur staff access'],
        ] as $item)
            <div class="rounded-3xl bg-slate-50 border border-slate-200 p-6 hover:bg-blue-50 transition">
                <h3 class="font-black text-lg">{{ $item[0] }}</h3>
                <p class="text-sm text-slate-500 mt-1">{{ $item[1] }}</p>
            </div>
        @endforeach
    </div>
</section>

{{-- FEATURES --}}
<section id="features" class="py-20 bg-[#f8fbff]">
    <div class="max-w-7xl mx-auto px-4 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-14">
            <p class="text-blue-600 font-black uppercase tracking-widest text-sm">Software Features</p>
            <h2 class="text-3xl lg:text-5xl font-black mt-3">Jewellery showroom ke daily billing work ke liye complete solution</h2>
            <p class="mt-4 text-slate-600 text-lg">Billing fast, reporting clear aur customer experience professional.</p>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach([
                ['🧾','GST Invoice Creation','Customer details, item details, tax, discount, round-off ke saath professional invoice.'],
                ['💍','Gold/Silver/Diamond Billing','Purity, weight, making charge, wastage aur jewellery item structure ke saath billing.'],
                ['📲','WhatsApp Invoice Share','Invoice generate hone ke baad customer ko PDF directly WhatsApp par send karein.'],
                ['📊','Sales & GST Reports','Daily sales, customer history, invoice report aur GST summary ek dashboard me.'],
                ['👥','Role Based Access','Owner, accountant aur billing staff ke liye alag permission control.'],
                ['☁️','Cloud Based Software','Laptop, desktop ya mobile browser se kahi se bhi billing manage karein.'],
            ] as $feature)
                <div class="bg-white rounded-[2rem] p-7 border border-slate-200 soft-card hover:-translate-y-1 transition">
                    <div class="h-16 w-16 rounded-2xl bg-blue-50 flex items-center justify-center text-3xl mb-6">
                        {{ $feature[0] }}
                    </div>
                    <h3 class="text-xl font-black">{{ $feature[1] }}</h3>
                    <p class="text-slate-600 mt-3 leading-7">{{ $feature[2] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- BENEFITS WITH IMAGE --}}
<section id="benefits" class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-14 items-center">
            <div class="relative">
                <img src="https://images.unsplash.com/photo-1556742502-ec7c0e9f34b1?auto=format&fit=crop&w=1000&q=80"
                     class="rounded-[2.5rem] w-full h-[540px] object-cover soft-card"
                     alt="Business billing software">

                <div class="absolute bottom-6 left-6 right-6 bg-white/90 backdrop-blur-xl rounded-3xl p-6 border border-white soft-card">
                    <div class="grid grid-cols-3 gap-4 text-center">
                        <div>
                            <div class="text-2xl font-black text-blue-600">Fast</div>
                            <div class="text-xs text-slate-500">Billing</div>
                        </div>
                        <div>
                            <div class="text-2xl font-black text-blue-600">Clean</div>
                            <div class="text-xs text-slate-500">Reports</div>
                        </div>
                        <div>
                            <div class="text-2xl font-black text-blue-600">Easy</div>
                            <div class="text-xs text-slate-500">Sharing</div>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <p class="text-blue-600 font-black uppercase tracking-widest text-sm">Advantages</p>
                <h2 class="text-3xl lg:text-5xl font-black mt-3">Manual billing se smart billing par shift karein</h2>
                <p class="mt-5 text-lg text-slate-600 leading-8">
                    Manual register, calculator aur repeated customer entry se time waste hota hai.
                    MyVictory Billing aapko ek organized system deta hai jisse business ka control better hota hai.
                </p>

                <div class="mt-8 space-y-4">
                    @foreach([
                        ['Calculation mistakes reduce hoti hain', 'GST, discount, total aur round-off system se manage hota hai.'],
                        ['Customer trust improve hota hai', 'Professional PDF invoice customer ko premium experience deta hai.'],
                        ['Staff productivity badhti hai', 'Saved customers/items se invoice jaldi ban jata hai.'],
                        ['Owner ko clear reports milti hain', 'Daily sales, invoice aur customer history easily track hoti hai.'],
                    ] as $benefit)
                        <div class="flex gap-4 rounded-3xl bg-slate-50 border border-slate-200 p-5">
                            <div class="h-11 w-11 rounded-2xl bg-blue-600 text-white flex items-center justify-center font-black shrink-0">✓</div>
                            <div>
                                <h3 class="font-black">{{ $benefit[0] }}</h3>
                                <p class="text-sm text-slate-600 mt-1">{{ $benefit[1] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

{{-- BUSINESS USE CASES --}}
<section id="business" class="py-20 bg-slate-950 text-white overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-14 items-center">
            <div>
                <p class="text-blue-300 font-black uppercase tracking-widest text-sm">Useful For</p>
                <h2 class="text-3xl lg:text-5xl font-black mt-3">Har jewellery business role ke liye simple workflow</h2>
                <p class="mt-5 text-slate-300 text-lg leading-8">
                    Software owner, accountant, billing staff aur customer — sabke experience ko easy banata hai.
                </p>

                <div class="mt-8 grid sm:grid-cols-2 gap-5">
                    @foreach([
                        ['For Owner','Sales reports, staff access aur business overview.'],
                        ['For Accountant','GST summary, invoice records aur reports.'],
                        ['For Billing Staff','Simple screen se fast bill generation.'],
                        ['For Customer','WhatsApp par professional PDF invoice.'],
                    ] as $use)
                        <div class="rounded-3xl bg-white/10 border border-white/10 p-6">
                            <h3 class="text-xl font-black text-blue-200">{{ $use[0] }}</h3>
                            <p class="text-sm text-slate-300 mt-3 leading-6">{{ $use[1] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-white rounded-[2.5rem] p-5 soft-card">
                <img src="https://images.unsplash.com/photo-1520607162513-77705c0f0d4a?auto=format&fit=crop&w=1000&q=80"
                     class="rounded-[2rem] w-full h-[470px] object-cover"
                     alt="Business team working">
            </div>
        </div>
    </div>
</section>

{{-- HOW IT WORKS --}}
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-14">
            <p class="text-blue-600 font-black uppercase tracking-widest text-sm">How It Works</p>
            <h2 class="text-3xl lg:text-5xl font-black mt-3">Start billing in 3 easy steps</h2>
        </div>

        <div class="grid lg:grid-cols-3 gap-8">
            @foreach([
                ['01','Business Setup','Showroom name, GST number, address aur invoice settings add karein.'],
                ['02','Create Jewellery Bill','Customer select karein, item/weight/making charge add karein aur invoice generate karein.'],
                ['03','Share & Track','PDF download karein ya WhatsApp par bhejein aur reports me track karein.'],
            ] as $step)
                <div class="rounded-[2rem] bg-[#f8fbff] border border-slate-200 p-8 hover:bg-blue-600 hover:text-white transition">
                    <div class="text-6xl font-black opacity-20">{{ $step[0] }}</div>
                    <h3 class="text-2xl font-black mt-8">{{ $step[1] }}</h3>
                    <p class="mt-4 leading-7 opacity-80">{{ $step[2] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- TESTIMONIALS --}}
<section id="testimonials" class="py-20 bg-[#f8fbff]">
    <div class="max-w-7xl mx-auto px-4 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-14">
            <p class="text-blue-600 font-black uppercase tracking-widest text-sm">Testimonials</p>
            <h2 class="text-3xl lg:text-5xl font-black mt-3">Professional billing se customer impression better hota hai</h2>
        </div>

        <div class="grid md:grid-cols-3 gap-6">
            @foreach([
                ['R.K. Jewellers','Billing fast ho gaya aur WhatsApp invoice feature daily use me bahut helpful hai.'],
                ['Shree Gold House','Staff easily bill bana leta hai aur owner reports dekh sakta hai.'],
                ['Diamond Palace','Invoice PDF professional lagta hai, customer ko showroom ka premium feel milta hai.'],
            ] as $review)
                <div class="bg-white rounded-[2rem] p-8 border border-slate-200 soft-card">
                    <div class="text-yellow-400 text-xl">★★★★★</div>
                    <p class="text-slate-600 leading-8 mt-5">“{{ $review[1] }}”</p>
                    <div class="mt-7 flex items-center gap-3">
                        <div class="h-12 w-12 rounded-full bg-blue-600 text-white flex items-center justify-center font-black">
                            {{ substr($review[0], 0, 1) }}
                        </div>
                        <div>
                            <div class="font-black">{{ $review[0] }}</div>
                            <div class="text-sm text-slate-500">Jewellery Business</div>
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
            <p class="text-blue-600 font-black uppercase tracking-widest text-sm">Pricing</p>
            <h2 class="text-3xl lg:text-5xl font-black mt-3">Simple subscription pricing</h2>
            <p class="mt-4 text-slate-600">Start small, grow anytime.</p>
        </div>

        <div class="grid md:grid-cols-3 gap-8">
            <div class="rounded-[2rem] bg-slate-50 border border-slate-200 p-8">
                <h3 class="text-2xl font-black">Starter</h3>
                <p class="text-slate-500 mt-2">Single jewellery shop</p>
                <div class="text-4xl font-black mt-6">₹999 <span class="text-sm text-slate-500">/ year</span></div>
                <ul class="mt-7 space-y-3 text-sm text-slate-600">
                    <li>✓ 1 showroom/location</li>
                    <li>✓ Unlimited invoices</li>
                    <li>✓ Customer master</li>
                    <li>✓ PDF invoice download</li>
                </ul>
                <a href="https://api.whatsapp.com/send/?phone=917753800444" class="mt-8 block text-center rounded-full bg-slate-950 text-white py-4 font-black">Enquiry</a>
            </div>

            <div class="rounded-[2rem] bg-blue-600 text-white p-8 relative shadow-2xl shadow-blue-200 scale-105">
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
                <a href="https://api.whatsapp.com/send/?phone=917753800444" class="mt-8 block text-center rounded-full bg-white text-blue-700 py-4 font-black">Enquiry</a>
            </div>

            <div class="rounded-[2rem] bg-slate-50 border border-slate-200 p-8">
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
                <a href="tel:917753800444" class="mt-8 block text-center rounded-full bg-slate-950 text-white py-4 font-black">Call Now</a>
            </div>
        </div>
    </div>
</section>

{{-- CONTACT --}}
<section id="contact" class="py-20 hero-bg">
    <div class="max-w-7xl mx-auto px-4 lg:px-8">
        <div class="bg-white rounded-[2.5rem] overflow-hidden border border-slate-200 soft-card">
            <div class="grid lg:grid-cols-2">
                <div class="p-8 lg:p-12">
                    <p class="font-black uppercase tracking-widest text-blue-600 text-sm">Book Demo</p>
                    <h2 class="text-3xl lg:text-5xl font-black mt-4">Ready to see MyVictory in action?</h2>
                    <p class="mt-5 text-slate-600 text-lg leading-8">
                        Form fill karein ya WhatsApp par message bhejein. Hum aapko billing,
                        reports, WhatsApp invoices aur complete setup ka demo dikhaenge.
                    </p>

                    <div class="mt-8 space-y-5">
                        <div class="bg-slate-50 rounded-2xl p-5 border border-slate-200">
                            <div class="text-slate-500 text-sm">Phone / WhatsApp</div>
                            <a href="https://wa.me/917753800444" class="text-2xl font-black text-blue-600">+91-7753800444</a>
                        </div>
                        <div class="bg-slate-50 rounded-2xl p-5 border border-slate-200">
                            <div class="text-slate-500 text-sm">Email</div>
                            <a href="mailto:support@myvictory.in" class="font-bold">support@myvictory.in</a>
                        </div>
                        <div class="bg-slate-50 rounded-2xl p-5 border border-slate-200">
                            <div class="text-slate-500 text-sm">Office</div>
                            <p class="font-semibold">73 Basement, Ekta Enclave Society, Lakhanpur, Khyora, Kanpur, Uttar Pradesh 208024</p>
                        </div>
                    </div>
                </div>

                <div class="bg-[#f8fbff] p-8 lg:p-12">
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
                               class="w-full rounded-2xl border border-slate-300 px-4 py-4 outline-none focus:border-blue-600 bg-white">

                        <input type="text" name="mobile" value="{{ old('mobile') }}" maxlength="10" pattern="[6-9][0-9]{9}" inputmode="numeric" placeholder="Mobile / WhatsApp"
                               class="w-full rounded-2xl border border-slate-300 px-4 py-4 outline-none focus:border-blue-600 bg-white">

                        <input type="text" name="city" value="{{ old('city') }}" placeholder="City"
                               class="w-full rounded-2xl border border-slate-300 px-4 py-4 outline-none focus:border-blue-600 bg-white">

                        <input type="text" name="business_name" value="{{ old('business_name') }}" placeholder="Jewellery business name"
                               class="w-full rounded-2xl border border-slate-300 px-4 py-4 outline-none focus:border-blue-600 bg-white">

                        <textarea name="message" rows="4" placeholder="How can we help?"
                                  class="w-full rounded-2xl border border-slate-300 px-4 py-4 outline-none focus:border-blue-600 bg-white">{{ old('message') }}</textarea>

                        <button type="submit" class="w-full rounded-2xl bg-blue-600 text-white py-4 font-black hover:bg-blue-700">
                            Submit Demo Request
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