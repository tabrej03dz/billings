@extends('frontend.layout')

@section('content')

@include('frontend.pages.partials.page-hero', [
    'title' => 'Terms & Conditions',
    'subtitle' => 'Rules and conditions for using MyVictory Billing Software.'
])

<section class="py-16 bg-white">
    <div class="max-w-5xl mx-auto px-4 lg:px-8">
        <div class="bg-white rounded-[2rem] border border-slate-200 soft-card p-8 lg:p-12 space-y-8">

            <p class="text-sm font-bold text-slate-500">Last Updated: {{ date('d M Y') }}</p>

            @foreach([
                ['Services', 'My Victory provides cloud-based billing, invoicing, inventory management, and related business management software services.'],
                ['User Accounts', 'Users are responsible for maintaining the confidentiality of their account credentials.'],
                ['Subscription & Payments', 'Software services are offered on subscription plans. Subscription fees may be charged monthly or yearly. Payments once completed are subject to our Refund Policy.'],
                ['Intellectual Property', 'All software, branding, content, logos, and technology belong to My Victory.'],
                ['Service Availability', 'We strive to maintain uninterrupted services but do not guarantee 100% uptime.'],
                ['Changes to Terms', 'We reserve the right to update these Terms at any time.'],
            ] as $item)
                <div>
                    <h3 class="text-xl font-black text-mvDark">{{ $item[0] }}</h3>
                    <p class="mt-3 text-slate-600 leading-8">{{ $item[1] }}</p>
                </div>
            @endforeach

            <div>
                <h3 class="text-xl font-black text-mvDark">Acceptable Usage</h3>
                <ul class="mt-4 space-y-3 text-slate-600">
                    <li>✅ Do not use the software for unlawful activities</li>
                    <li>✅ Do not attempt unauthorized access</li>
                    <li>✅ Do not disrupt or misuse the platform</li>
                    <li>✅ Do not reverse engineer or copy the software</li>
                </ul>
            </div>

            <div>
                <h3 class="text-xl font-black text-mvDark">Limitation of Liability</h3>
                <ul class="mt-4 space-y-3 text-slate-600">
                    <li>✅ My Victory shall not be held liable for data loss due to user negligence</li>
                    <li>✅ Internet or hosting downtime</li>
                    <li>✅ Indirect business losses</li>
                </ul>
            </div>

            <div class="rounded-3xl brand-gradient text-white p-6">
                <h3 class="text-xl font-black">Contact</h3>
                <p class="mt-2">Website: https://myvictory.in</p>
                <p>Email: support@myvictory.in</p>
            </div>

        </div>
    </div>
</section>

@endsection