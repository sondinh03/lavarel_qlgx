<?php

namespace App\Actions\Student;

use App\Imports\StudentPreviewImport;
use App\Models\CatechismClass;
use App\Models\Holymanagement;
use App\Models\ParishGroup;
use App\Models\StudentNew;
use App\Support\ExcelDateParser;
use Maatwebsite\Excel\Facades\Excel;

class ImportStudentAction
{
    /**
     * Import students từ file Excel và xếp vào lớp.
     *
     * @param  mixed  $file      Livewire TemporaryUploadedFile
     * @param  int    $parishId
     * @param  int    $classId   ID lớp cần xếp vào
     * @return array{imported: int, skipped: int, errors: array}
     */
    public function handle(mixed $file, int $parishId, int $classId): array
    {
        $catechismClass = CatechismClass::findOrFail($classId);

        // Cache lookups để tránh N+1
        $saintMap = Holymanagement::pluck('id', 'name')
            ->mapWithKeys(fn($id, $name) => [trim($name) => $id])
            ->toArray();

        $parishGroupMap = ParishGroup::active()
            ->pluck('id', 'name')
            ->toArray();

        $rows = Excel::toArray(new StudentPreviewImport, $file)[0] ?? [];

        $imported          = 0;
        $skipped_empty     = 0;
        $skipped_duplicate = 0;
        $errors            = [];

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // +2 vì row 1 là header

            // Bỏ qua dòng trống
            if (empty(trim($row['ho_ten_dem'] ?? '')) && empty(trim($row['ten'] ?? ''))) {
                $skipped_empty++;
                continue;
            }

            try {
                // Parse ngày sinh trước để dùng cho duplicate check
                $birthday = null;
                if (!empty($row['ngay_sinh'])) {
                    $birthday = ExcelDateParser::parse($row['ngay_sinh']);
                }

                // Kiểm tra trùng học sinh — skip nếu đã tồn tại
                $fullName = mb_strtolower(trim(($row['ho_ten_dem'] ?? '') . ' ' . ($row['ten'] ?? '')), 'UTF-8');
                $isDuplicate = StudentNew::whereRaw(
                    "LOWER(CONCAT(TRIM(last_name), ' ', TRIM(first_name))) = ?",
                    [$fullName]
                )->where(function ($q) use ($birthday) {
                    if ($birthday) {
                        $q->whereDate('birthday', $birthday);
                    } else {
                        $q->whereNull('birthday');
                    }
                })->exists();

                if ($isDuplicate) {
                    $skipped_duplicate++;
                    continue;
                }

                // Resolve saint_id từ tên thánh
                $saintId = null;
                if (!empty(trim($row['ten_thanh'] ?? ''))) {
                    $saintId = $saintMap[trim($row['ten_thanh'])] ?? null;
                }

                // Resolve parish_group_id từ tên giáo họ
                $parishGroupId = null;
                if (!empty(trim($row['giao_ho'] ?? ''))) {
                    $parishGroupId = $parishGroupMap[trim($row['giao_ho'])] ?? null;
                }

                // Parse giới tính
                $gender      = 'male';
                $gioiTinhRaw = mb_strtolower(trim($row['gioi_tinh'] ?? ''), 'UTF-8');
                if (in_array($gioiTinhRaw, ['nữ', 'nu', 'female', 'f', '0'])) {
                    $gender = 'female';
                }

                $student = StudentNew::create([
                    'last_name'       => trim($row['ho_ten_dem'] ?? ''),
                    'first_name'      => trim($row['ten'] ?? ''),
                    'saint_id'        => $saintId,
                    'gender'          => $gender,
                    'birthday'        => $birthday,
                    'father_name'     => trim($row['ho_ten_bo'] ?? '') ?: null,
                    'mother_name'     => trim($row['ho_ten_me'] ?? '') ?: null,
                    'parish_group_id' => $parishGroupId,
                    'parish_id'       => $parishId,
                    'is_active'       => true,
                    'phone'           => trim($row['so_dien_thoai'] ?? '') ?: null,
                    'email'           => trim($row['email'] ?? '') ?: null,
                    'note'            => trim($row['ghi_chu'] ?? '') ?: null,
                ]);

                $student->classes()->attach($catechismClass->id, [
                    'enrolled_at' => now(),
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);

                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Dòng {$rowNumber}: " . $e->getMessage();
            }
        }

        return compact('imported', 'skipped_empty', 'skipped_duplicate', 'errors');
    }

