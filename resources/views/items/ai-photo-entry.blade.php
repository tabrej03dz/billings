<x-layouts.app :title="__('AI Photo Entry')">
    <div class="max-w-5xl mx-auto flex flex-col gap-5">

        <div class="flex items-center justify-between bg-[#BFE0E0] dark:bg-[#354A54] p-6 rounded-xl">
            <div>
                <h1 class="text-2xl font-bold text-black dark:text-white">AI Photo Entry</h1>
                <p class="text-sm text-gray-700 dark:text-gray-200 mt-1">
                    Jewellery / product photo upload karke item details auto fill karein.
                </p>
            </div>

            <a href="{{ route('items.index') }}"
               class="px-4 py-2 text-sm font-medium text-white bg-gray-700 rounded-lg hover:bg-gray-800">
                Back
            </a>
        </div>

        <div id="alertBox" class="hidden p-3 rounded-lg text-sm"></div>

        <div class="bg-white dark:bg-neutral-900 border border-gray-200 dark:border-gray-700 rounded-xl p-6">
            <form id="aiPhotoForm" enctype="multipart/form-data" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-semibold mb-2 text-gray-800 dark:text-white">
                        Upload Product Photo
                    </label>

                    <input type="file"
                           name="photo"
                           id="photo"
                           accept="image/*"
                           class="w-full border border-gray-300 dark:border-gray-600 rounded-lg p-3 bg-white dark:bg-neutral-800 text-gray-900 dark:text-white">

                    <p class="text-xs text-gray-500 mt-2">
                        JPG, PNG, WEBP allowed. Clear photo upload karein.
                    </p>
                </div>

                <div id="previewBox" class="hidden">
                    <p class="text-sm font-semibold mb-2 text-gray-800 dark:text-white">Preview</p>
                    <img id="previewImage"
                         class="max-h-72 rounded-xl border border-gray-200 dark:border-gray-700 object-contain">
                </div>

                <button type="submit"
                        id="aiBtn"
                        class="inline-flex items-center px-5 py-3 text-sm font-bold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">
                    📷 Photo Se Details Nikalo
                </button>
            </form>
        </div>

        <div id="resultSection" class="hidden bg-white dark:bg-neutral-900 border border-gray-200 dark:border-gray-700 rounded-xl p-6">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                    AI Filled Item Details
                </h2>

                <button type="button"
                        onclick="submitFinalItem()"
                        id="saveBtn"
                        class="px-5 py-2 text-sm font-bold text-white bg-green-600 rounded-lg hover:bg-green-700">
                    Save Item
                </button>
            </div>

            <form id="finalItemForm" method="POST" action="{{ route('items.store') }}" class="space-y-6">
                @csrf

                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold mb-1 dark:text-white">Item Name *</label>
                        <input type="text" name="name" required class="inputBox">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-1 dark:text-white">SKU</label>
                        <input type="text" name="sku" class="inputBox">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-1 dark:text-white">Type *</label>
                        <select name="type" required class="inputBox">
                            <option value="product">Product</option>
                            <option value="service">Service</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-1 dark:text-white">Category *</label>
                        <select name="category_id" required class="inputBox">
                            <option value="">Select Category</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-1 dark:text-white">Price</label>
                        <input type="number" step="0.01" name="price" class="inputBox">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-1 dark:text-white">Cost Price</label>
                        <input type="number" step="0.01" name="cost_price" class="inputBox">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-1 dark:text-white">Stock Qty *</label>
                        <input type="number" name="stock_qty" value="1" class="inputBox">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-1 dark:text-white">Unit</label>
                        <input type="text" name="unit" value="pcs" class="inputBox">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-1 dark:text-white">Tax Rate % *</label>
                        <input type="number" step="0.01" name="tax_rate" value="3" required class="inputBox">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-1 dark:text-white">Making Charge</label>
                        <input type="number" step="0.01" name="making_charge" class="inputBox">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1 dark:text-white">Description</label>
                    <textarea name="description" rows="3" class="inputBox"></textarea>
                </div>

                <div class="border-t border-gray-200 dark:border-gray-700 pt-5">
                    <h3 class="text-lg font-bold mb-4 dark:text-white">Jewellery Details</h3>

                    <div class="grid md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-semibold mb-1 dark:text-white">Metal Type</label>
                            <select name="metal_type" class="inputBox">
                                <option value="gold">Gold</option>
                                <option value="silver">Silver</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold mb-1 dark:text-white">Purity</label>
                            <input type="text" name="purity" class="inputBox">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold mb-1 dark:text-white">Gross Weight</label>
                            <input type="number" step="0.001" name="gross_weight" class="inputBox">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold mb-1 dark:text-white">Metal Weight</label>
                            <input type="number" step="0.001" name="metal_weight" class="inputBox">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold mb-1 dark:text-white">Stone Weight</label>
                            <input type="number" step="0.001" name="stone_weight" class="inputBox">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold mb-1 dark:text-white">Stone Charges</label>
                            <input type="number" step="0.01" name="stone_charges" class="inputBox">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold mb-1 dark:text-white">Gold Weight</label>
                            <input type="number" step="0.001" name="gold_weight" class="inputBox">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold mb-1 dark:text-white">Gold Purity</label>
                            <input type="text" name="gold_purity" class="inputBox">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold mb-1 dark:text-white">Silver Weight</label>
                            <input type="number" step="0.001" name="silver_weight" class="inputBox">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold mb-1 dark:text-white">Silver Purity</label>
                            <input type="text" name="silver_purity" class="inputBox">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold mb-1 dark:text-white">Diamond Weight</label>
                            <input type="number" step="0.001" name="diamond_weight" class="inputBox">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold mb-1 dark:text-white">Diamond Charges</label>
                            <input type="number" step="0.01" name="diamond_charges" class="inputBox">
                        </div>
                    </div>
                </div>

                <div>
                    <label class="inline-flex items-center gap-2 dark:text-white">
                        <input type="checkbox" name="is_active" value="1" checked>
                        Active
                    </label>
                </div>
            </form>
        </div>
    </div>

    <style>
        .inputBox {
            width: 100%;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 10px 12px;
            font-size: 14px;
            background: white;
            color: #111827;
        }

        .dark .inputBox {
            background: #262626;
            color: white;
            border-color: #525252;
        }
    </style>

    <script>
        const photoInput = document.getElementById('photo');
        const previewBox = document.getElementById('previewBox');
        const previewImage = document.getElementById('previewImage');
        const aiPhotoForm = document.getElementById('aiPhotoForm');
        const aiBtn = document.getElementById('aiBtn');
        const resultSection = document.getElementById('resultSection');
        const alertBox = document.getElementById('alertBox');

        photoInput.addEventListener('change', function () {
            const file = this.files[0];

            if (!file) {
                previewBox.classList.add('hidden');
                return;
            }

            previewImage.src = URL.createObjectURL(file);
            previewBox.classList.remove('hidden');
        });

        aiPhotoForm.addEventListener('submit', async function (e) {
            e.preventDefault();

            const file = photoInput.files[0];

            if (!file) {
                showAlert('Pehle photo upload karein.', 'error');
                return;
            }

            const formData = new FormData();
            formData.append('photo', file);
            formData.append('_token', '{{ csrf_token() }}');

            aiBtn.disabled = true;
            aiBtn.innerText = 'AI Reading Photo...';

            try {
                const response = await fetch("{{ route('items.ai-photo-entry') }}", {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (!response.ok) {
                    console.log(result);
                    showAlert(result.message || 'AI se details nahi mili.', 'error');
                    return;
                }

                fillForm(result.data);
                resultSection.classList.remove('hidden');
                showAlert('AI ne details fill kar di. Save karne se pehle check kar lein.', 'success');

            } catch (error) {
                console.log(error);
                showAlert('Something went wrong. Console check karein.', 'error');
            } finally {
                aiBtn.disabled = false;
                aiBtn.innerText = '📷 Photo Se Details Nikalo';
            }
        });

        function fillForm(data) {
            setValue('name', data.name);
            setValue('sku', data.sku);
            setValue('type', data.type || 'product');
            setValue('description', data.description);
            setValue('price', data.price);
            setValue('cost_price', data.cost_price);
            setValue('stock_qty', data.stock_qty || 1);
            setValue('unit', data.unit || 'pcs');
            setValue('tax_rate', data.tax_rate || 3);
            setValue('making_charge', data.making_charge);

            setValue('metal_type', data.metal_type || 'gold');
            setValue('purity', data.purity);
            setValue('gross_weight', data.gross_weight);
            setValue('metal_weight', data.metal_weight);
            setValue('stone_weight', data.stone_weight);
            setValue('stone_charges', data.stone_charges);
            setValue('gold_weight', data.gold_weight);
            setValue('gold_purity', data.gold_purity);
            setValue('silver_weight', data.silver_weight);
            setValue('silver_purity', data.silver_purity);
            setValue('diamond_weight', data.diamond_weight);
            setValue('diamond_charges', data.diamond_charges);
        }

        function setValue(name, value) {
            const el = document.querySelector(`#finalItemForm [name="${name}"]`);

            if (!el || value === null || value === undefined) {
                return;
            }

            el.value = value;
        }

        function submitFinalItem() {
            document.getElementById('finalItemForm').submit();
        }

        function showAlert(message, type = 'success') {
            alertBox.classList.remove('hidden');
            alertBox.innerText = message;

            if (type === 'success') {
                alertBox.className = 'p-3 rounded-lg text-sm bg-green-50 text-green-700 border border-green-200';
            } else {
                alertBox.className = 'p-3 rounded-lg text-sm bg-red-50 text-red-700 border border-red-200';
            }
        }
    </script>
</x-layouts.app>