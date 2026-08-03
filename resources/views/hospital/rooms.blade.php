<x-layouts.app :title="__('Rooms')">
<div x-data="{edit:null}" class="min-h-screen bg-slate-50 p-3 sm:p-5 dark:bg-neutral-950">
@include('hospital.partials.alerts')
@include('hospital.partials.nav')

<div class="grid gap-5 xl:grid-cols-3">
    <div class="rounded-2xl border bg-white p-4 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
        <h1 class="font-black dark:text-white">Add Room</h1>
        <form method="POST" action="{{ route('hospital.rooms.store') }}" class="mt-4 grid gap-3">
            @csrf
            <select name="ward_id" required class="rounded-lg border px-3 py-2">
                <option value="">Select Ward *</option>
                @foreach($wards as $ward)<option value="{{ $ward->id }}">{{ $ward->name }}</option>@endforeach
            </select>
            <input name="room_number" required placeholder="Room number *" class="rounded-lg border px-3 py-2">
            <input name="room_type" placeholder="Room type" class="rounded-lg border px-3 py-2">
            <input name="daily_charge" type="number" step=".01" min="0" placeholder="Daily charge" class="rounded-lg border px-3 py-2">
            <label class="dark:text-white"><input type="checkbox" name="is_active" value="1" checked> Active</label>
            <button class="rounded-lg bg-cyan-700 py-2 font-bold text-white">Save Room</button>
        </form>
    </div>

    <div class="overflow-hidden rounded-2xl border bg-white shadow-sm xl:col-span-2 dark:border-neutral-800 dark:bg-neutral-900">
        <table class="w-full text-xs">
            <thead class="bg-cyan-800 text-white">
                <tr><th class="p-3 text-left">Room</th><th class="p-3">Ward</th><th class="p-3">Beds</th><th class="p-3">Charge</th><th class="p-3">Action</th></tr>
            </thead>
            <tbody>
            @forelse($rooms as $room)
                <tr class="border-t dark:border-neutral-800">
                    <td class="p-3"><b class="dark:text-white">{{ $room->room_number }}</b><div>{{ $room->room_type }}</div></td>
                    <td class="p-3 text-center">{{ $room->ward?->name }}</td>
                    <td class="p-3 text-center">{{ $room->beds_count }}</td>
                    <td class="p-3 text-center">₹{{ number_format($room->daily_charge,2) }}</td>
                    <td class="p-3 text-center">
                        <button @click="edit={{ Js::from($room) }}" class="font-bold text-cyan-700">Edit</button>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="p-10 text-center">No rooms found.</td></tr>
            @endforelse
            </tbody>
        </table>
        <div class="p-4">{{ $rooms->links() }}</div>
    </div>
</div>

<div x-show="edit" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
    <form :action="'{{ url('/hospital/rooms') }}/'+edit.id" method="POST" class="w-full max-w-lg rounded-2xl bg-white p-5 dark:bg-neutral-900">
        @csrf @method('PUT')
        <h3 class="font-black dark:text-white">Edit Room</h3>
        <div class="mt-4 grid gap-3">
            <select name="ward_id" x-model="edit.ward_id" class="rounded-lg border px-3 py-2">
                @foreach($wards as $ward)<option value="{{ $ward->id }}">{{ $ward->name }}</option>@endforeach
            </select>
            <input name="room_number" x-model="edit.room_number" class="rounded-lg border px-3 py-2">
            <input name="room_type" x-model="edit.room_type" class="rounded-lg border px-3 py-2">
            <input name="daily_charge" x-model="edit.daily_charge" class="rounded-lg border px-3 py-2">
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