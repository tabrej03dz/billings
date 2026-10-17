@extends('frontend.layout')

@section('content')

<style>
    /* =========================================================
       PLAN PAGE ANIMATIONS
    ========================================================= */

    @keyframes planButtonWave {
        0%,
        100% {
            transform: translateY(0) scale(1);
        }

        25% {
            transform: translateY(-3px) scale(1.01);
        }

        50% {
            transform: translateY(0) scale(1.02);
        }

        75% {
            transform: translateY(-2px) scale(1.01);
        }
    }

    @keyframes planButtonGlow {
        0%,
        100% {
            box-shadow: 0 0 0 0 rgba(79, 70, 229, 0);
        }

        50% {
            box-shadow: 0 0 0 7px rgba(79, 70, 229, 0.12);
        }
    }

    @keyframes trialButtonGlow {
        0%,
        100% {
            box-shadow: 0 0 0 0 rgba(14, 165, 233, 0);
        }

        50% {
            box-shadow: 0 0 0 6px rgba(14, 165, 233, 0.12);
        }
    }

    @keyframes shineMove {
        0% {
            transform: translateX(-160%) skewX(-18deg);
        }

        55%,
        100% {
            transform: translateX(240%) skewX(-18deg);
        }
    }

    @keyframes popularPulse {
        0%,
        100% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.04);
        }
    }

    .plan-action-button {
        position: relative;
        overflow: hidden;
        isolation: isolate;
        transition:
            transform 0.25s ease,
            box-shadow 0.25s ease,
            background-color 0.25s ease;
        -webkit-tap-highlight-color: transparent;
    }

    .plan-action-button::before {
        content: "";
        position: absolute;
        top: -40%;
        left: -35%;
        z-index: -1;
        width: 28%;
        height: 180%;
        pointer-events: none;
        background: rgba(255, 255, 255, 0.32);
        transform: translateX(-160%) skewX(-18deg);
        animation: shineMove 3.2s ease-in-out infinite;
    }

    .plan-action-button:hover {
        transform: translateY(-2px);
    }

    .plan-action-button:active {
        transform: scale(0.97);
    }

    .trial-action-button {
        animation:
            planButtonWave 2.8s ease-in-out infinite,
            trialButtonGlow 2.8s ease-in-out infinite;
    }

    .start-action-button {
        animation:
            planButtonWave 2.4s ease-in-out infinite,
            planButtonGlow 2.4s ease-in-out infinite;
    }

    .popular-plan-badge {
        animation: popularPulse 2.2s ease-in-out infinite;
    }

    .plan-feature-list {
        scrollbar-width: thin;
    }

    .plan-feature-list::-webkit-scrollbar {
        width: 4px;
    }

    .plan-feature-list::-webkit-scrollbar-thumb {
        border-radius: 999px;
        background: rgba(148, 163, 184, 0.55);
    }

    /*
     * Accessibility:
     * Motion preference enabled होने पर animation बंद हो जाएगी.
     */
    @media (prefers-reduced-motion: reduce) {
        .plan-action-button,
        .popular-plan-badge {
            animation: none !important;
        }

        .plan-action-button::before {
            animation: none !important;
        }
    }

    /*
     * Mobile specific improvements
     */
    @media (max-width: 767px) {
        .mobile-plan-card {
            transform: none !important;
        }

        .mobile-plan-card:hover {
            transform: none !important;
        }

        .plan-feature-list {
            max-height: 220px;
            overflow-y: auto;
            padding-right: 4px;
        }
    }
</style>

