@extends('frontend.layout')

@section('content')

@include('frontend.pages.partials.page-hero', [
    'title' => 'Refund & Cancellation Policy',
    'subtitle' => 'Subscription cancellation and refund related information.'
])

<section class="py-16 bg-white">
    <div class="max-w-5xl mx-auto px-4 lg:px-8">
        <div class="bg-white rounded-[2rem] border border-slate-200 soft-card p-8 lg:p-12 space-y-8">

            <p class="text-sm font-bold text-slate-500">Last Updated: {{ date('d M Y') }}</p>

            @foreach([
                ['Subscription Cancellation', 'Users may cancel their subscription renewal at any time.'],
                ['Refund Policy', 'Due to the digital nature of software services, refunds are generally not provided after successful activation. In exceptional cases, refund requests may be reviewed at our sole discretion.'],
                ['Duplicate Payments', 'Any duplicate payment made accidentally will be refunded after verification.'],
                ['Failed Transactions', 'If payment is deducted but subscription is not activated, users should contact support for resolution.'],
            ] as $item)
                <div class="rounded-3xl bg-slate-50 border border-slate-200 p-6">
                    <h3 class="text-xl font-black text-mvDark">{{ $item[0] }}</h3>
                    <p class="mt-3 text-slate-600 leading-8">{{ $item[1] }}</p>
                </div>
            @endforeach

            <div class="rounded-3xl brand-gradient text-white p-6">
                <h3 class="text-xl font-black">Contact for Refund Issues</h3>
                <p class="mt-2">Email: support@myvictory.in</p>
                <p>Website: https://myvictory.in</p>
            </div>

        </div>
    </div>
</section>

@endsection