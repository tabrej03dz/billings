@extends('frontend.layout')

@section('content')

@include('frontend.pages.partials.page-hero', [
    'title' => 'Contact Us',
    'subtitle' => 'Get in touch for demo, activation and support.'
])

<section class="py-16 hero-bg">
    <div class="max-w-6xl mx-auto px-4 lg:px-8">
        <div class="bg-white rounded-[2.5rem] overflow-hidden border border-slate-200 soft-card">
            <div class="grid lg:grid-cols-2">

                <div class="p-8 lg:p-12">
                    <p class="font-black uppercase tracking-widest text-mvBlue text-sm">Contact Details</p>
                    <h2 class="text-3xl lg:text-5xl font-black mt-4 text-mvDark">MyVictory Billing Software</h2>

                    <div class="mt-8 space-y-5">
                        <div class="bg-slate-50 rounded-2xl p-5 border border-slate-200">
                            <div class="text-slate-500 text-sm">Website</div>
                            <a href="https://myvictory.in" class="font-black text-mvBlue">https://myvictory.in</a>
                        </div>

                        <div class="bg-slate-50 rounded-2xl p-5 border border-slate-200">
                            <div class="text-slate-500 text-sm">Email</div>
                            <a href="mailto:support@myvictory.in" class="font-black text-mvDark">support@myvictory.in</a>
                        </div>

                        <div class="bg-slate-50 rounded-2xl p-5 border border-slate-200">
                            <div class="text-slate-500 text-sm">Phone / WhatsApp</div>
                            <a href="https://wa.me/917753800444" class="text-2xl font-black text-mvBlue">+91-7753800444</a>
                        </div>

                        <div class="bg-slate-50 rounded-2xl p-5 border border-slate-200">
                            <div class="text-slate-500 text-sm">Address</div>
                            <p class="font-semibold text-mvDark">
                                73 Basement, Ekta Enclave Society, Lakhanpur, Khyora, Kanpur, Uttar Pradesh 208024
                            </p>
                        </div>

                        <div class="bg-slate-50 rounded-2xl p-5 border border-slate-200">
                            <div class="text-slate-500 text-sm">Business Hours</div>
                            <p class="font-semibold text-mvDark">Monday to Saturday — 10:00 AM to 7:00 PM</p>
                        </div>
                    </div>
                </div>

                <div class="bg-[#f5f7ff] p-8 lg:p-12">
                    <h3 class="text-2xl font-black mb-5 text-mvDark">Request a free demo</h3>

                    <form action="{{ route('demo-requests.save') }}#contact" method="POST" class="space-y-4">
                        @csrf

                        <input type="text" name="name" value="{{ old('name') }}" required placeholder="Name"
                            class="w-full rounded-2xl border border-slate-300 px-4 py-4 outline-none focus:border-mvBlue bg-white">

                        <input type="text" name="mobile" value="{{ old('mobile') }}" maxlength="10" pattern="[6-9][0-9]{9}" inputmode="numeric" placeholder="Mobile / WhatsApp"
                            class="w-full rounded-2xl border border-slate-300 px-4 py-4 outline-none focus:border-mvBlue bg-white">

                        <input type="text" name="city" value="{{ old('city') }}" placeholder="City"
                            class="w-full rounded-2xl border border-slate-300 px-4 py-4 outline-none focus:border-mvBlue bg-white">

                        <textarea name="message" rows="4" placeholder="Message"
                            class="w-full rounded-2xl border border-slate-300 px-4 py-4 outline-none focus:border-mvBlue bg-white">{{ old('message') }}</textarea>

                        <button type="submit" class="w-full rounded-2xl brand-gradient text-white py-4 font-black hover:opacity-90">
                            Submit Demo Request
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>
</section>

@endsection