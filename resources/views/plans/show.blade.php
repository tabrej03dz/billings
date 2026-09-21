<x-layouts.app :title="__('Plan Details')">
    <div class="max-w-5xl mx-auto flex flex-col gap-4">

        @if(session('success'))
            <div class="p-3 rounded-lg bg-green-50 text-green-700 border border-green-200">
                {{ session('success') }}
            </div>
        @endif


        @if(session('generated_payment_link'))

            <div
                class="mb-5 rounded-xl border border-green-200 bg-green-50 p-5"
            >

                <div class="font-bold text-green-800 mb-3">
                    Payment Link Ready
                </div>


                <div
                    class="flex flex-col md:flex-row gap-3"
                >

                    <input
                        id="generatedPaymentLink"
                        type="text"
                        readonly
                        value="{{ session('generated_payment_link') }}"
                        class="flex-1 rounded-lg border-gray-300 bg-white"
                    >


                    <button
                        type="button"
                        onclick="copyPaymentLink()"
                        class="rounded-lg bg-blue-600 px-5 py-2 text-white font-bold"
                    >
                        Copy Link
                    </button>


                    <a
                        href="{{ session('generated_payment_link') }}"
                        target="_blank"
                        class="rounded-lg bg-green-600 px-5 py-2 text-white font-bold text-center"
                    >
                        Open Link
                    </a>

                </div>

            </div>


            <script>

                function copyPaymentLink() {

                    const input =
                        document.getElementById(
                            'generatedPaymentLink'
                        );

                    navigator.clipboard.writeText(
                        input.value
                    );

                    alert(
                        'Payment link copied.'
                    );
                }

            </script>

        @endif

        @if ($errors->any())
            <div class="p-4 mb-4 rounded-lg bg-red-50 border border-red-200 text-red-700">
                <div class="font-bold mb-2">
                    Payment Link Error
                </div>

                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="flex items-center justify-between bg-[#BFE0E0] dark:bg-[#354A54] p-6 rounded-xl">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Plan Details</h1>

            <div class="flex items-center gap-2">
                <a href="{{ route('plans.edit', $plan->id) }}"
                   class="px-4 py-2 text-sm font-medium text-white bg-yellow-500 rounded-lg hover:bg-yellow-600">
                    Edit
                </a>

                <a href="{{ route('plans.index') }}"
                   class="px-4 py-2 text-sm font-medium text-white bg-gray-600 rounded-lg hover:bg-gray-700">
                    Back
                </a>

                <button
                    type="button"
                    onclick="openPaymentLinkModal()"
                    class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700"
                >
                    Generate Payment Link
                </button>
            </div>
        </div>

        <div class="bg-white dark:bg-neutral-900 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Plan Name</p>
                    <p class="mt-1 text-base font-semibold text-gray-900 dark:text-white">{{ $plan->name }}</p>
                </div>

                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Subtitle</p>
                    <p class="mt-1 text-base font-semibold text-gray-900 dark:text-white">
                        {{ $plan->subtitle ?: '—' }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Slug</p>
                    <p class="mt-1 text-base font-semibold text-gray-900 dark:text-white">{{ $plan->slug }}</p>
                </div>

                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Price</p>
                    <p class="mt-1 text-base font-semibold text-gray-900 dark:text-white">
                        ₹{{ number_format($plan->price, 2) }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Duration</p>
                    <p class="mt-1 text-base font-semibold text-gray-900 dark:text-white">
                        {{ $plan->duration_days }} days
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Sort Order</p>
                    <p class="mt-1 text-base font-semibold text-gray-900 dark:text-white">
                        {{ $plan->sort_order }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Status</p>
                    <p class="mt-1">
                        @if($plan->status)
                            <span class="inline-flex items-center px-2 py-1 text-xs rounded bg-green-100 text-green-700">
                                Active
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-1 text-xs rounded bg-red-100 text-red-700">
                                Inactive
                            </span>
                        @endif
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Recommended</p>
                    <p class="mt-1">
                        @if($plan->is_recommended)
                            <span class="inline-flex items-center px-2 py-1 text-xs rounded bg-yellow-100 text-yellow-700">
                                Yes
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-1 text-xs rounded bg-gray-100 text-gray-700">
                                No
                            </span>
                        @endif
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Created At</p>
                    <p class="mt-1 text-base font-semibold text-gray-900 dark:text-white">
                        {{ $plan->created_at?->format('d M Y h:i A') }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Updated At</p>
                    <p class="mt-1 text-base font-semibold text-gray-900 dark:text-white">
                        {{ $plan->updated_at?->format('d M Y h:i A') }}
                    </p>
                </div>

                <div class="md:col-span-2">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Description</p>
                    <div class="mt-1 text-base text-gray-900 dark:text-white">
                        {{ $plan->description ?: '—' }}
                    </div>
                </div>

                <div class="md:col-span-2">
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">Plan Features</p>

                    @if($plan->planFeatures && $plan->planFeatures->count())
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            @foreach($plan->planFeatures as $feature)
                                <div class="rounded-lg border border-gray-200 dark:border-neutral-700 p-4 bg-gray-50 dark:bg-neutral-800">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                                @if($feature->icon)
                                                    <span class="mr-1">{{ $feature->icon }}</span>
                                                @endif

                                                {{ $feature->title }}
                                            </p>

                                            @if($feature->description)
                                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                                                    {{ $feature->description }}
                                                </p>
                                            @endif
                                        </div>

                                        <div class="flex flex-col items-end gap-1">
                                            @if($feature->is_active)
                                                <span class="inline-flex items-center px-2 py-1 text-xs rounded bg-green-100 text-green-700">
                                                    Active
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-1 text-xs rounded bg-red-100 text-red-700">
                                                    Inactive
                                                </span>
                                            @endif

                                            <span class="text-xs text-gray-500">
                                                Order: {{ $feature->sort_order }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500">No features added.</p>
                    @endif
                </div>

                <div class="md:col-span-2">
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Permissions</p>

                    <div class="flex flex-wrap gap-2">
                        @forelse($plan->permissions as $permission)
                            <span class="inline-flex items-center px-3 py-1 text-sm rounded-full bg-blue-100 text-blue-700">
                                {{ $permission->name }}
                            </span>
                        @empty
                            <span class="text-gray-500">No permissions assigned.</span>
                        @endforelse
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div
        id="paymentLinkModal"
        class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4"
    >
        <div
            class="w-full max-w-lg rounded-2xl bg-white dark:bg-neutral-900 shadow-2xl max-h-[95vh] overflow-y-auto"
        >

            <div
                class="flex items-center justify-between border-b border-gray-200 dark:border-neutral-700 p-5"
            >

                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                        Generate Payment Link
                    </h2>

                    <p class="text-sm text-gray-500 mt-1">
                        {{ $plan->name }}
                    </p>
                </div>


                <button
                    type="button"
                    onclick="closePaymentLinkModal()"
                    class="text-2xl text-gray-500 hover:text-black dark:hover:text-white"
                >
                    ×
                </button>

            </div>


            <div class="p-5">

                {{-- Error Box --}}
                <div
                    id="paymentLinkError"
                    class="hidden mb-4 rounded-lg border border-red-300 bg-red-50 p-4 text-sm text-red-700"
                >
                </div>


                {{-- Success Box --}}
                <div
                    id="paymentLinkSuccess"
                    class="hidden mb-4 rounded-lg border border-green-300 bg-green-50 p-4"
                >

                    <div
                        class="font-bold text-green-700 mb-3"
                    >
                        Payment Link Ready
                    </div>


                    <input
                        id="generatedPaymentLink"
                        type="text"
                        readonly
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm mb-3"
                    >


                    <div
                        class="grid grid-cols-1 sm:grid-cols-3 gap-2"
                    >

                        <button
                            type="button"
                            onclick="copyGeneratedPaymentLink()"
                            class="rounded-lg bg-blue-600 px-4 py-2 text-white font-semibold"
                        >
                            Copy Link
                        </button>


                        <a
                            id="openGeneratedPaymentLink"
                            href="#"
                            target="_blank"
                            class="rounded-lg bg-green-600 px-4 py-2 text-white text-center font-semibold"
                        >
                            Open Link
                        </a>


                        <a
                            id="whatsappGeneratedPaymentLink"
                            href="#"
                            target="_blank"
                            class="rounded-lg bg-emerald-500 px-4 py-2 text-white text-center font-semibold"
                        >
                            WhatsApp
                        </a>

                    </div>

                </div>


                <form
                    id="paymentLinkForm"
                    action="{{ route('plans.generate-payment-link', $plan) }}"
                    method="POST"
                    class="space-y-5"
                >

                    @csrf


                    <div>

                        <label
                            class="block text-sm font-semibold mb-2 text-gray-800 dark:text-gray-200"
                        >
                            Customer Name
                        </label>

                        <input
                            id="paymentCustomerName"
                            type="text"
                            name="customer_name"
                            value="{{ old('customer_name') }}"
                            required
                            autocomplete="off"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 dark:bg-neutral-800 dark:text-white"
                            placeholder="Customer name"
                        >

                    </div>


                    <div>

                        <label
                            class="block text-sm font-semibold mb-2 text-gray-800 dark:text-gray-200"
                        >
                            Mobile Number
                        </label>

                        <input
                            id="paymentCustomerPhone"
                            type="text"
                            name="customer_phone"
                            value="{{ old('customer_phone') }}"
                            required
                            maxlength="15"
                            autocomplete="off"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 dark:bg-neutral-800 dark:text-white"
                            placeholder="9876543210"
                        >

                    </div>


                    <div>

                        <label
                            class="block text-sm font-semibold mb-2 text-gray-800 dark:text-gray-200"
                        >
                            Email
                        </label>

                        <input
                            id="paymentCustomerEmail"
                            type="email"
                            name="customer_email"
                            value="{{ old('customer_email') }}"
                            autocomplete="off"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 dark:bg-neutral-800 dark:text-white"
                            placeholder="customer@gmail.com"
                        >

                    </div>


                    @php

                        $basePrice =
                            (float) $plan->price;

                        $gstRate =
                            (float) (
                                $plan->tax
                                ?? 18
                            );

                        $gstAmount =
                            round(
                                (
                                    $basePrice *
                                    $gstRate
                                ) / 100,
                                2
                            );

                        $totalAmount =
                            round(
                                $basePrice +
                                $gstAmount,
                                2
                            );

                    @endphp


                    <div
                        class="rounded-xl bg-gray-50 dark:bg-neutral-800 p-4 space-y-3"
                    >

                        <div
                            class="flex justify-between"
                        >

                            <span>
                                Plan Price
                            </span>

                            <strong>
                                ₹{{ number_format($basePrice, 2) }}
                            </strong>

                        </div>


                        <div
                            class="flex justify-between"
                        >

                            <span>
                                GST {{ $gstRate }}%
                            </span>

                            <strong>
                                ₹{{ number_format($gstAmount, 2) }}
                            </strong>

                        </div>


                        <div
                            class="border-t pt-3 flex justify-between text-lg"
                        >

                            <strong>
                                Total
                            </strong>

                            <strong>
                                ₹{{ number_format($totalAmount, 2) }}
                            </strong>

                        </div>

                    </div>


                    <button
                        id="generatePaymentLinkBtn"
                        type="submit"
                        class="w-full rounded-lg bg-green-600 text-white py-3 font-bold hover:bg-green-700 disabled:opacity-60 disabled:cursor-not-allowed"
                    >

                        <span
                            id="paymentLinkButtonText"
                        >
                            Generate Razorpay Link
                        </span>

                    </button>

                </form>

            </div>

        </div>
    </div>

    <script>
document.addEventListener('DOMContentLoaded', function () {

    const form =
        document.getElementById(
            'paymentLinkForm'
        );

    if (!form) {
        console.error(
            'Payment link form not found.'
        );

        return;
    }


    form.addEventListener(
        'submit',
        async function (event) {

            event.preventDefault();


            const button =
                document.getElementById(
                    'generatePaymentLinkBtn'
                );

            const buttonText =
                document.getElementById(
                    'paymentLinkButtonText'
                );

            const errorBox =
                document.getElementById(
                    'paymentLinkError'
                );

            const successBox =
                document.getElementById(
                    'paymentLinkSuccess'
                );


            /*
            |--------------------------------------------------------------------------
            | Reset UI
            |--------------------------------------------------------------------------
            */

            errorBox.classList.add(
                'hidden'
            );

            errorBox.innerHTML =
                '';

            successBox.classList.add(
                'hidden'
            );


            button.disabled =
                true;

            buttonText.innerText =
                'Generating Payment Link...';


            try {

                const formData =
                    new FormData(
                        form
                    );


                const response =
                    await fetch(
                        form.action,
                        {
                            method: 'POST',

                            body:
                                formData,

                            headers: {

                                'Accept':
                                    'application/json',

                                'X-Requested-With':
                                    'XMLHttpRequest',
                            },
                        }
                    );


                let result = null;


                try {

                    result =
                        await response.json();

                } catch (jsonError) {

                    throw new Error(
                        'Server se valid JSON response nahi mila. HTTP Status: ' +
                        response.status
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Error
                |--------------------------------------------------------------------------
                */

                if (
                    !response.ok
                    ||
                    result.status !== true
                ) {

                    throw new Error(
                        result.message
                        ||
                        'Payment link generate nahi hua.'
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Link Missing
                |--------------------------------------------------------------------------
                */

                if (
                    !result.payment_link
                ) {

                    throw new Error(
                        'Payment link response me nahi mila.'
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Success UI
                |--------------------------------------------------------------------------
                */

                const link =
                    result.payment_link;


                document.getElementById(
                    'generatedPaymentLink'
                ).value =
                    link;


                document.getElementById(
                    'openGeneratedPaymentLink'
                ).href =
                    link;


                document.getElementById(
                    'whatsappGeneratedPaymentLink'
                ).href =
                    'https://wa.me/?text=' +
                    encodeURIComponent(
                        'Payment Link: ' +
                        link
                    );


                successBox.classList.remove(
                    'hidden'
                );


                /*
                |--------------------------------------------------------------------------
                | Button Success
                |--------------------------------------------------------------------------
                */

                buttonText.innerText =
                    'Payment Link Generated';


                setTimeout(
                    function () {

                        button.disabled =
                            false;

                        buttonText.innerText =
                            'Generate Razorpay Link';

                    },
                    1500
                );


            } catch (error) {

                console.error(
                    'Payment Link Error:',
                    error
                );


                errorBox.innerHTML =
                    '<strong>Payment Link Error:</strong><br>' +
                    escapePaymentHtml(
                        error.message
                    );


                errorBox.classList.remove(
                    'hidden'
                );


                button.disabled =
                    false;

                buttonText.innerText =
                    'Generate Razorpay Link';
            }

        }
    );

});


function openPaymentLinkModal()
{
    const modal =
        document.getElementById(
            'paymentLinkModal'
        );

    modal.classList.remove(
        'hidden'
    );
}


function closePaymentLinkModal()
{
    const modal =
        document.getElementById(
            'paymentLinkModal'
        );

    modal.classList.add(
        'hidden'
    );
}


async function copyGeneratedPaymentLink()
{
    const input =
        document.getElementById(
            'generatedPaymentLink'
        );


    try {

        await navigator.clipboard.writeText(
            input.value
        );


        alert(
            'Payment link copied.'
        );

    } catch (error) {

        input.select();

        document.execCommand(
            'copy'
        );


        alert(
            'Payment link copied.'
        );
    }
}


function escapePaymentHtml(text)
{
    const div =
        document.createElement(
            'div'
        );

    div.textContent =
        text;

    return div.innerHTML;
}
</script>
</x-layouts.app>