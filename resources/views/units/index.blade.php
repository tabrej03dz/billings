<x-layouts.app :title="__('Units')">

    {{-- Alpine JS --}}
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    <div
        x-data="unitForm()"
        class="flex flex-col gap-6 bg-[#F3F4F6] dark:bg-[#1A1D23]"
    >

        {{-- Header --}}
        <div class="flex items-center justify-between bg-[#BFE0E0] dark:bg-[#354A54] p-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">
                    Units
                </h1>

                <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                    Create and manage item measurement units.
                </p>
            </div>

            @can('create unit')
                <button
                    type="button"
                    @click="openForm()"
                    class="inline-flex items-center rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white shadow hover:bg-green-700"
                >
                    + New Unit
                </button>
            @endcan
        </div>

        {{-- Success Message --}}
        <div
            x-cloak
            x-show="message"
            x-transition
            class="mx-4 rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-700 dark:border-green-800 dark:bg-green-900/30 dark:text-green-300"
        >
            <span x-text="message"></span>
        </div>

        {{-- Error Message --}}
        <div
            x-cloak
            x-show="errorMessage"
            x-transition
            class="mx-4 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/30 dark:text-red-300"
        >
            <span x-text="errorMessage"></span>
        </div>

        {{-- Inline Create/Edit Form --}}
        <div
            x-cloak
            x-show="showForm"
            x-transition
            class="mx-4 rounded-xl border border-gray-200 bg-white p-5 shadow dark:border-gray-700 dark:bg-[#242833]"
        >
            <div class="mb-4 flex items-center justify-between">
                <h2
                    class="text-lg font-semibold text-gray-800 dark:text-white"
                    x-text="form.id ? 'Edit Unit' : 'Create Unit'"
                ></h2>

                <button
                    type="button"
                    @click="resetForm()"
                    class="text-2xl leading-none text-gray-500 hover:text-red-600 dark:text-gray-300"
                >
                    &times;
                </button>
            </div>

            <form @submit.prevent="submitForm">

                <div class="grid gap-4 sm:grid-cols-2">

                    {{-- Unit Name --}}
                    <div class="sm:col-span-2">
                        <label
                            for="unit_name"
                            class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >
                            Unit Name
                            <span class="text-red-600">*</span>
                        </label>

                        <input
                            id="unit_name"
                            x-model="form.name"
                            type="text"
                            maxlength="255"
                            placeholder="Example: Piece, Kilogram, Litre"
                            class="w-full rounded-lg border border-gray-300 bg-slate-100 p-3 text-gray-800 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-[#1A1D23] dark:text-white"
                        >

                        <template x-if="errors.name">
                            <p
                                class="mt-1 text-sm text-red-600"
                                x-text="errors.name[0]"
                            ></p>
                        </template>
                    </div>

                    {{-- Description --}}
                    <div class="sm:col-span-2">
                        <label
                            for="unit_description"
                            class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >
                            Description
                        </label>

                        <textarea
                            id="unit_description"
                            x-model="form.description"
                            rows="4"
                            maxlength="2000"
                            placeholder="Enter unit description"
                            class="w-full resize-y rounded-lg border border-gray-300 bg-slate-100 p-3 text-gray-800 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-[#1A1D23] dark:text-white"
                        ></textarea>

                        <template x-if="errors.description">
                            <p
                                class="mt-1 text-sm text-red-600"
                                x-text="errors.description[0]"
                            ></p>
                        </template>
                    </div>

                </div>

                <div class="mt-5 flex justify-end gap-3">
                    <button
                        type="button"
                        @click="resetForm()"
                        :disabled="loading"
                        class="rounded-lg bg-gray-200 px-4 py-2 text-gray-800 hover:bg-gray-300 disabled:cursor-not-allowed disabled:opacity-60 dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600"
                    >
                        Cancel
                    </button>

                    <button
                        type="submit"
                        :disabled="loading"
                        class="inline-flex items-center rounded-lg bg-green-600 px-4 py-2 text-white hover:bg-green-700 disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        <svg
                            x-cloak
                            x-show="loading"
                            class="mr-2 h-4 w-4 animate-spin"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                        >
                            <circle
                                class="opacity-25"
                                cx="12"
                                cy="12"
                                r="10"
                                stroke="currentColor"
                                stroke-width="4"
                            ></circle>

                            <path
                                class="opacity-75"
                                fill="currentColor"
                                d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"
                            ></path>
                        </svg>

                        <span
                            x-text="
                                loading
                                    ? 'Saving...'
                                    : (form.id ? 'Update Unit' : 'Create Unit')
                            "
                        ></span>
                    </button>
                </div>

            </form>
        </div>

        {{-- Search --}}
        <div class="mx-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="w-full sm:max-w-md">
                <input
                    x-model.debounce.300ms="search"
                    type="search"
                    placeholder="Search unit..."
                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-800 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-[#242833] dark:text-white"
                >
            </div>

            <div class="text-sm text-gray-600 dark:text-gray-300">
                Total Units:
                <span class="font-semibold" x-text="filteredUnits.length"></span>
            </div>
        </div>

        {{-- Units Table --}}
        <div class="mx-4 mb-6 overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-[#242833]">

            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm text-gray-700 dark:text-gray-300">

                    <thead class="bg-[#BFE0E0] text-xs font-medium uppercase tracking-wider dark:bg-[#354A54]">
                        <tr>
                            <th class="whitespace-nowrap px-6 py-4">#</th>
                            <th class="whitespace-nowrap px-6 py-4">Unit Name</th>
                            <th class="min-w-[300px] px-6 py-4">Description</th>
                            <th class="whitespace-nowrap px-6 py-4 text-center">
                                Actions
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">

                        <template
                            x-for="(unit, index) in filteredUnits"
                            :key="unit.id"
                        >
                            <tr class="hover:bg-gray-50 dark:hover:bg-[#2D323E]">

                                <td
                                    class="whitespace-nowrap px-6 py-4"
                                    x-text="index + 1"
                                ></td>

                                <td class="whitespace-nowrap px-6 py-4">
                                    <span
                                        class="font-semibold text-gray-900 dark:text-white"
                                        x-text="unit.name"
                                    ></span>
                                </td>

                                <td class="px-6 py-4">
                                    <span
                                        class="line-clamp-2"
                                        x-text="unit.description || '—'"
                                    ></span>
                                </td>

                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="flex items-center justify-center gap-2">

                                        @can('edit unit')
                                            <button
                                                type="button"
                                                @click="editUnit(unit)"
                                                class="rounded-lg bg-yellow-500 px-3 py-2 text-xs font-medium text-white hover:bg-yellow-600"
                                            >
                                                Edit
                                            </button>
                                        @endcan

                                        @can('delete unit')
                                            <button
                                                type="button"
                                                @click="deleteUnit(unit.id)"
                                                :disabled="deletingId === unit.id"
                                                class="rounded-lg bg-red-600 px-3 py-2 text-xs font-medium text-white hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-60"
                                            >
                                                <span
                                                    x-text="
                                                        deletingId === unit.id
                                                            ? 'Deleting...'
                                                            : 'Delete'
                                                    "
                                                ></span>
                                            </button>
                                        @endcan

                                    </div>
                                </td>

                            </tr>
                        </template>

                        {{-- Empty Data --}}
                        <tr x-cloak x-show="filteredUnits.length === 0">
                            <td
                                colspan="4"
                                class="px-6 py-10 text-center text-gray-500 dark:text-gray-400"
                            >
                                No units found.
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>

        </div>

        {{-- Laravel Pagination --}}
        @if($units instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="mx-4 mb-6">
                {{ $units->links() }}
            </div>
        @endif

    </div>

    <script>
        function unitForm() {
            return {
                showForm: false,
                loading: false,
                deletingId: null,
                search: '',
                message: '',
                errorMessage: '',
                errors: {},

                units: @json(
                    $units instanceof \Illuminate\Pagination\LengthAwarePaginator
                        ? $units->items()
                        : $units
                ),

                form: {
                    id: null,
                    name: '',
                    description: '',
                },

                get filteredUnits() {
                    const keyword = this.search.trim().toLowerCase();

                    if (!keyword) {
                        return this.units;
                    }

                    return this.units.filter(unit => {
                        const name = String(unit.name ?? '').toLowerCase();
                        const description = String(unit.description ?? '').toLowerCase();

                        return name.includes(keyword) ||
                            description.includes(keyword);
                    });
                },

                openForm() {
                    this.clearMessages();

                    this.form = {
                        id: null,
                        name: '',
                        description: '',
                    };

                    this.showForm = true;

                    this.$nextTick(() => {
                        document.getElementById('unit_name')?.focus();
                    });
                },

                editUnit(unit) {
                    this.clearMessages();

                    this.form = {
                        id: unit.id,
                        name: unit.name ?? '',
                        description: unit.description ?? '',
                    };

                    this.showForm = true;

                    this.$nextTick(() => {
                        document.getElementById('unit_name')?.focus();
                        window.scrollTo({
                            top: 0,
                            behavior: 'smooth',
                        });
                    });
                },

                resetForm() {
                    this.form = {
                        id: null,
                        name: '',
                        description: '',
                    };

                    this.errors = {};
                    this.errorMessage = '';
                    this.showForm = false;
                },

                clearMessages() {
                    this.message = '';
                    this.errorMessage = '';
                    this.errors = {};
                },

                showSuccessMessage(message) {
                    this.message = message;

                    setTimeout(() => {
                        this.message = '';
                    }, 3000);
                },

                async submitForm() {
                    if (this.loading) {
                        return;
                    }

                    this.clearMessages();

                    if (!this.form.name.trim()) {
                        this.errors = {
                            name: ['Unit name is required.'],
                        };

                        return;
                    }

                    this.loading = true;

                    const isEdit = Boolean(this.form.id);

                    const url = isEdit
                        ? "{{ url('/units') }}/" + this.form.id
                        : "{{ route('units.store') }}";

                    const method = isEdit ? 'PUT' : 'POST';

                    const payload = {
                        name: this.form.name.trim(),
                        description: this.form.description
                            ? this.form.description.trim()
                            : null,
                    };

                    try {
                        const response = await fetch(url, {
                            method: method,
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            },
                            body: JSON.stringify(payload),
                        });

                        let data = {};

                        try {
                            data = await response.json();
                        } catch (error) {
                            data = {};
                        }

                        if (!response.ok || !data.success) {
                            if (response.status === 422) {
                                this.errors = data.errors ?? {};
                            }

                            this.errorMessage =
                                data.message ??
                                'Unable to save unit. Please try again.';

                            return;
                        }

                        if (isEdit) {
                            const unitIndex = this.units.findIndex(
                                unit => Number(unit.id) === Number(this.form.id)
                            );

                            if (unitIndex !== -1) {
                                this.units.splice(unitIndex, 1, data.unit);
                            }
                        } else {
                            this.units.unshift(data.unit);
                        }

                        this.resetForm();
                        this.showSuccessMessage(
                            data.message ?? 'Unit saved successfully.'
                        );
                    } catch (error) {
                        console.error(error);

                        this.errorMessage =
                            'Something went wrong. Please check your connection.';
                    } finally {
                        this.loading = false;
                    }
                },

                async deleteUnit(id) {
                    if (this.deletingId) {
                        return;
                    }

                    const confirmed = confirm(
                        'Are you sure you want to delete this unit?'
                    );

                    if (!confirmed) {
                        return;
                    }

                    this.clearMessages();
                    this.deletingId = id;

                    try {
                        const response = await fetch(
                            "{{ url('/units') }}/" + id,
                            {
                                method: 'DELETE',
                                headers: {
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                },
                            }
                        );

                        let data = {};

                        try {
                            data = await response.json();
                        } catch (error) {
                            data = {};
                        }

                        if (!response.ok || !data.success) {
                            this.errorMessage =
                                data.message ??
                                'Unit could not be deleted.';

                            return;
                        }

                        this.units = this.units.filter(
                            unit => Number(unit.id) !== Number(id)
                        );

                        if (Number(this.form.id) === Number(id)) {
                            this.resetForm();
                        }

                        this.showSuccessMessage(
                            data.message ?? 'Unit deleted successfully.'
                        );
                    } catch (error) {
                        console.error(error);

                        this.errorMessage =
                            'Something went wrong while deleting the unit.';
                    } finally {
                        this.deletingId = null;
                    }
                },
            };
        }
    </script>

</x-layouts.app>