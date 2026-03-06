<?php

namespace App\Support;

use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class ExcelDateParser
{
    /**
     * Parse ngày từ Excel (serial number hoặc string dd/MM/yyyy)
     * về định dạng Y-m-d cho DB.
     */
    public static function parse(mixed $value): ?string
    {
        if (empty($value)) return null;

        // Excel serial number
        if (is_numeric($value)) {
            return Date::excelToDateTimeObject((float) $value)
                ->format('Y-m-d');
        }

        try {
            $date = Carbon::createFromFormat('d/m/Y', trim($value));

            // Kiểm tra overflow: 31/02, 15/13...
            $errors = Carbon::getLastErrors();
            if ($errors['warning_count'] > 0 || $errors['error_count'] > 0) {
                return null;
            }

            return $date->format('Y-m-d');
        } catch (\Exception) {
            return null;
        }
    }
}
