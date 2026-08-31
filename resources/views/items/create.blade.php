<x-layouts.app :title="__('Create Item')">

    <div class="min-h-screen bg-slate-100 py-5 dark:bg-[#0f1419] sm:py-8">
        <div class="mx-auto max-w-5xl px-3 sm:px-6 lg:px-8">

            <div class="relative mb-6 overflow-hidden rounded-2xl bg-gradient-to-r from-cyan-700 via-teal-600 to-emerald-600 px-5 py-6 shadow-lg sm:px-8 sm:py-8">
                <div class="absolute -right-14 -top-14 h-40 w-40 rounded-full bg-white/10"></div>
                <div class="absolute -bottom-20 -left-10 h-44 w-44 rounded-full bg-white/10"></div>

                <div class="relative flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div class="mb-2 flex items-center gap-2">
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-white/20">
                                <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                            </span>

                            <span class="rounded-full bg-white/15 px-3 py-1 text-xs font-semibold text-white">
                                Inventory Management
                            </span>
                        </div>

                        <h1 class="text-2xl font-bold text-white sm:text-3xl">
                            Create New Item
                        </h1>

                        <p class="mt-2 max-w-2xl text-sm text-cyan-50 sm:text-base">
                            Product ya service ki details, pricing aur stock information add karein.
                        </p>
                    </div>

                    <a href="{{ route('items.index') }}"
                       class="inline-flex w-fit items-center justify-center gap-2 rounded-xl border border-white/30 bg-white/15 px-4 py-2.5 text-sm font-semibold text-white backdrop-blur-sm transition hover:bg-white hover:text-teal-700">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        Back to Items
                    </a>
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl shadow-slate-200/60 dark:border-slate-700 dark:bg-[#171c22] dark:shadow-none">
                <div class="border-b border-slate-200 bg-slate-50 px-5 py-4 dark:border-slate-700 dark:bg-[#1d242c] sm:px-7">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-teal-100 text-teal-700 dark:bg-teal-900/40 dark:text-teal-300">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l3.414 3.414A1 1 0 0117 7.414V19a2 2 0 01-2 2z"/>
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
                </div>

                @if($errors->any())
                    <div class="mx-5 mt-5 rounded-xl border border-red-200 bg-red-50 p-4 dark:border-red-900 dark:bg-red-950/30 sm:mx-7">
                        <div class="flex items-start gap-3">
                            <div class="mt-0.5 flex h-8 w-8 flex-none items-center justify-center rounded-lg bg-red-100 text-red-600 dark:bg-red-900/50">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.667 1.73-3L13.73 4c-.77-1.333-2.69-1.333-3.46 0L3.34 16c-.77 1.333.19 3 1.73 3z"/>
                                </svg>
                            </div>

                            <div>
                                <p class="text-sm font-bold text-red-800 dark:text-red-300">
                                    Please correct the following errors:
                                </p>

                                <ul class="mt-2 list-inside list-disc space-y-1 text-sm text-red-700 dark:text-red-400">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                <form action="{{ route('items.store') }}"
                      method="POST"
                      id="createItemForm" enctype="multipart/form-data"
                      class="p-5 sm:p-6">
                    @csrf

                    {{-- @include('items._form', [
                        'item' => null,
                        'categories' => $categories,
                        'units' => $units,
                        'allowedFields' => $allowedFields ?? []
                    ]) --}}

                    @include('items._form', [
                        'item' => null,
                        'categories' => $categories,
                        'units' => $units,
                        'allowedFields' => $allowedFields ?? [],
                        'generatedBarcode' => $generatedBarcode ?? null,
                    ])
                </form>
            </div>

            <div class="mt-5 flex items-start gap-3 rounded-xl border border-blue-200 bg-blue-50 p-4 dark:border-blue-900 dark:bg-blue-950/30">
                <svg class="mt-0.5 h-5 w-5 flex-none text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>

                <p class="text-sm leading-6 text-blue-800 dark:text-blue-300">
                    Category available nahi hone par <strong>Add Category</strong> button blink karega.
                    Popup se category create hone ke baad page automatically refresh ho jayega.
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
        const categoryDescription = document.getElementById('categoryDescription');

        const categoryNameError = document.getElementById('categoryNameError');
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
            categoryDescriptionError?.classList.add('hidden');
            categoryGeneralError?.classList.add('hidden');
            categorySuccessMessage?.classList.add('hidden');

            if (categoryNameError) {
                categoryNameError.textContent = '';
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

                categorySuccessMessage.textContent =
                    result.message || 'Category successfully created.';

                categorySuccessMessage.classList.remove('hidden');

                categoryForm.reset();

                setTimeout(function () {
                    window.location.reload();
                }, 700);

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