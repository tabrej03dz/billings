@extends('frontend.layout')

@section('content')

@php
    $basePrice = (float) ($plan->price ?? 0);
    $gstRate = (float) ($plan->tax ?? 18);

    $gstAmount = round(($basePrice * $gstRate) / 100, 2);
    $totalPayable = round($basePrice + $gstAmount, 2);

    $durationLabel = ($plan->duration_days ?? 0) >= 365
        ? '1 Year'
        : ($plan->duration_days ?? 0) . ' Days';
@endphp


<main class="min-h-screen bg-[#f5f7ff] py-10 sm:py-16">

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Success Message --}}
        @if(session('success'))
            <div
                class="mb-6 bg-green-100 border border-green-300 text-green-700 px-5 py-4 rounded-2xl font-bold">
                {{ session('success') }}
            </div>
        @endif


        {{-- Error Message --}}
        @if(session('error'))
            <div
                class="mb-6 bg-red-100 border border-red-300 text-red-700 px-5 py-4 rounded-2xl font-bold">
                {{ session('error') }}
            </div>
        @endif


        {{-- Validation Errors --}}
        @if($errors->any())
            <div
                class="mb-6 bg-red-100 border border-red-300 text-red-700 px-5 py-4 rounded-2xl">

                <p class="font-black mb-2">
                    Please check the following:
                </p>

                <ul class="list-disc list-inside space-y-1 text-sm">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>

            </div>
        @endif


        <div class="grid lg:grid-cols-2 gap-8 items-start">


            {{-- =========================================================
                LEFT SIDE - PLAN DETAILS
            ========================================================== --}}
            <div
                class="bg-white rounded-[2rem] sm:rounded-[2.5rem] border border-slate-200 p-6 sm:p-8 soft-card">


                {{-- Selected Plan Badge --}}
                <div
                    class="inline-flex items-center gap-2 rounded-full bg-[#f1f0ff] border border-[#d8d6ff] px-5 py-2 text-sm font-black text-mvBlue mb-6">

                    <span
                        class="h-2.5 w-2.5 rounded-full brand-gradient">
                    </span>

                    Selected Plan

                </div>


                {{-- Plan Name --}}
                <h1
                    class="text-3xl lg:text-5xl font-black text-mvDark break-words">

                    {{ $plan->name }}

                </h1>


                {{-- Description --}}
                <p class="mt-4 text-slate-600 leading-8">

                    {{
                        $plan->subtitle
                        ?? $plan->description
                        ?? 'Perfect billing plan for your business.'
                    }}

                </p>



                {{-- =====================================================
                    PRICE CARD
                ====================================================== --}}
                <div
                    class="mt-8 rounded-3xl bg-[#f5f7ff] border border-slate-200 p-6">


                    <div
                        class="text-sm text-slate-500 font-bold">
                        Plan Amount
                    </div>


                    <div
                        class="mt-2 text-4xl sm:text-5xl font-black text-mvBlue">

                        ₹{{ number_format($basePrice, 2) }}

                    </div>


                    {{-- GST Rate Only --}}
                    <div
                        class="mt-3 inline-flex items-center rounded-full bg-white border border-slate-200 px-4 py-2 text-sm font-black text-slate-600">

                        + {{ number_format($gstRate, 2) }}% GST

                    </div>


                    <div
                        class="mt-4 text-sm text-slate-500 font-bold">

                        Validity:
                        <span class="text-slate-800">
                            {{ $durationLabel }}
                        </span>

                    </div>

                </div>



                {{-- =====================================================
                    FEATURES
                ====================================================== --}}
                <div class="mt-8">

                    <h3
                        class="text-xl font-black text-mvDark mb-4">
                        Plan Features
                    </h3>


                    <ul class="space-y-3 text-slate-600">

                        @forelse($plan->planFeatures ?? [] as $feature)

                            <li class="flex items-start gap-3">

                                <span
                                    class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-green-100 text-green-700 text-xs font-black">

                                    {{ $feature->icon ?: '✓' }}

                                </span>

                                <span class="leading-6">
                                    {{ $feature->title }}
                                </span>

                            </li>

                        @empty

                            <li class="flex gap-3">
                                <span class="text-green-600 font-black">✓</span>
                                <span>GST Billing</span>
                            </li>

                            <li class="flex gap-3">
                                <span class="text-green-600 font-black">✓</span>
                                <span>Customer Management</span>
                            </li>

                            <li class="flex gap-3">
                                <span class="text-green-600 font-black">✓</span>
                                <span>Invoice PDF Download</span>
                            </li>

                            <li class="flex gap-3">
                                <span class="text-green-600 font-black">✓</span>
                                <span>WhatsApp Invoice Sharing</span>
                            </li>

                            <li class="flex gap-3">
                                <span class="text-green-600 font-black">✓</span>
                                <span>Stock Management</span>
                            </li>

                        @endforelse

                    </ul>

                </div>

            </div>



            {{-- =========================================================
                RIGHT SIDE - PAYMENT BOX
            ========================================================== --}}
            <div
                class="bg-white rounded-[2rem] sm:rounded-[2.5rem] border border-slate-200 p-6 sm:p-8 soft-card">


                <h2
                    class="text-3xl font-black text-mvDark">

                    Complete Payment

                </h2>


                <p class="mt-3 text-slate-600 leading-7">

                    Payment complete karne ke baad aapka plan activate ho jayega.

                </p>



                {{-- =====================================================
                    PAYMENT SUMMARY
                ====================================================== --}}
                <div class="mt-8 space-y-4">


                    {{-- Plan --}}
                    <div
                        class="flex justify-between gap-4 border-b border-slate-200 pb-4">

                        <span class="text-slate-500 font-bold">
                            Plan
                        </span>

                        <span
                            class="font-black text-mvDark text-right">
                            {{ $plan->name }}
                        </span>

                    </div>



                    {{-- Duration --}}
                    <div
                        class="flex justify-between gap-4 border-b border-slate-200 pb-4">

                        <span class="text-slate-500 font-bold">
                            Duration
                        </span>

                        <span class="font-black text-mvDark">
                            {{ $durationLabel }}
                        </span>

                    </div>



                    {{-- Base Price --}}
                    <div
                        class="flex justify-between gap-4 border-b border-slate-200 pb-4">

                        <span class="text-slate-500 font-bold">
                            Plan Amount
                        </span>

                        <span class="font-black text-mvDark">

                            ₹{{ number_format($basePrice, 2) }}

                        </span>

                    </div>



                    {{-- GST --}}
                    <div
                        class="flex justify-between gap-4 border-b border-slate-200 pb-4">

                        <span class="text-slate-500 font-bold">

                            GST
                            ({{ number_format($gstRate, 2) }}%)

                        </span>

                        <span class="font-black text-mvDark">

                            ₹{{ number_format($gstAmount, 2) }}

                        </span>

                    </div>



                    {{-- Total --}}
                    <div
                        class="rounded-2xl bg-[#f5f7ff] border border-[#d8d6ff] p-5">

                        <div
                            class="flex items-center justify-between gap-4">

                            <div>

                                <p
                                    class="text-sm font-bold text-slate-500">
                                    Total Payable
                                </p>

                                <p
                                    class="mt-1 text-xs text-slate-400">
                                    Including GST
                                </p>

                            </div>

                            <span
                                class="text-2xl sm:text-3xl font-black text-mvBlue">

                                ₹{{ number_format($totalPayable, 2) }}

                            </span>

                        </div>

                    </div>

                </div>



                {{-- =====================================================
                    GUEST CUSTOMER DATA
                ====================================================== --}}
                @if((int) request('trial', 0) === 0 && !auth()->check())

                    <div class="mt-8 space-y-4">


                        {{-- Name --}}
                        <div>

                            <label
                                for="customer_name"
                                class="block text-sm font-black text-slate-700 mb-2">

                                Name

                            </label>

                            <input
                                type="text"
                                id="customer_name"
                                autocomplete="name"
                                class="w-full rounded-2xl border border-slate-300 px-5 py-4 font-bold focus:outline-none focus:ring-2 focus:ring-mvBlue"
                                placeholder="Enter your name"
                                required>

                        </div>



                        {{-- Email --}}
                        <div>

                            <label
                                for="customer_email"
                                class="block text-sm font-black text-slate-700 mb-2">

                                Email

                            </label>

                            <input
                                type="email"
                                id="customer_email"
                                autocomplete="email"
                                class="w-full rounded-2xl border border-slate-300 px-5 py-4 font-bold focus:outline-none focus:ring-2 focus:ring-mvBlue"
                                placeholder="Enter your email"
                                required>

                        </div>

                    </div>

                @endif



                {{-- =====================================================
                    PAY BUTTON
                ====================================================== --}}
                <button
                    type="button"
                    id="payBtn"
                    class="mt-8 w-full rounded-full brand-gradient text-white py-4 px-6 font-black shadow-xl hover:opacity-90 disabled:opacity-60 disabled:cursor-not-allowed transition">

                    Pay ₹{{ number_format($totalPayable, 2) }}

                </button>


                <p
                    class="mt-5 text-xs text-center text-slate-500">

                    Secure payment powered by Razorpay

                </p>

            </div>

        </div>

    </div>

