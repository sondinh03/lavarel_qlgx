<?php

namespace App\Actions\Teacher;

use App\Models\Holymanagement;
use App\Models\ParishGroup;
use App\Models\Teacher;
use App\Models\User;
use App\Support\ExcelDateParser;
use App\Support\UserAccountEmailResolver;
use Illuminate\Support\Facades\Hash;

class ImportTeacherAction
{
    /**
     * Import teachers từ rows đã được preview/validate.
     * Nhận $rows trực tiếp thay vì parse file lần 2.
     *
     * @param  array  $rows     Mảng rows từ TeacherImportPreview::$rows
     * @param  int    $parishId
     * @return array{imported: int, skipped: int, errors: array}
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

        $imported = 0;
        $skipped  = 0;
        $errors   = [];

        foreach ($rows as $row) {
            $rowNumber = $row['row_number'];

            // Bỏ qua dòng trống
            if (empty(trim($row['ho_ten'] ?? ''))) {
                $skipped++;
                continue;
            }

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

                // Parse giới tính
                $gender      = 'male';
                $gioiTinhRaw = mb_strtolower(trim($row['gioi_tinh'] ?? ''), 'UTF-8');
                if (in_array($gioiTinhRaw, ['nữ', 'nu', 'female', 'f', '0'])) {
                    $gender = 'female';
                }

                // Tách họ tên
                $fullName  = trim($row['ho_ten'] ?? '');
                $parts     = explode(' ', $fullName);
                $firstName = array_pop($parts);
                $lastName  = implode(' ', $parts);

                $phone = $row['so_dien_thoai'] ?? null;
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
                            'password'  => $normalizedPhone ?: '12345678',
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

                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Dòng {$rowNumber}: " . $e->getMessage();
            }
        }

        return compact('imported', 'skipped', 'errors');
    }
}
