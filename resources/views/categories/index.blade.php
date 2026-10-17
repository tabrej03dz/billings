<x-layouts.app :title="__('Categories')">

    {{-- Alpine JS --}}
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    <div
        x-data="categoryForm()"
        class="flex flex-col gap-6 bg-[#F3F4F6] dark:bg-[#1A1D23]"
    >

        {{-- Header --}}
        <div
            class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4
                   bg-[#BFE0E0] dark:bg-[#354A54] p-5 sm:p-6 rounded-xl"
        >
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">
                    Categories
                </h1>

                <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                    Manage categories and sub categories
                </p>
            </div>

            @can('create category')
                <button
                    type="button"
                    @click="openForm()"
                    class="inline-flex items-center justify-center gap-2
                           px-4 py-2.5 text-sm font-medium text-white
                           bg-green-600 hover:bg-green-700
                           rounded-lg shadow transition"
                >
                    <span class="text-lg leading-none">+</span>
                    New Category
                </button>
            @endcan
        </div>


        {{-- Form --}}
        <div
            x-cloak
            x-show="showForm"
            x-transition
            class="bg-white dark:bg-[#20242c]
                   border border-gray-200 dark:border-gray-700
                   rounded-xl shadow-sm p-5"
        >

            <div class="flex items-center justify-between mb-5">
                <div>
                    <h2
                        class="text-lg font-semibold text-gray-900 dark:text-white"
                        x-text="form.id ? 'Edit Category' : 'Add Category'"
                    ></h2>

                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Select a parent category only when creating a sub category.
                    </p>
                </div>

                <button
                    type="button"
                    @click="resetForm()"
                    class="text-gray-400 hover:text-gray-700 dark:hover:text-white text-2xl"
                >
                    &times;
                </button>
            </div>


            <form @submit.prevent="submitForm">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                    {{-- Name --}}
                    <div>
                        <label
                            class="block text-sm font-medium
                                   text-gray-700 dark:text-gray-300 mb-2"
                        >
                            Category Name
                            <span class="text-red-500">*</span>
                        </label>

                        <input
                            x-model.trim="form.name"
                            type="text"
                            required
                            placeholder="Enter category name"
                            class="w-full rounded-lg
                                   border border-gray-300 dark:border-gray-700
                                   bg-slate-100 dark:bg-[#242833]
                                   text-gray-900 dark:text-white
                                   px-3 py-2.5
                                   focus:ring-2 focus:ring-blue-500
                                   focus:border-blue-500 outline-none"
                        >
                    </div>


                    {{-- Parent Category --}}
                    <div>
                        <label
                            class="block text-sm font-medium
                                   text-gray-700 dark:text-gray-300 mb-2"
                        >
                            Parent Category
                        </label>

                        <select
                            x-model="form.parent_id"
                            class="w-full rounded-lg
                                   border border-gray-300 dark:border-gray-700
                                   bg-slate-100 dark:bg-[#242833]
                                   text-gray-900 dark:text-white
                                   px-3 py-2.5
                                   focus:ring-2 focus:ring-blue-500
                                   focus:border-blue-500 outline-none"
                        >
                            <option value="">
                                Main Category
                            </option>

                            <template
                                x-for="parent in availableParentCategories()"
                                :key="parent.id"
                            >
                                <option
                                    :value="parent.id"
                                    x-text="parent.name"
                                ></option>
                            </template>
                        </select>

                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Leave as Main Category if this is not a sub category.
                        </p>
                    </div>


                    {{-- Description --}}
                    <div class="md:col-span-2">
                        <label
                            class="block text-sm font-medium
                                   text-gray-700 dark:text-gray-300 mb-2"
                        >
                            Description
                        </label>

                        <textarea
                            x-model="form.description"
                            rows="3"
                            placeholder="Optional description"
                            class="w-full rounded-lg
                                   border border-gray-300 dark:border-gray-700
                                   bg-slate-100 dark:bg-[#242833]
                                   text-gray-900 dark:text-white
                                   px-3 py-2.5
                                   focus:ring-2 focus:ring-blue-500
                                   focus:border-blue-500 outline-none"
                        ></textarea>
                    </div>


                    {{-- Status --}}
                    <div class="md:col-span-2">
                        <label class="inline-flex items-center gap-3 cursor-pointer">

                            <input
                                x-model="form.is_active"
                                type="checkbox"
                                class="w-4 h-4 rounded
                                       border-gray-300
                                       text-blue-600
                                       focus:ring-blue-500"
                            >

                            <span class="text-sm text-gray-700 dark:text-gray-300">
                                Active Category
                            </span>

                        </label>
                    </div>

                </div>


                {{-- Buttons --}}
                <div
                    class="mt-6 flex flex-col-reverse sm:flex-row
                           sm:justify-end gap-3"
                >
                    <button
                        type="button"
                        @click="resetForm()"
                        class="px-5 py-2.5
                               bg-gray-200 hover:bg-gray-300
                               dark:bg-gray-700 dark:hover:bg-gray-600
                               text-gray-800 dark:text-white
                               rounded-lg transition"
                    >
                        Cancel
                    </button>

                    <button
                        type="submit"
                        :disabled="saving"
                        class="px-5 py-2.5
                               bg-green-600 hover:bg-green-700
                               disabled:opacity-60 disabled:cursor-not-allowed
                               text-white rounded-lg transition
                               flex items-center justify-center gap-2"
                    >

                        <span
                            x-show="saving"
                            class="w-4 h-4 border-2 border-white
                                   border-t-transparent rounded-full animate-spin"
                        ></span>

                        <span
                            x-text="
                                saving
                                    ? 'Saving...'
                                    : (
                                        form.id
                                            ? 'Update Category'
                                            : 'Create Category'
                                    )
                            "
                        ></span>

                    </button>
                </div>

            </form>
        </div>


        {{-- Categories Table --}}
        <div
            class="bg-white dark:bg-[#20242c]
                   overflow-hidden rounded-xl
                   border border-gray-200 dark:border-gray-700
                   shadow-sm"
        >

            <div class="overflow-x-auto">

                <table
                    class="min-w-full text-sm
                           text-left text-gray-700 dark:text-gray-300"
                >

                    <thead
                        class="bg-[#BFE0E0] dark:bg-[#354A54]
                               text-xs uppercase font-semibold tracking-wider"
                    >
                        <tr>
                            <th class="px-5 py-4 w-16">
                                #
                            </th>

                            <th class="px-5 py-4">
                                Name
                            </th>

                            <th class="px-5 py-4">
                                Type
                            </th>

                            <th class="px-5 py-4">
                                Description
                            </th>

                            <th class="px-5 py-4">
                                Status
                            </th>

                            <th class="px-5 py-4 text-right">
                                Actions
                            </th>
                        </tr>
                    </thead>


                    <tbody
                        class="divide-y divide-gray-200 dark:divide-gray-700"
                    >

                        {{-- Empty State --}}
                        <template x-if="flattenedCategories().length === 0">
                            <tr>
                                <td
                                    colspan="6"
                                    class="px-5 py-10 text-center
                                           text-gray-500 dark:text-gray-400"
                                >
                                    No categories found.
                                </td>
                            </tr>
                        </template>


                        {{-- Categories --}}
                        <template
                            x-for="(item, index) in flattenedCategories()"
                            :key="item.id"
                        >

                            <tr
                                class="hover:bg-gray-50
                                       dark:hover:bg-[#282d37]
                                       transition"
                            >

                                {{-- Index --}}
                                <td
                                    class="px-5 py-4 text-gray-500"
                                    x-text="index + 1"
                                ></td>


                                {{-- Name --}}
                                <td class="px-5 py-4">

                                    <div
                                        class="flex items-center gap-2"
                                        :style="`padding-left: ${item.level * 24}px`"
                                    >

                                        {{-- Tree icon --}}
                                        <span
                                            x-show="item.level > 0"
                                            class="text-gray-400 text-lg"
                                        >
                                            ↳
                                        </span>


                                        {{-- Main Category Icon --}}
                                        <span
                                            x-show="item.level === 0"
                                            class="inline-flex items-center justify-center
                                                   w-8 h-8 rounded-lg
                                                   bg-blue-100 dark:bg-blue-900/30
                                                   text-blue-600 dark:text-blue-400"
                                        >
                                            C
                                        </span>


                                        {{-- Sub Category Icon --}}
                                        <span
                                            x-show="item.level > 0"
                                            class="inline-flex items-center justify-center
                                                   w-7 h-7 rounded-md
                                                   bg-purple-100 dark:bg-purple-900/30
                                                   text-purple-600 dark:text-purple-400
                                                   text-xs"
                                        >
                                            S
                                        </span>


                                        <div>
                                            <div
                                                class="font-medium text-gray-900 dark:text-white"
                                                x-text="item.name"
                                            ></div>

                                            <div
                                                x-show="item.parent_name"
                                                class="text-xs text-gray-500 mt-0.5"
                                            >
                                                Parent:
                                                <span x-text="item.parent_name"></span>
                                            </div>
                                        </div>

                                    </div>

                                </td>


                                {{-- Type --}}
                                <td class="px-5 py-4">

                                    <template x-if="item.level === 0">
                                        <span
                                            class="inline-flex px-2.5 py-1
                                                   text-xs font-medium rounded-full
                                                   bg-blue-100 text-blue-700
                                                   dark:bg-blue-900/30 dark:text-blue-300"
                                        >
                                            Main Category
                                        </span>
                                    </template>


                                    <template x-if="item.level > 0">
                                        <span
                                            class="inline-flex px-2.5 py-1
                                                   text-xs font-medium rounded-full
                                                   bg-purple-100 text-purple-700
                                                   dark:bg-purple-900/30 dark:text-purple-300"
                                        >
                                            Sub Category
                                        </span>
                                    </template>

                                </td>


                                {{-- Description --}}
                                <td
                                    class="px-5 py-4
                                           max-w-xs text-gray-600 dark:text-gray-400"
                                >
                                    <span
                                        x-text="item.description || '—'"
                                    ></span>
                                </td>


                                {{-- Status --}}
                                <td class="px-5 py-4">

                                    <span
                                        class="inline-flex px-2.5 py-1
                                               rounded-full text-xs font-medium"
                                        :class="
                                            item.is_active
                                                ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300'
                                                : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300'
                                        "
                                        x-text="
                                            item.is_active
                                                ? 'Active'
                                                : 'Inactive'
                                        "
                                    ></span>

                                </td>


                                {{-- Actions --}}
                                <td class="px-5 py-4">

                                    <div
                                        class="flex items-center justify-end gap-2"
                                    >

                                        {{-- Add Sub Category --}}
                                        @can('create category')
                                            <button
                                                x-show="item.level === 0"
                                                type="button"
                                                @click="openSubCategoryForm(item)"
                                                class="px-3 py-2 text-xs font-medium
                                                       bg-blue-600 hover:bg-blue-700
                                                       text-white rounded-lg transition"
                                            >
                                                + Sub Category
                                            </button>
                                        @endcan


                                        {{-- Edit --}}
                                        @can('edit category')
                                            <button
                                                type="button"
                                                @click="editCategory(item)"
                                                class="px-3 py-2 text-xs font-medium
                                                       bg-yellow-500 hover:bg-yellow-600
                                                       text-white rounded-lg transition"
                                            >
                                                Edit
                                            </button>
                                        @endcan


                                        {{-- Delete --}}
                                        @can('delete category')
                                            <button
                                                type="button"
                                                @click="deleteCategory(item.id)"
                                                class="px-3 py-2 text-xs font-medium
                                                       bg-red-600 hover:bg-red-700
                                                       text-white rounded-lg transition"
                                            >
                                                Delete
                                            </button>
                                        @endcan

                                    </div>

                                </td>

                            </tr>

                        </template>

                    </tbody>

                </table>

            </div>
        </div>


        {{-- Pagination --}}
        @if(
            $categories instanceof \Illuminate\Pagination\LengthAwarePaginator
            && $categories->hasPages()
        )
            <div class="px-2">
                {{ $categories->links() }}
            </div>
        @endif

    </div>


    <script>
        function categoryForm() {

            return {

                showForm: false,
                saving: false,

                /*
                |--------------------------------------------------------------------------
                | Server Categories
                |--------------------------------------------------------------------------
                */

                categories: @json(
                    $categories instanceof \Illuminate\Pagination\LengthAwarePaginator
                        ? $categories->items()
                        : $categories
                ),


                /*
                |--------------------------------------------------------------------------
                | Form
                |--------------------------------------------------------------------------
                */

                form: {
                    id: null,
                    parent_id: '',
                    name: '',
                    description: '',
                    is_active: true,
                },


                /*
                |--------------------------------------------------------------------------
                | Open Main Category Form
                |--------------------------------------------------------------------------
                */

                openForm() {

                    this.form = {
                        id: null,
                        parent_id: '',
                        name: '',
                        description: '',
                        is_active: true,
                    };

                    this.showForm = true;

                    this.scrollToForm();
                },


                /*
                |--------------------------------------------------------------------------
                | Open Sub Category Form
                |--------------------------------------------------------------------------
                */

                openSubCategoryForm(parent) {

                    this.form = {
                        id: null,
                        parent_id: parent.id,
                        name: '',
                        description: '',
                        is_active: true,
                    };

                    this.showForm = true;

                    this.scrollToForm();
                },


                /*
                |--------------------------------------------------------------------------
                | Edit
                |--------------------------------------------------------------------------
                */

                editCategory(cat) {

                    this.form = {
                        id: cat.id,

                        parent_id:
                            cat.parent_id
                                ? String(cat.parent_id)
                                : '',

                        name: cat.name ?? '',

                        description:
                            cat.description ?? '',

                        is_active:
                            !!Number(cat.is_active),
                    };

                    this.showForm = true;

                    this.scrollToForm();
                },


                /*
                |--------------------------------------------------------------------------
                | Reset
                |--------------------------------------------------------------------------
                */

                resetForm() {

                    this.form = {
                        id: null,
                        parent_id: '',
                        name: '',
                        description: '',
                        is_active: true,
                    };

                    this.showForm = false;
                    this.saving = false;
                },


                /*
                |--------------------------------------------------------------------------
                | Available Parent Categories
                |--------------------------------------------------------------------------
                |
                | Sirf main categories parent dropdown me dikhenge.
                | Editing ke waqt current category ko khud ka parent
                | banne se bhi prevent kiya gaya hai.
                |
                */

                availableParentCategories() {

                    return this.categories.filter(category => {

                        const isMainCategory =
                            !category.parent_id;

                        const isNotCurrentCategory =
                            Number(category.id) !== Number(this.form.id);

                        return (
                            isMainCategory &&
                            isNotCurrentCategory
                        );

                    });

                },


                /*
                |--------------------------------------------------------------------------
                | Flatten Categories
                |--------------------------------------------------------------------------
                |
                | Controller se:
                |
                | Main Category
                |   -> children
                |
                | structure aa raha hai.
                |
                | Table ke liye use flat list me convert karenge.
                |
                */

                flattenedCategories() {

                    let result = [];

                    const walk = (
                        category,
                        level = 0,
                        parentName = null
                    ) => {

                        result.push({
                            ...category,
                            level: level,
                            parent_name: parentName,
                        });


                        if (
                            category.children &&
                            Array.isArray(category.children)
                        ) {

                            category.children.forEach(child => {

                                walk(
                                    child,
                                    level + 1,
                                    category.name
                                );

                            });

                        }

                    };


                    this.categories.forEach(category => {

                        walk(
                            category,
                            0,
                            null
                        );

                    });


                    return result;
                },


                /*
                |--------------------------------------------------------------------------
                | Submit
                |--------------------------------------------------------------------------
                */

                async submitForm() {

                    if (this.saving) {
                        return;
                    }


                    if (!this.form.name.trim()) {

                        alert('Category name is required.');

                        return;
                    }


                    this.saving = true;


                    const isEdit =
                        !!this.form.id;


                    const payload = {

                        name:
                            this.form.name.trim(),

                        parent_id:
                            this.form.parent_id
                                ? Number(this.form.parent_id)
                                : null,

                        description:
                            this.form.description
                                ? this.form.description.trim()
                                : null,

                        is_active:
                            this.form.is_active
                                ? 1
                                : 0,
                    };


                    const url = isEdit

                        ? "{{ url('/categories') }}/" + this.form.id

                        : "{{ route('categories.store') }}";


                    const method =
                        isEdit
                            ? 'PUT'
                            : 'POST';


                    try {

                        const res = await fetch(
                            url,
                            {
                                method: method,

                                headers: {

                                    'Content-Type':
                                        'application/json',

                                    'Accept':
                                        'application/json',

                                    'X-Requested-With':
                                        'XMLHttpRequest',

                                    'X-CSRF-TOKEN':
                                        '{{ csrf_token() }}',
                                },

                                body:
                                    JSON.stringify(payload),
                            }
                        );


                        let data = {};


                        try {

                            data =
                                await res.json();

                        } catch (error) {

                            console.error(
                                'Invalid JSON Response:',
                                error
                            );

                        }


                        /*
                        |--------------------------------------------------------------------------
                        | Validation / Error
                        |--------------------------------------------------------------------------
                        */

                        if (!res.ok) {

                            let message =
                                data.message ??
                                'Something went wrong.';


                            if (data.errors) {

                                const errors =
                                    Object.values(
                                        data.errors
                                    ).flat();

                                if (errors.length) {
                                    message =
                                        errors.join('\n');
                                }

                            }


                            alert(message);

                            return;
                        }


                        if (!data.success) {

                            alert(
                                data.message ??
                                'Unable to save category.'
                            );

                            return;
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | Update Local Data
                        |--------------------------------------------------------------------------
                        |
                        | Nested category structure ko safely refresh karne ke liye
                        | page reload karna sabse reliable rahega.
                        |
                        | Isse parent-child placement galat nahi hogi.
                        |
                        */

                        alert(
                            data.message ??
                            'Category saved successfully.'
                        );


                        window.location.reload();


                    } catch (error) {

                        console.error(
                            'Category Save Error:',
                            error
                        );


                        alert(
                            'Server connection error. Please try again.'
                        );

                    } finally {

                        this.saving =
                            false;

                    }

                },


                /*
                |--------------------------------------------------------------------------
                | Delete
                |--------------------------------------------------------------------------
                */

                async deleteCategory(id) {

                    const confirmed =
                        confirm(
                            'Are you sure you want to delete this category?'
                        );


                    if (!confirmed) {
                        return;
                    }


                    try {

                        const res =
                            await fetch(

                                "{{ url('/categories') }}/" + id,

                                {
                                    method:
                                        'DELETE',

                                    headers: {

                                        'X-CSRF-TOKEN':
                                            '{{ csrf_token() }}',

                                        'X-Requested-With':
                                            'XMLHttpRequest',

                                        'Accept':
                                            'application/json',
                                    },
                                }
                            );


                        let data = {};


                        try {

                            data =
                                await res.json();

                        } catch (e) {

                        }


                        if (!res.ok) {

                            alert(
                                data.message ??
                                'Delete failed.'
                            );

                            return;
                        }


                        if (!data.success) {

                            alert(
                                data.message ??
                                'Delete failed.'
                            );

                            return;
                        }


                        alert(
                            data.message ??
                            'Category deleted successfully.'
                        );


                        window.location.reload();


                    } catch (error) {

                        console.error(
                            'Delete Error:',
                            error
                        );


                        alert(
                            'Server connection error.'
                        );

                    }

                },


                /*
                |--------------------------------------------------------------------------
                | Scroll To Form
                |--------------------------------------------------------------------------
                */

                scrollToForm() {

                    this.$nextTick(() => {

                        window.scrollTo({
                            top: 0,
                            behavior: 'smooth',
                        });

                    });

                },

            };

        }
    </script>

</x-layouts.app>