<main id="top">

    <section class="hero-bg relative overflow-hidden py-10 sm:py-14 lg:py-20">

        {{-- Decorative background --}}
        <div
            class="pointer-events-none absolute -top-24 -left-24 h-72 w-72 rounded-full bg-blue-200/30 blur-3xl">
        </div>

        <div
            class="pointer-events-none absolute -bottom-24 -right-24 h-72 w-72 rounded-full bg-violet-200/30 blur-3xl">
        </div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Heading --}}
            <div class="text-center max-w-4xl mx-auto mb-8 sm:mb-10 lg:mb-14">

                <div
                    class="inline-flex items-center justify-center gap-2 rounded-full bg-white border border-[#d8d6ff] shadow-sm px-4 sm:px-5 py-2 text-xs sm:text-sm font-black text-mvBlue mb-4 sm:mb-6">

                    <span class="h-2.5 w-2.5 shrink-0 rounded-full brand-gradient"></span>

                    <span>Choose Your Plan</span>
                </div>

                <h1
                    class="text-3xl sm:text-4xl lg:text-6xl font-black leading-tight text-mvDark">
                    Start Your Billing Journey
                </h1>

                <p
                    class="mt-4 sm:mt-5 max-w-2xl mx-auto text-sm sm:text-base lg:text-lg text-slate-600 leading-6 sm:leading-8">
                    पहले free trial लेकर features देखें या payment करके अपना paid plan तुरंत activate करें।
                </p>

                {{-- Mobile guidance --}}
                <div
                    class="mt-5 sm:mt-6 grid grid-cols-1 sm:grid-cols-2 gap-3 max-w-2xl mx-auto text-left">

                    <div
                        class="flex items-start gap-3 rounded-2xl bg-white/80 border border-sky-100 px-4 py-3 shadow-sm">

                        <div
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-sky-100 text-sky-700 font-black">
                            1
                        </div>

                        <div>
                            <p class="text-sm font-black text-slate-800">
                                Start Trial
                            </p>

                            <p class="mt-0.5 text-xs leading-5 text-slate-500">
                                बिना payment के plan try करें।
                            </p>
                        </div>
                    </div>

                    <div
                        class="flex items-start gap-3 rounded-2xl bg-white/80 border border-violet-100 px-4 py-3 shadow-sm">

                        <div
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-violet-100 text-violet-700 font-black">
                            2
                        </div>

                        <div>
                            <p class="text-sm font-black text-slate-800">
                                Start Plan
                            </p>

                            <p class="mt-0.5 text-xs leading-5 text-slate-500">
                                Payment करके paid plan activate करें।
                            </p>
                        </div>
                    </div>

                </div>
            </div>

            {{-- Success message --}}
            @if(session('success'))
                <div
                    class="mb-5 sm:mb-6 flex items-start gap-3 bg-green-100 border border-green-300 text-green-800 px-4 sm:px-5 py-4 rounded-2xl font-bold shadow-sm">

                    <span
                        class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-green-600 text-white text-sm">
                        ✓
                    </span>

                    <span class="text-sm sm:text-base leading-6">
                        {{ session('success') }}
                    </span>
                </div>
            @endif

            {{-- Error message --}}
            @if(session('error'))
                <div
                    class="mb-5 sm:mb-6 flex items-start gap-3 bg-red-100 border border-red-300 text-red-800 px-4 sm:px-5 py-4 rounded-2xl font-bold shadow-sm">

                    <span
                        class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-red-600 text-white text-sm">
                        !
                    </span>

                    <span class="text-sm sm:text-base leading-6">
                        {{ session('error') }}
                    </span>
                </div>
            @endif

            {{-- Validation errors --}}
            @if($errors->any())
                <div
                    class="mb-5 sm:mb-6 bg-red-100 border border-red-300 text-red-800 px-4 sm:px-5 py-4 rounded-2xl shadow-sm">

                    <div class="flex items-start gap-3">

                        <span
                            class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-red-600 text-white text-sm font-black">
                            !
                        </span>

                        <div class="min-w-0">
                            <p class="font-black text-sm sm:text-base">
                                Please check the following:
                            </p>

                            <ul class="mt-2 list-disc list-inside space-y-1 text-sm leading-6">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

                </div>
            @endif

            @if($plans->count())

                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5 sm:gap-7 lg:gap-8 items-start">

                    @foreach($plans as $plan)

                        @php
                            $isPopular = (bool) ($plan->is_recommended ?? false);

                            $featureCount = collect($plan->planFeatures ?? [])->count();

                            if ($featureCount === 0) {
                                $featureCount = collect($plan->permissions ?? [])->count();
                            }

                            $durationLabel = $plan->duration_days >= 365
                                ? 'Year'
                                : $plan->duration_days . ' Days';
                        @endphp

                        <article
                            class="mobile-plan-card relative flex h-full flex-col overflow-visible rounded-[1.5rem] sm:rounded-[2rem] p-5 sm:p-7 lg:p-8 transition duration-300
                            {{ $isPopular
                                ? 'brand-gradient text-white shadow-2xl shadow-blue-200 md:scale-[1.03]'
                                : 'bg-white border border-slate-200 text-mvDark soft-card hover:-translate-y-1 hover:shadow-xl'
                            }}">

                            {{-- Recommended badge --}}
                            @if($isPopular)
                                <div
                                    class="popular-plan-badge absolute -top-3 sm:-top-4 left-1/2 -translate-x-1/2 whitespace-nowrap bg-white text-mvBlue border border-blue-100 text-[10px] sm:text-xs font-black px-4 py-2 rounded-full shadow-lg">
                                    Most Recommended ⭐
                                </div>
                            @endif


                            {{-- Plan Heading --}}
                            <div class="{{ $isPopular ? 'pt-3 sm:pt-2' : '' }}">

                                <div class="flex items-start justify-between gap-3">

                                    <div class="min-w-0">

                                        <p
                                            class="text-[11px] sm:text-xs font-black uppercase tracking-[0.18em]
                                            {{ $isPopular ? 'text-blue-100' : 'text-mvBlue' }}">
                                            Billing Plan
                                        </p>

                                        <h3 class="mt-1 text-xl sm:text-2xl font-black uppercase break-words">
                                            {{ $plan->name }}
                                        </h3>

                                    </div>

                                    @if($featureCount > 0)
                                        <span
                                            class="shrink-0 rounded-full px-3 py-1 text-[10px] sm:text-xs font-black
                                            {{ $isPopular
                                                ? 'bg-white/15 text-white border border-white/20'
                                                : 'bg-slate-100 text-slate-600'
                                            }}">

                                            {{ $featureCount }}+ Features
                                        </span>
                                    @endif

                                </div>

                                <p
                                    class="mt-3 min-h-0 sm:min-h-[48px] text-sm leading-6
                                    {{ $isPopular ? 'text-blue-100' : 'text-slate-500' }}">

                                    {{ $plan->subtitle ?? $plan->description ?? 'Perfect for Small & Medium Businesses' }}
                                </p>

                            </div>


                            {{-- Price --}}
                            <div
                                class="mt-5 sm:mt-6 rounded-2xl p-4
                                {{ $isPopular
                                    ? 'bg-white/10 border border-white/15'
                                    : 'bg-slate-50 border border-slate-100'
                                }}">

                                <p
                                    class="text-xs font-bold
                                    {{ $isPopular ? 'text-blue-100' : 'text-slate-500' }}">
                                    Plan Price
                                </p>

                                <div class="mt-1 flex flex-wrap items-end gap-x-2 gap-y-1">

                                    <span class="text-3xl sm:text-4xl font-black leading-none">
                                        ₹{{ number_format($plan->price, 0) }}
                                    </span>

                                    <span
                                        class="pb-0.5 text-xs sm:text-sm font-bold
                                        {{ $isPopular ? 'text-blue-100' : 'text-slate-500' }}">
                                        / {{ $durationLabel }}
                                    </span>

                                </div>

                                {{-- Sirf GST Rate --}}
                                <p
                                    class="mt-2 text-xs font-bold
                                    {{ $isPopular ? 'text-blue-100' : 'text-slate-500' }}">
                                    + {{ $plan->tax ?? '18' }}% GST
                                </p>

                                @if($plan->duration_days >= 365)
                                    <p
                                        class="mt-2 text-xs
                                        {{ $isPopular ? 'text-blue-100' : 'text-slate-500' }}">
                                        पूरे एक वर्ष के लिए plan access
                                    </p>
                                @endif

                            </div>


                            {{-- Features --}}
                            <div class="mt-5 sm:mt-6 flex-1">

                                <p
                                    class="mb-3 text-xs font-black uppercase tracking-wider
                                    {{ $isPopular ? 'text-blue-100' : 'text-slate-500' }}">
                                    Plan Includes
                                </p>

                                <ul
                                    class="plan-feature-list space-y-3 text-sm
                                    {{ $isPopular ? 'text-blue-50' : 'text-slate-600' }}">

                                    @forelse($plan->planFeatures ?? [] as $feature)

                                        <li class="flex items-start gap-2.5">

                                            <span
                                                class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full text-[11px] font-black
                                                {{ $isPopular
                                                    ? 'bg-white/15 text-white'
                                                    : 'bg-green-100 text-green-700'
                                                }}">

                                                {{ $feature->icon ?: '✓' }}
                                            </span>

                                            <span class="min-w-0 leading-5 break-words">
                                                {{ $feature->title }}
                                            </span>

                                        </li>

                                    @empty

                                        @forelse($plan->permissions ?? [] as $permission)

                                            <li class="flex items-start gap-2.5">

                                                <span
                                                    class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full text-[11px] font-black
                                                    {{ $isPopular
                                                        ? 'bg-white/15 text-white'
                                                        : 'bg-green-100 text-green-700'
                                                    }}">
                                                    ✓
                                                </span>

                                                <span class="min-w-0 leading-5 break-words">
                                                    {{ ucwords(str_replace(['-', '_'], ' ', $permission->name)) }}
                                                </span>

                                            </li>

                                        @empty

                                            <li class="flex items-start gap-2.5">
                                                <span
                                                    class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full text-[11px] font-black
                                                    {{ $isPopular
                                                        ? 'bg-white/15 text-white'
                                                        : 'bg-green-100 text-green-700'
                                                    }}">
                                                    ✓
                                                </span>

                                                <span>GST Billing</span>
                                            </li>

                                            <li class="flex items-start gap-2.5">
                                                <span
                                                    class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full text-[11px] font-black
                                                    {{ $isPopular
                                                        ? 'bg-white/15 text-white'
                                                        : 'bg-green-100 text-green-700'
                                                    }}">
                                                    ✓
                                                </span>

                                                <span>Customer Management</span>
                                            </li>

                                            <li class="flex items-start gap-2.5">
                                                <span
                                                    class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full text-[11px] font-black
                                                    {{ $isPopular
                                                        ? 'bg-white/15 text-white'
                                                        : 'bg-green-100 text-green-700'
                                                    }}">
                                                    ✓
                                                </span>

                                                <span>Invoice Print / Share</span>
                                            </li>

                                        @endforelse

                                    @endforelse

                                </ul>

                            </div>


                            {{-- Explanation --}}
                            <div
                                class="mt-6 rounded-2xl px-4 py-3 text-xs leading-5
                                {{ $isPopular
                                    ? 'bg-black/10 text-blue-50'
                                    : 'bg-blue-50 text-slate-600 border border-blue-100'
                                }}">

                                <strong class="{{ $isPopular ? 'text-white' : 'text-mvBlue' }}">
                                    Confused?
                                </strong>

                                Trial के लिए पहला button और paid activation के लिए दूसरा button चुनें।
                            </div>


                            {{-- Action Buttons --}}
                            <div class="mt-5 grid grid-cols-1 gap-3">

                                {{-- Trial --}}
                                <form
                                    action="{{ route('plan.choose.store') }}"
                                    method="POST"
                                    class="w-full">

                                    @csrf

                                    <input
                                        type="hidden"
                                        name="plan_id"
                                        value="{{ $plan->id }}">

                                    <input
                                        type="hidden"
                                        name="trial"
                                        value="1">

                                    <button
                                        type="submit"
                                        class="plan-action-button trial-action-button flex min-h-[54px] w-full items-center justify-center gap-2 rounded-2xl px-4 py-3.5 text-sm sm:text-base font-black border
                                        {{ $isPopular
                                            ? 'bg-white/10 text-white border-white/40 hover:bg-white/20'
                                            : 'bg-white text-mvBlue border-mvBlue hover:bg-blue-50'
                                        }}">

                                        <span
                                            class="flex h-7 w-7 items-center justify-center rounded-full
                                            {{ $isPopular
                                                ? 'bg-white/15'
                                                : 'bg-blue-100'
                                            }}">
                                            ▶
                                        </span>

                                        <span>Start Free Trial</span>

                                    </button>

                                </form>


                                {{-- Paid --}}
                                <a
                                    href="{{ route('plan.payment', ['plan' => $plan->id]) }}"
                                    class="plan-action-button start-action-button flex min-h-[56px] w-full items-center justify-center gap-2 rounded-2xl px-4 py-3.5 text-sm sm:text-base font-black
                                    {{ $isPopular
                                        ? 'bg-white text-mvBlue hover:bg-blue-50'
                                        : 'bg-mvDark text-white hover:opacity-95'
                                    }}">

                                    <span
                                        class="flex h-7 w-7 items-center justify-center rounded-full
                                        {{ $isPopular
                                            ? 'bg-blue-100 text-mvBlue'
                                            : 'bg-white/15 text-white'
                                        }}">
                                        ₹
                                    </span>

                                    <span>Start Paid Plan</span>

                                </a>

                            </div>


                            <p
                                class="mt-4 text-center text-[11px] leading-5
                                {{ $isPopular ? 'text-blue-100' : 'text-slate-400' }}">

                                Secure process • Quick activation • Easy billing
                            </p>

                        </article>

                    @endforeach

                </div>
            @else

                <div
                    class="text-center bg-white border border-slate-200 rounded-3xl p-7 sm:p-10 soft-card">

                    <div
                        class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-slate-100 text-2xl">
                        📋
                    </div>

                    <h3 class="mt-4 text-xl sm:text-2xl font-black text-mvDark">
                        No active plans available
                    </h3>

                    <p class="text-sm sm:text-base text-slate-500 mt-2">
                        Please add active plans from admin panel.
                    </p>

                </div>

            @endif
        </div>
    </section>

</main>

@endsection