<?php

namespace App\Imports;

use App\Models\Category;
use App\Models\Item;
use App\Services\StockService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ItemsImport implements ToCollection, WithHeadingRow
{
    protected int $businessId;

    protected array $allowedFields;

    protected array $requiredFields;

    protected StockService $stockService;

    protected int $imported = 0;

    protected int $skipped = 0;

    protected array $errors = [];

    /*
    |--------------------------------------------------------------------------
    | Constructor
    |--------------------------------------------------------------------------
    */
    public function __construct(
        int $businessId,
        array $allowedFields,
        array $requiredFields,
        StockService $stockService
    ) {
        $this->businessId = $businessId;
        $this->allowedFields = $allowedFields;
        $this->requiredFields = $requiredFields;
        $this->stockService = $stockService;
    }

    /*
    |--------------------------------------------------------------------------
    | Process Excel Rows
    |--------------------------------------------------------------------------
    */
    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {

            /*
             * Heading row = row 1
             * Actual Excel data starts row 2
             */
            $excelRowNumber = $index + 2;

            try {

                /*
                |--------------------------------------------------------------------------
                | Convert Row To Normal Array
                |--------------------------------------------------------------------------
                */
                $row = collect($row)
                    ->map(function ($value) {

                        if (is_string($value)) {
                            $value = trim($value);

                            return $value === ''
                                ? null
                                : $value;
                        }

                        return $value;
                    })
                    ->toArray();

                /*
                |--------------------------------------------------------------------------
                | Completely Empty Row Skip
                |--------------------------------------------------------------------------
                */
                if ($this->isEmptyRow($row)) {
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Normalize Excel Data
                |--------------------------------------------------------------------------
                */
                $data = $this->normalizeRow($row);

                /*
                |--------------------------------------------------------------------------
                | Validation
                |--------------------------------------------------------------------------
                */
                $validator = Validator::make(
                    $data,
                    $this->rules()
                );

                if ($validator->fails()) {

                    $this->skipped++;

                    $this->errors[] = [
                        'row' => $excelRowNumber,
                        'message' => implode(
                            ' | ',
                            $validator->errors()->all()
                        ),
                    ];

                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Category Name -> Category ID
                |--------------------------------------------------------------------------
                */
                $categoryId = null;

                if (
                    $this->isAllowed('category_id')
                    && !empty($data['category'])
                ) {
                    /*
                    |--------------------------------------------------------------------------
                    | Category Name -> Category ID
                    |--------------------------------------------------------------------------
                    |
                    | Excel me category name diya jayega.
                    | Agar category nahi milegi to automatically create ho jayegi.
                    |
                    */
                    $categoryId = null;

                    if (
                        $this->isAllowed('category_id')
                        && !empty($data['category'])
                    ) {

                        $categoryName = trim(
                            (string) $data['category']
                        );

                        /*
                        |--------------------------------------------------------------------------
                        | Find Existing Category
                        |--------------------------------------------------------------------------
                        */
                        $category = Category::query()
                            ->where(
                                'business_id',
                                $this->businessId
                            )
                            ->whereRaw(
                                'LOWER(TRIM(name)) = ?',
                                [
                                    mb_strtolower(
                                        $categoryName
                                    )
                                ]
                            )
                            ->first();

                        /*
                        |--------------------------------------------------------------------------
                        | Category Not Found -> Auto Create
                        |--------------------------------------------------------------------------
                        */
                        if (!$category) {

                            $category = Category::create([
                                'business_id' =>
                                    $this->businessId,

                                'name' =>
                                    $categoryName,

                                'description' =>
                                    'Created automatically from item Excel import',
                            ]);
                        }

                        $categoryId = $category->id;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Required Category Check
                    |--------------------------------------------------------------------------
                    */
                    if (
                        $this->isRequired('category_id')
                        && !$categoryId
                    ) {

                        $this->skipped++;

                        $this->errors[] = [
                            'row' =>
                                $excelRowNumber,

                            'message' =>
                                'Category required hai.',
                        ];

                        continue;
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | Category Required Check
                |--------------------------------------------------------------------------
                */
                if (
                    $this->isRequired('category_id')
                    && !$categoryId
                ) {
                    $this->skipped++;

                    $this->errors[] = [
                        'row' => $excelRowNumber,
                        'message' =>
                            'Category required hai.',
                    ];

                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Barcode
                |--------------------------------------------------------------------------
                |
                | Blank barcode => automatically generate
                |
                */
                $barcode = !empty($data['barcode'])
                    ? trim((string) $data['barcode'])
                    : $this->generateUniqueBarcode();

                /*
                |--------------------------------------------------------------------------
                | Duplicate Barcode Check
                |--------------------------------------------------------------------------
                */
                if (
                    Item::query()
                        ->where('business_id', $this->businessId)
                        ->where('barcode', $barcode)
                        ->exists()
                ) {
                    $this->skipped++;

                    $this->errors[] = [
                        'row' => $excelRowNumber,
                        'message' =>
                            'Barcode "'
                            . $barcode
                            . '" already exists.',
                    ];

                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Duplicate SKU Check
                |--------------------------------------------------------------------------
                */
                if (
                    !empty($data['sku'])
                    && Item::query()
                        ->where('business_id', $this->businessId)
                        ->where('sku', $data['sku'])
                        ->exists()
                ) {
                    $this->skipped++;

                    $this->errors[] = [
                        'row' => $excelRowNumber,
                        'message' =>
                            'SKU "'
                            . $data['sku']
                            . '" already exists.',
                    ];

                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Duplicate HUID Check
                |--------------------------------------------------------------------------
                */
                if (
                    !empty($data['huid'])
                    && Item::query()
                        ->where('business_id', $this->businessId)
                        ->where('huid', $data['huid'])
                        ->exists()
                ) {
                    $this->skipped++;

                    $this->errors[] = [
                        'row' => $excelRowNumber,
                        'message' =>
                            'HUID "'
                            . $data['huid']
                            . '" already exists.',
                    ];

                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Opening Stock
                |--------------------------------------------------------------------------
                */
                $openingQty = $this->isAllowed('stock_qty')
                    ? (int) ($data['stock_qty'] ?? 0)
                    : 0;

                /*
                |--------------------------------------------------------------------------
                | Prepare Item Payload
                |--------------------------------------------------------------------------
                */
                $payload = [
                    'business_id' => $this->businessId,

                    /*
                     * Stock initially zero.
                     * StockService opening transaction create karega.
                     */
                    'stock_qty' => 0,

                    'barcode' => $barcode,

                    /*
                     * Default active
                     */
                    'is_active' => $this->parseBoolean(
                        $data['is_active'] ?? 1
                    ),
                ];

                /*
                |--------------------------------------------------------------------------
                | Category
                |--------------------------------------------------------------------------
                */
                if ($this->isAllowed('category_id')) {
                    $payload['category_id'] = $categoryId;
                }

                /*
                |--------------------------------------------------------------------------
                | Allowed Fields Only
                |--------------------------------------------------------------------------
                */
                $normalFields = [
                    'name',
                    'huid',
                    'sku',
                    'type',
                    'sac',
                    'description',
                    'price',
                    'cost_price',
                    'making_charge_type',
                    'making_charge',
                    'unit',
                    'tax_rate',
                    'metal_type',
                    'purity',
                    'gross_weight',
                    'metal_weight',
                    'stone_weight',
                    'stone_charges',
                    'gold_weight',
                    'gold_purity',
                    'silver_weight',
                    'silver_purity',
                    'diamond_weight',
                    'diamond_charges',
                ];

                foreach ($normalFields as $field) {

                    if (!$this->isAllowed($field)) {
                        continue;
                    }

                    $payload[$field] =
                        $data[$field] ?? null;
                }

                /*
                |--------------------------------------------------------------------------
                | Default Type
                |--------------------------------------------------------------------------
                */
                if (
                    $this->isAllowed('type')
                    && empty($payload['type'])
                ) {
                    $payload['type'] = 'product';
                }

                /*
                |--------------------------------------------------------------------------
                | Default Making Charge Type
                |--------------------------------------------------------------------------
                */
                if (
                    $this->isAllowed('making_charge')
                    && empty($payload['making_charge_type'])
                ) {
                    $payload['making_charge_type'] =
                        'percentage';
                }

                /*
                |--------------------------------------------------------------------------
                | HUID Uppercase
                |--------------------------------------------------------------------------
                */
                if (!empty($payload['huid'])) {
                    $payload['huid'] =
                        strtoupper(trim($payload['huid']));
                }

                /*
                |--------------------------------------------------------------------------
                | Database Transaction Per Row
                |--------------------------------------------------------------------------
                */
                DB::transaction(function () use (
                    $payload,
                    $openingQty,
                    $excelRowNumber
                ) {

                    $item = Item::create($payload);

                    /*
                    |--------------------------------------------------------------------------
                    | Opening Stock
                    |--------------------------------------------------------------------------
                    */
                    if ($openingQty > 0) {

                        $this->stockService->recordOpening(
                            $item,
                            $openingQty,
                            'Opening stock from Excel import'
                        );
                    }
                });

                $this->imported++;

            } catch (\Throwable $e) {

                $this->skipped++;

                $this->errors[] = [
                    'row' => $excelRowNumber,
                    'message' => $e->getMessage(),
                ];

                Log::error(
                    'Excel item import row failed',
                    [
                        'business_id' =>
                            $this->businessId,

                        'row' =>
                            $excelRowNumber,

                        'message' =>
                            $e->getMessage(),

                        'line' =>
                            $e->getLine(),

                        'file' =>
                            $e->getFile(),
                    ]
                );
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Normalize Excel Row
    |--------------------------------------------------------------------------
    */
    protected function normalizeRow(array $row): array
    {
        $data = [];

        /*
        |--------------------------------------------------------------------------
        | String Fields
        |--------------------------------------------------------------------------
        */
        $stringFields = [
            'name',
            'huid',
            'sku',
            'category',
            'type',
            'sac',
            'description',
            'unit',
            'making_charge_type',
            'metal_type',
            'purity',
            'gold_purity',
            'silver_purity',
            'barcode',
        ];

        foreach ($stringFields as $field) {

            $value = $row[$field] ?? null;

            if (is_string($value)) {
                $value = trim($value);
            }

            $data[$field] =
                $value === ''
                    ? null
                    : $value;
        }

        /*
        |--------------------------------------------------------------------------
        | Lower Case Enum Fields
        |--------------------------------------------------------------------------
        */
        foreach (
            [
                'type',
                'making_charge_type',
                'metal_type',
            ] as $field
        ) {
            if (!empty($data[$field])) {
                $data[$field] =
                    strtolower(trim($data[$field]));
            }
        }

        /*
        |--------------------------------------------------------------------------
        | HUID Upper Case
        |--------------------------------------------------------------------------
        */
        if (!empty($data['huid'])) {
            $data['huid'] =
                strtoupper(trim($data['huid']));
        }

        /*
        |--------------------------------------------------------------------------
        | Numeric Fields
        |--------------------------------------------------------------------------
        */
        $numericFields = [
            'price',
            'cost_price',
            'making_charge',
            'stock_qty',
            'tax_rate',
            'gross_weight',
            'metal_weight',
            'stone_weight',
            'stone_charges',
            'gold_weight',
            'silver_weight',
            'diamond_weight',
            'diamond_charges',
        ];

        foreach ($numericFields as $field) {

            $value = $row[$field] ?? null;

            if (
                $value === ''
                || $value === null
            ) {
                $data[$field] = null;

                continue;
            }

            /*
             * Remove commas:
             * 1,25,000 -> 125000
             */
            if (is_string($value)) {
                $value = str_replace(',', '', $value);
            }

            $data[$field] = $value;
        }

        /*
        |--------------------------------------------------------------------------
        | Active
        |--------------------------------------------------------------------------
        */
        $data['is_active'] =
            $row['is_active'] ?? 1;

        return $data;
    }

    /*
    |--------------------------------------------------------------------------
    | Validation Rules
    |--------------------------------------------------------------------------
    */
    protected function rules(): array
    {
        $rules = [];

        /*
        |--------------------------------------------------------------------------
        | Name
        |--------------------------------------------------------------------------
        |
        | Item practically name ke bina useful nahi hai,
        | isliye import me name required rakha hai.
        |
        */
        if ($this->isAllowed('name')) {
            $rules['name'] = [
                'required',
                'string',
                'max:255',
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | SKU
        |--------------------------------------------------------------------------
        */
        if ($this->isAllowed('sku')) {
            $rules['sku'] = [
                'nullable',
                'string',
                'max:100',
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | HUID
        |--------------------------------------------------------------------------
        */
        if ($this->isAllowed('huid')) {
            $rules['huid'] = [
                $this->requiredRule('huid'),
                'nullable',
                'string',
                'max:50',
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Category
        |--------------------------------------------------------------------------
        |
        | Excel me category_id nahi,
        | category name denge.
        |
        */
        if ($this->isAllowed('category_id')) {

            $rules['category'] = [
                $this->requiredRule(
                    'category_id'
                ),
                'nullable',
                'string',
                'max:255',
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Type
        |--------------------------------------------------------------------------
        */
        if ($this->isAllowed('type')) {
            $rules['type'] = [
                $this->requiredRule('type'),
                'nullable',
                Rule::in([
                    'product',
                    'service',
                ]),
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | SAC
        |--------------------------------------------------------------------------
        */
        if ($this->isAllowed('sac')) {
            $rules['sac'] = [
                $this->requiredRule('sac'),
                'nullable',
                'string',
                'max:32',
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Description
        |--------------------------------------------------------------------------
        */
        if ($this->isAllowed('description')) {
            $rules['description'] = [
                'nullable',
                'string',
                'max:2000',
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Price
        |--------------------------------------------------------------------------
        */
        if ($this->isAllowed('price')) {
            $rules['price'] = [
                $this->requiredRule('price'),
                'nullable',
                'numeric',
                'min:0',
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Cost Price
        |--------------------------------------------------------------------------
        */
        if ($this->isAllowed('cost_price')) {
            $rules['cost_price'] = [
                'nullable',
                'numeric',
                'min:0',
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Making Charge
        |--------------------------------------------------------------------------
        */
        if ($this->isAllowed('making_charge')) {

            $rules['making_charge_type'] = [
                'nullable',
                Rule::in([
                    'percentage',
                    'fixed',
                    'per_gram',
                    'per_product',
                ]),
            ];

            $rules['making_charge'] = [
                'nullable',
                'numeric',
                'min:0',
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Stock
        |--------------------------------------------------------------------------
        */
        if ($this->isAllowed('stock_qty')) {
            $rules['stock_qty'] = [
                $this->requiredRule(
                    'stock_qty'
                ),
                'nullable',
                'integer',
                'min:0',
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Unit
        |--------------------------------------------------------------------------
        */
        if ($this->isAllowed('unit')) {
            $rules['unit'] = [
                'nullable',
                'string',
                'max:50',
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Tax
        |--------------------------------------------------------------------------
        */
        if ($this->isAllowed('tax_rate')) {
            $rules['tax_rate'] = [
                $this->requiredRule(
                    'tax_rate'
                ),
                'nullable',
                'numeric',
                'min:0',
                'max:100',
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Metal Type
        |--------------------------------------------------------------------------
        */
        if ($this->isAllowed('metal_type')) {
            $rules['metal_type'] = [
                'nullable',
                Rule::in([
                    'gold',
                    'silver',
                    'other',
                ]),
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Purity
        |--------------------------------------------------------------------------
        */
        if ($this->isAllowed('purity')) {
            $rules['purity'] = [
                'nullable',
                'string',
                'max:50',
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Numeric Jewellery Fields
        |--------------------------------------------------------------------------
        */
        $numericJewelleryFields = [
            'gross_weight',
            'metal_weight',
            'stone_weight',
            'stone_charges',
            'gold_weight',
            'silver_weight',
            'diamond_weight',
            'diamond_charges',
        ];

        foreach (
            $numericJewelleryFields as $field
        ) {
            if ($this->isAllowed($field)) {
                $rules[$field] = [
                    'nullable',
                    'numeric',
                    'min:0',
                ];
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Purity Fields
        |--------------------------------------------------------------------------
        */
        foreach (
            [
                'gold_purity',
                'silver_purity',
            ] as $field
        ) {
            if ($this->isAllowed($field)) {
                $rules[$field] = [
                    'nullable',
                    'string',
                    'max:50',
                ];
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Barcode
        |--------------------------------------------------------------------------
        */
        $rules['barcode'] = [
            'nullable',
            'string',
            'max:100',
        ];

        return $rules;
    }

    /*
    |--------------------------------------------------------------------------
    | Allowed
    |--------------------------------------------------------------------------
    */
    protected function isAllowed(
        string $field
    ): bool {
        return in_array(
            $field,
            $this->allowedFields,
            true
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Required
    |--------------------------------------------------------------------------
    */
    protected function isRequired(
        string $field
    ): bool {
        return in_array(
            $field,
            $this->requiredFields,
            true
        );
    }

    protected function requiredRule(
        string $field
    ): string {
        return $this->isRequired($field)
            ? 'required'
            : 'nullable';
    }

    /*
    |--------------------------------------------------------------------------
    | Is Empty Row
    |--------------------------------------------------------------------------
    */
    protected function isEmptyRow(
        array $row
    ): bool {
        foreach ($row as $value) {

            if (
                $value !== null
                && trim((string) $value) !== ''
            ) {
                return false;
            }
        }

        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | Boolean Parser
    |--------------------------------------------------------------------------
    |
    | Supported:
    | 1, yes, true, active
    | 0, no, false, inactive
    |
    */
    protected function parseBoolean(
        mixed $value
    ): bool {
        if (is_bool($value)) {
            return $value;
        }

        if (
            $value === null
            || $value === ''
        ) {
            return true;
        }

        $value = strtolower(
            trim((string) $value)
        );

        return in_array(
            $value,
            [
                '1',
                'yes',
                'y',
                'true',
                'active',
                'on',
            ],
            true
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Unique Barcode
    |--------------------------------------------------------------------------
    */
    protected function generateUniqueBarcode(): string
    {
        do {

            $barcode = (string) random_int(
                100000000000,
                999999999999
            );

        } while (
            Item::query()
                ->where(
                    'business_id',
                    $this->businessId
                )
                ->where(
                    'barcode',
                    $barcode
                )
                ->exists()
        );

        return $barcode;
    }

    /*
    |--------------------------------------------------------------------------
    | Result Getters
    |--------------------------------------------------------------------------
    */
    public function getImportedCount(): int
    {
        return $this->imported;
    }

    public function getSkippedCount(): int
    {
        return $this->skipped;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}