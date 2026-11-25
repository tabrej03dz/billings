<?php

namespace App\Services;

use App\Models\BillRequest;
use App\Models\Business;
use App\Models\Invoice;
use RuntimeException;

class BillRequestTaxConversion
{
    public static function appliesTo(int $businessId): bool
    {
        return $businessId > 0
            && $businessId === (int) config('services.bill_request_invoice.business_id');
    }

    // Both controllers call this inside their existing DB transaction.
    public function convert(BillRequest $billRequest, Business $business, int $userId): Invoice
    {
        $billRequest = BillRequest::query()
            ->lockForUpdate()
            ->findOrFail($billRequest->id);

        $linkedInvoices = Invoice::withoutGlobalScope('business')
            ->where('business_id', $business->id)
            ->where('bil_request_id', (string) $billRequest->id);

        $taxInvoice = (clone $linkedInvoices)->where('invoice_type', 'tax')->first();
        if ($taxInvoice) {
            return $taxInvoice;
        }

        $invoice = $linkedInvoices->where('invoice_type', 'quotation')
            ->lockForUpdate()
            ->first();

        if (!$invoice || (float) $invoice->total <= 0 || !$invoice->items()->exists()) {
            throw new RuntimeException('Is business ke liye valid Bill Request quotation nahi mila.');
        }

        $total = round((float) $invoice->total, 2);
        $paymentData = [];

        // Keep any payment already recorded against this quotation.
        if (!$invoice->payments()->exists() && (float) $invoice->received_amount <= 0) {
            $payload = json_decode($billRequest->full_payload ?? '', true) ?: [];
            $sourceAmount = array_key_exists('source_payment_amount', $payload)
                ? $payload['source_payment_amount']
                : $billRequest->payment_amount;

            $received = round((float) ($sourceAmount ?? 0), 2);

            if ($received < 0 || $received > $total) {
                throw new RuntimeException('Bill Request payment amount quotation total se verify karein.');
            }

            $method = $received > 0
                ? strtolower(trim((string) $billRequest->payment_method))
                : null;

            if ($received > 0) {
                $method = match ($method) {
                    'online', 'neft', 'rtgs', 'imps', 'bank transfer', 'bank_transfer' => 'bank',
                    default => $method,
                };

                $column = match ($method) {
                    'cash' => 'cash_amount',
                    'upi', 'bank' => 'online_amount',
                    'card' => 'card_amount',
                    'cheque' => 'cheque_amount',
                    default => throw new RuntimeException('Bill Request ka payment method verify karein.'),
                };

                $invoice->payments()->create([
                    'business_id' => $business->id,
                    'client_id' => $invoice->client_id,
                    'total_value' => $total,
                    $column => $received,
                    'received_total' => $received,
                    'online_mode' => $column === 'online_amount' ? $method : null,
                    'online_ref' => $billRequest->transaction_id,
                    'bank_name' => $billRequest->bank,
                    'paid_at' => $billRequest->payment_date ?? now(),
                    'notes' => 'Payment imported from Bill Request',
                ]);
            }

            $paymentData = [
                'received_amount' => $received,
                'balance' => round($total - $received, 2),
                'payment_method' => $method,
            ];
        }

        $date = now()->toDateString();
        $prefix = InvoiceNumber::previewPrefix($date, $business->invoice_base_prefix ?: 'INV');
        $number = InvoiceNumber::next((int) $business->id, $date, $prefix, 3, 'tax')['full'];

        $invoice->update([
            'invoice_type' => 'tax',
            'invoice_prefix' => $prefix,
            'invoice_number' => $number,
            'invoice_date' => $date,
            'pdf_url' => null,
            'updated_by' => $userId,
            ...$paymentData,
        ]);

        $api = json_decode($billRequest->api_response ?? '', true) ?: [];
        $billRequest->update([
            'status' => 'processed',
            'remarks' => 'Quotation converted to Tax Invoice: ' . $number,
            'api_response' => json_encode(array_replace($api, [
                'created_invoice_id' => $invoice->id,
                'created_invoice_number' => $number,
                'created_client_id' => $invoice->client_id,
                'invoice_type' => 'tax',
                'invoice_date' => $date,
                'processed_at' => now()->toDateTimeString(),
            ]), JSON_UNESCAPED_UNICODE),
        ]);

        return $invoice;
    }
}