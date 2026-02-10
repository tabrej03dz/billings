<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MyVictory – Jewellery Billing Software</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
</head>
<body class="bg-slate-950 text-slate-100">

<!-- Header -->
<header class="sticky top-0 z-40 bg-slate-950/80 backdrop-blur border-b border-slate-800">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-16">

        <!-- Logo -->
        <a href="#top" class="flex items-center gap-2">
            <div class="h-9 w-9 rounded-lg bg-gradient-to-tr from-emerald-400 to-cyan-500 flex items-center justify-center text-xl font-bold">
                <img src="{{asset('asset/img/logo.png')}}" alt="" style="border-radius: 2px;">
            </div>
            <div>
                <div class="font-semibold text-lg">MyVictory Billing</div>
                <div class="text-xs text-slate-400">By Real Victory Groups</div>
            </div>
        </a>

        <!-- Nav -->
        <nav class="hidden md:flex items-center gap-6 text-sm">
            <a href="#features" class="hover:text-emerald-400 transition">Features</a>
            <a href="#pricing" class="hover:text-emerald-400 transition">Pricing</a>
            <a href="#how-it-works" class="hover:text-emerald-400 transition">How it works</a>
            <a href="#faq" class="hover:text-emerald-400 transition">FAQ</a>
            <a href="#contact" class="hover:text-emerald-400 transition">Contact</a>
        </nav>

        <!-- CTA & Login/Register -->
        <div class="hidden md:flex items-center gap-3">

            <a href="/login" class="text-sm px-3 py-1.5 rounded-lg border border-slate-700 hover:border-emerald-400 hover:text-emerald-400 transition">
                Login
            </a>

            <a href="{{route('no-business.whatsapp')}}" class="text-sm px-3 py-1.5 rounded-lg border border-slate-700 hover:border-emerald-400 hover:text-emerald-400 transition">
                Send Invoice
            </a>

            <a href="/register" class="text-sm px-3 py-1.5 rounded-lg border border-cyan-500 text-cyan-300 hover:bg-cyan-500 hover:text-slate-900 transition">
                Register
            </a>

            <a href="#contact" class="text-sm px-4 py-1.5 rounded-lg bg-emerald-500 hover:bg-emerald-400 text-slate-900 font-semibold transition">
                Book Demo
            </a>
        </div>

        <!-- Mobile menu btn -->
        <button id="mobileToggle" class="md:hidden inline-flex items-center justify-center h-9 w-9 rounded-lg border border-slate-700">
            <svg id="iconOpen" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
            <svg id="iconClose" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 hidden" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    <!-- Mobile Menu -->
    <div id="mobileMenu" class="md:hidden hidden border-t border-slate-800 bg-slate-950/95">
        <nav class="px-4 py-3 flex flex-col gap-2 text-sm">
            <a href="#features" class="py-1 hover:text-emerald-400">Features</a>
            <a href="#pricing" class="py-1 hover:text-emerald-400">Pricing</a>
            <a href="#how-it-works" class="py-1 hover:text-emerald-400">How it works</a>
            <a href="#faq" class="py-1 hover:text-emerald-400">FAQ</a>
            <a href="#contact" class="py-1 hover:text-emerald-400">Contact</a>

            <a href="/login" class="py-2 px-3 rounded-lg border border-slate-600 text-center hover:border-emerald-400 hover:text-emerald-400">
                Login (Admin)
            </a>

            <a href="/register" class="py-2 px-3 rounded-lg bg-cyan-500 text-slate-900 text-center font-semibold">
                Register
            </a>

            <a href="#contact" class="py-2 px-3 rounded-lg border border-slate-700 text-center">
                Book Demo
            </a>
        </nav>
    </div>
</header>

