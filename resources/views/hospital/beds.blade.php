<x-layouts.app :title="__('Beds')">
<div x-data="{edit:null}" class="min-h-screen bg-slate-50 p-3 sm:p-5 dark:bg-neutral-950">
@include('hospital.partials.alerts')
@include('hospital.partials.nav')

<div class="grid gap-5 xl:grid-cols-3">
    <div class="rounded-2xl border bg-white p-4 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
        <h1 class="font-black dark:text-white">Add Bed</h1>
        <form method="POST" action="{{ route('hospital.beds.store') }}" class="mt-4 grid gap-3">
            @csrf
            <select name="room_id" required class="rounded-lg border px-3 py-2">
                <option value="">Select Room *</option>
                @foreach($rooms as $room)
                    <option value="{{ $room->id }}">{{ $room->ward?->name }} / {{ $room->room_number }}</option>
                @endforeach
            </select>
            <input name="bed_number" required placeholder="Bed number *" class="rounded-lg border px-3 py-2">
            <input name="daily_charge" type="number" step=".01" min="0" placeholder="Daily charge" class="rounded-lg border px-3 py-2">
            <select name="status" class="rounded-lg border px-3 py-2">
                @foreach(['available','occupied','reserved','maintenance'] as $status)
                    <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                @endforeach
            </select>
            <label class="dark:text-white"><input type="checkbox" name="is_active" value="1" checked> Active</label>
            <button class="rounded-lg bg-cyan-700 py-2 font-bold text-white">Save Bed</button>
        </form>
    </div>

    <div class="overflow-hidden rounded-2xl border bg-white shadow-sm xl:col-span-2 dark:border-neutral-800 dark:bg-neutral-900">
        <div class="border-b p-3 dark:border-neutral-800">
            <form method="GET">
                <select name="status" onchange="this.form.submit()" class="rounded-lg border px-3 py-2">
                    <option value="">All status</option>
                    @foreach(['available','occupied','reserved','maintenance'] as $status)
                        <option value="{{ $status }}" @selected(request('status')===$status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        <table class="w-full text-xs">
            <thead class="bg-cyan-800 text-white">
                <tr><th class="p-3 text-left">Bed</th><th class="p-3">Ward / Room</th><th class="p-3">Charge</th><th class="p-3">Status</th><th class="p-3">Action</th></tr>
            </thead>
            <tbody>
            @forelse($beds as $bed)
                <tr class="border-t dark:border-neutral-800">
                    <td class="p-3 font-bold dark:text-white">{{ $bed->bed_number }}</td>
                    <td class="p-3 text-center">{{ $bed->room?->ward?->name }} / {{ $bed->room?->room_number }}</td>
                    <td class="p-3 text-center">₹{{ number_format($bed->daily_charge,2) }}</td>
                    <td class="p-3 text-center">
                        <span class="rounded-full px-2 py-1 font-bold
                            {{ $bed->status==='available'
                                ? 'bg-emerald-50 text-emerald-700'
                                : ($bed->status==='occupied'
                                    ? 'bg-red-50 text-red-700'
                                    : 'bg-amber-50 text-amber-700') }}">
                            {{ ucfirst($bed->status) }}
                        </span>
                    </td>
                    <td class="p-3 text-center">
                        <button @click="edit={{ Js::from($bed) }}" class="font-bold text-cyan-700">Edit</button>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="p-10 text-center">No beds found.</td></tr>
            @endforelse
            </tbody>
        </table>
        <div class="p-4">{{ $beds->links() }}</div>
    </div>
</div>

<div x-show="edit" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
    <form :action="'{{ url('/hospital/beds') }}/'+edit.id" method="POST" class="w-full max-w-lg rounded-2xl bg-white p-5 dark:bg-neutral-900">
        @csrf @method('PUT')
        <h3 class="font-black dark:text-white">Edit Bed</h3>
        <div class="mt-4 grid gap-3">
            <select name="room_id" x-model="edit.room_id" class="rounded-lg border px-3 py-2">
                @foreach($rooms as $room)
                    <option value="{{ $room->id }}">{{ $room->ward?->name }} / {{ $room->room_number }}</option>
                @endforeach
            </select>
            <input name="bed_number" x-model="edit.bed_number" class="rounded-lg border px-3 py-2">
            <input name="daily_charge" x-model="edit.daily_charge" class="rounded-lg border px-3 py-2">
            <select name="status" x-model="edit.status" class="rounded-lg border px-3 py-2">
                @foreach(['available','occupied','reserved','maintenance'] as $status)
                    <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                @endforeach
            </select>
            <label class="dark:text-white"><input type="checkbox" name="is_active" value="1" x-model="edit.is_active"> Active</label>
            <div class="flex justify-end gap-2">
                <button type="button" @click="edit=null" class="rounded-lg border px-4 py-2">Cancel</button>
                <button class="rounded-lg bg-cyan-700 px-4 py-2 text-white">Update</button>
            </div>
        </div>
    </form>
</div>
</div>
</x-layouts.app>