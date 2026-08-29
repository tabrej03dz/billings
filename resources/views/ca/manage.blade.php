<x-layouts.app :title="__('CA Access')">
    <div class="max-w-6xl mx-auto space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">CA Access</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-neutral-300">
                {{ $business->name }} ka invoice/report access apne Chartered Accountant ko assign karein.
            </p>
        </div>

        @if (session('success'))
            <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
            <h2 class="font-semibold text-gray-900 dark:text-white">Assign CA</h2>
            <form method="POST" action="{{ route('business.ca.store') }}" class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-4">
                @csrf
                <div>
                    <label class="mb-1 block text-xs font-medium">CA Name</label>
                    <input name="name" value="{{ old('name') }}" class="w-full rounded-xl border-gray-300 text-sm dark:border-neutral-700 dark:bg-neutral-800" placeholder="CA name">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium">CA Email *</label>
                    <input type="email" name="email" required value="{{ old('email') }}" class="w-full rounded-xl border-gray-300 text-sm dark:border-neutral-700 dark:bg-neutral-800" placeholder="ca@example.com">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium">Mobile</label>
                    <input name="mobile" value="{{ old('mobile') }}" class="w-full rounded-xl border-gray-300 text-sm dark:border-neutral-700 dark:bg-neutral-800" placeholder="Mobile number">
                </div>
                <div class="flex items-end">
                    <button class="w-full rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">Assign CA</button>
                </div>
            </form>
            <p class="mt-3 text-xs text-gray-500">Email existing user ka ho to wahi account use hoga. Naya email ho to account create hoga aur password set link email kiya jayega.</p>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
            <div class="border-b px-5 py-4 dark:border-neutral-800"><h2 class="font-semibold">Assigned CAs</h2></div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500 dark:bg-neutral-800">
                        <tr><th class="px-5 py-3">CA</th><th class="px-5 py-3">Contact</th><th class="px-5 py-3">Status</th><th class="px-5 py-3 text-right">Action</th></tr>
                    </thead>
                    <tbody class="divide-y dark:divide-neutral-800">
                        @forelse ($assignments as $assignment)
                            <tr>
                                <td class="px-5 py-4 font-medium">{{ $assignment->ca?->name ?? 'User' }}</td>
                                <td class="px-5 py-4"><div>{{ $assignment->ca?->email }}</div><div class="text-xs text-gray-500">{{ $assignment->ca?->mobile }}</div></td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $assignment->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                        {{ $assignment->is_active ? 'Active' : 'Revoked' }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    @if ($assignment->is_active)
                                        <form method="POST" action="{{ route('business.ca.destroy', $assignment) }}" class="inline">@csrf @method('DELETE')<button class="rounded-lg border border-red-300 px-3 py-1.5 text-xs font-semibold text-red-700">Revoke</button></form>
                                    @else
                                        <form method="POST" action="{{ route('business.ca.reactivate', $assignment) }}" class="inline">@csrf @method('PATCH')<button class="rounded-lg border border-green-300 px-3 py-1.5 text-xs font-semibold text-green-700">Activate</button></form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-5 py-10 text-center text-gray-500">Abhi koi CA assign nahi hai.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.app>