<main id="top">
    <!-- Hero Section -->
    <section class="relative overflow-hidden">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(16,185,129,0.18),_transparent_60%),radial-gradient(circle_at_bottom,_rgba(56,189,248,0.12),_transparent_60%)] pointer-events-none"></div>

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-20 relative">
            <div class="grid lg:grid-cols-2 gap-10 items-center">
                <!-- Left -->
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full border border-emerald-500/40 bg-emerald-500/10 px-3 py-1 text-xs mb-4">
                        <span class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span>Specially crafted for Jewellery Showrooms</span>
                    </div>

                    <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold tracking-tight leading-tight">
                        Smart GST Billing for
                        <span class="bg-gradient-to-r from-emerald-400 via-cyan-400 to-sky-400 bg-clip-text text-transparent">
                            Jewellery Businesses
                        </span>
                    </h1>

                    <p class="mt-4 text-sm sm:text-base text-slate-300 max-w-xl">
                        Create customers, generate GST bills, and send invoices directly to your client’s
                        <span class="font-semibold text-emerald-300">WhatsApp and Email</span> – in just a few clicks.
                        Cloud-based, secure and built for Indian jewellery market.
                    </p>

                    <div class="mt-6 flex flex-wrap gap-3">
                        <a href="#contact"
                           class="px-6 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-900 text-sm font-semibold shadow-lg shadow-emerald-500/30">
                            Get Free Demo
                        </a>
                        <a href="#features"
                           class="px-5 py-2.5 rounded-xl border border-slate-600 hover:border-emerald-400 text-sm text-slate-200 flex items-center gap-2">
                            View Features
                            <span>→</span>
                        </a>
                    </div>

                    <div class="mt-6 flex flex-wrap items-center gap-4 text-xs text-slate-400">
                        <div class="flex items-center gap-2">
                            <span class="h-6 w-6 rounded-full bg-emerald-500/20 border border-emerald-400 flex items-center justify-center text-[10px]">
                                ✔
                            </span>
                            <span>No installation – runs in browser</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="h-6 w-6 rounded-full bg-emerald-500/20 border border-emerald-400 flex items-center justify-center text-[10px]">
                                ₹
                            </span>
                            <span>Simple subscription pricing</span>
                        </div>
                    </div>
                </div>

                <!-- Right: Hero card -->
                <div>
                    <div class="bg-slate-900/70 border border-slate-800 rounded-2xl p-5 sm:p-6 shadow-xl">
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <h3 class="text-sm font-semibold text-slate-100">Sample Jewellery Invoice</h3>
                                <p class="text-xs text-slate-400">Auto-calculated GST • Metal & making charges</p>
                            </div>
                            <span class="px-2 py-1 rounded-full bg-emerald-500/15 border border-emerald-500/40 text-[10px] uppercase tracking-wide text-emerald-300">
                                Live Preview
                            </span>
                        </div>

                        <div class="bg-slate-950/80 border border-slate-800 rounded-xl p-4 text-xs space-y-3">
                            <div class="flex justify-between">
                                <div>
                                    <div class="font-semibold text-slate-100">Mangal Jewellers</div>
                                    <div class="text-[10px] text-slate-400">Customer: Priya Sharma</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-[10px] text-slate-400">Invoice No.</div>
                                    <div class="font-semibold">MV-INV-1024</div>
                                </div>
                            </div>

                            <div class="border border-slate-800 rounded-lg overflow-hidden">
                                <table class="w-full text-[10px]">
                                    <thead class="bg-slate-900/80 text-slate-300">
                                    <tr>
                                        <th class="py-1 px-2 text-left">Item</th>
                                        <th class="py-1 px-2 text-right">G.Wt</th>
                                        <th class="py-1 px-2 text-right">Rate/gm</th>
                                        <th class="py-1 px-2 text-right">Amount</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr class="border-t border-slate-800">
                                        <td class="py-1 px-2">22K Gold Necklace</td>
                                        <td class="py-1 px-2 text-right">18.750</td>
                                        <td class="py-1 px-2 text-right">₹5,950</td>
                                        <td class="py-1 px-2 text-right">₹1,11,563</td>
                                    </tr>
                                    <tr class="border-t border-slate-800">
                                        <td class="py-1 px-2">Making Charges</td>
                                        <td class="py-1 px-2 text-right">–</td>
                                        <td class="py-1 px-2 text-right">–</td>
                                        <td class="py-1 px-2 text-right">₹12,000</td>
                                    </tr>
                                    <tr class="border-t border-slate-800 bg-slate-900/70">
                                        <td class="py-1 px-2 font-semibold">Subtotal</td>
                                        <td></td><td></td>
                                        <td class="py-1 px-2 text-right font-semibold">₹1,23,563</td>
                                    </tr>
                                    <tr class="border-t border-slate-800">
                                        <td class="py-1 px-2">GST (3%)</td>
                                        <td></td><td></td>
                                        <td class="py-1 px-2 text-right">₹3,707</td>
                                    </tr>
                                    <tr class="border-t border-slate-800 bg-emerald-500/10">
                                        <td class="py-1 px-2 font-semibold">Round Off</td>
                                        <td></td><td></td>
                                        <td class="py-1 px-2 text-right font-semibold">₹1,27,270</td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="flex flex-wrap items-center justify-between gap-3 pt-1">
                                <div class="flex items-center gap-2 text-[11px] text-slate-300">
                                    <span class="inline-flex h-6 w-6 rounded-full bg-emerald-500/20 border border-emerald-500/60 items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                                            <path d="M8 12h8M8 8h8M8 16h4"/>
                                            <path d="M6 5h8l4 4v10a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2z"/>
                                        </svg>
                                    </span>
                                    <span>One-click WhatsApp & Email invoice share</span>
                                </div>
                                <div class="flex items-center gap-1 text-[11px] text-emerald-300">
                                    <span class="h-2 w-2 rounded-full bg-emerald-400 animate-ping"></span>
                                    <span>Real-time cloud sync enabled</span>
                                </div>
                            </div>
                        </div>

                        <p class="mt-3 text-[11px] text-slate-400">
                            MyVictory connects with your jewellery business workflow – items, metal purity, making charges,
                            GST, discounts and customer wise billing all in one place.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features -->
    <section id="features" class="py-12 lg:py-16 border-t border-slate-800 bg-slate-950/60">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-end justify-between gap-4 mb-8">
                <div>
                    <h2 class="text-2xl sm:text-3xl font-semibold">Why Jewellery Stores love MyVictory</h2>
                    <p class="text-sm text-slate-400 mt-1">Designed for gold, silver & diamond showrooms – not generic billing.</p>
                </div>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Feature card -->
                <div class="bg-slate-900/70 border border-slate-800 rounded-2xl p-5">
                    <div class="h-9 w-9 rounded-lg bg-emerald-500/20 border border-emerald-500/40 flex items-center justify-center mb-3 text-emerald-300">
                        📱
                    </div>
                    <h3 class="font-semibold mb-1">WhatsApp + Email Invoices</h3>
                    <p class="text-sm text-slate-400">
                        Generate GST bill and instantly share PDF or image to your customer’s WhatsApp and email – directly from the software.
                    </p>
                </div>

                <div class="bg-slate-900/70 border border-slate-800 rounded-2xl p-5">
                    <div class="h-9 w-9 rounded-lg bg-emerald-500/20 border border-emerald-500/40 flex items-center justify-center mb-3 text-emerald-300">
                        💍
                    </div>
                    <h3 class="font-semibold mb-1">Jewellery-Focused Structure</h3>
                    <p class="text-sm text-slate-400">
                        Metal purity, gross/metals weight, stone weight, making charges, wastage – sab kuchh ek hi screen par.
                    </p>
                </div>

                <div class="bg-slate-900/70 border border-slate-800 rounded-2xl p-5">
                    <div class="h-9 w-9 rounded-lg bg-emerald-500/20 border border-emerald-500/40 flex items-center justify-center mb-3 text-emerald-300">
                        📊
                    </div>
                    <h3 class="font-semibold mb-1">Customer & Sales Reports</h3>
                    <p class="text-sm text-slate-400">
                        Customer-wise purchase history, day-wise sales, GST summary – sab report ready, Excel/PDF export ke saath.
                    </p>
                </div>

                <div class="bg-slate-900/70 border border-slate-800 rounded-2xl p-5">
                    <div class="h-9 w-9 rounded-lg bg-emerald-500/20 border border-emerald-500/40 flex items-center justify-center mb-3 text-emerald-300">
                        ☁️
                    </div>
                    <h3 class="font-semibold mb-1">Cloud & Multi-Device</h3>
                    <p class="text-sm text-slate-400">
                        Laptop, desktop, showroom PC – jahan bhi browser hai, wahan MyVictory chalega. No installation, no pen-drive.
                    </p>
                </div>

                <div class="bg-slate-900/70 border border-slate-800 rounded-2xl p-5">
                    <div class="h-9 w-9 rounded-lg bg-emerald-500/20 border border-emerald-500/40 flex items-center justify-center mb-3 text-emerald-300">
                        🔐
                    </div>
                    <h3 class="font-semibold mb-1">Secure & Role Based</h3>
                    <p class="text-sm text-slate-400">
                        Owner, billing staff, accountant – har role ke liye alag access. Aap control karein ki kaun kya dekh sakta hai.
                    </p>
                </div>

                <div class="bg-slate-900/70 border border-slate-800 rounded-2xl p-5">
                    <div class="h-9 w-9 rounded-lg bg-emerald-500/20 border border-emerald-500/40 flex items-center justify-center mb-3 text-emerald-300">
                        🧾
                    </div>
                    <h3 class="font-semibold mb-1">GST & Rounding Control</h3>
                    <p class="text-sm text-slate-400">
                        GST on/off toggle, rounding mode (up / down / nearest), custom round-off step – sab aapke business hisaab se.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing -->
    <section id="pricing" class="py-12 lg:py-16 border-t border-slate-800 bg-slate-950">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-8">
                <h2 class="text-2xl sm:text-3xl font-semibold">Simple Subscription Pricing</h2>
                <p class="text-sm text-slate-400 mt-1">
                    Start small, grow anytime. Jewellery businesses of all sizes ke liye plans.
                </p>
            </div>

            <div class="grid md:grid-cols-3 gap-6 max-w-5xl mx-auto">
                <!-- Basic Plan -->
                <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-6 flex flex-col">
                    <h3 class="text-lg font-semibold mb-1">Starter</h3>
                    <p class="text-xs text-slate-400 mb-4">Single jewellery shop • Basic billing</p>
                    <div class="flex items-baseline gap-1">
                        <span class="text-2xl font-bold">₹X,XXX</span>
                        <span class="text-xs text-slate-400">/ year</span>
                    </div>
                    <ul class="mt-4 space-y-2 text-sm text-slate-300">
                        <li>• 1 showroom / location</li>
                        <li>• Unlimited invoices</li>
                        <li>• Customer master & item master</li>
                        <li>• PDF invoice download</li>
                    </ul>
                    <button class="mt-6 text-sm w-full py-2.5 rounded-xl border border-slate-700 hover:border-emerald-400 hover:text-emerald-300 transition">
                        Choose Starter
                    </button>
                </div>

                <!-- Recommended Plan -->
                <div class="bg-gradient-to-b from-emerald-500/10 to-slate-900/90 border border-emerald-500/60 rounded-2xl p-6 flex flex-col relative shadow-lg shadow-emerald-500/20">
                    <div class="absolute -top-3 right-4 text-[10px] px-2 py-0.5 rounded-full bg-emerald-500 text-slate-900 font-semibold">
                        Most Popular
                    </div>
                    <h3 class="text-lg font-semibold mb-1">Growth</h3>
                    <p class="text-xs text-slate-300 mb-4">Growing jewellery brand • WhatsApp + reports</p>
                    <div class="flex items-baseline gap-1">
                        <span class="text-2xl font-bold">₹Y,YYY</span>
                        <span class="text-xs text-slate-300">/ year</span>
                    </div>
                    <ul class="mt-4 space-y-2 text-sm text-slate-100">
                        <li>• Up to 3 showrooms</li>
                        <li>• WhatsApp & Email invoice share</li>
                        <li>• Customer & sales reports</li>
                        <li>• Basic stock & item tracking</li>
                        <li>• Priority support</li>
                    </ul>
                    <button class="mt-6 text-sm w-full py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-900 font-semibold transition">
                        Choose Growth
                    </button>
                </div>

                <!-- Premium Plan -->
                <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-6 flex flex-col">
                    <h3 class="text-lg font-semibold mb-1">Enterprise</h3>
                    <p class="text-xs text-slate-400 mb-4">Chains • Multi-city & advanced control</p>
                    <div class="flex items-baseline gap-1">
                        <span class="text-2xl font-bold">Custom</span>
                    </div>
                    <ul class="mt-4 space-y-2 text-sm text-slate-300">
                        <li>• Unlimited showrooms</li>
                        <li>• Advanced roles & approvals</li>
                        <li>• Custom reports & integration</li>
                        <li>• Dedicated account manager</li>
                    </ul>
                    <button class="mt-6 text-sm w-full py-2.5 rounded-xl border border-slate-700 hover:border-emerald-400 hover:text-emerald-300 transition">
                        Talk to Sales
                    </button>
                </div>
            </div>

            <p class="text-center text-xs text-slate-500 mt-5">
                *Above prices are placeholders. Replace with your actual subscription prices.
            </p>
        </div>
    </section>

    <!-- How it works -->
    <section id="how-it-works" class="py-12 lg:py-16 border-t border-slate-800 bg-slate-950/60">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-end justify-between gap-4 mb-8">
                <div>
                    <h2 class="text-2xl sm:text-3xl font-semibold">How MyVictory works</h2>
                    <p class="text-sm text-slate-400 mt-1">3 simple steps – showroom ready billing.</p>
                </div>
            </div>

            <div class="grid md:grid-cols-3 gap-6">
                <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-5">
                    <div class="flex items-center gap-3 mb-2">
                        <span class="h-7 w-7 rounded-full bg-emerald-500/20 border border-emerald-500/50 flex items-center justify-center text-xs">1</span>
                        <h3 class="font-semibold text-sm">Create your showroom & items</h3>
                    </div>
                    <p class="text-sm text-slate-400">
                        Business profile, GST, showroom address, jewellery items – sab kuchh once set up. Phir har bill fast.
                    </p>
                </div>

                <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-5">
                    <div class="flex items-center gap-3 mb-2">
                        <span class="h-7 w-7 rounded-full bg-emerald-500/20 border border-emerald-500/50 flex items-center justify-center text-xs">2</span>
                        <h3 class="font-semibold text-sm">Add customer & generate bill</h3>
                    </div>
                    <p class="text-sm text-slate-400">
                        Customer details, item selection, weight, making & GST – system auto calculate karega. Ek click me invoice ready.
                    </p>
                </div>

                <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-5">
                    <div class="flex items-center gap-3 mb-2">
                        <span class="h-7 w-7 rounded-full bg-emerald-500/20 border border-emerald-500/50 flex items-center justify-center text-xs">3</span>
                        <h3 class="font-semibold text-sm">Share on WhatsApp & Email</h3>
                    </div>
                    <p class="text-sm text-slate-400">
                        Invoice PDF/ image ko directly customer ke WhatsApp number aur email pe bhejo – record hamari system me safe rahega.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ -->
    <section id="faq" class="py-12 lg:py-16 border-t border-slate-800 bg-slate-950">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-8">
                <h2 class="text-2xl sm:text-3xl font-semibold">Frequently Asked Questions</h2>
                <p class="text-sm text-slate-400 mt-1">Jewellers ke common questions ke simple answers.</p>
            </div>

            <div class="space-y-4 text-sm">
                <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-4">
                    <h3 class="font-semibold mb-1">Kya MyVictory sirf jewellery business ke liye hai?</h3>
                    <p class="text-slate-400">
                        Haan, humne system specially gold, silver, diamond aur artificial jewellery showrooms ke liye design kiya hai.
                        Item structure, weight & charges jewellery industry ke hisaab se ready hai.
                    </p>
                </div>

                <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-4">
                    <h3 class="font-semibold mb-1">Software computer pe install karna padega?</h3>
                    <p class="text-slate-400">
                        Nahi. MyVictory ek cloud based web app hai. Normal browser me chalega – Chrome / Edge, etc. Bas internet chahiye.
                    </p>
                </div>

                <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-4">
                    <h3 class="font-semibold mb-1">WhatsApp billing kaise kaam karega?</h3>
                    <p class="text-slate-400">
                        Invoice generate karne ke baad, customer ka WhatsApp number select karein aur "Send to WhatsApp" click karein.
                        System aapke configured WhatsApp API ya bridge ke through invoice bhej dega.
                    </p>
                </div>

                <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-4">
                    <h3 class="font-semibold mb-1">Data safe rahega?</h3>
                    <p class="text-slate-400">
                        Data secure cloud server par store hota hai. Regular backup aur restricted access ke saath aapka billing data safe rahta hai.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact / CTA -->
    <section id="contact" class="py-12 lg:py-16 border-t border-slate-800 bg-slate-950/60">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-2 gap-8 items-start">
                <div>
                    <h2 class="text-2xl sm:text-3xl font-semibold">Ready to see MyVictory in action?</h2>
                    <p class="text-sm text-slate-400 mt-2">
                        Form fill karein ya WhatsApp pe message bhejein. Hum aapko complete demo dikhaenge – billing, reports, WhatsApp invoices, sab kuch.
                    </p>

                    <div class="mt-5 space-y-3 text-sm text-slate-300">
                        <div>
                            <div class="text-slate-400 text-xs uppercase tracking-wide mb-1">Phone / WhatsApp</div>
                            <a href="https://wa.me/917753800444" target="_blank" class="font-semibold hover:text-emerald-400">
                                +91-7753800444
                            </a>
                        </div>
                        <div>
                            <div class="text-slate-400 text-xs uppercase tracking-wide mb-1">Email</div>
                            <a href="mailto:support@myvictory.in" class="hover:text-emerald-400">
                                support@myvictory.in
                            </a>
                            <div class="text-[11px] text-slate-500">(*change this to your official email)</div>
                        </div>
                        <div>
                            <div class="text-slate-400 text-xs uppercase tracking-wide mb-1">Office</div>
                            <p>
                                73 Basement, Ekta Enclave Society, Lakhanpur, Khyora,<br>
                                Kanpur, Uttar Pradesh 208024
                            </p>
                        </div>
                        <div>
                            <div class="text-slate-400 text-xs uppercase tracking-wide mb-1">GST Number</div>
                            <p>GSTIN: <span class="text-slate-400 italic">[Yahan apna GST number daalein]</span></p>
                        </div>
                    </div>
                </div>

                <!-- Simple form (non-functional HTML, tum baad me Laravel route se jodo) -->
                <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-6">
                    <h3 class="text-sm font-semibold mb-3">Request a free demo / callback</h3>
                    <form action="#" method="POST" class="space-y-4 text-sm">
                        <!-- @csrf (Laravel me use karna) -->
                        <div>
                            <label class="block text-xs text-slate-400 mb-1">Name</label>
                            <input type="text" name="name" required
                                   class="w-full bg-slate-950/70 border border-slate-700 rounded-lg px-3 py-2 outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-400 mb-1">Mobile / WhatsApp</label>
                            <input type="text" name="phone" required
                                   class="w-full bg-slate-950/70 border border-slate-700 rounded-lg px-3 py-2 outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-400 mb-1">City</label>
                            <input type="text" name="city"
                                   class="w-full bg-slate-950/70 border border-slate-700 rounded-lg px-3 py-2 outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-400 mb-1">Your jewellery business name</label>
                            <input type="text" name="business_name"
                                   class="w-full bg-slate-950/70 border border-slate-700 rounded-lg px-3 py-2 outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-400 mb-1">How can we help?</label>
                            <textarea name="message" rows="3"
                                      class="w-full bg-slate-950/70 border border-slate-700 rounded-lg px-3 py-2 outline-none focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500"
                                      placeholder="Billing, WhatsApp integration, multi-branch setup, etc."></textarea>
                        </div>
                        <button type="submit"
                                class="w-full mt-2 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-900 font-semibold text-sm">
                            Submit Request
                        </button>
                        <p class="mt-2 text-[11px] text-slate-500">
                            Form backend later Laravel se connect kar sakte ho – abhi design purpose se HTML ready hai.
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </section>
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

<!-- Minimal JS for mobile menu -->
<script>
    const btn = document.getElementById('mobileToggle');
    const menu = document.getElementById('mobileMenu');
    const iconOpen = document.getElementById('iconOpen');
    const iconClose = document.getElementById('iconClose');

    if (btn) {
        btn.addEventListener('click', () => {
            menu.classList.toggle('hidden');
            iconOpen.classList.toggle('hidden');
            iconClose.classList.toggle('hidden');
        });
    }
</script>

</body>
</html>
