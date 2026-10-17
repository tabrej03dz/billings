<x-layouts.app :title="__('Departments')">
<div x-data="{edit:null}" class="min-h-screen bg-slate-50 p-3 sm:p-5 dark:bg-neutral-950">
@include('hospital.partials.alerts')
@include('hospital.partials.nav')

<div class="grid gap-5 xl:grid-cols-3">
    <div class="rounded-2xl border bg-white p-4 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
        <h1 class="font-black dark:text-white">Add Department</h1>
        <form method="POST" action="{{ route('hospital.departments.store') }}" class="mt-4 grid gap-3">
            @csrf
            <input name="name" required placeholder="Department name *" class="rounded-lg border px-3 py-2">
            <input name="code" placeholder="Code" class="rounded-lg border px-3 py-2">
            <textarea name="description" placeholder="Description" class="rounded-lg border px-3 py-2"></textarea>
            <label class="dark:text-white"><input type="checkbox" name="is_active" value="1" checked> Active</label>
            <button class="rounded-lg bg-cyan-700 py-2 font-bold text-white">Save Department</button>
        </form>
    </div>

    <div class="overflow-hidden rounded-2xl border bg-white shadow-sm xl:col-span-2 dark:border-neutral-800 dark:bg-neutral-900">
        <table class="w-full text-xs">
            <thead class="bg-cyan-800 text-white">
                <tr><th class="p-3 text-left">Department</th><th class="p-3">Doctors</th><th class="p-3">Status</th><th class="p-3">Action</th></tr>
            </thead>
            <tbody>
            @forelse($departments as $department)
                <tr class="border-t dark:border-neutral-800">
                    <td class="p-3"><b class="dark:text-white">{{ $department->name }}</b><div class="text-slate-500">{{ $department->code }}</div></td>
                    <td class="p-3 text-center">{{ $department->doctors_count }}</td>
                    <td class="p-3 text-center">{{ $department->is_active ? 'Active' : 'Inactive' }}</td>
                    <td class="p-3">
                        <div class="flex justify-center gap-2">
                            <button @click="edit={{ Js::from($department) }}" class="font-bold text-cyan-700">Edit</button>
                            <form method="POST" action="{{ route('hospital.departments.destroy',$department) }}" onsubmit="return confirm('Delete department?')">
                                @csrf @method('DELETE')
                                <button class="font-bold text-red-600">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="p-10 text-center">No departments found.</td></tr>
            @endforelse
            </tbody>
        </table>
        <div class="p-4">{{ $departments->links() }}</div>
    </div>
</div>

<div x-show="edit" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
    <form :action="'{{ url('/hospital/departments') }}/'+edit.id" method="POST" class="w-full max-w-lg rounded-2xl bg-white p-5 dark:bg-neutral-900">
        @csrf @method('PUT')
        <h3 class="font-black dark:text-white">Edit Department</h3>
        <div class="mt-4 grid gap-3">
            <input name="name" x-model="edit.name" class="rounded-lg border px-3 py-2">
            <input name="code" x-model="edit.code" class="rounded-lg border px-3 py-2">
            <textarea name="description" x-model="edit.description" class="rounded-lg border px-3 py-2"></textarea>
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