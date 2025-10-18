<x-layouts.app :title="__('Categories')">
    {{-- Alpine JS load (zaroori) --}}
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <div x-data="categoryForm()" class="flex flex-col gap-6">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Categories</h1>
            <button @click="openForm()"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow">
                + New Category
            </button>
        </div>

        {{-- Inline Form --}}
        <div x-cloak x-show="showForm" x-transition
             class="p-4 bg-white dark:bg-neutral-900 rounded-xl shadow border">
            <form @submit.prevent="submitForm">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Category Name</label>
                        <input x-model="form.name" type="text"
                               class="w-full border-gray-300 dark:border-gray-700 dark:bg-neutral-800 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                        <textarea x-model="form.description"
                                  class="w-full border-gray-300 dark:border-gray-700 dark:bg-neutral-800 rounded-lg focus:ring-blue-500 focus:border-blue-500"></textarea>
                    </div>
                    <div class="flex items-center gap-2 sm:col-span-2">
                        <input x-model="form.is_active" type="checkbox" class="rounded border-gray-300 text-blue-600">
                        <span class="text-sm text-gray-700 dark:text-gray-300">Active</span>
                    </div>
                </div>

                <div class="mt-4 flex justify-end gap-3">
                    <button type="button" @click="resetForm"
                            class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg">Cancel</button>
                    <button type="submit"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
                        <span x-text="form.id ? 'Update Category' : 'Create Category'"></span>
                    </button>
                </div>
            </form>
        </div>

        {{-- Category Table --}}
        <div class="overflow-auto rounded-xl border border-gray-200 dark:border-gray-700">
            <table class="min-w-full text-sm text-left text-gray-700 dark:text-gray-300">
                <thead class="bg-gray-100 dark:bg-neutral-800 text-xs uppercase font-medium tracking-wider">
                <tr>
                    <th class="px-6 py-3">#</th>
                    <th class="px-6 py-3">Name</th>
                    <th class="px-6 py-3">Description</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3">Actions</th>
                </tr>
                </thead>
                <tbody>
                <template x-for="(category, index) in categories" :key="category.id">
                    <tr class="border-t border-gray-200">
                        <td class="px-6 py-3" x-text="index + 1"></td>
                        <td class="px-6 py-3" x-text="category.name"></td>
                        <td class="px-6 py-3" x-text="category.description || '—'"></td>
                        <td class="px-6 py-3">
                            <span x-text="category.is_active ? 'Active' : 'Inactive'"
                                  :class="category.is_active ? 'text-green-600' : 'text-red-600'"></span>
                        </td>
                        <td class="px-6 py-3 flex gap-3">
                            <button @click="editCategory(category)" class="text-yellow-600 hover:underline">Edit</button>
                            <button @click="deleteCategory(category.id)" class="text-red-600 hover:underline">Delete</button>
                        </td>
                    </tr>
                </template>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function categoryForm() {
            return {
                showForm: false,
                // NOTE: Agar $categories paginate ho raha ho to controller se ->items() bhejo
                categories: @json(
                    $categories instanceof \Illuminate\Pagination\LengthAwarePaginator ? $categories->items() : $categories
                ),
                form: { id: null, name: '', description: '', is_active: true },

                openForm() {
                    this.resetForm();
                    this.showForm = true;
                },
                editCategory(cat) {
                    this.form = {
                        id: cat.id,
                        name: cat.name,
                        description: cat.description,
                        is_active: !!cat.is_active,
                    };
                    this.showForm = true;
                },
                resetForm() {
                    this.form = { id: null, name: '', description: '', is_active: true };
                    this.showForm = false;
                },

                async submitForm() {
                    const isEdit = !!this.form.id;

                    // Server ko boolean ke bajay 1/0 bhej do
                    const payload = { ...this.form, is_active: this.form.is_active ? 1 : 0 };

                    const url = isEdit
                        ? "{{ url('/categories') }}/" + this.form.id   // PUT /categories/{id}
                        : "{{ route('categories.store') }}";           // POST /categories

                    const method = isEdit ? 'PUT' : 'POST';

                    const res = await fetch(url, {
                        method,
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                        body: JSON.stringify(payload),
                    });

                    const data = await res.json();
                    if (!res.ok || !data.success) {
                        alert(data.message || 'Validation error');
                        return;
                    }

                    if (isEdit) {
                        const i = this.categories.findIndex(c => c.id === this.form.id);
                        if (i > -1) this.categories[i] = data.category;
                    } else {
                        this.categories.unshift(data.category);
                    }
                    this.resetForm();
                    alert(data.message || 'Saved');
                },

                async deleteCategory(id) {
                    if (!confirm('Delete?')) return;

                    const res = await fetch("{{ url('/categories') }}/" + id, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                    });

                    if (res.ok) {
                        this.categories = this.categories.filter(c => c.id !== id);
                        alert('Deleted');
                    } else {
                        alert('Delete failed');
                    }
                },
            }
        }
    </script>
</x-layouts.app>
