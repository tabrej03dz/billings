@extends('frontend.layout')

@section('content')

@include('frontend.pages.partials.page-hero', [
    'title' => 'Privacy Policy',
    'subtitle' => 'How we collect, use and protect your information.'
])

<section class="py-16 bg-white">
    <div class="max-w-5xl mx-auto px-4 lg:px-8">
        <div class="bg-white rounded-[2rem] border border-slate-200 soft-card p-8 lg:p-12 space-y-8">

            <p class="text-sm font-bold text-slate-500">Last Updated: {{ date('d M Y') }}</p>

            <div>
                <h2 class="text-2xl font-black text-mvDark">Welcome to MyVictory Billing Software</h2>
                <p class="mt-3 text-slate-600 leading-8">
                    My Victory (“we”, “our”, “us”) values your privacy and is committed to protecting your personal and business information.
                    By using our website and software services, you agree to the collection and use of information in accordance with this Privacy Policy.
                </p>
            </div>

            <div>
                <h3 class="text-xl font-black text-mvDark">Information We Collect</h3>
                <ul class="mt-4 grid md:grid-cols-2 gap-3 text-slate-600">
                    <li>✅ Name</li>
                    <li>✅ Mobile number</li>
                    <li>✅ Email address</li>
                    <li>✅ Business information</li>
                    <li>✅ Billing information</li>
                    <li>✅ Device and browser information</li>
                    <li>✅ Payment details processed through third-party payment gateways</li>
                </ul>
            </div>

            <div>
                <h3 class="text-xl font-black text-mvDark">How We Use Your Information</h3>
                <ul class="mt-4 space-y-3 text-slate-600">
                    <li>✅ Provide billing and inventory software services</li>
                    <li>✅ Improve our software and user experience</li>
                    <li>✅ Process subscriptions and payments</li>
                    <li>✅ Provide customer support</li>
                    <li>✅ Send important service notifications</li>
                </ul>
            </div>

            <div>
                <h3 class="text-xl font-black text-mvDark">Data Security</h3>
                <p class="mt-3 text-slate-600 leading-8">
                    We implement reasonable security measures to protect user data from unauthorized access, misuse, or disclosure.
                </p>
            </div>

            <div>
                <h3 class="text-xl font-black text-mvDark">Third-Party Services</h3>
                <p class="mt-3 text-slate-600 leading-8">
                    We may use trusted third-party services including payment gateways, cloud hosting providers and analytics services.
                    These providers may access limited data required to provide their services.
                </p>
            </div>

            <div>
                <h3 class="text-xl font-black text-mvDark">Cookies</h3>
                <p class="mt-3 text-slate-600 leading-8">
                    Our website may use cookies to improve user experience and website performance.
                </p>
            </div>

            <div>
                <h3 class="text-xl font-black text-mvDark">User Rights</h3>
                <ul class="mt-4 space-y-3 text-slate-600">
                    <li>✅ Access their data</li>
                    <li>✅ Correct inaccurate information</li>
                    <li>✅ Request account deletion</li>
                </ul>
            </div>

            <div class="rounded-3xl brand-gradient text-white p-6">
                <h3 class="text-xl font-black">Contact Us</h3>
                <p class="mt-2">Email: support@myvictory.in</p>
                <p>Website: https://myvictory.in</p>
            </div>

        </div>
    </div>
</section>

@endsection