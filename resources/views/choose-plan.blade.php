@extends('frontend.layout')

@section('content')

<main id="top">

    <section class="hero-bg relative overflow-hidden py-20">
        <div class="max-w-7xl mx-auto px-4 lg:px-8">

            <div class="text-center max-w-4xl mx-auto mb-14">
                <div class="inline-flex items-center gap-2 rounded-full bg-white border border-[#d8d6ff] shadow-sm px-5 py-2 text-sm font-black text-mvBlue mb-6">
                    <span class="h-2.5 w-2.5 rounded-full brand-gradient"></span>
                    Choose Your Plan
                </div>

                <h1 class="text-4xl lg:text-6xl font-black text-mvDark">
                    Start Your Billing Journey
                </h1>

                <p class="mt-5 text-lg text-slate-600 leading-8">
                    Trial start karein ya paid plan activate karne ke liye payment continue karein.
                </p>
            </div>

            @if(session('success'))
                <div class="mb-6 bg-green-100 border border-green-300 text-green-700 px-5 py-4 rounded-2xl font-bold">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 bg-red-100 border border-red-300 text-red-700 px-5 py-4 rounded-2xl font-bold">
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 bg-red-100 border border-red-300 text-red-700 px-5 py-4 rounded-2xl">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if($plans->count())
                <div class="grid md:grid-cols-3 gap-8">
                    @foreach($plans as $plan)
                        @php
                            $isPopular = $plan->is_recommended ?? false;
                        @endphp

                        <div class="rounded-[2rem] p-8 relative transition hover:-translate-y-1
                            {{ $isPopular ? 'brand-gradient text-white shadow-2xl shadow-blue-200 scale-105' : 'bg-white border border-slate-200 text-mvDark soft-card' }}">

                            @if($isPopular)
                                <div class="absolute -top-4 right-8 bg-white text-mvBlue text-xs font-black px-4 py-2 rounded-full shadow-lg">
                                    Most Recommended ⭐
                                </div>
                            @endif

                            <h3 class="text-2xl font-black uppercase">
                                {{ $plan->name }}
                            </h3>

                            <p class="mt-2 {{ $isPopular ? 'text-blue-100' : 'text-slate-500' }}">
                                {{ $plan->subtitle ?? $plan->description ?? 'Perfect for Small & Medium Businesses' }}
                            </p>

                            <div class="text-4xl font-black mt-6">
                                ₹{{ number_format($plan->price, 0) }}
                                <span class="text-sm {{ $isPopular ? 'text-blue-100' : 'text-slate-500' }}">
                                    / {{ $plan->duration_days >= 365 ? 'Year' : $plan->duration_days . ' Days' }}
                                </span>
                            </div>

                            <ul class="mt-7 space-y-3 text-sm {{ $isPopular ? 'text-blue-50' : 'text-slate-600' }}">
                                @forelse($plan->planFeatures ?? [] as $feature)
                                    <li>
                                        {{ $feature->icon ?: '✔' }}
                                        {{ $feature->title }}
                                    </li>
                                @empty
                                    @forelse($plan->permissions ?? [] as $permission)
                                        <li>
                                            ✔ {{ ucwords(str_replace(['-', '_'], ' ', $permission->name)) }}
                                        </li>
                                    @empty
                                        <li>✔ GST Billing</li>
                                        <li>✔ Customer Management</li>
                                        <li>✔ Invoice Print / Share</li>
                                    @endforelse
                                @endforelse
                            </ul>

                            <div class="mt-8 grid grid-cols-1 sm:grid-cols-2 gap-3">

                                <form action="{{ route('plan.choose.store') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                                    <input type="hidden" name="trial" value="1">

                                    <button type="submit"
                                        class="w-full rounded-full py-4 font-black border
                                        {{ $isPopular ? 'bg-white/10 text-white border-white/40' : 'bg-white text-mvBlue border-mvBlue' }}">
                                        Start Trial
                                    </button>
                                </form>

                                <a href="{{ route('plan.payment', ['plan' => $plan->id]) }}"
                                    class="block text-center rounded-full py-4 font-black
                                    {{ $isPopular ? 'bg-white text-mvBlue' : 'bg-mvDark text-white' }}">
                                    Start Plan
                                </a>

                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center bg-white border border-slate-200 rounded-3xl p-10 soft-card">
                    <h3 class="text-2xl font-black text-mvDark">No active plans available</h3>
                    <p class="text-slate-500 mt-2">Please add active plans from admin panel.</p>
                </div>
            @endif

        </div>
    </section>

</main>

@endsection