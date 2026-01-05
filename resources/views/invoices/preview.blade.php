<x-layouts.app :title="__('Invoices')">
    <div class="max-w-7xl mx-auto px-3 sm:px-6 py-6">

        {{-- Alerts --}}
        @if(session('success'))
            <div class="mb-3 p-3 rounded-xl bg-green-50 border border-green-200 text-green-800">
                {{ session('success') }}
            </div>
        @endif
        @if($errors->any())
            <div class="mb-3 p-3 rounded-xl bg-red-50 border border-red-200 text-red-800">
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                </ul>
            </div>
        @endif

        {{-- Top Bar --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-3">
            <div>
                <div class="text-xl font-extrabold text-slate-900">
                    Invoice Preview <span class="text-slate-500">#{{ $invoice->invoice_number }}</span>
                </div>
                <div class="text-sm text-slate-500">
                    {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d M Y') }}
                    • {{ strtoupper($invoice->invoice_type ?? 'TAX') }}
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('invoices.download', $invoice->id) }}"
                   class="px-4 py-2 rounded-xl bg-slate-900 text-white text-sm font-semibold hover:bg-slate-800">
                    ⬇ Download
                </a>

                <a href="{{ route('invoices.edit', $invoice->id) }}"
                   class="px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-500">
                    ✏ Edit
                </a>

                <form method="POST" action="{{ route('invoices.send', $invoice->id) }}"
                      onsubmit="return confirm('Send invoice?');">
                    @csrf
                    <button class="px-4 py-2 rounded-xl bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-500">
                        📤 Send
                    </button>
                </form>

                <form method="get" action="{{ route('invoices.index', $invoice->id) }}"
                      onsubmit="return confirm('Cancel this invoice?');">
                    @csrf
                    <button class="px-4 py-2 rounded-xl bg-red-600 text-white text-sm font-semibold hover:bg-red-500">
                        ✖ Cancel
                    </button>
                </form>
            </div>
        </div>

        {{-- PDF Container --}}
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-200 flex items-center justify-between">
                <div class="text-sm font-semibold text-slate-700">PDF Preview (Same as pdf_simple)</div>
                <a href="{{ $pdfSrc }}" target="_blank" class="text-sm text-slate-600 underline">
                    Open PDF in new tab
                </a>
            </div>

            <iframe src="{{ $pdfSrc }}#toolbar=1&navpanes=0"
                    style="width:100%; height:80vh; border:0;"></iframe>
        </div>

    </div>
</x-layouts.app>
