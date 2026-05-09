<x-layouts.app :title="__('Recycle Bin')">
    <div class="flex flex-col gap-4">

        @if(session('success'))
            <div class="p-3 rounded-lg bg-green-50 text-green-700 border border-green-200">
                {{ session('success') }}
            </div>
        @endif

        <div class="flex items-center justify-between mb-4 bg-[#BFE0E0] dark:bg-[#354A54] p-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Recycle Bin</h1>
                <p class="text-sm text-gray-600 dark:text-gray-300">
                    Deleted users aur businesses yahan show honge.
                </p>
            </div>

            <div class="flex gap-2">
                <a href="{{ route('recycle.index', ['type' => 'users']) }}"
                   class="px-4 py-2 rounded-lg text-sm font-medium {{ $type === 'users' ? 'bg-black text-white' : 'bg-white text-gray-700' }}">
                    Users
                </a>

                <a href="{{ route('recycle.index', ['type' => 'businesses']) }}"
                   class="px-4 py-2 rounded-lg text-sm font-medium {{ $type === 'businesses' ? 'bg-black text-white' : 'bg-white text-gray-700' }}">
                    Businesses
                </a>
            </div>
        </div>

        @if($type === 'users')
            <div class="overflow-auto rounded-xl border border-gray-200 dark:border-gray-700">
                <table class="min-w-full text-sm text-left text-gray-700 dark:text-gray-300">
                    <thead class="bg-[#BFE0E0] dark:bg-[#354A54] text-xs uppercase font-medium tracking-wider">
                    <tr>
                        <th class="px-6 py-3">Name</th>
                        <th class="px-6 py-3">Email</th>
                        <th class="px-6 py-3">Deleted At</th>
                        <th class="px-6 py-3">Actions</th>
                    </tr>
                    </thead>

                    <tbody class="bg-white divide-y divide-gray-200 dark:bg-neutral-900 dark:divide-neutral-700">
                    @forelse ($users as $user)
                        <tr>
                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                {{ $user->name ?? 'N/A' }}
                            </td>

                            <td class="px-6 py-4">
                                {{ $user->email ?? 'N/A' }}
                            </td>

                            <td class="px-6 py-4">
                                {{ $user->deleted_at ? $user->deleted_at->format('d M Y h:i A') : 'N/A' }}
                            </td>

                            <td class="px-6 py-4 space-x-2">
                                <form action="{{ route('recycle.users.restore', $user->id) }}" method="POST" class="inline-block">
                                    @csrf
                                    <button type="submit"
                                            class="bg-green-600 text-white p-2 m-2 hover:bg-green-700">
                                        Restore
                                    </button>
                                </form>

                                <form action="{{ route('recycle.users.forceDelete', $user->id) }}" method="POST" class="inline-block"
                                      onsubmit="return confirm('Ye user permanently delete ho jayega. Are you sure?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="bg-red-600 text-white p-2 m-2 hover:bg-red-700">
                                        Delete Forever
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                                No deleted users found.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $users->withQueryString()->links() }}
            </div>
        @endif

        @if($type === 'businesses')
            <div class="overflow-auto rounded-xl border border-gray-200 dark:border-gray-700">
                <table class="min-w-full text-sm text-left text-gray-700 dark:text-gray-300">
                    <thead class="bg-[#BFE0E0] dark:bg-[#354A54] text-xs uppercase font-medium tracking-wider">
                    <tr>
                        <th class="px-6 py-3">Logo</th>
                        <th class="px-6 py-3">Name</th>
                        <th class="px-6 py-3">Email</th>
                        <th class="px-6 py-3">Mobile</th>
                        <th class="px-6 py-3">GSTIN</th>
                        <th class="px-6 py-3">Deleted At</th>
                        <th class="px-6 py-3">Actions</th>
                    </tr>
                    </thead>

                    <tbody class="bg-white divide-y divide-gray-200 dark:bg-neutral-900 dark:divide-neutral-700">
                    @forelse ($businesses as $business)
                        <tr>
                            <td class="px-6 py-4">
                                @if($business->logo)
                                    <img src="{{ asset('storage/' . $business->logo) }}"
                                         alt="Logo"
                                         class="w-10 h-10 rounded-full object-cover">
                                @else
                                    <span class="text-gray-400">N/A</span>
                                @endif
                            </td>

                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                {{ $business->name }}
                                <div class="text-xs text-gray-500">/{{ $business->slug }}</div>
                            </td>

                            <td class="px-6 py-4">
                                {{ $business->email ?? 'N/A' }}
                            </td>

                            <td class="px-6 py-4">
                                {{ $business->mobile ?? 'N/A' }}
                            </td>

                            <td class="px-6 py-4">
                                {{ $business->gstin ?? 'N/A' }}
                            </td>

                            <td class="px-6 py-4">
                                {{ $business->deleted_at ? $business->deleted_at->format('d M Y h:i A') : 'N/A' }}
                            </td>

                            <td class="px-6 py-4 space-x-2">
                                <form action="{{ route('recycle.businesses.restore', $business->id) }}" method="POST" class="inline-block">
                                    @csrf
                                    <button type="submit"
                                            class="bg-green-600 text-white p-2 m-2 hover:bg-green-700">
                                        Restore
                                    </button>
                                </form>

                                <form action="{{ route('recycle.businesses.forceDelete', $business->id) }}" method="POST" class="inline-block"
                                      onsubmit="return confirm('Ye business permanently delete ho jayega. Are you sure?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="bg-red-600 text-white p-2 m-2 hover:bg-red-700">
                                        Delete Forever
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                No deleted businesses found.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $businesses->withQueryString()->links() }}
            </div>
        @endif

    </div>
</x-layouts.app>