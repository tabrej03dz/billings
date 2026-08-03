<x-layouts.app :title="__('Patients')">
<div x-data="{ createOpen:false, edit:null }" class="min-h-screen bg-slate-50 p-3 sm:p-5 dark:bg-neutral-950">
    @include('hospital.partials.alerts')
    @include('hospital.partials.nav')

    <div class="mb-4 flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between dark:border-neutral-800 dark:bg-neutral-900">
        <div>
            <h1 class="text-xl font-black text-slate-900 dark:text-white">Patient Registry</h1>
            <p class="mt-1 text-xs text-slate-500 dark:text-neutral-400">UHID, personal details, guardian, insurance aur medical history manage karein.</p>
        </div>

        <div class="flex flex-wrap gap-2">
            <form method="GET" class="flex gap-2">
                <input name="search" value="{{ request('search') }}" placeholder="Name, mobile, UHID..."
                    class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-800 dark:text-white">
                <button class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-bold dark:border-neutral-700 dark:text-white">Search</button>
            </form>

            <button type="button" @click="createOpen=true"
                class="rounded-xl bg-cyan-700 px-4 py-2 text-sm font-bold text-white shadow hover:bg-cyan-800">
                + Register Patient
            </button>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-cyan-800 text-white">
                    <tr>
                        <th class="p-3">UHID</th>
                        <th class="p-3">Patient</th>
                        <th class="p-3">Age/Gender</th>
                        <th class="p-3">Blood</th>
                        <th class="p-3">Guardian</th>
                        <th class="p-3">Insurance</th>
                        <th class="p-3 text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($patients as $patient)
                        <tr class="border-t border-slate-100 dark:border-neutral-800">
                            <td class="p-3 font-bold text-cyan-700 dark:text-cyan-300">{{ $patient->patient_code }}</td>
                            <td class="p-3">
                                <div class="font-bold text-slate-900 dark:text-white">{{ $patient->client?->name ?? '-' }}</div>
                                <div class="text-slate-500">{{ $patient->client?->mobile ?? '-' }}</div>
                            </td>
                            <td class="p-3">
                                {{ $patient->age ?: '-' }} /
                                {{ ucfirst($patient->gender ?: '-') }}
                            </td>
                            <td class="p-3 font-bold">{{ $patient->blood_group ?: '-' }}</td>
                            <td class="p-3">{{ $patient->guardian_name ?: '-' }}</td>
                            <td class="p-3">{{ $patient->insurance_provider ?: '-' }}</td>
                            <td class="p-3 text-center">
                                <button type="button"
                                    @click="edit={{ Js::from(array_merge($patient->toArray(), [
                                        'name'=>$patient->client?->name,
                                        'mobile'=>$patient->client?->mobile,
                                        'email'=>$patient->client?->email,
                                        'address'=>$patient->client?->address,
                                        'state'=>$patient->client?->state,
                                        'pincode'=>$patient->client?->pincode,
                                    ])) }}"
                                    class="font-bold text-cyan-700 dark:text-cyan-300">
                                    Edit
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-10 text-center text-slate-500">No patients registered.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4">{{ $patients->links() }}</div>
    </div>

    <div x-show="createOpen || edit" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/55 p-4">
        <div class="mx-auto my-5 max-w-5xl rounded-2xl bg-white p-5 shadow-2xl dark:bg-neutral-900"
             @click.outside="createOpen=false; edit=null">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-black text-slate-900 dark:text-white"
                    x-text="edit ? 'Edit Patient' : 'Register New Patient'"></h2>
                <button type="button" @click="createOpen=false; edit=null" class="text-xl dark:text-white">×</button>
            </div>

            <form :action="edit ? '{{ url('/hospital/patients') }}/'+edit.id : '{{ route('hospital.patients.store') }}'"
                  method="POST" class="mt-4 grid gap-3 md:grid-cols-4">
                @csrf
                <template x-if="edit"><input type="hidden" name="_method" value="PUT"></template>

                <input name="name" :value="edit?.name ?? ''" required placeholder="Patient name *" class="rounded-lg border px-3 py-2 md:col-span-2">
                <input name="patient_code" :value="edit?.patient_code ?? ''" placeholder="UHID (auto if blank)" class="rounded-lg border px-3 py-2">
                <input name="mobile" :value="edit?.mobile ?? ''" placeholder="Mobile" class="rounded-lg border px-3 py-2">

                <input name="date_of_birth" type="date" :value="edit?.date_of_birth ?? ''" class="rounded-lg border px-3 py-2">
                <input name="age" type="number" min="0" max="150" :value="edit?.age ?? ''" placeholder="Age" class="rounded-lg border px-3 py-2">

                <select name="gender" class="rounded-lg border px-3 py-2">
                    <option value="">Gender</option>
                    <option value="male" :selected="edit?.gender==='male'">Male</option>
                    <option value="female" :selected="edit?.gender==='female'">Female</option>
                    <option value="other" :selected="edit?.gender==='other'">Other</option>
                </select>

                <select name="blood_group" class="rounded-lg border px-3 py-2">
                    <option value="">Blood Group</option>
                    @foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $group)
                        <option value="{{ $group }}" :selected="edit?.blood_group==='{{ $group }}'">{{ $group }}</option>
                    @endforeach
                </select>

                <input name="guardian_name" :value="edit?.guardian_name ?? ''" placeholder="Guardian name" class="rounded-lg border px-3 py-2">
                <input name="guardian_relation" :value="edit?.guardian_relation ?? ''" placeholder="Relation" class="rounded-lg border px-3 py-2">
                <input name="emergency_contact" :value="edit?.emergency_contact ?? ''" placeholder="Emergency contact" class="rounded-lg border px-3 py-2">
                <input name="abha_number" :value="edit?.abha_number ?? ''" placeholder="ABHA number" class="rounded-lg border px-3 py-2">

                <input name="email" type="email" :value="edit?.email ?? ''" placeholder="Email" class="rounded-lg border px-3 py-2 md:col-span-2">
                <input name="insurance_provider" :value="edit?.insurance_provider ?? ''" placeholder="Insurance / TPA" class="rounded-lg border px-3 py-2">
                <input name="insurance_policy_number" :value="edit?.insurance_policy_number ?? ''" placeholder="Policy no." class="rounded-lg border px-3 py-2">

                <textarea name="address" placeholder="Address" class="rounded-lg border px-3 py-2 md:col-span-2" x-text="edit?.address ?? ''"></textarea>
                <div class="grid grid-cols-2 gap-2 md:col-span-2">
                    <input name="state" :value="edit?.state ?? ''" placeholder="State" class="rounded-lg border px-3 py-2">
                    <input name="pincode" :value="edit?.pincode ?? ''" placeholder="Pincode" class="rounded-lg border px-3 py-2">
                </div>

                <textarea name="allergies" placeholder="Allergies" class="rounded-lg border px-3 py-2 md:col-span-2" x-text="edit?.allergies ?? ''"></textarea>
                <textarea name="medical_history" placeholder="Medical history" class="rounded-lg border px-3 py-2 md:col-span-2" x-text="edit?.medical_history ?? ''"></textarea>

                <div class="flex justify-end gap-2 md:col-span-4">
                    <button type="button" @click="createOpen=false; edit=null" class="rounded-lg border px-4 py-2">Cancel</button>
                    <button class="rounded-lg bg-cyan-700 px-5 py-2 font-bold text-white"
                        x-text="edit ? 'Update Patient' : 'Register Patient'"></button>
                </div>
            </form>
        </div>
    </div>
</div>
</x-layouts.app>