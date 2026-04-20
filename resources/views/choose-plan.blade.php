<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choose Plan – MyVictory Billing</title>
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body {
            background:
                radial-gradient(circle at top left, rgba(16,185,129,0.08), transparent 24%),
                radial-gradient(circle at top right, rgba(6,182,212,0.08), transparent 26%),
                linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
        }

        .glass-card {
            background: rgba(255,255,255,0.94);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            box-shadow:
                0 10px 30px rgba(15, 23, 42, 0.06),
                0 1px 2px rgba(15, 23, 42, 0.04);
        }

        .popular-ring {
            box-shadow:
                0 0 0 1px rgba(16,185,129,0.18),
                0 12px 30px rgba(16,185,129,0.12);
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

            <a href="{{ route('login') }}"
               class="inline-flex items-center rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800 transition">
                Login
            </a>
        </div>
    </header>

    <main class="relative overflow-hidden">
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute top-10 left-10 h-56 w-56 rounded-full bg-emerald-200/30 blur-3xl"></div>
            <div class="absolute top-24 right-10 h-56 w-56 rounded-full bg-cyan-200/30 blur-3xl"></div>
        </div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 lg:py-14">
            <!-- top intro -->
            <div class="text-center mb-10">
                <div class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-medium text-emerald-700">
                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                    Final step before dashboard access
                </div>

                <h1 class="mt-5 text-3xl sm:text-4xl font-bold tracking-tight text-slate-900">
                    Choose Your Plan
                </h1>

                <p class="mt-3 text-sm sm:text-base leading-7 text-slate-600 max-w-3xl mx-auto">
                    Registration complete ho gaya hai. Ab apna suitable plan select kijiye.
                    Har plan ke niche uski permissions-based features dikha di gayi hain.
                </p>
            </div>

            @if(session('success'))
                <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    <ul class="list-disc ml-5 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @php
                $popularPlanId = optional($plans->sortBy('price')->values()->get(1))->id ?? optional($plans->first())->id;
            @endphp

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                @forelse($plans as $plan)
                    @php
                        $isPopular = $plan->id == $popularPlanId;
                    @endphp

                    <div class="glass-card rounded-[28px] border {{ $isPopular ? 'border-emerald-300 popular-ring' : 'border-slate-200' }} p-6 flex flex-col h-full relative overflow-hidden">
                        @if($isPopular)
                            <div class="absolute top-4 right-4">
                                <span class="inline-flex items-center rounded-full bg-emerald-100 border border-emerald-200 px-3 py-1 text-[11px] font-semibold text-emerald-700">
                                    Most Popular
                                </span>
                            </div>
                        @endif

                        <div class="mb-5">
                            <h2 class="text-2xl font-bold text-slate-900">{{ $plan->name }}</h2>

                            @if($plan->description)
                                <p class="mt-2 text-sm leading-6 text-slate-600">
                                    {{ $plan->description }}
                                </p>
                            @endif
                        </div>

                        <div class="rounded-2xl bg-slate-50 border border-slate-200 p-4 mb-5">
                            <div class="flex items-end gap-2">
                                <span class="text-3xl font-bold text-emerald-600">₹{{ number_format($plan->price, 2) }}</span>
                            </div>
                            <p class="mt-1 text-sm text-slate-500">
                                Valid for {{ $plan->duration_days }} days
                            </p>
                        </div>

                        <div class="mb-5">
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="text-sm font-semibold text-slate-900">Plan Features</h3>
                                <span class="text-xs text-slate-500">
                                    {{ $plan->permissions->count() }} features
                                </span>
                            </div>

                            <div class="rounded-2xl border border-slate-200 bg-white p-4 min-h-[220px]">
                                @if($plan->permissions->count())
                                    <ul class="space-y-3">
                                        @foreach($plan->permissions as $permission)
                                            <li class="flex items-start gap-3">
                                                <span class="mt-1 inline-flex h-5 w-5 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 text-xs font-bold">
                                                    ✓
                                                </span>
                                                <span class="text-sm leading-6 text-slate-700">
                                                    {{ ucwords(str_replace(['-', '_'], ' ', $permission->name)) }}
                                                </span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <div class="rounded-xl bg-amber-50 border border-amber-200 px-3 py-3 text-sm text-amber-700">
                                        Is plan me abhi koi permission/feature assign nahi hai.
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="mt-auto">
                            <form action="{{ route('plan.choose.store') }}" method="POST">
                                @csrf
                                <input type="hidden" name="plan_id" value="{{ $plan->id }}">

                                <button type="submit"
                                    class="w-full rounded-2xl {{ $isPopular ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-slate-900 hover:bg-slate-800' }} px-5 py-3 text-sm font-semibold text-white transition">
                                    Select {{ $plan->name }}
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="md:col-span-2 xl:col-span-3">
                        <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-4 text-amber-700 text-center">
                            Koi active plan available nahi hai.
                        </div>
                    </div>
                @endforelse
            </div>

            <div class="mt-10 text-center text-sm text-slate-500">
                Plan select karne ke baad aapke account me us plan ki permissions automatically assign ho jayengi.
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

</body>
</html>