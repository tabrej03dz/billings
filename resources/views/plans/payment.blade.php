@extends('frontend.layout')

@section('content')

<main class="min-h-screen bg-[#f5f7ff] py-16">
    <div class="max-w-5xl mx-auto px-4 lg:px-8">

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

        <div class="grid lg:grid-cols-2 gap-8 items-start">

            {{-- Plan Details --}}
            <div class="bg-white rounded-[2.5rem] border border-slate-200 p-8 soft-card">
                <div class="inline-flex items-center gap-2 rounded-full bg-[#f1f0ff] border border-[#d8d6ff] px-5 py-2 text-sm font-black text-mvBlue mb-6">
                    <span class="h-2.5 w-2.5 rounded-full brand-gradient"></span>
                    Selected Plan
                </div>

                <h1 class="text-3xl lg:text-5xl font-black text-mvDark">
                    {{ $plan->name }}
                </h1>

                <p class="mt-4 text-slate-600 leading-8">
                    {{ $plan->subtitle ?? $plan->description ?? 'Perfect billing plan for your business.' }}
                </p>

                <div class="mt-8 rounded-3xl bg-[#f5f7ff] border border-slate-200 p-6">
                    <div class="text-sm text-slate-500 font-bold">Plan Amount</div>

                    <div class="mt-2 text-5xl font-black text-mvBlue">
                        ₹{{ number_format($plan->price, 2) }}
                    </div>

                    <div class="mt-2 text-sm text-slate-500 font-bold">
                        Validity:
                        {{ $plan->duration_days >= 365 ? '1 Year' : $plan->duration_days . ' Days' }}
                    </div>
                </div>

                <div class="mt-8">
                    <h3 class="text-xl font-black text-mvDark mb-4">Plan Features</h3>

                    <ul class="space-y-3 text-slate-600">
                        @forelse($plan->planFeatures ?? [] as $feature)
                            <li class="flex gap-3">
                                <span class="text-mvBlue font-black">
                                    {{ $feature->icon ?: '✔' }}
                                </span>
                                <span>{{ $feature->title }}</span>
                            </li>
                        @empty
                            <li>✔ GST Billing</li>
                            <li>✔ Customer Management</li>
                            <li>✔ Invoice PDF Download</li>
                            <li>✔ WhatsApp Invoice Sharing</li>
                            <li>✔ Stock Management</li>
                        @endforelse
                    </ul>
                </div>
            </div>

            {{-- Payment Box --}}
            <div class="bg-white rounded-[2.5rem] border border-slate-200 p-8 soft-card">
                <h2 class="text-3xl font-black text-mvDark">
                    Complete Payment
                </h2>

                <p class="mt-3 text-slate-600">
                    Payment complete karne ke baad aapka plan activate ho jayega.
                </p>

                <div class="mt-8 space-y-4">
                    <div class="flex justify-between border-b border-slate-200 pb-4">
                        <span class="text-slate-500 font-bold">Plan</span>
                        <span class="font-black text-mvDark">{{ $plan->name }}</span>
                    </div>

                    <div class="flex justify-between border-b border-slate-200 pb-4">
                        <span class="text-slate-500 font-bold">Duration</span>
                        <span class="font-black text-mvDark">
                            {{ $plan->duration_days >= 365 ? '1 Year' : $plan->duration_days . ' Days' }}
                        </span>
                    </div>

                    <div class="flex justify-between border-b border-slate-200 pb-4">
                        <span class="text-slate-500 font-bold">Total Payable</span>
                        <span class="text-2xl font-black text-mvBlue">
                            ₹{{ number_format($plan->price, 2) }}
                        </span>
                    </div>
                </div>

                @if((int) request('trial', 0) === 0 && !auth()->check())
                    <div class="mt-8 space-y-4">
                        <div>
                            <label class="block text-sm font-black text-slate-700 mb-2">
                                Name
                            </label>
                            <input type="text" id="customer_name"
                                class="w-full rounded-2xl border border-slate-300 px-5 py-4 font-bold focus:outline-none focus:ring-2 focus:ring-mvBlue"
                                placeholder="Enter your name" required>
                        </div>

                        <div>
                            <label class="block text-sm font-black text-slate-700 mb-2">
                                Email
                            </label>
                            <input type="email" id="customer_email"
                                class="w-full rounded-2xl border border-slate-300 px-5 py-4 font-bold focus:outline-none focus:ring-2 focus:ring-mvBlue"
                                placeholder="Enter your email" required>
                        </div>
                    </div>
                @endif

                <button id="payBtn"
                    class="mt-8 w-full rounded-full brand-gradient text-white py-4 font-black shadow-xl hover:opacity-90">
                    Pay ₹{{ number_format($plan->price, 2) }}
                </button>

                <p class="mt-5 text-xs text-center text-slate-500">
                    Secure payment powered by Razorpay
                </p>
            </div>
        </div>
    </div>
