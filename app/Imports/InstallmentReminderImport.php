<?php

namespace App\Imports;

use App\Models\InstallmentReminder;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class InstallmentReminderImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
//    public function collection(Collection $rows)
//    {
//        foreach ($rows as $row) {
//
//            $contact = trim((string)($row['contact_number'] ?? ''));
//            if ($contact === '') continue;
//
//            $reminderDate = $this->parseDate($row['reminder_date'] ?? null);
//            $installDate  = $this->parseDate($row['installment_date'] ?? null);
//
//            if (!$reminderDate || !$installDate) continue;
//
//            $reminderTime = $this->parseTime($row['reminder_time'] ?? null) ?: '10:00:00';
//
//            InstallmentReminder::create([
//                'contact_number'     => $contact,
//                'reminder_date'      => $reminderDate,
//                'reminder_time'      => $reminderTime,
//                'snme_number'        => ($row['snme_number'] ?? null) ?: null,
//                'installment_amount' => (float)($row['installment_amount'] ?? 0),
//                'installment_date'   => $installDate,
//                'status'             => ($row['status'] ?? 'uploaded') ?: 'uploaded',
//            ]);
//        }
//    }
//
//    private function parseDate($value): ?string
//    {
//        try {
//            if (is_numeric($value)) {
//                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value))
//                    ->format('Y-m-d');
//            }
//            if (!$value) return null;
//            return Carbon::parse($value)->format('Y-m-d');
//        } catch (\Throwable $e) {
//            return null;
//        }
//    }
//
//    private function parseTime($value): ?string
//    {
//        try {
//            if (is_numeric($value)) {
//                $dt = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value);
//                return Carbon::instance($dt)->format('H:i:s');
//            }
//            if (!$value) return null;
//            return Carbon::parse($value)->format('H:i:s');
//        } catch (\Throwable $e) {
//            return null;
//        }
//    }


    public function collection(Collection $rows)
    {
        // ✅ Last non-empty values store karne ke liye
        $last = [
            'contact_number'     => null,
            'reminder_date'      => null,
            'reminder_time'      => null,
            'snme_number'        => null,
            'installment_amount' => null,
            'installment_date'   => null,
            'status'             => null,
        ];

        foreach ($rows as $row) {

            // ✅ Resolve "Same As Above" / empty => last value
            $contactRaw   = $this->resolveValue($row, 'contact_number', $last);
            $reminderRaw  = $this->resolveValue($row, 'reminder_date', $last);
            $timeRaw      = $this->resolveValue($row, 'reminder_time', $last);
            $snmeRaw      = $this->resolveValue($row, 'snme_number', $last);
            $amountRaw    = $this->resolveValue($row, 'installment_amount', $last);
            $installRaw   = $this->resolveValue($row, 'installment_date', $last);
            $statusRaw    = $this->resolveValue($row, 'status', $last);

            // ✅ contact blank => skip
            $contact = trim((string)$contactRaw);
            if ($contact === '') continue;

            // (optional) ✅ Only digits + 91 prefix if 10 digits
            $contact = preg_replace('/\D+/', '', $contact);
            if (strlen($contact) === 10) $contact = '91' . $contact;

            $reminderDate = $this->parseDate($reminderRaw);
            $installDate  = $this->parseDate($installRaw);

            if (!$reminderDate || !$installDate) continue;

            $reminderTime = $this->parseTime($timeRaw) ?: '10:00:00';

            $amount = is_numeric($amountRaw)
                ? (float)$amountRaw
                : (float)preg_replace('/[^\d.]/', '', (string)$amountRaw);

            InstallmentReminder::create([
                'contact_number'     => $contact,
                'reminder_date'      => $reminderDate,
                'reminder_time'      => $reminderTime,
                'snme_number'        => ($snmeRaw ?? null) ? trim((string)$snmeRaw) : null,
                'installment_amount' => $amount,
                'installment_date'   => $installDate,
                'status'             => ($statusRaw ?? 'uploaded') ?: 'uploaded',
                'user_id'            => auth()->user()->id ?? null,
            ]);
        }
    }

    /**
     * ✅ If value is empty or "Same As Above" => returns last value.
     * Otherwise updates last and returns current value.
     */
    private function resolveValue(Collection|array $row, string $key, array &$last)
    {
        $val = $row[$key] ?? null;

        // normalize
        $str = is_string($val) ? trim($val) : $val;

        $isSameAsAbove = is_string($str) && preg_match('/^same\s+as\s+above$/i', $str);

        // Excel merged cells sometimes return null/empty
        if ($val === null || $str === '' || $isSameAsAbove) {
            return $last[$key];
        }

        // ✅ update last with current value
        $last[$key] = $val;

        return $val;
    }

    private function parseDate($value): ?string
    {
        try {
            if (is_numeric($value)) {
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value))
                    ->format('Y-m-d');
            }
            if (!$value) return null;
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function parseTime($value): ?string
    {
        try {
            if (is_numeric($value)) {
                $dt = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value);
                return Carbon::instance($dt)->format('H:i:s');
            }
            if (!$value) return null;
            return Carbon::parse($value)->format('H:i:s');
        } catch (\Throwable $e) {
            return null;
        }
    }


}
