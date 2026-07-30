<?php

namespace App\Actions\Teacher;

use App\Models\Holymanagement;
use App\Models\ParishGroup;
use App\Models\Teacher;
use App\Models\User;
use App\Support\CatechistDefaultPassword;
use App\Support\ExcelDateParser;
use App\Support\TeacherImportDuplicateMessage;
use App\Support\UserAccountEmailResolver;

class ImportTeacherAction
{
    /**
     * Import teachers từ rows đã được preview/validate.
     * Nhận $rows trực tiếp thay vì parse file lần 2.
     *
     * @param  array  $rows     Mảng rows từ TeacherImportPreview::$rows
     * @param  int    $parishId
     * @return array{imported: int, skipped: int, skipped_duplicate: int, errors: array}
     */
    public function handle(array $rows, int $parishId): array
    {
        // Cache lookups để tránh N+1
        $saintMap = Holymanagement::pluck('id', 'name')
            ->mapWithKeys(fn($id, $name) => [trim($name) => $id])
            ->toArray();

        $parishGroupMap = ParishGroup::active()
            ->pluck('id', 'name')
            ->mapWithKeys(fn($id, $name) => [trim($name) => $id])
            ->toArray();

        // Khoá hồ sơ đã có — chặn trùng ngay cả khi preview đã cũ
        $existingKeys = [];
        Teacher::where('parish_id', $parishId)
            ->get(['saint_id', 'last_name', 'first_name', 'birthday'])
            ->each(function (Teacher $teacher) use (&$existingKeys) {
                $existingKeys[TeacherImportDuplicateMessage::duplicateKey(
                    $teacher->saint_id,
                    $teacher->last_name,
                    $teacher->first_name,
                    $teacher->birthday?->format('Y-m-d'),
                )] = true;
            });

        $imported          = 0;
        $skipped           = 0;
        $skipped_duplicate = 0;
        $errors            = [];

        foreach ($rows as $row) {
            $rowNumber = $row['row_number'];

            // Họ tên tách thành 2 cột: ho_dem (họ + tên đệm) và ten
            $lastName  = trim($row['ho_dem'] ?? '');
            $firstName = trim($row['ten'] ?? '');

            // Bỏ qua dòng trống
            if ($lastName === '' && $firstName === '') {
                $skipped++;
                continue;
            }

            if ($firstName === '') {
                $errors[] = "Dòng {$rowNumber}: Thiếu cột \"Tên\" — bỏ qua dòng";
                $skipped++;
                continue;
            }

            // Preview đã đánh dấu trùng (hồ sơ đã có, hoặc lặp trong file)
            if (!empty($row['is_duplicate'])) {
                $skipped_duplicate++;
                continue;
            }

            $fullName = trim($lastName . ' ' . $firstName);

            try {
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

                // Parse ngày sinh
                $birthday = null;
                if (!empty($row['ngay_sinh'])) {
                    $birthday = ExcelDateParser::parse($row['ngay_sinh']);
                }

                // Chốt lại lần cuối theo tên thánh + họ tên + ngày sinh
                $duplicateKey = TeacherImportDuplicateMessage::duplicateKey(
                    $saintId,
                    $lastName,
                    $firstName,
                    $birthday
                );

                if (isset($existingKeys[$duplicateKey])) {
                    $skipped_duplicate++;
                    continue;
                }

                // Parse giới tính
                $gender      = 'male';
                $gioiTinhRaw = mb_strtolower(trim($row['gioi_tinh'] ?? ''), 'UTF-8');
                if (in_array($gioiTinhRaw, ['nữ', 'nu', 'female', 'f', '0'])) {
                    $gender = 'female';
                }

                $phone = trim((string) ($row['so_dien_thoai'] ?? '')) ?: null;
                $email = trim($row['email'] ?? '') ?: null;
                $normalizedPhone = $phone
                    ? UserAccountEmailResolver::normalizePhone((string) $phone)
                    : null;

                if ($phone && $normalizedPhone === null) {
                    $errors[] = "Dòng {$rowNumber}: Số điện thoại không hợp lệ — bỏ qua dòng";
                    $skipped++;
                    continue;
                }

                // Tạo user account nếu tao_tai_khoan = có
                $userId      = null;
                $taotk       = mb_strtolower(trim($row['tao_tai_khoan'] ?? ''), 'UTF-8');
                $shouldCreate = in_array($taotk, ['có', 'co', 'yes', '1']);

                if ($shouldCreate) {
                    try {
                        $accountEmail = UserAccountEmailResolver::resolveAccountEmail($email, $normalizedPhone);
                    } catch (\InvalidArgumentException $e) {
                        $errors[] = "Dòng {$rowNumber}: {$e->getMessage()}";
                        $skipped++;
                        continue;
                    }

                    if (User::where('email', $accountEmail)->exists()) {
                        // Không throw — chỉ ghi warning, vẫn tạo teacher
                        $errors[] = "Dòng {$rowNumber}: \"{$accountEmail}\" đã tồn tại — bỏ qua tạo tài khoản";
                    } else {
                        $user = User::create([
                            'name'      => $fullName,
                            'email'     => $accountEmail,
                            'password'  => CatechistDefaultPassword::fromBirthday($birthday),
                            'parish_id' => $parishId,
                        ]);

                        $user->assignRole('catechist');
                        $userId = $user->id;
                    }
                }

                Teacher::create([
                    'last_name'       => $lastName,
                    'first_name'      => $firstName,
                    'saint_id'        => $saintId,
                    'gender'          => $gender,
                    'birthday'        => $birthday,
                    'email'           => $email,
                    'phone_number'    => $normalizedPhone ?? $phone,
                    'parish_group_id' => $parishGroupId,
                    'parish_id'       => $parishId,
                    'user_id'         => $userId,
                    'is_active'       => true,
                ]);

                $existingKeys[$duplicateKey] = true;
                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Dòng {$rowNumber}: " . $e->getMessage();
            }
        }

        return compact('imported', 'skipped', 'skipped_duplicate', 'errors');
    }
}
