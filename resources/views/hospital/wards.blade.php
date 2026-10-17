<x-layouts.app :title="__('Wards')">
<div x-data="{edit:null}" class="min-h-screen bg-slate-50 p-3 sm:p-5 dark:bg-neutral-950">
@include('hospital.partials.alerts')
@include('hospital.partials.nav')

<div class="grid gap-5 xl:grid-cols-3">
    <div class="rounded-2xl border bg-white p-4 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
        <h1 class="font-black dark:text-white">Add Ward</h1>
        <form method="POST" action="{{ route('hospital.wards.store') }}" class="mt-4 grid gap-3">
            @csrf
            <input name="name" required placeholder="Ward name *" class="rounded-lg border px-3 py-2">
            <div class="grid grid-cols-2 gap-2">
                <input name="code" placeholder="Code" class="rounded-lg border px-3 py-2">
                <input name="ward_type" placeholder="General / ICU" class="rounded-lg border px-3 py-2">
            </div>
            <input name="daily_charge" type="number" step=".01" min="0" placeholder="Daily charge" class="rounded-lg border px-3 py-2">
            <label class="dark:text-white"><input type="checkbox" name="is_active" value="1" checked> Active</label>
            <button class="rounded-lg bg-cyan-700 py-2 font-bold text-white">Save Ward</button>
        </form>
    </div>

    <div class="overflow-hidden rounded-2xl border bg-white shadow-sm xl:col-span-2 dark:border-neutral-800 dark:bg-neutral-900">
        <table class="w-full text-xs">
            <thead class="bg-cyan-800 text-white">
                <tr><th class="p-3 text-left">Ward</th><th class="p-3">Rooms</th><th class="p-3">Beds</th><th class="p-3">Daily Charge</th><th class="p-3">Action</th></tr>
            </thead>
            <tbody>
            @forelse($wards as $ward)
                <tr class="border-t dark:border-neutral-800">
                    <td class="p-3"><b class="dark:text-white">{{ $ward->name }}</b><div>{{ $ward->ward_type }}</div></td>
                    <td class="p-3 text-center">{{ $ward->rooms_count }}</td>
                    <td class="p-3 text-center">{{ $ward->beds_count }}</td>
                    <td class="p-3 text-center">₹{{ number_format($ward->daily_charge,2) }}</td>
                    <td class="p-3">
                        <div class="flex justify-center gap-2">
                            <button @click="edit={{ Js::from($ward) }}" class="font-bold text-cyan-700">Edit</button>
                            <form method="POST" action="{{ route('hospital.wards.destroy',$ward) }}" onsubmit="return confirm('Delete ward?')">
                                @csrf @method('DELETE')
                                <button class="font-bold text-red-600">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="p-10 text-center">No wards found.</td></tr>
            @endforelse
            </tbody>
        </table>
        <div class="p-4">{{ $wards->links() }}</div>
    </div>
</div>

<div x-show="edit" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
    <form :action="'{{ url('/hospital/wards') }}/'+edit.id" method="POST" class="w-full max-w-lg rounded-2xl bg-white p-5 dark:bg-neutral-900">
        @csrf @method('PUT')
        <h3 class="font-black dark:text-white">Edit Ward</h3>
        <div class="mt-4 grid gap-3">
            <input name="name" x-model="edit.name" class="rounded-lg border px-3 py-2">
            <input name="code" x-model="edit.code" class="rounded-lg border px-3 py-2">
            <input name="ward_type" x-model="edit.ward_type" class="rounded-lg border px-3 py-2">
            <input name="daily_charge" type="number" x-model="edit.daily_charge" class="rounded-lg border px-3 py-2">
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