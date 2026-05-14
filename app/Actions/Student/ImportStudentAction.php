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

        $saintMap = Holymanagement::pluck('id', 'name')
            ->mapWithKeys(fn($id, $name) => [trim($name) => $id])
            ->toArray();

        $parishGroupMap = ParishGroup::active()
            ->pluck('id', 'name')
            ->toArray();

        $rows = Excel::toArray(new StudentPreviewImport, $file)[0] ?? [];

        $imported          = 0;
        $updated           = 0;
        $skipped_empty     = 0;
        $skipped_duplicate = 0;
        $errors            = [];

        foreach ($rows as $index => $row) {
            $rowNumber   = $index + 2;
            $studentCode = trim($row['ma_hoc_sinh'] ?? '');

            if (empty(trim($row['ho_ten_dem'] ?? '')) && empty(trim($row['ten'] ?? ''))) {
                $skipped_empty++;
                continue;
            }

            try {
                $birthday = null;
                if (!empty($row['ngay_sinh'])) {
                    $birthday = ExcelDateParser::parse($row['ngay_sinh']);
                }

                $saintId = null;
                if (!empty(trim($row['ten_thanh'] ?? ''))) {
                    $saintId = $saintMap[trim($row['ten_thanh'])] ?? null;
                }

                $parishGroupId = null;
                if (!empty(trim($row['giao_ho'] ?? ''))) {
                    $parishGroupId = $parishGroupMap[trim($row['giao_ho'])] ?? null;
                }

                $gender      = 'male';
                $gioiTinhRaw = mb_strtolower(trim($row['gioi_tinh'] ?? ''), 'UTF-8');
                if (in_array($gioiTinhRaw, ['nữ', 'nu', 'female', 'f', '0'])) {
                    $gender = 'female';
                }

                $data = [
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
                ];

                if ($studentCode) {
                    // Có mã → tìm và update
                    $student = StudentNew::where('student_code', $studentCode)
                        ->where('parish_id', $parishId)
                        ->first();

                    if (!$student) {
                        $errors[] = "Dòng {$rowNumber}: Không tìm thấy học sinh với mã '{$studentCode}'";
                        continue;
                    }

                    $student->update($data);
                    $updated++;
                } else {
                    // Không có mã → check duplicate rồi create
                    $fullName    = mb_strtolower(trim(($row['ho_ten_dem'] ?? '') . ' ' . ($row['ten'] ?? '')), 'UTF-8');
                    $isDuplicate = StudentNew::whereRaw(
                        "LOWER(CONCAT(TRIM(last_name), ' ', TRIM(first_name))) = ?",
                        [$fullName]
                    )->where(function ($q) use ($birthday) {
                        $birthday ? $q->whereDate('birthday', $birthday) : $q->whereNull('birthday');
                    })->exists();

                    if ($isDuplicate) {
                        $skipped_duplicate++;
                        continue;
                    }

                    $student = StudentNew::create($data);
                    $imported++;
                }

                $student->classes()->syncWithoutDetaching([
                    $catechismClass->id => ['enrolled_at' => now(), 'updated_at' => now()],
                ]);
            } catch (\Exception $e) {
                $errors[] = "Dòng {$rowNumber}: " . $e->getMessage();
            }
        }

        return compact('imported', 'updated', 'skipped_empty', 'skipped_duplicate', 'errors');
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
        $updated           = 0;
        $skipped_empty     = 0;
        $skipped_duplicate = 0;
        $errors            = [];

        foreach ($rows as $row) {
            $rowNumber   = $row['row_number'] ?? '?';
            $studentCode = trim($row['ma_hoc_sinh'] ?? '');

            if (empty(trim($row['ho_ten_dem'] ?? '')) && empty(trim($row['ten'] ?? ''))) {
                $skipped_empty++;
                continue;
            }

            // Không có mã + đã đánh dấu duplicate từ preview → skip
            if (!$studentCode && !empty($row['is_duplicate'])) {
                $skipped_duplicate++;
                continue;
            }

            try {
                $birthday = null;
                if (!empty($row['ngay_sinh'])) {
                    $birthday = ExcelDateParser::parse($row['ngay_sinh']);
                }

                $saintId = null;
                if (!empty(trim($row['ten_thanh'] ?? ''))) {
                    $saintId = $saintMap[trim($row['ten_thanh'])] ?? null;
                }

                $parishGroupId = null;
                if (!empty(trim($row['giao_ho'] ?? ''))) {
                    $parishGroupId = $parishGroupMap[trim($row['giao_ho'])] ?? null;
                }

                $gender      = 'male';
                $gioiTinhRaw = mb_strtolower(trim($row['gioi_tinh'] ?? ''), 'UTF-8');
                if (in_array($gioiTinhRaw, ['nữ', 'nu', 'female', 'f', '0'])) {
                    $gender = 'female';
                }

                $data = [
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
                ];

                if ($studentCode) {
                    // Có mã → update (will_update đã được đánh dấu từ preview)
                    $student = StudentNew::where('student_code', $studentCode)
                        ->where('parish_id', $parishId)
                        ->first();

                    if (!$student) {
                        $errors[] = "Dòng {$rowNumber}: Không tìm thấy học sinh với mã '{$studentCode}'";
                        continue;
                    }

                    $belongsToClass = $student->classes()
                        ->where('class_id', $catechismClass->id)
                        ->exists();

                    if (!$belongsToClass) {
                        $errors[] = "Dòng {$rowNumber}: Học sinh '{$studentCode}' không thuộc lớp này, không thể cập nhật";
                        continue;
                    }

                    $belongsToClass = $student->classes()
                        ->where('class_id', $catechismClass->id)
                        ->exists();

                    if (!$belongsToClass) {
                        $errors[] = "Dòng {$rowNumber}: Học sinh '{$studentCode}' không thuộc lớp này, không thể cập nhật";
                        continue;
                    }

                    $student->update($data);
                    $updated++;
                } else {
                    // Không có mã → double-check duplicate rồi create
                    $fullName    = mb_strtolower(trim(($row['ho_ten_dem'] ?? '') . ' ' . ($row['ten'] ?? '')), 'UTF-8');
                    $isDuplicate = StudentNew::whereRaw(
                        "LOWER(CONCAT(TRIM(last_name), ' ', TRIM(first_name))) = ?",
                        [$fullName]
                    )->where(function ($q) use ($birthday) {
                        $birthday ? $q->whereDate('birthday', $birthday) : $q->whereNull('birthday');
                    })->exists();

                    if ($isDuplicate) {
                        $skipped_duplicate++;
                        continue;
                    }

                    $student = StudentNew::create($data);
                    $imported++;
                }

                $student->classes()->syncWithoutDetaching([
                    $catechismClass->id => ['enrolled_at' => now(), 'updated_at' => now()],
                ]);
            } catch (\Exception $e) {
                $errors[] = "Dòng {$rowNumber}: " . $e->getMessage();
            }
        }

        return compact('imported', 'updated', 'skipped_empty', 'skipped_duplicate', 'errors');
    }
}
