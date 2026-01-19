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
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {

            $contact = trim((string)($row['contact_number'] ?? ''));
            if ($contact === '') continue;

            $reminderDate = $this->parseDate($row['reminder_date'] ?? null);
            $installDate  = $this->parseDate($row['installment_date'] ?? null);

            if (!$reminderDate || !$installDate) continue;

            $reminderTime = $this->parseTime($row['reminder_time'] ?? null) ?: '10:00:00';

            InstallmentReminder::create([
                'contact_number'     => $contact,
                'reminder_date'      => $reminderDate,
                'reminder_time'      => $reminderTime,
                'snme_number'        => ($row['snme_number'] ?? null) ?: null,
                'installment_amount' => (float)($row['installment_amount'] ?? 0),
                'installment_date'   => $installDate,
                'status'             => ($row['status'] ?? 'uploaded') ?: 'uploaded',
            ]);
        }
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
