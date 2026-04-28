<x-layouts.app :title="__('Bill Request Details')">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-4 bg-[#BFE0E0] dark:bg-[#354A54] p-6 rounded-2xl">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-neutral-100">
                Bill Request Details
            </h1>
            <div class="text-xs text-gray-500 dark:text-neutral-400">
                View complete billing request information
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('bill-requests.index') }}"
               class="inline-flex items-center justify-center px-3 py-2 rounded-md bg-[#46837d] text-white text-sm font-medium hover:bg-[#35655f]">
                Back
            </a>

            @if(($billRequest->status ?? 'pending') !== 'processed')
                <form method="POST" action="{{ route('bill-requests.create-invoice', $billRequest->id) }}"
                      onsubmit="return confirm('Is bill request se invoice create karna hai?')">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center justify-center px-3 py-2 rounded-md bg-blue-600 text-white text-sm font-medium hover:bg-blue-700">
                        Create Invoice
                    </button>
                </form>
            @endif

            <form method="POST" action="{{ route('bill-requests.destroy', $billRequest->id) }}"
                  onsubmit="return confirm('Delete this bill request?')">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="inline-flex items-center justify-center px-3 py-2 rounded-md bg-red-600 text-white text-sm font-medium hover:bg-red-700">
                    Delete
                </button>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="p-3 mb-4 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="p-3 mb-4 bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    @php
        $status = strtolower($billRequest->status ?? 'pending');

        $statusClass = match($status) {
            'approved'  => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-200 dark:border-emerald-800',
            'rejected'  => 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-900/30 dark:text-rose-200 dark:border-rose-800',
            'completed' => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-900/30 dark:text-blue-200 dark:border-blue-800',
            'processed' => 'bg-green-50 text-green-700 border-green-200 dark:bg-green-900/30 dark:text-green-200 dark:border-green-800',
            'failed'    => 'bg-red-50 text-red-700 border-red-200 dark:bg-red-900/30 dark:text-red-200 dark:border-red-800',
            default     => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-900/30 dark:text-amber-200 dark:border-amber-800',
        };

        $apiPretty = $apiResponse ? json_encode($apiResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : null;
    @endphp

    {{-- Top Summary --}}
    <div class="grid gap-4 md:grid-cols-4 mb-4">
        <div class="rounded-2xl border border-gray-200 dark:border-neutral-800 bg-[#BFE0E0] dark:bg-[#354A54] p-5">
            <div class="text-xs text-gray-500 dark:text-neutral-400">Request ID</div>
            <div class="mt-2 text-lg font-bold text-gray-900 dark:text-white">
                {{ $billRequest->source_request_id ?? $billRequest->id }}
            </div>
            <div class="text-xs text-gray-500 dark:text-neutral-400 mt-1">
                Internal ID: #{{ $billRequest->id }}
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 dark:border-neutral-800 bg-[#BFE0E0] dark:bg-[#354A54] p-5">
            <div class="text-xs text-gray-500 dark:text-neutral-400">Status</div>
            <div class="mt-2">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full border text-xs font-semibold {{ $statusClass }}">
                    {{ ucfirst($status) }}
                </span>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 dark:border-neutral-800 bg-[#BFE0E0] dark:bg-[#354A54] p-5">
            <div class="text-xs text-gray-500 dark:text-neutral-400">Amount</div>
            <div class="mt-2 text-lg font-bold text-gray-900 dark:text-white">
                ₹ {{ number_format((float)($billRequest->payment_amount ?? $billRequest->selling_price ?? $billRequest->package_price ?? 0), 2) }}
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 dark:border-neutral-800 bg-[#BFE0E0] dark:bg-[#354A54] p-5">
            <div class="text-xs text-gray-500 dark:text-neutral-400">Requested At</div>
            <div class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">
                {{ optional($billRequest->requested_at ?? $billRequest->created_at)->format('d M Y, h:i A') }}
            </div>
        </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        {{-- Customer Details --}}
        <div class="rounded-2xl border border-gray-200 dark:border-neutral-800 bg-[#BFE0E0] dark:bg-[#354A54] overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-200 dark:border-neutral-700">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white">Customer Details</h2>
            </div>

            <div class="p-5 grid gap-4 sm:grid-cols-2">
                <div>
                    <div class="text-xs text-gray-500 dark:text-neutral-400">Customer Name</div>
                    <div class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $billRequest->customer_name ?: '-' }}</div>
                </div>

                <div>
                    <div class="text-xs text-gray-500 dark:text-neutral-400">Business Name</div>
                    <div class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $billRequest->business_name ?: '-' }}</div>
                </div>

                <div>
                    <div class="text-xs text-gray-500 dark:text-neutral-400">Email</div>
                    <div class="mt-1 text-sm font-medium text-gray-900 dark:text-white break-all">{{ $billRequest->customer_email ?: '-' }}</div>
                </div>

                <div>
                    <div class="text-xs text-gray-500 dark:text-neutral-400">Phone</div>
                    <div class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $billRequest->customer_phone ?: '-' }}</div>
                </div>

                <div>
                    <div class="text-xs text-gray-500 dark:text-neutral-400">Alternate Phone</div>
                    <div class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $billRequest->customer_phone1 ?: '-' }}</div>
                </div>

                <div>
                    <div class="text-xs text-gray-500 dark:text-neutral-400">GST Number</div>
                    <div class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $billRequest->gst_number ?: '-' }}</div>
                </div>

                <div>
                    <div class="text-xs text-gray-500 dark:text-neutral-400">Country</div>
                    <div class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $billRequest->country ?: '-' }}</div>
                </div>

                <div>
                    <div class="text-xs text-gray-500 dark:text-neutral-400">State</div>
                    <div class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $billRequest->state ?: '-' }}</div>
                </div>

                <div>
                    <div class="text-xs text-gray-500 dark:text-neutral-400">City</div>
                    <div class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $billRequest->city ?: '-' }}</div>
                </div>

                <div>
                    <div class="text-xs text-gray-500 dark:text-neutral-400">PIN</div>
                    <div class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $billRequest->pin ?: '-' }}</div>
                </div>

                <div class="sm:col-span-2">
                    <div class="text-xs text-gray-500 dark:text-neutral-400">Address</div>
                    <div class="mt-1 text-sm font-medium text-gray-900 dark:text-white whitespace-pre-line">
                        {{ $billRequest->address ?: '-' }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Package & Payment --}}
        <div class="rounded-2xl border border-gray-200 dark:border-neutral-800 bg-[#BFE0E0] dark:bg-[#354A54] overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-200 dark:border-neutral-700">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white">Package & Payment Details</h2>
            </div>

            <div class="p-5 grid gap-4 sm:grid-cols-2">
                <div>
                    <div class="text-xs text-gray-500 dark:text-neutral-400">Package Name</div>
                    <div class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $billRequest->item?->name ?: '-' }}</div>
                </div>

                <div>
                    <div class="text-xs text-gray-500 dark:text-neutral-400">Package Price</div>
                    <div class="mt-1 text-sm font-medium text-gray-900 dark:text-white">
                        ₹ {{ number_format((float)($billRequest->package_price ?? 0), 2) }}
                    </div>
                </div>

                <div>
                    <div class="text-xs text-gray-500 dark:text-neutral-400">Selling Price</div>
                    <div class="mt-1 text-sm font-medium text-gray-900 dark:text-white">
                        ₹ {{ number_format((float)($billRequest->selling_price ?? 0), 2) }}
                    </div>
                </div>

                <div>
                    <div class="text-xs text-gray-500 dark:text-neutral-400">Payment Amount</div>
                    <div class="mt-1 text-sm font-medium text-gray-900 dark:text-white">
                        ₹ {{ number_format((float)($billRequest->payment_amount ?? 0), 2) }}
                    </div>
                </div>

                <div>
                    <div class="text-xs text-gray-500 dark:text-neutral-400">Payment Method</div>
                    <div class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $billRequest->payment_method ?: '-' }}</div>
                </div>

                <div>
                    <div class="text-xs text-gray-500 dark:text-neutral-400">Transaction ID</div>
                    <div class="mt-1 text-sm font-medium text-gray-900 dark:text-white break-all">{{ $billRequest->transaction_id ?: '-' }}</div>
                </div>

                <div>
                    <div class="text-xs text-gray-500 dark:text-neutral-400">Bank</div>
                    <div class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $billRequest->bank ?: '-' }}</div>
                </div>

                <div>
                    <div class="text-xs text-gray-500 dark:text-neutral-400">Payment Date</div>
                    <div class="mt-1 text-sm font-medium text-gray-900 dark:text-white">
                        {{ $billRequest->payment_date ? \Carbon\Carbon::parse($billRequest->payment_date)->format('d M Y') : '-' }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Source Details --}}
        <div class="rounded-2xl border border-gray-200 dark:border-neutral-800 bg-[#BFE0E0] dark:bg-[#354A54] overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-200 dark:border-neutral-700">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white">Source Details</h2>
            </div>

            <div class="p-5 grid gap-4 sm:grid-cols-2">
                <div>
                    <div class="text-xs text-gray-500 dark:text-neutral-400">Source Software</div>
                    <div class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $billRequest->source_software ?: '-' }}</div>
                </div>

                <div>
                    <div class="text-xs text-gray-500 dark:text-neutral-400">Source Request ID</div>
                    <div class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $billRequest->source_request_id ?: '-' }}</div>
                </div>

                <div>
                    <div class="text-xs text-gray-500 dark:text-neutral-400">Source Customer ID</div>
                    <div class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $billRequest->source_customer_id ?: '-' }}</div>
                </div>

                <div>
                    <div class="text-xs text-gray-500 dark:text-neutral-400">Source Package ID</div>
                    <div class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $billRequest->source_package_id ?: '-' }}</div>
                </div>

                <div>
                    <div class="text-xs text-gray-500 dark:text-neutral-400">Source User Package ID</div>
                    <div class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $billRequest->source_user_package_id ?: '-' }}</div>
                </div>

                <div>
                    <div class="text-xs text-gray-500 dark:text-neutral-400">Source Payment ID</div>
                    <div class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $billRequest->source_payment_id ?: '-' }}</div>
                </div>
            </div>
        </div>

        {{-- Remarks / Audit --}}
        <div class="rounded-2xl border border-gray-200 dark:border-neutral-800 bg-[#BFE0E0] dark:bg-[#354A54] overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-200 dark:border-neutral-700">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white">Remarks & Audit</h2>
            </div>

            <div class="p-5 grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <div class="text-xs text-gray-500 dark:text-neutral-400">Remarks</div>
                    <div class="mt-1 text-sm font-medium text-gray-900 dark:text-white whitespace-pre-line">
                        {{ $billRequest->remarks ?: '-' }}
                    </div>
                </div>

                <div>
                    <div class="text-xs text-gray-500 dark:text-neutral-400">Created At</div>
                    <div class="mt-1 text-sm font-medium text-gray-900 dark:text-white">
                        {{ optional($billRequest->created_at)->format('d M Y, h:i A') ?: '-' }}
                    </div>
                </div>

                <div>
                    <div class="text-xs text-gray-500 dark:text-neutral-400">Updated At</div>
                    <div class="mt-1 text-sm font-medium text-gray-900 dark:text-white">
                        {{ optional($billRequest->updated_at)->format('d M Y, h:i A') ?: '-' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Invoice info if processed --}}
    @if(isset($apiResponse['created_invoice_id']) || isset($apiResponse['created_invoice_number']))
        <div class="mt-4 rounded-2xl border border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/20 p-5">
            <h2 class="text-base font-semibold text-green-800 dark:text-green-200 mb-3">Created Invoice Info</h2>

            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <div class="text-xs text-green-700/80 dark:text-green-300/80">Invoice ID</div>
                    <div class="mt-1 text-sm font-semibold text-green-900 dark:text-green-100">
                        {{ $apiResponse['created_invoice_id'] ?? '-' }}
                    </div>
                </div>

                <div>
                    <div class="text-xs text-green-700/80 dark:text-green-300/80">Invoice Number</div>
                    <div class="mt-1 text-sm font-semibold text-green-900 dark:text-green-100">
                        {{ $apiResponse['created_invoice_number'] ?? '-' }}
                    </div>
                </div>

                <div>
                    <div class="text-xs text-green-700/80 dark:text-green-300/80">Client ID</div>
                    <div class="mt-1 text-sm font-semibold text-green-900 dark:text-green-100">
                        {{ $apiResponse['created_client_id'] ?? '-' }}
                    </div>
                </div>
            </div>

            @if(isset($apiResponse['created_invoice_id']) && Route::has('invoices.preview'))
                <div class="mt-4">
                    <a href="{{ route('invoices.preview', $apiResponse['created_invoice_id']) }}"
                       class="inline-flex items-center px-4 py-2 rounded-md bg-green-700 text-white text-sm font-medium hover:bg-green-800">
                        View Invoice
                    </a>
                </div>
            @endif
        </div>
    @endif

    {{-- API Response --}}
    <div class="mt-4 rounded-2xl border border-gray-200 dark:border-neutral-800 bg-[#BFE0E0] dark:bg-[#354A54] overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-200 dark:border-neutral-700">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white">API Response / Debug Data</h2>
        </div>

        <div class="p-5">
            @if($apiPretty)
                <pre class="w-full overflow-auto rounded-xl bg-neutral-900 text-green-300 text-xs sm:text-sm p-4">{{ $apiPretty }}</pre>
            @else
                <div class="text-sm text-gray-500 dark:text-neutral-400">
                    No API response available.
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>