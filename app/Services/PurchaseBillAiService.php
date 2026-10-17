<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class PurchaseBillAiService
{
    public function extract(UploadedFile $file): array
    {
        $apiKey = (string) config('services.openai.key');
        $model = (string) config('services.openai.purchase_bill_model', 'gpt-5.6-luna');

        if ($apiKey === '') {
            throw new RuntimeException('OPENAI_API_KEY is not configured.');
        }

        $mime = $file->getMimeType() ?: $file->getClientMimeType() ?: 'application/octet-stream';
        $bytes = file_get_contents($file->getRealPath());

        if ($bytes === false) {
            throw new RuntimeException('Unable to read the uploaded purchase bill.');
        }

        $base64 = base64_encode($bytes);

        $documentPart = str_starts_with($mime, 'image/')
            ? [
                'type' => 'input_image',
                'image_url' => "data:{$mime};base64,{$base64}",
                'detail' => 'high',
            ]
            : [
                'type' => 'input_file',
                'filename' => $file->getClientOriginalName() ?: 'purchase-bill.pdf',
                'file_data' => $base64,
            ];

        $response = Http::withToken($apiKey)
            ->acceptJson()
            ->asJson()
            ->connectTimeout(15)
            ->timeout(120)
            ->retry(2, 800, throw: false)
            ->post('https://api.openai.com/v1/responses', [
                'model' => $model,
                'input' => [
                    [
                        'role' => 'user',
                        'content' => [
                            [
                                'type' => 'input_text',
                                'text' => $this->prompt(),
                            ],
                            $documentPart,
                        ],
                    ],
                ],
                'text' => [
                    'format' => [
                        'type' => 'json_schema',
                        'name' => 'purchase_bill_extraction',
                        'strict' => true,
                        'schema' => $this->schema(),
                    ],
                ],
            ]);

        if (!$response->successful()) {
            $message = data_get($response->json(), 'error.message')
                ?: 'OpenAI could not read this purchase bill.';

            throw new RuntimeException($message);
        }

        $text = $this->extractOutputText($response->json());

        if ($text === '') {
            throw new RuntimeException('AI returned an empty purchase bill result.');
        }

        $decoded = json_decode($text, true);

        if (!is_array($decoded)) {
            throw new RuntimeException('AI returned an invalid purchase bill response.');
        }

        return $this->sanitize($decoded);
    }

    private function extractOutputText(array $response): string
    {
        foreach (($response['output'] ?? []) as $output) {
            foreach (($output['content'] ?? []) as $content) {
                if (($content['type'] ?? null) === 'output_text' && isset($content['text'])) {
                    return trim((string) $content['text']);
                }
            }
        }

        return trim((string) ($response['output_text'] ?? ''));
    }

    private function prompt(): string
    {
        return <<<'PROMPT'
Read this supplier purchase invoice/bill carefully and return only the data required by the JSON schema.

Rules:
1. Do not invent values. If a value is not visible, return null (or 0 only where schema requires a numeric default).
2. invoice_date must be YYYY-MM-DD when a date is visible.
3. tax_type must be "inter_state" when the bill uses IGST, otherwise "intra_state" when it uses CGST+SGST. If unclear, infer only from visible tax columns; otherwise use "intra_state".
4. For every bill line, extract the actual printed item/product name, SKU/code if visible, quantity, unit, rate before GST, GST percentage, taxable amount and line total.
5. gst_rate means the combined GST rate. Example: CGST 9% + SGST 9% => gst_rate 18.
6. rate should be the per-unit taxable rate before GST whenever possible.
7. Do not include freight/round-off/discount as an item unless it is clearly a stock item line.
8. paid_amount should be null unless the bill explicitly shows an amount already paid. Do not assume the invoice total was paid.
PROMPT;
    }

    private function schema(): array
    {
        $nullableString = ['type' => ['string', 'null']];
        $nullableNumber = ['type' => ['number', 'null']];

        return [
            'type' => 'object',
            'additionalProperties' => false,
            'properties' => [
                'supplier_name' => $nullableString,
                'supplier_gstin' => $nullableString,
                'supplier_mobile' => $nullableString,
                'invoice_no' => $nullableString,
                'invoice_date' => $nullableString,
                'tax_type' => [
                    'type' => 'string',
                    'enum' => ['intra_state', 'inter_state'],
                ],
                'discount_amount' => $nullableNumber,
                'round_off' => $nullableNumber,
                'paid_amount' => $nullableNumber,
                'grand_total' => $nullableNumber,
                'items' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'additionalProperties' => false,
                        'properties' => [
                            'name' => ['type' => 'string'],
                            'sku' => $nullableString,
                            'hsn_sac' => $nullableString,
                            'qty' => ['type' => 'number'],
                            'unit' => $nullableString,
                            'rate' => $nullableNumber,
                            'gst_rate' => $nullableNumber,
                            'taxable_amount' => $nullableNumber,
                            'line_total' => $nullableNumber,
                        ],
                        'required' => [
                            'name',
                            'sku',
                            'hsn_sac',
                            'qty',
                            'unit',
                            'rate',
                            'gst_rate',
                            'taxable_amount',
                            'line_total',
                        ],
                    ],
                ],
            ],
            'required' => [
                'supplier_name',
                'supplier_gstin',
                'supplier_mobile',
                'invoice_no',
                'invoice_date',
                'tax_type',
                'discount_amount',
                'round_off',
                'paid_amount',
                'grand_total',
                'items',
            ],
        ];
    }

    private function sanitize(array $data): array
    {
        $data['supplier_name'] = $this->nullableTrim($data['supplier_name'] ?? null);
        $data['supplier_gstin'] = $this->nullableTrim($data['supplier_gstin'] ?? null);
        $data['supplier_mobile'] = $this->nullableTrim($data['supplier_mobile'] ?? null);
        $data['invoice_no'] = $this->nullableTrim($data['invoice_no'] ?? null);
        $data['invoice_date'] = $this->nullableTrim($data['invoice_date'] ?? null);
        $data['tax_type'] = in_array(($data['tax_type'] ?? null), ['intra_state', 'inter_state'], true)
            ? $data['tax_type']
            : 'intra_state';

        foreach (['discount_amount', 'round_off', 'paid_amount', 'grand_total'] as $key) {
            $data[$key] = isset($data[$key]) && is_numeric($data[$key])
                ? (float) $data[$key]
                : null;
        }

        $data['items'] = collect($data['items'] ?? [])
            ->filter(fn ($row) => is_array($row) && trim((string) ($row['name'] ?? '')) !== '')
            ->map(function (array $row) {
                return [
                    'name' => trim((string) ($row['name'] ?? '')),
                    'sku' => $this->nullableTrim($row['sku'] ?? null),
                    'hsn_sac' => $this->nullableTrim($row['hsn_sac'] ?? null),
                    'qty' => max(0.001, (float) ($row['qty'] ?? 1)),
                    'unit' => $this->nullableTrim($row['unit'] ?? null),
                    'rate' => isset($row['rate']) && is_numeric($row['rate']) ? (float) $row['rate'] : null,
                    'gst_rate' => isset($row['gst_rate']) && is_numeric($row['gst_rate']) ? (float) $row['gst_rate'] : null,
                    'taxable_amount' => isset($row['taxable_amount']) && is_numeric($row['taxable_amount']) ? (float) $row['taxable_amount'] : null,
                    'line_total' => isset($row['line_total']) && is_numeric($row['line_total']) ? (float) $row['line_total'] : null,
                ];
            })
            ->values()
            ->all();

        return $data;
    }

    private function nullableTrim(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
