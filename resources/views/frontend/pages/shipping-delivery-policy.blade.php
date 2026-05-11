@extends('frontend.layout')

@section('content')

@include('frontend.pages.partials.page-hero', [
    'title' => 'Shipping & Delivery Policy',
    'subtitle' => 'MyVictory provides digital software services only.'
])

<section class="py-16 bg-white">
    <div class="max-w-5xl mx-auto px-4 lg:px-8">
        <div class="bg-white rounded-[2rem] border border-slate-200 soft-card p-8 lg:p-12 space-y-8">

            <p class="text-sm font-bold text-slate-500">Last Updated: {{ date('d M Y') }}</p>

            <div>
                <h3 class="text-xl font-black text-mvDark">No Physical Shipping</h3>
                <p class="mt-3 text-slate-600 leading-8">
                    My Victory provides digital software services only. No physical products are shipped.
                </p>
            </div>

            <div class="grid md:grid-cols-3 gap-5">
                @foreach([
                    ['Digital Access', 'Software access is delivered digitally.'],
                    ['Fast Activation', 'Account activation is generally completed instantly or within a few hours after successful payment.'],
                    ['Platform Access', 'Users receive access through mobile applications, web platform, or account credentials.'],
                ] as $item)
                    <div class="rounded-3xl bg-slate-50 border border-slate-200 p-6">
                        <h3 class="text-lg font-black text-mvDark">{{ $item[0] }}</h3>
                        <p class="mt-3 text-sm text-slate-600 leading-7">{{ $item[1] }}</p>
                    </div>
                @endforeach
            </div>

            <div class="rounded-3xl brand-gradient text-white p-6">
                <h3 class="text-xl font-black">Support</h3>
                <p class="mt-2">Email: support@myvictory.in</p>
                <p>Website: https://myvictory.in</p>
            </div>

        </div>
    </div>
</section>

@endsection