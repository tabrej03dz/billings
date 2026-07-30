<x-layouts.app :title="__('Users')">
    <div class="flex flex-col gap-4">

        @if(session('success'))
            <div class="p-3 rounded-lg bg-green-50 text-green-700 border border-green-200">
                {{ session('success') }}
            </div>
        @endif

        <div class="flex items-center justify-between mb-2  bg-[#BFE0E0] dark:bg-[#354A54] p-6">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Users</h1>
            <a href="{{ route('users.create') }}"
               class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700">
                + New User
            </a>
            @if($show === 'deleted')
                <a href="{{ route('users.index') }}"
                   class="px-3 py-2 rounded bg-green-600 text-white">
                    ← Back to Active
                </a>
            @else
                <a href="{{ route('users.index', ['show' => 'deleted']) }}"
                   class="px-3 py-2 rounded bg-red-600 text-white">
                    Deleted
                </a>
            @endif

        </div>

        <div class="bg-white dark:bg-neutral-900 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
            <form method="GET" class="flex flex-wrap gap-3 items-end">

                <div>
                    <label class="block text-sm font-medium mb-1">Search</label>
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Name, Email or Phone"
                        class="border rounded px-3 py-2 w-64 dark:bg-neutral-800"
                    >
                </div>

                @if(auth()->user()->hasRole('super admin') || auth()->user()->can('view all users'))
                    <div>
                        <label class="block text-sm font-medium mb-1">Business</label>
                        <select
                            name="business_id"
                            class="border rounded px-3 py-2 w-64 dark:bg-neutral-800"
                        >
                            <option value="">All Businesses</option>

                            @foreach($allBusinesses as $business)
                                <option
                                    value="{{ $business->id }}"
                                    @selected(request('business_id') == $business->id)
                                >
                                    {{ $business->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <input type="hidden" name="show" value="{{ request('show') }}">

                <button
                    type="submit"
                    class="px-4 py-2 bg-blue-600 text-white rounded"
                >
                    Filter
                </button>

                <a
                    href="{{ route('users.index', request('show') ? ['show' => request('show')] : []) }}"
                    class="px-4 py-2 bg-gray-500 text-white rounded"
                >
                    Reset
                </a>

            </form>
        </div>

        <div class="overflow-auto rounded-xl border border-gray-200 dark:border-gray-700">
            <table class="min-w-full text-sm text-left text-gray-700 dark:text-gray-300">
                <thead class=" bg-[#BFE0E0] dark:bg-[#354A54] text-xs uppercase font-medium tracking-wider">
                <tr>
                    <th class="px-6 py-3">Name</th>
                    <th class="px-6 py-3">Email</th>
                    <th class="px-6 py-3">Businesses</th>
                    <th class="px-6 py-3">Actions</th>
                </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-neutral-900 dark:divide-neutral-700">
                @forelse ($users as $u)
                    <tr>
                        <td class="px-6 py-3 font-medium text-gray-900 dark:text-white">{{ $u->name }}</td>
                        <td class="px-6 py-3">{{ $u->email }}</td>
                        <td class="px-6 py-3">{{ $u->businesses_count }}</td>
                        {{-- <td class="px-6 py-3 space-x-2">

                            @if(!$u->trashed())
                                <a href="{{ route('users.edit', $u->id) }}" class="bg-yellow-600 p-2 text-white">Edit</a>

                                <form action="{{ route('users.destroy', $u->id) }}" method="POST" class="inline-block"
                                      onsubmit="return confirm('Delete this user?');">
                                    @csrf @method('DELETE')
                                    <button class="bg-red-600 p-2 text-white">Delete</button>
                                </form>

                            @else
                                <form action="{{ route('users.restore', $u->id) }}" method="POST" class="inline-block">
                                    @csrf
                                    <button class="bg-green-600 p-2 text-white">Restore</button>
                                </form>

                                <form action="{{ route('users.force', $u->id) }}" method="POST" class="inline-block"
                                      onsubmit="return confirm('Permanently delete?');">
                                    @csrf @method('DELETE')
                                    <button class="bg-black p-2 text-white">Force Delete</button>
                                </form>
                            @endif

                            <a href="{{ route('users.permissions', $u->id) }}" class="bg-sky-600 p-2 text-white">Permissions</a>
                        </td> --}}


                        <td class="px-6 py-3 space-x-2">

                            @if(!$u->trashed())
                                <a href="{{ route('users.edit', $u->id) }}" class="bg-yellow-600 p-2 text-white">Edit</a>

                                @if(auth()->id() !== $u->id && (auth()->user()->hasRole('super admin') || auth()->user()->can('view all users')))
                                    @if(!$u->hasRole('super admin'))
                                        <form action="{{ route('users.impersonate', $u->id) }}" method="POST" class="inline-block"
                                            onsubmit="return confirm('Login as {{ $u->name }}?');">
                                            @csrf
                                            <button class="bg-purple-600 p-2 text-white">
                                                Login As
                                            </button>
                                        </form>
                                    @endif
                                @endif

                                <form action="{{ route('users.destroy', $u->id) }}" method="POST" class="inline-block"
                                    onsubmit="return confirm('Delete this user?');">
                                    @csrf 
                                    @method('DELETE')
                                    <button class="bg-red-600 p-2 text-white">Delete</button>
                                </form>

                            @else
                                <form action="{{ route('users.restore', $u->id) }}" method="POST" class="inline-block">
                                    @csrf
                                    <button class="bg-green-600 p-2 text-white">Restore</button>
                                </form>

                                <form action="{{ route('users.force', $u->id) }}" method="POST" class="inline-block"
                                    onsubmit="return confirm('Permanently delete?');">
                                    @csrf 
                                    @method('DELETE')
                                    <button class="bg-black p-2 text-white">Force Delete</button>
                                </form>
                            @endif

                            <a href="{{ route('users.permissions', $u->id) }}" class="bg-sky-600 p-2 text-white">Permissions</a>
                        </td>

                    </tr>
                @empty
                    <tr><td colspan="4" class="px-6 py-4 text-center text-gray-500">No users found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $users->withQueryString()->links() }}
        </div>
    </div>
</x-layouts.app>
