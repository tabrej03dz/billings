<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choose Plan</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-50 text-slate-800">
    <div class="max-w-6xl mx-auto px-4 py-10">
        <div class="text-center mb-10">
            <h1 class="text-3xl font-bold text-slate-900">Choose Your Plan</h1>
            <p class="mt-2 text-slate-600">Registration complete ho gaya hai. Ab apna plan select kijiye.</p>
        </div>

        @if(session('success'))
            <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid md:grid-cols-3 gap-6">
            @forelse($plans as $plan)
                <div class="rounded-3xl bg-white border border-slate-200 shadow-sm p-6 flex flex-col">
                    <h2 class="text-xl font-semibold text-slate-900">{{ $plan->name }}</h2>
                    <p class="mt-2 text-3xl font-bold text-emerald-600">₹{{ number_format($plan->price, 2) }}</p>
                    <p class="mt-1 text-sm text-slate-500">{{ $plan->duration_days }} days</p>

                    @if($plan->description)
                        <p class="mt-4 text-sm text-slate-600 leading-6">{{ $plan->description }}</p>
                    @endif

                    <form action="{{ route('plan.choose.store') }}" method="POST" class="mt-6">
                        @csrf
                        <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                        <button type="submit"
                            class="w-full rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white hover:bg-slate-800 transition">
                            Select Plan
                        </button>
                    </form>
                </div>
            @empty
                <div class="col-span-3 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-4 text-amber-700 text-center">
                    Koi active plan available nahi hai.
                </div>
            @endforelse
        </div>
    </div>
</body>
</html>