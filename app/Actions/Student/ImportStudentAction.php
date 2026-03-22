<?php

namespace App\Actions\Student;

use App\Imports\StudentPreviewImport;
use App\Models\CatechismClass;
use App\Models\Holymanagement;
use App\Models\ParishGroup;
use App\Models\StudentNew;
use App\Support\ExcelDateParser;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Shared\Date;

use function PHPUnit\Framework\returnArgument;

class ImportStudentAction
{
    /**
     * Import students từ file Excel và xếp vào lớp.
     *
     * @param  mixed   $file      Livewire TemporaryUploadedFile
     * @param  int     $parishId
     * @param  int     $classId   ID lớp cần xếp vào
     * @return array{imported: int, skipped: int, errors: array}
     */
    public function handle(mixed $file, int $parishId, int $classId): array
    {
        $catechismClass = CatechismClass::findOrFail($classId);

        // Cache lookups để tránh N+1
        $saintMap       = Holymanagement::pluck('id', 'name')->mapWithKeys(fn($id, $name) => [trim($name) => $id])
            ->toArray();
        $parishGroupMap = ParishGroup::active()
            ->pluck('id', 'name')
            ->toArray();

        $rows = Excel::toArray(new StudentPreviewImport, $file)[0] ?? [];

        $imported = 0;
        $skipped  = 0;
        $errors   = [];

        try {
            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2; // +2 vì row 1 là header

                // Bỏ qua dòng trống
                if (empty(trim($row['ho_ten_dem'] ?? '')) && empty(trim($row['ten'] ?? ''))) {
                    $skipped++;
                    continue;
                }

                try {
                    // Resolve saint_id từ tên thánh
                    $saintId = null;
                    if (!empty(trim($row['ten_thanh'] ?? ''))) {
                        $saintName = trim($row['ten_thanh']);
                        $saintId   = $saintMap[$saintName] ?? null;
                    }

                    // Resolve parish_group_id từ tên giáo họ
                    $parishGroupId = null;
                    if (!empty(trim($row['giao_ho'] ?? ''))) {
                        $groupName     = trim($row['giao_ho']);
                        $parishGroupId = $parishGroupMap[$groupName] ?? null;
                    }

                    // Parse ngày sinh
                    $birthday = null;
                    if (!empty($row['ngay_sinh'])) {
                        $birthday = ExcelDateParser::parse($row['ngay_sinh']);
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
        } catch (\Exception $e) {
            throw $e;
        }

        return compact('imported', 'skipped', 'errors');
    }

    private function parseDate(mixed $value): ?string
    {
        if (empty($value)) return null;

        if (is_numeric($value)) {
            // Excel date serial number
            return Date::excelToDateTimeObject($value)->format('Y-m-d');
        }

        try {
            $date = Carbon::createFromFormat('d/m/Y', trim($value));

            // Kiểm tra overflow (31/02, 15/13...)
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
