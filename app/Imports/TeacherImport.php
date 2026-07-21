<?php

namespace App\Imports;

use App\Models\Teacher;
use App\Services\Holy\HolyResolver;
use App\Services\Parish\ParishChildResolver;
use App\Services\User\CreateCatechistAccount;
use App\Support\CatechistDefaultPassword;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\{
    ToModel,
    WithHeadingRow
};
use PhpOffice\PhpSpreadsheet\Shared\Date;

class TeacherImport implements ToModel, WithHeadingRow
{
    public function __construct(
        protected int $parishId,
        // protected int $deaneryId,
        // protected int $dioceseId,
    ) {}

    public function model(array $row)
    {
        if (empty($row['ho_ten'])) {
            return null;
        }

        $phone = preg_replace('/[^0-9]/', '', $row['so_dien_thoai'] ?? '');

        // Chặn trùng phone trong cùng giáo xứ
        if ($phone && Teacher::where('pid', $this->parishId)
            ->where('phone_number', $phone)
            ->exists()) {
            return null;
        }

        // Resolve holy_id từ tên thánh (TEXT)
        $holyId = app(HolyResolver::class)
            ->resolve($row['ten_thanh'] ?? null);

        $parishChildId = null;
        if (!empty($row['giao_ho'] ?? null)) {
            $parishChildId = app(ParishChildResolver::class)
                ->resolve(
                    parishChildName:$row['giao_ho'],
                    parishId:$this->parishId,
                    // $this->deaneryId,
                    // $this->dioceseId,
                );
        }

        // Tạo tài khoản?
        $userId = null;
        if ($this->isYes($row['tao_tai_khoan'] ?? null)) {
            $email = $phone
                ? $phone
                : uniqid('glv') . '@giaoxu.com';

            $password = CatechistDefaultPassword::fromBirthday(
                $this->parseDate($row['ngay_sinh'] ?? null)
            );

            $user = app(CreateCatechistAccount::class)
                ->create($row['ho_ten'], $email, $password, $this->parishId);

            $userId = $user->id;
        }

        return new Teacher([
            'holy_id' => $holyId,
            'name' => trim($row['ho_ten']),
            'birthday' => $this->parseDate($row['ngay_sinh'] ?? null),
            'phone_number' => $phone,
            'paid' => $parishChildId ?? 0,
            'user_id' => $userId,
            'pid' => $this->parishId,
            'did' => 0,
            'deid' => 0,
            'status' => 1,
        ]);
    }

    /* ---------------- HELPERS ---------------- */

    private function isYes($value): bool
    {
        return in_array(
            mb_strtolower(trim((string)$value)),
            ['co', 'có', 'yes', '1', 'true']
        );
    }

    private function parseDate($value): ?Carbon
    {
        try {
            if (is_numeric($value)) {
                return Carbon::instance(Date::excelToDateTimeObject($value));
            }
            return Carbon::createFromFormat('d/m/Y', $value);
        } catch (\Exception $e) {
            return null;
        }
    }

}