</main>



{{-- =============================================================
    PAYMENT SUCCESS FORM
============================================================= --}}
<form
    id="paymentForm"
    method="POST"
    action="{{ route('plans.payment.success', $plan->id) }}">

    @csrf


    <input
        type="hidden"
        name="razorpay_order_id"
        id="razorpay_order_id">


    <input
        type="hidden"
        name="razorpay_payment_id"
        id="razorpay_payment_id">


    <input
        type="hidden"
        name="razorpay_signature"
        id="razorpay_signature">


    <input
        type="hidden"
        name="name"
        id="payment_name">


    <input
        type="hidden"
        name="email"
        id="payment_email">

</form>



{{-- =============================================================
    RAZORPAY
============================================================= --}}
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>


<script>

document.addEventListener('DOMContentLoaded', function () {

    const payBtn = document.getElementById('payBtn');

    if (!payBtn) {
        return;
    }


    const normalButtonText =
        @json('Pay ₹' . number_format($totalPayable, 2));


    payBtn.addEventListener('click', async function () {


        /* =========================================================
            Guest validation
        ========================================================= */
        const customerNameField =
            document.getElementById('customer_name');

        const customerEmailField =
            document.getElementById('customer_email');


        if (customerNameField) {

            const customerName =
                customerNameField.value.trim();

            if (!customerName) {

                alert('Please enter your name.');

                customerNameField.focus();

                return;
            }
        }


        if (customerEmailField) {

            const customerEmail =
                customerEmailField.value.trim();

            if (!customerEmail) {

                alert('Please enter your email.');

                customerEmailField.focus();

                return;
            }


            const emailRegex =
                /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (!emailRegex.test(customerEmail)) {

                alert('Please enter a valid email address.');

                customerEmailField.focus();

                return;
            }
        }



        /* =========================================================
            Disable button
        ========================================================= */
        payBtn.disabled = true;

        payBtn.innerText =
            'Creating secure payment...';


        try {


            /* =====================================================
                CREATE RAZORPAY ORDER
            ====================================================== */
            const response = await fetch(
                "{{ route('plans.payment.order', $plan->id) }}",
                {
                    method: "POST",

                    credentials: "same-origin",

                    headers: {

                        "X-CSRF-TOKEN":
                            "{{ csrf_token() }}",

                        "Accept":
                            "application/json",

                        "Content-Type":
                            "application/json"
                    },

                    body: JSON.stringify({})
                }
            );



            let data;

            try {

                data = await response.json();

            } catch (jsonError) {

                console.error(
                    'Invalid JSON Response:',
                    jsonError
                );

                throw new Error(
                    'Server se valid payment response nahi mila.'
                );
            }



            /* =====================================================
                SERVER ERROR
            ====================================================== */
            if (!response.ok) {

                console.error(
                    'Order Error:',
                    data
                );

                throw new Error(
                    data.message
                    || 'Payment order create nahi ho paaya.'
                );
            }



            /* =====================================================
                VALIDATE RESPONSE
            ====================================================== */
            if (
                !data.key ||
                !data.order_id ||
                !data.amount
            ) {

                console.error(
                    'Invalid Razorpay Data:',
                    data
                );

                throw new Error(
                    'Razorpay order data incomplete hai.'
                );
            }



            /* =====================================================
                CUSTOMER DETAILS
            ====================================================== */
            const customerName =
                customerNameField
                    ? customerNameField.value.trim()
                    : @json(auth()->user()->name ?? '');


            const customerEmail =
                customerEmailField
                    ? customerEmailField.value.trim()
                    : @json(auth()->user()->email ?? '');



            /* =====================================================
                RAZORPAY OPTIONS
            ====================================================== */
            const options = {

                key: data.key,

                /*
                 * IMPORTANT:
                 * Amount server se aa raha hai.
                 *
                 * Blade amount par payment depend nahi karega.
                 */
                amount: data.amount,

                currency:
                    data.currency || "INR",

                name:
                    "MyVictory Billing",

                description:
                    "MyVictory Billing Plan",

                order_id:
                    data.order_id,


                /* =================================================
                    PAYMENT SUCCESS
                ================================================= */
                handler: function (response) {

                    document
                        .getElementById('razorpay_order_id')
                        .value =
                        response.razorpay_order_id;


                    document
                        .getElementById('razorpay_payment_id')
                        .value =
                        response.razorpay_payment_id;


                    document
                        .getElementById('razorpay_signature')
                        .value =
                        response.razorpay_signature;


                    document
                        .getElementById('payment_name')
                        .value =
                        customerName;


                    document
                        .getElementById('payment_email')
                        .value =
                        customerEmail;


                    payBtn.innerText =
                        'Payment successful...';


                    document
                        .getElementById('paymentForm')
                        .submit();
                },


                prefill: {

                    name:
                        customerName,

                    email:
                        customerEmail
                },


                notes: {

                    plan_id:
                        "{{ $plan->id }}",

                    plan_name:
                        "Billing Plan"
                },


                theme: {

                    color:
                        "#2624CC"
                },


                modal: {

                    ondismiss: function () {

                        payBtn.disabled =
                            false;

                        payBtn.innerText =
                            normalButtonText;
                    }
                }
            };



            /* =====================================================
                OPEN RAZORPAY
            ====================================================== */
            const razorpay =
                new Razorpay(options);


            razorpay.on(
                'payment.failed',
                function (response) {

                    console.error(
                        'Payment Failed:',
                        response.error
                    );


                    alert(
                        response.error.description
                        || 'Payment failed. Please try again.'
                    );


                    payBtn.disabled =
                        false;


                    payBtn.innerText =
                        normalButtonText;
                }
            );


            razorpay.open();


        } catch (error) {


            console.error(
                'Payment Error:',
                error
            );


            alert(
                error.message
                || 'Payment start nahi ho paaya.'
            );


            payBtn.disabled =
                false;


            payBtn.innerText =
                normalButtonText;
        }

    });

});

</script>

@endsection