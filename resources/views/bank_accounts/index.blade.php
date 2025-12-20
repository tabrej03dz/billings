@extends('backend.layout.root', ['title' => 'Add Bank Account'])

@section('content')
    <script src="https://cdn.tailwindcss.com"></script>

    <div class="max-w-4xl mx-auto px-3 py-5">

        <div class="bg-white rounded-2xl shadow p-4 sm:p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h1 class="text-xl font-extrabold text-slate-900">Add Bank Account</h1>
                    <p class="text-sm text-slate-500 mt-1">Business-wise bank/UPI add karo.</p>
                </div>
                <a href="{{ route('bank-accounts.index') }}"
                   class="px-4 py-2 rounded-xl border border-slate-200 font-bold hover:bg-slate-50">
                    Back
                </a>
            </div>

            @if($errors->any())
                <div class="mb-4 rounded-xl border border-red-200 bg-red-50 text-red-800 px-4 py-3">
                    <ul class="list-disc ml-5">
                        @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('bank-accounts.store') }}" class="space-y-4">
                @csrf

                @include('bank_accounts._form')

                <div class="flex gap-2 justify-end pt-2">
                    <a href="{{ route('bank-accounts.index') }}"
                       class="px-4 py-2 rounded-xl border border-slate-200 font-bold hover:bg-slate-50">
                        Cancel
                    </a>
                    <button class="px-5 py-2 rounded-xl bg-blue-600 text-white font-extrabold hover:bg-blue-700">
                        Save
                    </button>
                </div>

            </form>
        </div>

    </div>
@endsection