</main>

<form id="paymentForm" method="POST" action="{{ route('plans.payment.success', $plan->id) }}">
    @csrf
    <input type="hidden" name="razorpay_order_id" id="razorpay_order_id">
    <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
    <input type="hidden" name="razorpay_signature" id="razorpay_signature">

    <input type="hidden" name="name" id="payment_name">
    <input type="hidden" name="email" id="payment_email">
</form>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>

<script>
document.getElementById('payBtn').onclick = function () {
    fetch("{{ route('plans.payment.order', $plan->id) }}", {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": "{{ csrf_token() }}",
            "Accept": "application/json",
            "Content-Type": "application/json"
        }
    })
    .then(res => res.json())
    .then(data => {
        let options = {
            key: data.key,
            amount: data.amount,
            currency: "INR",
            name: "MyVictory Billing",
            description: "MyVictory Billing Plan",
            order_id: data.order_id,

            // handler: function (response) {
            //     document.getElementById('razorpay_order_id').value = response.razorpay_order_id;
            //     document.getElementById('razorpay_payment_id').value = response.razorpay_payment_id;
            //     document.getElementById('razorpay_signature').value = response.razorpay_signature;

            //     const customerName = document.getElementById('customer_name')?.value || "{{ auth()->user()->name ?? '' }}";
            //     const customerEmail = document.getElementById('customer_email')?.value || "{{ auth()->user()->email ?? '' }}";

            //     document.getElementById('payment_name').value = customerName;
            //     document.getElementById('payment_email').value = customerEmail;

            //     document.getElementById('paymentForm').submit();
            // },

            handler: function (response) {
                document.getElementById('razorpay_order_id').value = response.razorpay_order_id;
                document.getElementById('razorpay_payment_id').value = response.razorpay_payment_id;
                document.getElementById('razorpay_signature').value = response.razorpay_signature;

                document.getElementById('payment_name').value =
                    document.getElementById('customer_name')?.value || '';

                document.getElementById('payment_email').value =
                    document.getElementById('customer_email')?.value || '';

                document.getElementById('paymentForm').submit();
            },

            // prefill: {
            //     name: "{{ auth()->user()->name ?? '' }}",
            //     email: "{{ auth()->user()->email ?? '' }}"
            // },

            prefill: {
                name: document.getElementById('customer_name')?.value || "{{ auth()->user()->name ?? '' }}",
                email: document.getElementById('customer_email')?.value || "{{ auth()->user()->email ?? '' }}"
            },

            theme: {
                color: "#2624CC"
            }
//         };

//         let rzp = new Razorpay(options);
//         rzp.open();
//     });
// };
// </script>


<script src="https://checkout.razorpay.com/v1/checkout.js"></script>

<script>
const payBtn = document.getElementById('payBtn');

payBtn.addEventListener('click', async function () {
    payBtn.disabled = true;
    payBtn.innerText = 'Please wait...';

    try {
        const response = await fetch("{{ route('plans.payment.order', $plan->id) }}", {
            method: "POST",
            credentials: "same-origin",
            headers: {
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "Accept": "application/json",
                "Content-Type": "application/json"
            },
            body: JSON.stringify({})
        });

        const data = await response.json();

        if (!response.ok) {
            alert(data.message || 'Order create nahi ho paaya.');
            console.log('Order Error:', data);
            return;
        }

        if (!data.key || !data.order_id || !data.amount) {
            alert('Razorpay order data incomplete hai.');
            console.log('Invalid Razorpay Data:', data);
            return;
        }

        const options = {
            key: data.key,
            amount: data.amount,
            currency: "INR",
            name: "MyVictory Billing",
            description: "MyVictory Billing Plan",
            order_id: data.order_id,

            handler: function (response) {
                document.getElementById('razorpay_order_id').value = response.razorpay_order_id;
                document.getElementById('razorpay_payment_id').value = response.razorpay_payment_id;
                document.getElementById('razorpay_signature').value = response.razorpay_signature;

                document.getElementById('payment_name').value =
                document.getElementById('customer_name')?.value || '';

                document.getElementById('payment_email').value =
                document.getElementById('customer_email')?.value || '';

                document.getElementById('paymentForm').submit();
            },

            prefill: {
                name: "{{ auth()->user()->name ?? '' }}",
                email: "{{ auth()->user()->email ?? '' }}"
            },

            theme: {
                color: "#2624CC"
            }
        };

        const rzp = new Razorpay(options);
        rzp.open();

    } catch (error) {
        alert('Payment start nahi ho paaya. Console check karo.');
        console.error('Payment JS Error:', error);
    } finally {
        payBtn.disabled = false;
        payBtn.innerText = "Pay ₹{{ number_format($plan->price, 2) }}";
    }
});
</script>

@endsection