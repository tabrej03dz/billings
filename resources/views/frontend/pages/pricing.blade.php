@extends('frontend.layout')

@section('content')

@include('frontend.pages.partials.page-hero', [
    'title' => 'Pricing Plans',
    'subtitle' => 'Choose the best plan for your shop or growing business.'
])

<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 lg:px-8">

        <div class="grid md:grid-cols-3 gap-8">

            @foreach([
                [
                    'name' => 'Starter Plan',
                    'price' => '₹999',
                    'tag' => 'Best for small shops',
                    'features' => ['GST Billing', 'Basic Inventory', 'Customer Management', 'Invoice Sharing'],
                    'popular' => false,
                ],
                [
                    'name' => 'Smart Plan',
                    'price' => '₹2999',
                    'tag' => 'Most Recommended',
                    'features' => ['Advanced Billing', 'Stock Management', 'Purchase Management', 'Cloud Backup', 'Smart Features'],
                    'popular' => true,
                ],
                [
                    'name' => 'Pro Plan',
                    'price' => '₹5999',
                    'tag' => 'For growing businesses',
                    'features' => ['Multi-user Access', 'Advanced Reports', 'Priority Support', 'Business Insights'],
                    'popular' => false,
                ],
            ] as $plan)

                <div class="rounded-[2rem] p-8 relative transition hover:-translate-y-1
                    {{ $plan['popular'] ? 'brand-gradient text-white shadow-2xl shadow-blue-200 scale-105' : 'bg-slate-50 border border-slate-200 text-mvDark' }}">

                    @if($plan['popular'])
                        <div class="absolute -top-4 right-8 bg-white text-mvBlue text-xs font-black px-4 py-2 rounded-full shadow-lg">
                            Most Recommended ⭐
                        </div>
                    @endif

                    <h3 class="text-2xl font-black uppercase">{{ $plan['name'] }}</h3>

                    <p class="mt-2 {{ $plan['popular'] ? 'text-blue-100' : 'text-slate-500' }}">
                        {{ $plan['tag'] }}
                    </p>

                    <div class="text-4xl font-black mt-6">
                        {{ $plan['price'] }}
                        <span class="text-sm {{ $plan['popular'] ? 'text-blue-100' : 'text-slate-500' }}">
                            / Year
                        </span>
                    </div>

                    <ul class="mt-7 space-y-3 text-sm {{ $plan['popular'] ? 'text-blue-50' : 'text-slate-600' }}">
                        @foreach($plan['features'] as $feature)
                            <li>✔ {{ $feature }}</li>
                        @endforeach
                    </ul>

                    <a href="https://api.whatsapp.com/send/?phone=917753800444&text=I%20want%20{{ urlencode($plan['name']) }}%20demo"
                       class="mt-8 block text-center rounded-full py-4 font-black
                       {{ $plan['popular'] ? 'bg-white text-mvBlue' : 'bg-mvDark text-white' }}">
                        Contact for Demo
                    </a>
                </div>

            @endforeach

        </div>

        <div class="mt-14 text-center rounded-[2rem] brand-gradient text-white p-8">
            <h2 class="text-3xl font-black">Need help choosing a plan?</h2>
            <p class="mt-3 text-blue-100">Contact us for demo and activation.</p>
            <a href="https://wa.me/917753800444" class="mt-6 inline-block bg-white text-mvBlue rounded-full px-8 py-4 font-black">
                WhatsApp Now
            </a>
        </div>

    </div>
</section>

@endsection