    public function handleFromArray(array $rows, int $parishId, int $classId): array
    {
        $catechismClass = CatechismClass::findOrFail($classId);

        $saintMap = Holymanagement::pluck('id', 'name')
            ->mapWithKeys(fn($id, $name) => [trim($name) => $id])
            ->toArray();

        $parishGroupMap = ParishGroup::active()
            ->pluck('id', 'name')
            ->toArray();

        $imported          = 0;
        $skipped_empty     = 0;
        $skipped_duplicate = 0;
        $errors            = [];

        foreach ($rows as $row) {
            $rowNumber = $row['row_number'] ?? '?';

            // Bỏ qua dòng trống
            if (empty(trim($row['ho_ten_dem'] ?? '')) && empty(trim($row['ten'] ?? ''))) {
                $skipped_empty++;
                continue;
            }

            // Bỏ qua duplicate đã được đánh dấu từ bước preview
            if (!empty($row['is_duplicate'])) {
                $skipped_duplicate++;
                continue;
            }

            try {
                $birthday = null;
                if (!empty($row['ngay_sinh'])) {
                    $birthday = ExcelDateParser::parse($row['ngay_sinh']);
                }

                // Double-check duplicate tại thời điểm import (tránh race condition)
                $fullName = mb_strtolower(trim(($row['ho_ten_dem'] ?? '') . ' ' . ($row['ten'] ?? '')), 'UTF-8');
                $isDuplicate = StudentNew::whereRaw(
                    "LOWER(CONCAT(TRIM(last_name), ' ', TRIM(first_name))) = ?",
                    [$fullName]
                )->where(function ($q) use ($birthday) {
                    if ($birthday) {
                        $q->whereDate('birthday', $birthday);
                    } else {
                        $q->whereNull('birthday');
                    }
                })->exists();

                if ($isDuplicate) {
                    $skipped_duplicate++;
                    continue;
                }

                // Resolve saint_id
                $saintId = null;
                if (!empty(trim($row['ten_thanh'] ?? ''))) {
                    $saintId = $saintMap[trim($row['ten_thanh'])] ?? null;
                }

                // Resolve parish_group_id
                $parishGroupId = null;
                if (!empty(trim($row['giao_ho'] ?? ''))) {
                    $parishGroupId = $parishGroupMap[trim($row['giao_ho'])] ?? null;
                }

                // Parse giới tính
                $gender      = 'male';
                $gioiTinhRaw = mb_strtolower(trim($row['gioi_tinh'] ?? ''), 'UTF-8');
                if (in_array($gioiTinhRaw, ['nữ', 'nu', 'female', 'f', '0'])) {
                    $gender = 'female';
                }

                $student = StudentNew::create([
                    'last_name'       => trim($row['ho_ten_dem'] ?? ''),
                    'first_name'      => trim($row['ten'] ?? ''),
                    'saint_id'        => $saintId,
                    'gender'          => $gender,
                    'birthday'        => $birthday,
                    'father_name'     => trim($row['ho_ten_bo'] ?? '') ?: null,
                    'mother_name'     => trim($row['ho_ten_me'] ?? '') ?: null,
                    'parish_group_id' => $parishGroupId,
                    'parish_id'       => $parishId,
                    'is_active'       => true,
                    'phone'           => trim($row['so_dien_thoai'] ?? '') ?: null,
                    'email'           => trim($row['email'] ?? '') ?: null,
                    'note'            => trim($row['ghi_chu'] ?? '') ?: null,
                ]);

                $student->classes()->attach($catechismClass->id, [
                    'enrolled_at' => now(),
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);

                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Dòng {$rowNumber}: " . $e->getMessage();
            }
        }

        return compact('imported', 'skipped_empty', 'skipped_duplicate', 'errors');
    }
}
