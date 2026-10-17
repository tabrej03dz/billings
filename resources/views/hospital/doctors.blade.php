<x-layouts.app :title="__('Doctors')">
<div x-data="{ edit:null }" class="min-h-screen bg-slate-50 p-3 sm:p-5 dark:bg-neutral-950">
    @include('hospital.partials.alerts')
    @include('hospital.partials.nav')

    <div class="grid gap-5 xl:grid-cols-3">
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
            <h1 class="text-lg font-black text-slate-900 dark:text-white">Add Doctor</h1>

            <form method="POST" action="{{ route('hospital.doctors.store') }}" class="mt-4 grid gap-3">
                @csrf
                <input name="name" required placeholder="Doctor name *" class="rounded-lg border px-3 py-2">
                <div class="grid grid-cols-2 gap-2">
                    <input name="doctor_code" placeholder="Doctor code" class="rounded-lg border px-3 py-2">
                    <input name="mobile" placeholder="Mobile" class="rounded-lg border px-3 py-2">
                </div>
                <input name="email" type="email" placeholder="Email" class="rounded-lg border px-3 py-2">
                <div class="grid grid-cols-2 gap-2">
                    <input name="qualification" placeholder="Qualification" class="rounded-lg border px-3 py-2">
                    <input name="specialization" placeholder="Specialization" class="rounded-lg border px-3 py-2">
                </div>
                <input name="registration_number" placeholder="Medical registration no." class="rounded-lg border px-3 py-2">
                <input name="consultation_fee" type="number" step="0.01" min="0" placeholder="Consultation fee" class="rounded-lg border px-3 py-2">
                <label class="flex items-center gap-2 text-sm dark:text-white">
                    <input type="checkbox" name="is_active" value="1" checked> Active
                </label>
                <button class="rounded-xl bg-cyan-700 px-4 py-2 font-bold text-white">Save Doctor</button>
            </form>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm xl:col-span-2 dark:border-neutral-800 dark:bg-neutral-900">
            <div class="flex items-center justify-between border-b p-4 dark:border-neutral-800">
                <h2 class="font-black dark:text-white">Doctors</h2>
                <form method="GET">
                    <input name="search" value="{{ request('search') }}" placeholder="Search doctor..."
                        class="rounded-lg border px-3 py-2 text-xs">
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead class="bg-cyan-800 text-white">
                        <tr>
                            <th class="p-3 text-left">Doctor</th>
                            <th class="p-3 text-left">Specialization</th>
                            <th class="p-3">Fee</th>
                            <th class="p-3">Status</th>
                            <th class="p-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($doctors as $doctor)
                            <tr class="border-t dark:border-neutral-800">
                                <td class="p-3">
                                    <div class="font-bold dark:text-white">{{ $doctor->name }}</div>
                                    <div class="text-slate-500">{{ $doctor->mobile }}</div>
                                </td>
                                <td class="p-3">{{ $doctor->specialization ?: '-' }}</td>
                                <td class="p-3 text-center">₹{{ number_format($doctor->consultation_fee,2) }}</td>
                                <td class="p-3 text-center">{{ $doctor->is_active ? 'Active' : 'Inactive' }}</td>
                                <td class="p-3">
                                    <div class="flex justify-center gap-2">
                                        <button type="button" @click="edit={{ Js::from($doctor) }}" class="font-bold text-cyan-700">Edit</button>
                                        <form method="POST" action="{{ route('hospital.doctors.destroy',$doctor) }}" onsubmit="return confirm('Delete doctor?')">
                                            @csrf @method('DELETE')
                                            <button class="font-bold text-red-600">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="p-10 text-center text-slate-500">No doctors found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4">{{ $doctors->links() }}</div>
        </div>
    </div>

    <div x-show="edit" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
        <div @click.outside="edit=null" class="w-full max-w-xl rounded-2xl bg-white p-5 dark:bg-neutral-900">
            <h3 class="text-lg font-black dark:text-white">Edit Doctor</h3>

            <form :action="'{{ url('/hospital/doctors') }}/'+edit.id" method="POST" class="mt-4 grid gap-3">
                @csrf @method('PUT')
                <input name="name" x-model="edit.name" required class="rounded-lg border px-3 py-2">
                <div class="grid grid-cols-2 gap-2">
                    <input name="doctor_code" x-model="edit.doctor_code" class="rounded-lg border px-3 py-2">
                    <input name="mobile" x-model="edit.mobile" class="rounded-lg border px-3 py-2">
                </div>
                <input name="email" x-model="edit.email" type="email" class="rounded-lg border px-3 py-2">
                <div class="grid grid-cols-2 gap-2">
                    <input name="qualification" x-model="edit.qualification" class="rounded-lg border px-3 py-2">
                    <input name="specialization" x-model="edit.specialization" class="rounded-lg border px-3 py-2">
                </div>
                <input name="registration_number" x-model="edit.registration_number" class="rounded-lg border px-3 py-2">
                <input name="consultation_fee" type="number" step="0.01" x-model="edit.consultation_fee" class="rounded-lg border px-3 py-2">
                <label class="flex gap-2 dark:text-white">
                    <input type="checkbox" name="is_active" value="1" x-model="edit.is_active"> Active
                </label>
                <div class="flex justify-end gap-2">
                    <button type="button" @click="edit=null" class="rounded-lg border px-4 py-2">Cancel</button>
                    <button class="rounded-lg bg-cyan-700 px-4 py-2 font-bold text-white">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
</x-layouts.app>