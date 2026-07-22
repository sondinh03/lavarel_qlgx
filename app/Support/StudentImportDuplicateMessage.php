<?php

namespace App\Support;

use App\Models\CatechismClass;
use App\Models\StudentNew;

class StudentImportDuplicateMessage
{
    public static function duplicateKey(?int $saintId, string $lastName, string $firstName, ?string $birthdayYmd): string
    {
        $fullName = mb_strtolower(trim($lastName . ' ' . $firstName), 'UTF-8');

        return ($saintId ?? '')
            . '_' . $fullName
            . '_' . ($birthdayYmd ?? '');
    }

    public static function forProfileMatch(
        StudentNew $student,
        ?int $importSchoolYearId,
        ?int $importClassId,
        ?string $importClassName = null,
        ?string $importSchoolYearName = null,
    ): string {
        $label = e($student->full_name_with_saint);
        $code = e($student->student_code ?? '—');
        $birthday = $student->birthday?->format('d/m/Y');
        $birthdayPart = $birthday ? ", sinh {$birthday}" : '';

        $parts = [
            "Đã có hồ sơ trong giáo xứ: <strong>{$label}</strong> (mã <strong>{$code}</strong>{$birthdayPart}).",
        ];

        $inImportClass = $importClassId
            ? $student->classes->firstWhere('id', $importClassId)
            : null;
        $inImportYearClass = self::classInSchoolYear($student, $importSchoolYearId);

        if ($inImportClass) {
            $classLabel = e($importClassName ?? $inImportClass->name);
            $yearLabel = $importSchoolYearName ? ' — năm học ' . e($importSchoolYearName) : '';
            $parts[] = "Học sinh này <strong>đã có trong lớp {$classLabel}</strong>{$yearLabel}.";
            $parts[] = '→ Điền mã học sinh vào cột «Mã học sinh» rồi import lại để <strong>cập nhật</strong> thông tin (SĐT, giáo họ, ghi chú…).';
        } elseif ($inImportYearClass) {
            $yearLabel = e($importSchoolYearName ?? $inImportYearClass->schoolYear?->name ?? 'năm học hiện tại');
            $parts[] = 'Đang học lớp <strong>' . e($inImportYearClass->name) . "</strong> — năm học {$yearLabel}.";
            $parts[] = '→ Không thể import tạo mới. Nếu cần chuyển lớp, hãy xử lý trên trang <strong>Học sinh</strong> (gỡ khỏi lớp cũ rồi ghi danh lại).';
        } elseif ($latest = self::latestEnrollment($student)) {
            $parts[] = 'Lần ghi danh gần nhất: lớp <strong>' . e($latest->name) . '</strong> — năm học ' . e($latest->schoolYear?->name ?? '—') . '.';
            if ($importSchoolYearName) {
                $parts[] = 'Chưa xếp lớp năm học <strong>' . e($importSchoolYearName) . '</strong>.';
            } else {
                $parts[] = 'Chưa được xếp lớp trong năm học đang import.';
            }
            $parts[] = "→ Không tạo hồ sơ mới. Vào <strong>Học sinh → Ghi danh → Học sinh có sẵn</strong> để thêm vào lớp, hoặc điền mã <strong>{$code}</strong> để cập nhật thông tin.";
        } else {
            if ($importSchoolYearName) {
                $parts[] = 'Hiện <strong>chưa được xếp vào lớp nào</strong> trong năm học ' . e($importSchoolYearName) . '.';
            } else {
                $parts[] = 'Hiện <strong>chưa được xếp vào lớp nào</strong>.';
            }
            $parts[] = '→ Không tạo hồ sơ mới. Vào <strong>Học sinh → Ghi danh → Học sinh có sẵn</strong> để thêm vào lớp.';
        }

        $parts[] = 'Dòng này sẽ bị <strong>bỏ qua</strong> khi xác nhận import.';

        return implode(' ', $parts);
    }

    public static function forInvalidCode(string $studentCode): string
    {
        $code = e($studentCode);

        return "Mã học sinh <strong>{$code}</strong> không tồn tại trong giáo xứ. "
            . '→ Kiểm tra lại mã, hoặc bỏ trống cột mã để hệ thống tự nhận diện theo tên thánh, họ tên và ngày sinh. '
            . 'Dòng này sẽ bị <strong>bỏ qua</strong> khi xác nhận import.';
    }

    public static function forCodeWrongClass(
        StudentNew $student,
        string $studentCode,
        ?int $importSchoolYearId,
        ?string $importClassName = null,
    ): string {
        $code = e($studentCode);
        $label = e($student->full_name_with_saint);
        $inImportYearClass = self::classInSchoolYear($student, $importSchoolYearId);

        $parts = [
            "Mã <strong>{$code}</strong> thuộc hồ sơ <strong>{$label}</strong>.",
        ];

        if ($inImportYearClass) {
            $parts[] = 'Hiện đang ở lớp <strong>' . e($inImportYearClass->name) . '</strong>'
                . ($inImportYearClass->schoolYear?->name ? ' — năm học ' . e($inImportYearClass->schoolYear->name) : '')
                . ($importClassName ? ', không phải lớp <strong>' . e($importClassName) . '</strong> đang import' : ', không phải lớp đang import')
                . '.';
            $parts[] = '→ Không cập nhật qua import này. Xử lý chuyển lớp trên trang <strong>Học sinh</strong> nếu cần.';
        } elseif ($latest = self::latestEnrollment($student)) {
            $parts[] = 'Lần ghi danh gần nhất: lớp <strong>' . e($latest->name) . '</strong> — năm học ' . e($latest->schoolYear?->name ?? '—') . '.';
            $parts[] = '→ Học sinh chưa thuộc lớp đang import. Dùng <strong>Học sinh → Ghi danh → Học sinh có sẵn</strong> hoặc xử lý chuyển lớp trước khi import.';
        } else {
            $parts[] = 'Học sinh chưa thuộc lớp đang import.';
            $parts[] = '→ Dùng <strong>Học sinh → Ghi danh → Học sinh có sẵn</strong> để thêm vào lớp trước, sau đó import lại với mã học sinh để cập nhật thông tin.';
        }

        $parts[] = 'Dòng này sẽ bị <strong>bỏ qua</strong> khi xác nhận import.';

        return implode(' ', $parts);
    }

    public static function forCodeWillUpdate(string $studentCode): string
    {
        $code = e($studentCode);

        return "Học sinh mã <strong>{$code}</strong> đã có trong lớp — thông tin sẽ được <strong>cập nhật</strong> khi xác nhận.";
    }

    private static function classInSchoolYear(StudentNew $student, ?int $schoolYearId): ?CatechismClass
    {
        if (!$schoolYearId) {
            return null;
        }

        return $student->classes
            ->first(fn (CatechismClass $class) => (int) $class->school_year_id === (int) $schoolYearId);
    }

    private static function latestEnrollment(StudentNew $student): ?CatechismClass
    {
        return $student->classes
            ->sortByDesc(fn (CatechismClass $class) => $class->schoolYear?->name ?? '')
            ->first();
    }
}
