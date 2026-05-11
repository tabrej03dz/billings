@extends('frontend.layout')

@section('content')

@include('frontend.pages.partials.page-hero', [
    'title' => 'About Us',
    'subtitle' => 'Cloud-based billing software for small and medium businesses.'
])

<section class="py-16 bg-white">
    <div class="max-w-6xl mx-auto px-4 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-10 items-center">

            <div class="bg-white rounded-[2rem] border border-slate-200 soft-card p-8 lg:p-12">
                <h2 class="text-3xl font-black text-mvDark">MyVictory Billing Software</h2>
                <p class="mt-5 text-slate-600 leading-8">
                    My Victory Billing Software is a cloud-based billing and business management solution designed for small and medium businesses.
                    Our mission is to provide simple, fast, and easy-to-use billing software for modern businesses.
                </p>

                <div class="mt-8 rounded-3xl brand-gradient text-white p-6">
                    <h3 class="text-xl font-black">Available On</h3>
                    <div class="mt-4 flex flex-wrap gap-3 text-sm font-bold">
                        <span class="bg-white/15 px-4 py-2 rounded-full">Android</span>
                        <span class="bg-white/15 px-4 py-2 rounded-full">iPhone iOS</span>
                        <span class="bg-white/15 px-4 py-2 rounded-full">Web Platform</span>
                    </div>
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-5">
                @foreach([
                    'Create GST invoices',
                    'Manage inventory',
                    'Track sales and purchases',
                    'Manage customers',
                    'Share invoices digitally',
                    'Simplify daily billing operations',
                ] as $item)
                    <div class="rounded-3xl bg-[#f5f7ff] border border-slate-200 p-6">
                        <div class="h-12 w-12 rounded-2xl brand-gradient text-white flex items-center justify-center font-black mb-4">✓</div>
                        <h3 class="font-black text-mvDark">{{ $item }}</h3>
                    </div>
                @endforeach
            </div>

        </div>
    </div>
</section>

@endsection