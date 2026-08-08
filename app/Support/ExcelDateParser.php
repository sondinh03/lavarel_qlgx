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
        if ($value === null || $value === '') {
            return null;
        }

        // Excel serial number (kiểu số thật)
        if (is_int($value) || is_float($value)) {
            return Date::excelToDateTimeObject((float) $value)
                ->format('Y-m-d');
        }

        $value = ExcelString::trim($value);

        if ($value === '') {
            return null;
        }

        // Serial dạng text ("41650") — không nhầm với dd/mm/yyyy
        if (is_numeric($value) && ! str_contains($value, '/')) {
            return Date::excelToDateTimeObject((float) $value)
                ->format('Y-m-d');
        }

        try {
            $date = Carbon::createFromFormat('d/m/Y', $value);

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
