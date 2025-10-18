<x-layouts.app :title="__('Clients')">
    <div class="flex flex-col gap-4">

        @if(session('success'))
            <div class="p-3 rounded-lg bg-green-50 text-green-700 border border-green-200">
                {{ session('success') }}
            </div>
        @endif

        <div class="flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Clients</h1>

            <div class="flex items-center gap-2">
                <form method="GET" class="flex items-center gap-2">
                    <input type="text" name="q" value="{{ $q }}"
                           placeholder="Search name/mobile/gstin/pan..."
                           class="border rounded px-3 py-2 text-sm w-64" />
                    <button class="px-3 py-2 text-sm rounded bg-gray-100 hover:bg-gray-200">Search</button>
                    @if($q !== '')
                        <a href="{{ route('clients.index') }}" class="text-sm text-gray-600 hover:underline">Clear</a>
                    @endif
                </form>

                <a href="{{ route('clients.create') }}"
                   class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                    + New Client
                </a>
            </div>
        </div>

        <div class="overflow-auto rounded-xl border border-gray-200 dark:border-gray-700">
            <table class="min-w-full text-sm text-left text-gray-700 dark:text-gray-300">
                <thead class="bg-gray-100 dark:bg-neutral-800 text-xs uppercase font-medium tracking-wider">
                <tr>
                    <th class="px-6 py-3">Name</th>
                    <th class="px-6 py-3">Mobile</th>
                    <th class="px-6 py-3">GSTIN</th>
                    <th class="px-6 py-3">PAN</th>
                    <th class="px-6 py-3">State</th>
                    <th class="px-6 py-3">Address</th>
                    <th class="px-6 py-3">Actions</th>
                </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-neutral-900 dark:divide-neutral-700">
                @forelse ($clients as $c)
                    <tr>
                        <td class="px-6 py-3 font-medium text-gray-900 dark:text-white">{{ $c->name }}</td>
                        <td class="px-6 py-3">{{ $c->mobile }}</td>
                        <td class="px-6 py-3">{{ $c->gstin ?? '—' }}</td>
                        <td class="px-6 py-3">{{ $c->pan ?? '—' }}</td>
                        <td class="px-6 py-3">{{ $c->state ?? '—' }}</td>
                        <td class="px-6 py-3">{{ $c->address ? Str::limit($c->address, 60) : '—' }}</td>
                        <td class="px-6 py-3 space-x-2">
                            <a href="{{ route('clients.edit', $c->id) }}" class="text-yellow-600 hover:underline">Edit</a>
                            <form action="{{ route('clients.destroy', $c->id) }}" method="POST" class="inline-block"
                                  onsubmit="return confirm('Delete this client?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-6 py-4 text-center text-gray-500">No clients found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $clients->links() }}
        </div>
    </div>
</x-layouts.app>
