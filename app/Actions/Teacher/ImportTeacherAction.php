<?php

namespace App\Actions\Teacher;

use App\Models\Holymanagement;
use App\Models\ParishGroup;
use App\Models\Teacher;
use App\Models\User;
use App\Support\CatechistDefaultPassword;
use App\Support\ExcelDateParser;
use App\Support\ExcelString;
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
     * @return array{imported: int, updated: int, skipped: int, skipped_duplicate: int, errors: array}
     */
    public function handle(array $rows, int $parishId): array
    {
        $saintMap = Holymanagement::pluck('id', 'name')
            ->mapWithKeys(fn ($id, $name) => [ExcelString::lower($name) => $id])
            ->toArray();

        $parishGroupMap = ParishGroup::active()
            ->pluck('id', 'name')
            ->mapWithKeys(fn ($id, $name) => [ExcelString::lower($name) => $id])
            ->toArray();

        // Khoá hồ sơ đã có — chặn trùng khi tạo mới (không áp dụng khi cập nhật theo mã)
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
        $updated           = 0;
        $skipped           = 0;
        $skipped_duplicate = 0;
        $errors            = [];

        foreach ($rows as $row) {
            $rowNumber   = $row['row_number'] ?? '?';
            $teacherCode = ExcelString::trim((string) ($row['ma_giao_ly_vien'] ?? ''));
            $lastName    = ExcelString::trim($row['ho_dem'] ?? '');
            $firstName   = ExcelString::trim($row['ten'] ?? '');

            if ($lastName === '' && $firstName === '') {
                $skipped++;
                continue;
            }

            if ($firstName === '') {
                $errors[] = "Dòng {$rowNumber}: Thiếu cột \"Tên\" — bỏ qua dòng";
                $skipped++;
                continue;
            }

            // Không có mã + đã đánh dấu duplicate từ preview → skip
            if ($teacherCode === '' && ! empty($row['is_duplicate'])) {
                $skipped_duplicate++;
                continue;
            }

            // Có mã nhưng preview đánh dấu invalid → skip
            if ($teacherCode !== '' && ! empty($row['is_duplicate'])) {
                $skipped_duplicate++;
                continue;
            }

            $fullName = ExcelString::trim($lastName . ' ' . $firstName);

            try {
                $tenThanh = ExcelString::clean($row['ten_thanh'] ?? '');
                $saintId  = $tenThanh !== ''
                    ? ($saintMap[ExcelString::lower($tenThanh)] ?? null)
                    : null;

                $giaoHo        = ExcelString::clean($row['giao_ho'] ?? '');
                $parishGroupId = $giaoHo !== ''
                    ? ($parishGroupMap[ExcelString::lower($giaoHo)] ?? null)
                    : null;

                $birthday = null;
                if (($row['ngay_sinh'] ?? null) !== null && ($row['ngay_sinh'] ?? '') !== '') {
                    $birthday = ExcelDateParser::parse($row['ngay_sinh']);
                }

                $gender      = 'male';
                $gioiTinhRaw = ExcelString::lower($row['gioi_tinh'] ?? '');
                if (in_array($gioiTinhRaw, ['nữ', 'nu', 'female', 'f', '0'], true)) {
                    $gender = 'female';
                }

                $phone = ExcelString::trim((string) ($row['so_dien_thoai'] ?? '')) ?: null;
                $email = ExcelString::trim($row['email'] ?? '') ?: null;
                $normalizedPhone = $phone
                    ? UserAccountEmailResolver::normalizePhone((string) $phone)
                    : null;

                if ($phone && $normalizedPhone === null) {
                    $errors[] = "Dòng {$rowNumber}: Số điện thoại không hợp lệ — bỏ qua dòng";
                    $skipped++;
                    continue;
                }

                $data = [
                    'last_name'       => $lastName,
                    'first_name'      => $firstName,
                    'saint_id'        => $saintId,
                    'gender'          => $gender,
                    'birthday'        => $birthday,
                    'email'           => $email,
                    'phone_number'    => $normalizedPhone ?? $phone,
                    'parish_group_id' => $parishGroupId,
                    'is_active'       => true,
                ];

                $taotk        = ExcelString::lower($row['tao_tai_khoan'] ?? '');
                $shouldCreate = in_array($taotk, ['có', 'co', 'yes', '1'], true);

                if ($teacherCode !== '') {
                    $teacher = Teacher::where('teacher_code', $teacherCode)
                        ->where('parish_id', $parishId)
                        ->first();

                    if (! $teacher) {
                        $errors[] = "Dòng {$rowNumber}: Không tìm thấy giáo lý viên với mã '{$teacherCode}'";
                        $skipped_duplicate++;
                        continue;
                    }

                    $teacher->update($data);

                    // Chỉ tạo tài khoản khi cập nhật nếu hồ sơ chưa có user_id và cột = có
                    if ($shouldCreate && empty($teacher->user_id)) {
                        $accountResult = $this->createCatechistAccount(
                            $fullName,
                            $email,
                            $normalizedPhone,
                            $birthday,
                            $parishId,
                            $rowNumber,
                        );

                        if ($accountResult['error'] !== null && $accountResult['user_id'] === null) {
                            // Thiếu SĐT/email để tạo TK — vẫn giữ cập nhật hồ sơ, ghi lỗi
                            $errors[] = $accountResult['error'];
                        } elseif ($accountResult['error'] !== null) {
                            $errors[] = $accountResult['error'];
                        }

                        if ($accountResult['user_id'] !== null) {
                            $teacher->update(['user_id' => $accountResult['user_id']]);
                        }
                    }
                    // Đã có tài khoản → bỏ qua cột tạo TK (không reset mật khẩu)

                    $updated++;
                    continue;
                }

                // Không có mã → double-check duplicate rồi create
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

                $userId = null;

                if ($shouldCreate) {
                    $accountResult = $this->createCatechistAccount(
                        $fullName,
                        $email,
                        $normalizedPhone,
                        $birthday,
                        $parishId,
                        $rowNumber,
                    );

                    if ($accountResult['error'] !== null && $accountResult['user_id'] === null
                        && str_contains($accountResult['error'], 'Cần có SĐT hoặc email')) {
                        $errors[] = $accountResult['error'];
                        $skipped++;
                        continue;
                    }

                    if ($accountResult['error'] !== null) {
                        $errors[] = $accountResult['error'];
                    }

                    $userId = $accountResult['user_id'];
                }

                Teacher::create(array_merge($data, [
                    'parish_id' => $parishId,
                    'user_id'   => $userId,
                ]));

                $existingKeys[$duplicateKey] = true;
                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Dòng {$rowNumber}: " . $e->getMessage();
            }
        }

        return compact('imported', 'updated', 'skipped', 'skipped_duplicate', 'errors');
    }

    /**
     * @return array{user_id: int|null, error: string|null}
     */
    private function createCatechistAccount(
        string $fullName,
        ?string $email,
        ?string $normalizedPhone,
        ?string $birthday,
        int $parishId,
        int|string $rowNumber,
    ): array {
        try {
            $accountEmail = UserAccountEmailResolver::resolveAccountEmail($email, $normalizedPhone);
        } catch (\InvalidArgumentException $e) {
            return [
                'user_id' => null,
                'error'   => "Dòng {$rowNumber}: {$e->getMessage()}",
            ];
        }

        if (User::where('email', $accountEmail)->exists()) {
            return [
                'user_id' => null,
                'error'   => "Dòng {$rowNumber}: \"{$accountEmail}\" đã tồn tại — bỏ qua tạo tài khoản",
            ];
        }

        $user = User::create([
            'name'      => $fullName,
            'email'     => $accountEmail,
            'password'  => CatechistDefaultPassword::fromBirthday($birthday),
            'parish_id' => $parishId,
        ]);

        $user->assignRole('catechist');

        return [
            'user_id' => $user->id,
            'error'   => null,
        ];
    }
}
