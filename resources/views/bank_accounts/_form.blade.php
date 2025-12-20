@extends('backend.layout.root', ['title' => 'Bank Accounts'])

@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <div class="max-w-7xl mx-auto px-3 py-5">

        {{-- Flash --}}
        @if(session('success'))
            <div class="mb-4 rounded-xl border border-green-200 bg-green-50 text-green-800 px-4 py-3">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 text-red-800 px-4 py-3">
                <ul class="list-disc ml-5">
                    @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                </ul>
            </div>
        @endif

        {{-- Header --}}
        <div class="bg-white rounded-2xl shadow p-4 sm:p-5 mb-4 flex flex-col sm:flex-row gap-3 sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-extrabold text-slate-900">Business Bank Accounts</h1>
                <p class="text-sm text-slate-500 mt-1">Invoice “Payment Received In” dropdown yahin se manage hoga.</p>
            </div>

            <a href="{{ route('bank-accounts.create') }}"
               class="inline-flex items-center justify-center px-4 py-2 rounded-xl bg-blue-600 text-white font-bold hover:bg-blue-700">
                + Add Bank
            </a>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-2xl shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50">
                    <tr class="text-left text-xs font-extrabold text-slate-600">
                        <th class="px-4 py-3">Label</th>
                        <th class="px-4 py-3">UPI / Account</th>
                        <th class="px-4 py-3">Bank</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y">
                    @forelse($banks as $b)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3">
                                <div class="font-extrabold text-slate-900">
                                    {{ $b->label ?: ($b->bank_name ?: 'Bank') }}
                                </div>
                                <div class="text-xs text-slate-500">
                                    Holder: {{ $b->account_holder ?: '-' }}
                                    @if($b->ifsc) • IFSC: {{ $b->ifsc }} @endif
                                </div>

                                @if($b->is_default)
                                    <span class="inline-flex mt-2 px-2 py-1 rounded-lg bg-emerald-100 text-emerald-700 text-[11px] font-extrabold">
                                    DEFAULT
                                </span>
                                @endif
                            </td>

                            <td class="px-4 py-3">
                                <div class="font-bold text-slate-900">
                                    {{ $b->upi_id ?: ($b->account_no ?: '-') }}
                                </div>
                                <div class="text-xs text-slate-500">
                                    {{ $b->upi_id ? 'UPI' : 'Account No.' }}
                                </div>
                            </td>

                            <td class="px-4 py-3">
                                <div class="font-bold text-slate-900">{{ $b->bank_name ?: '-' }}</div>
                                <div class="text-xs text-slate-500">{{ $b->branch ?: '' }}</div>
                            </td>

                            <td class="px-4 py-3">
                                @if($b->is_active)
                                    <span class="px-2 py-1 rounded-lg bg-blue-100 text-blue-700 text-[11px] font-extrabold">ACTIVE</span>
                                @else
                                    <span class="px-2 py-1 rounded-lg bg-slate-200 text-slate-700 text-[11px] font-extrabold">INACTIVE</span>
                                @endif
                            </td>

                            <td class="px-4 py-3">
                                <div class="flex gap-2 justify-end flex-wrap">

                                    <a href="{{ route('bank-accounts.edit', $b->id) }}"
                                       class="px-3 py-2 rounded-xl border border-slate-200 font-bold hover:bg-slate-50">
                                        Edit
                                    </a>

                                    @if(!$b->is_default)
                                        <form method="POST" action="{{ route('bank-accounts.default', $b->id) }}"
                                              class="makeDefaultForm">
                                            @csrf
                                            <button type="submit"
                                                    class="px-3 py-2 rounded-xl bg-emerald-600 text-white font-bold hover:bg-emerald-700">
                                                Make Default
                                            </button>
                                        </form>
                                    @endif

                                    <form method="POST" action="{{ route('bank-accounts.destroy', $b->id) }}"
                                          class="deleteForm">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="px-3 py-2 rounded-xl bg-red-600 text-white font-bold hover:bg-red-700">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-slate-500">
                                No bank accounts found. <a class="text-blue-600 font-extrabold" href="{{ route('bank-accounts.create') }}">Add one</a>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <script>
        document.querySelectorAll('.deleteForm').forEach(form => {
            form.addEventListener('submit', function(e){
                e.preventDefault();
                Swal.fire({
                    title: 'Delete Bank Account?',
                    text: 'This action cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete',
                    cancelButtonText: 'Cancel',
                }).then((r) => {
                    if(r.isConfirmed) form.submit();
                });
            });
        });

        document.querySelectorAll('.makeDefaultForm').forEach(form => {
            form.addEventListener('submit', function(e){
                e.preventDefault();
                Swal.fire({
                    title: 'Set as Default?',
                    text: 'This will replace existing default bank.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, set default',
                    cancelButtonText: 'Cancel',
                }).then((r) => {
                    if(r.isConfirmed) form.submit();
                });
            });
        });
    </script>
@endsection
