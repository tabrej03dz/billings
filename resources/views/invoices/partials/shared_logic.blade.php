@php
    $termsText = $inv->terms ?? null;

    /** @var \App\Models\Invoice $inv */
    $b = $biz ?? ($inv->business ?? null);
    $c = $client ?? ($inv->client ?? null);
    $items = $items ?? collect();

    $fmt0 = fn($v) => number_format((float)$v, 0, '.', '');
    $fmt2 = fn($v) => number_format((float)$v, 2, '.', '');
    $dmy  = fn($date) => $date ? \Carbon\Carbon::parse($date)->format('d/m/Y') : '';

    $taxable      = (float)($subtotal ?? ($inv->subtotal ?? 0));
    $tax_total_db = (float)($tax_total ?? ($inv->tax_amount ?? 0));

    $cgst_db = (float)($cgst_amount ?? ($inv->cgst_amount ?? 0));
    $sgst_db = (float)($sgst_amount ?? ($inv->sgst_amount ?? 0));
    $igst_db = (float)($igst_amount ?? ($inv->igst_amount ?? 0));

    $grand_db = (float)($grand_total ?? ($inv->total ?? 0));
    $received_db = (float)($received ?? ($inv->received_amount ?? 0));

    $pay = $payRow ?? null;
    $cashAmt   = (float)($pay->cash_amount ?? 0);
    $onlineAmt = (float)($pay->online_amount ?? 0);
    $cardAmt   = (float)($pay->card_amount ?? 0);
    $chequeAmt = (float)($pay->cheque_amount ?? 0);
    $creditExcess = (float)($pay->credit_sales_excess_amount ?? 0);
    $advanceAmt   = (float)($pay->advance_amount ?? 0);

    $receivedTot = $inv->received_amount ?? 0;
    $balanceNow = (float)($balance ?? ($inv->balance ?? max(0, $grand_db - $receivedTot)));

    $taxPercent = (float)($inv->tax_percent ?? 0);
    $isIGST = $igst_db > 0;

    $finalTax = $isIGST
        ? $igst_db
        : (($cgst_db + $sgst_db) > 0 ? ($cgst_db + $sgst_db) : $tax_total_db);

    $finalTax   = round((float)$finalTax, 2);
    $finalTotal = round((float)$grand_db, 2);

    function inr_words($amount)
    {
        $amount = (float)$amount;
        $rupees = (int) floor($amount);
        $paise  = (int) round(($amount - $rupees) * 100);

        $ones = [
            '', 'One','Two','Three','Four','Five','Six','Seven','Eight','Nine','Ten',
            'Eleven','Twelve','Thirteen','Fourteen','Fifteen','Sixteen','Seventeen','Eighteen','Nineteen'
        ];
        $tens = ['', '', 'Twenty','Thirty','Forty','Fifty','Sixty','Seventy','Eighty','Ninety'];

        $twoDigits = function($n) use ($ones,$tens){
            $n = (int)$n;
            if ($n == 0) return '';
            if ($n < 20) return $ones[$n];
            return trim($tens[(int)($n/10)] . ' ' . $ones[$n%10]);
        };

        $parts = [];

        if ($rupees >= 10000000) {
            $cr = (int) floor($rupees / 10000000);
            $parts[] = $twoDigits($cr) . ' Crore';
            $rupees = $rupees % 10000000;
        }
        if ($rupees >= 100000) {
            $lk = (int) floor($rupees / 100000);
            $parts[] = $twoDigits($lk) . ' Lakh';
            $rupees = $rupees % 100000;
        }
        if ($rupees >= 1000) {
            $th = (int) floor($rupees / 1000);
            $parts[] = $twoDigits($th) . ' Thousand';
            $rupees = $rupees % 1000;
        }
        if ($rupees >= 100) {
            $hd = (int) floor($rupees / 100);
            $parts[] = $ones[$hd] . ' Hundred';
            $rupees = $rupees % 100;
        }
        if ($rupees > 0) {
            $parts[] = $twoDigits($rupees);
        }

        $words = trim(implode(' ', array_filter($parts)));
        if ($words === '') $words = 'Zero';

        $result = $words . ' Rupees';
        if ($paise > 0) $result .= ' and ' . $twoDigits($paise) . ' Paise';
        return $result;
    }

    $invoiceNo   = $inv->invoice_number ?? $inv->invoice_no ?? '-';
    $invoiceDate = $inv->invoice_date ?? $inv->date ?? null;

    $gstin  = $c->gstin ?? $c->gst_number ?? $c->gst ?? '';
    $pan    = $c->pan ?? $c->pan_number ?? '';
    $pos    = $c->place_of_supply ?? $c->state ?? '';
    $mobile = $c->mobile ?? $c->phone ?? $c->phone1 ?? '';

    $b_addr   = trim((string)($b->address ?? ''));
    $b_city   = trim((string)($b->city ?? ''));
    $b_pin    = trim((string)($b->pin ?? ''));
    $b_state  = trim((string)($b->state ?? ''));
    $b_mobile = $b->mobile ?? $b->phone ?? '';
    $b_email  = $b->email ?? '';
    $b_gstin  = $b->gstin ?? ($inv->gst_no ?? '');

    $single = ($items->count() === 1);
@endphp