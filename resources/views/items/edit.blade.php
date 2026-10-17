<x-layouts.app :title="__('Edit Item')">

    <div class="min-h-screen bg-slate-100 py-5 dark:bg-[#0f1419] sm:py-8">
        <div class="mx-auto max-w-5xl px-3 sm:px-6 lg:px-8">

            {{-- Page Header --}}
            <div class="relative mb-6 overflow-hidden rounded-2xl
                        bg-gradient-to-r from-indigo-700 via-blue-600 to-cyan-600
                        px-5 py-6 shadow-lg sm:px-8 sm:py-8">

                {{-- Decorative circles --}}
                <div class="absolute -right-14 -top-14 h-40 w-40 rounded-full bg-white/10"></div>
                <div class="absolute -bottom-20 -left-10 h-44 w-44 rounded-full bg-white/10"></div>

                <div class="relative flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">

                    <div>
                        <div class="mb-2 flex items-center gap-2">

                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-white/20">
                                <svg
                                    class="h-5 w-5 text-white"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                    />
                                </svg>
                            </span>

                            <span class="rounded-full bg-white/15 px-3 py-1 text-xs font-semibold text-white">
                                Inventory Management
                            </span>

                        </div>

                        <h1 class="text-2xl font-bold text-white sm:text-3xl">
                            Edit Item
                        </h1>

                        <p class="mt-2 max-w-2xl text-sm text-blue-50 sm:text-base">
                            {{ $item->name }} ki details, pricing aur stock information update karein.
                        </p>
                    </div>

                    <a
                        href="{{ route('items.index') }}"
                        class="inline-flex w-fit items-center justify-center gap-2 rounded-xl
                               border border-white/30 bg-white/15 px-4 py-2.5
                               text-sm font-semibold text-white backdrop-blur-sm
                               transition hover:bg-white hover:text-blue-700"
                    >
                        <svg
                            class="h-4 w-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M15 19l-7-7 7-7"
                            />
                        </svg>

                        Back to Items
                    </a>

                </div>
            </div>

            {{-- Main Card --}}
            <div class="overflow-hidden rounded-2xl border border-slate-200
                        bg-white shadow-xl shadow-slate-200/60
                        dark:border-slate-700 dark:bg-[#171c22]
                        dark:shadow-none">

                {{-- Card Header --}}
                <div class="border-b border-slate-200 bg-slate-50
                            px-5 py-4 dark:border-slate-700
                            dark:bg-[#1d242c] sm:px-7">

                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

                        <div class="flex items-center gap-3">

                            <div class="flex h-10 w-10 items-center justify-center
                                        rounded-xl bg-blue-100 text-blue-700
                                        dark:bg-blue-900/40 dark:text-blue-300">
                                <svg
                                    class="h-5 w-5"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l3.414 3.414A1 1 0 0117 7.414V19a2 2 0 01-2 2z"
                                    />
                                </svg>
                            </div>

                            <div>
                                <h2 class="text-lg font-bold text-slate-900 dark:text-white">
                                    Item Information
                                </h2>

                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                    Star (*) wali fields required hain
                                </p>
                            </div>

                        </div>

                        <div class="inline-flex w-fit items-center gap-2 rounded-lg
                                    bg-slate-200 px-3 py-1.5 text-xs font-semibold
                                    text-slate-700 dark:bg-slate-700
                                    dark:text-slate-200">
                            Item ID: #{{ $item->id }}
                        </div>

                    </div>
                </div>

                {{-- Validation Errors --}}
                @if($errors->any())
                    <div class="mx-5 mt-5 rounded-xl border border-red-200
                                bg-red-50 p-4 dark:border-red-900
                                dark:bg-red-950/30 sm:mx-7">

                        <div class="flex items-start gap-3">

                            <div class="mt-0.5 flex h-8 w-8 flex-none items-center
                                        justify-center rounded-lg bg-red-100
                                        text-red-600 dark:bg-red-900/50">
                                <svg
                                    class="h-5 w-5"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.667 1.73-3L13.73 4c-.77-1.333-2.69-1.333-3.46 0L3.34 16c-.77 1.333.19 3 1.73 3z"
                                    />
                                </svg>
                            </div>

                            <div>
                                <p class="text-sm font-bold text-red-800 dark:text-red-300">
                                    Please correct the following errors:
                                </p>

                                <ul class="mt-2 list-inside list-disc space-y-1
                                           text-sm text-red-700 dark:text-red-400">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>

                        </div>
                    </div>
                @endif

                {{-- Update Form --}}
                <form
                    action="{{ route('items.update', $item->id) }}" enctype="multipart/form-data"
                    method="POST"
                    id="editItemForm"
                    class="p-5 sm:p-7"
                >
                    @csrf
                    @method('PUT')

                    @include('items._form', [
                        'item' => $item,
                        'categories' => $categories,
                        'units' => $units,
                        'allowedFields' => $allowedFields ?? []
                    ])
                </form>

            </div>

            {{-- Information Box --}}
            <div class="mt-5 flex items-start gap-3 rounded-xl
                        border border-amber-200 bg-amber-50 p-4
                        dark:border-amber-900 dark:bg-amber-950/30">

                <svg
                    class="mt-0.5 h-5 w-5 flex-none text-amber-600 dark:text-amber-400"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                    />
                </svg>

                <p class="text-sm leading-6 text-amber-800 dark:text-amber-300">
                    Item update karne ke baad purani information replace ho jayegi.
                    Save karne se pehle price, stock aur category verify kar lein. Add Category se nayi category/sub-category banate hi woh automatically select ho jayegi.
                </p>

            </div>

        </div>
    </div>



{{-- Create Category Modal --}}
<div
    id="categoryModal"
    class="fixed inset-0 z-[9999] hidden items-center justify-center p-4"
>
    {{-- Overlay --}}
    <div
        id="categoryModalOverlay"
        class="absolute inset-0 bg-black/60 backdrop-blur-sm"
    ></div>

    {{-- Modal Box --}}
    <div
        class="relative z-10 w-full max-w-md rounded-2xl bg-white
               dark:bg-[#1A1D23] shadow-2xl overflow-hidden"
    >
        <div
            class="flex items-center justify-between border-b
                   border-gray-200 dark:border-gray-700 px-5 py-4"
        >
            <div>
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">
                    Create Category
                </h2>

                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Nayi item category add karein
                </p>
            </div>

            <button
                type="button"
                id="closeCategoryModal"
                class="flex h-9 w-9 items-center justify-center rounded-full
                       bg-gray-100 text-gray-600 hover:bg-red-100
                       hover:text-red-600 dark:bg-gray-800 dark:text-gray-300"
            >
                <span class="text-2xl leading-none">&times;</span>
            </button>
        </div>

        <form id="categoryCreateForm" class="p-5 space-y-4">
            @csrf

            <div
                id="categorySuccessMessage"
                class="hidden rounded-lg bg-green-100 px-4 py-3
                       text-sm font-medium text-green-700"
            ></div>

            <div
                id="categoryGeneralError"
                class="hidden rounded-lg bg-red-100 px-4 py-3
                       text-sm font-medium text-red-700"
            ></div>

            <div>
                <label
                    for="categoryName"
                    class="block text-sm font-medium text-gray-700
                           dark:text-gray-200 mb-1"
                >
                    Category Name
                    <span class="text-red-600">*</span>
                </label>

                <input
                    type="text"
                    id="categoryName"
                    name="name"
                    autocomplete="off"
                    placeholder="Example: Gold Jewellery"
                    class="w-full rounded-lg border border-gray-300
                           bg-slate-100 px-3 py-2.5 text-gray-900
                           outline-none focus:border-blue-500
                           focus:ring-2 focus:ring-blue-200
                           dark:border-gray-600 dark:bg-gray-800
                           dark:text-white"
                >

                <p
                    id="categoryNameError"
                    class="hidden mt-1 text-xs font-medium text-red-600"
                ></p>
            </div>

            {{-- Parent Category --}}
            <div>
                <label
                    for="categoryParent"
                    class="block text-sm font-medium text-gray-700
                           dark:text-gray-200 mb-1"
                >
                    Parent Category
                </label>

                <select
                    id="categoryParent"
                    name="parent_id"
                    class="w-full rounded-lg border border-gray-300
                           bg-slate-100 px-3 py-2.5 text-gray-900
                           outline-none focus:border-blue-500
                           focus:ring-2 focus:ring-blue-200
                           dark:border-gray-600 dark:bg-gray-800
                           dark:text-white"
                >
                    <option value="">— Main Category —</option>

                    @foreach($categories->whereNull('parent_id') as $parentCategory)
                        <option value="{{ $parentCategory->id }}">
                            {{ $parentCategory->name }}
                        </option>
                    @endforeach
                </select>

                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Main category banana ho to blank chhodein. Sub category ke liye parent select karein.
                </p>

                <p
                    id="categoryParentError"
                    class="hidden mt-1 text-xs font-medium text-red-600"
                ></p>
            </div>

            <div>
                <label
                    for="categoryDescription"
                    class="block text-sm font-medium text-gray-700
                           dark:text-gray-200 mb-1"
                >
                    Description
                </label>

                <textarea
                    id="categoryDescription"
                    name="description"
                    rows="3"
                    placeholder="Optional category description"
                    class="w-full rounded-lg border border-gray-300
                           bg-slate-100 px-3 py-2.5 text-gray-900
                           outline-none focus:border-blue-500
                           focus:ring-2 focus:ring-blue-200
                           dark:border-gray-600 dark:bg-gray-800
                           dark:text-white"
                ></textarea>

                <p
                    id="categoryDescriptionError"
                    class="hidden mt-1 text-xs font-medium text-red-600"
                ></p>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button
                    type="button"
                    id="cancelCategoryModal"
                    class="rounded-lg bg-gray-200 px-4 py-2
                           text-sm font-semibold text-gray-700
                           hover:bg-gray-300 dark:bg-gray-700
                           dark:text-white dark:hover:bg-gray-600"
                >
                    Cancel
                </button>

                <button
                    type="submit"
                    id="categorySubmitButton"
                    class="inline-flex items-center justify-center gap-2
                           rounded-lg bg-green-600 px-5 py-2
                           text-sm font-semibold text-white
                           hover:bg-green-700 disabled:cursor-not-allowed
                           disabled:opacity-60"
                >
                    <svg
                        id="categoryLoader"
                        class="hidden h-4 w-4 animate-spin"
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

                    <span id="categorySubmitText">
                        Create Category
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const categoryModal = document.getElementById('categoryModal');
        const openCategoryModal = document.getElementById('openCategoryModal');
        const closeCategoryModal = document.getElementById('closeCategoryModal');
        const cancelCategoryModal = document.getElementById('cancelCategoryModal');
        const categoryModalOverlay = document.getElementById('categoryModalOverlay');

        const categoryForm = document.getElementById('categoryCreateForm');
        const categoryName = document.getElementById('categoryName');
        const categoryParent = document.getElementById('categoryParent');
        const categoryDescription = document.getElementById('categoryDescription');

        // Main Item form ka Category dropdown
        const itemCategorySelect = document.getElementById('categorySelect');

        const categoryNameError = document.getElementById('categoryNameError');
        const categoryParentError = document.getElementById('categoryParentError');
        const categoryDescriptionError = document.getElementById(
            'categoryDescriptionError'
        );

        const categoryGeneralError = document.getElementById(
            'categoryGeneralError'
        );

        const categorySuccessMessage = document.getElementById(
            'categorySuccessMessage'
        );

        const categorySubmitButton = document.getElementById(
            'categorySubmitButton'
        );

        const categorySubmitText = document.getElementById(
            'categorySubmitText'
        );

        const categoryLoader = document.getElementById('categoryLoader');

        function showCategoryModal() {
            if (!categoryModal) {
                return;
            }

            categoryModal.classList.remove('hidden');
            categoryModal.classList.add('flex');
            document.body.classList.add('category-modal-open');

            setTimeout(function () {
                categoryName?.focus();
            }, 100);
        }

        function hideCategoryModal() {
            if (!categoryModal) {
                return;
            }

            categoryModal.classList.add('hidden');
            categoryModal.classList.remove('flex');
            document.body.classList.remove('category-modal-open');

            clearCategoryErrors();
            categoryForm?.reset();
        }

        function clearCategoryErrors() {
            categoryNameError?.classList.add('hidden');
            categoryParentError?.classList.add('hidden');
            categoryDescriptionError?.classList.add('hidden');
            categoryGeneralError?.classList.add('hidden');
            categorySuccessMessage?.classList.add('hidden');

            if (categoryNameError) {
                categoryNameError.textContent = '';
            }

            if (categoryParentError) {
                categoryParentError.textContent = '';
            }

            if (categoryDescriptionError) {
                categoryDescriptionError.textContent = '';
            }

            if (categoryGeneralError) {
                categoryGeneralError.textContent = '';
            }

            if (categorySuccessMessage) {
                categorySuccessMessage.textContent = '';
            }
        }

        function setCategoryLoading(loading) {
            if (!categorySubmitButton) {
                return;
            }

            categorySubmitButton.disabled = loading;

            if (loading) {
                categoryLoader?.classList.remove('hidden');

                if (categorySubmitText) {
                    categorySubmitText.textContent = 'Creating...';
                }
            } else {
                categoryLoader?.classList.add('hidden');

                if (categorySubmitText) {
                    categorySubmitText.textContent = 'Create Category';
                }
            }
        }

        openCategoryModal?.addEventListener('click', showCategoryModal);
        closeCategoryModal?.addEventListener('click', hideCategoryModal);
        cancelCategoryModal?.addEventListener('click', hideCategoryModal);
        categoryModalOverlay?.addEventListener('click', hideCategoryModal);

        document.addEventListener('keydown', function (event) {
            if (
                event.key === 'Escape' &&
                categoryModal &&
                !categoryModal.classList.contains('hidden')
            ) {
                hideCategoryModal();
            }
        });

        categoryForm?.addEventListener('submit', async function (event) {
            event.preventDefault();

            clearCategoryErrors();

            const name = categoryName?.value.trim() || '';

            if (!name) {
                if (categoryNameError) {
                    categoryNameError.textContent =
                        'Category name is required.';

                    categoryNameError.classList.remove('hidden');
                }

                categoryName?.focus();
                return;
            }

            setCategoryLoading(true);

            try {
                const response = await fetch(
                    "{{ route('categories.quick-store') }}",
                    {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector(
                                'meta[name="csrf-token"]'
                            )?.getAttribute('content') || ''
                        },
                        body: new FormData(categoryForm)
                    }
                );

                const result = await response.json();

                if (!response.ok) {
                    if (response.status === 422 && result.errors) {
                        if (result.errors.name?.length) {
                            categoryNameError.textContent =
                                result.errors.name[0];

                            categoryNameError.classList.remove('hidden');
                        }

                        if (result.errors.parent_id?.length) {
                            categoryParentError.textContent =
                                result.errors.parent_id[0];

                            categoryParentError.classList.remove('hidden');
                        }

                        if (result.errors.description?.length) {
                            categoryDescriptionError.textContent =
                                result.errors.description[0];

                            categoryDescriptionError.classList.remove(
                                'hidden'
                            );
                        }

                        return;
                    }

                    throw new Error(
                        result.message ||
                        'Category create nahi ho saki.'
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Category Successfully Created
                |--------------------------------------------------------------------------
                | Page reload nahi karenge.
                | Nayi category ko item ke Category dropdown me append karke
                | automatically select karenge.
                */

                const createdCategory = result.category;

                if (
                    !createdCategory ||
                    !createdCategory.id ||
                    !createdCategory.name
                ) {
                    throw new Error(
                        'Category create hui, lekin response me category data nahi mila.'
                    );
                }

                if (!itemCategorySelect) {
                    throw new Error(
                        'Item category dropdown nahi mila. categorySelect ID check karein.'
                    );
                }

                const createdId = String(createdCategory.id);

                /*
                |--------------------------------------------------------------------------
                | Duplicate option avoid
                |--------------------------------------------------------------------------
                */
                let newOption = Array.from(
                    itemCategorySelect.options
                ).find(function (option) {
                    return String(option.value) === createdId;
                });

                if (!newOption) {
                    newOption = document.createElement('option');

                    newOption.value = createdId;

                    const parentId =
                        createdCategory.parent_id
                            ? String(createdCategory.parent_id)
                            : '';

                    newOption.dataset.parentId = parentId;

                    /*
                    |--------------------------------------------------------------------------
                    | Dropdown Label
                    |--------------------------------------------------------------------------
                    | Sub category ho to:
                    | Jewellery → Gold Ring
                    |--------------------------------------------------------------------------
                    */
                    if (parentId) {
                        const selectedParentOption =
                            categoryParent?.options[
                                categoryParent.selectedIndex
                            ];

                        const parentName =
                            selectedParentOption?.textContent?.trim()
                                ?.replace(/^—|—$/g, '')
                                ?.trim() || '';

                        newOption.textContent =
                            parentName
                                ? '↳ ' + parentName + ' → ' + createdCategory.name
                                : '↳ ' + createdCategory.name;
                    } else {
                        newOption.textContent =
                            createdCategory.name;
                    }

                    itemCategorySelect.appendChild(newOption);
                }

                /*
                |--------------------------------------------------------------------------
                | AUTO SELECT NEW CATEGORY
                |--------------------------------------------------------------------------
                */
                itemCategorySelect.value = createdId;

                newOption.selected = true;

                // Kisi aur JS / Alpine / plugin ko change detect karna ho
                itemCategorySelect.dispatchEvent(
                    new Event('change', {
                        bubbles: true
                    })
                );

                categorySuccessMessage.textContent =
                    (result.message || 'Category successfully created.') +
                    ' Item me automatically select kar di gayi hai.';

                categorySuccessMessage.classList.remove('hidden');

                /*
                |--------------------------------------------------------------------------
                | Form reset baad me
                |--------------------------------------------------------------------------
                | Parent name read karne ke baad reset karna zaroori hai.
                */
                categoryForm.reset();

                /*
                |--------------------------------------------------------------------------
                | Modal Close
                |--------------------------------------------------------------------------
                */
                setTimeout(function () {
                    if (categoryModal) {
                        categoryModal.classList.add('hidden');
                        categoryModal.classList.remove('flex');
                    }

                    document.body.classList.remove(
                        'category-modal-open'
                    );

                    clearCategoryErrors();

                    // Item category dropdown ko visible/focus kara dein
                    itemCategorySelect.focus();
                }, 650);

            } catch (error) {
                categoryGeneralError.textContent =
                    error.message ||
                    'Something went wrong. Please try again.';

                categoryGeneralError.classList.remove('hidden');
            } finally {
                setCategoryLoading(false);
            }
        });
    });
</script>

</x-layouts.app>