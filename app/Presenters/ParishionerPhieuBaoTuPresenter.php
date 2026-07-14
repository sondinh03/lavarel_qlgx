<?php

namespace App\Presenters;

use App\Models\Parishioner;
use Carbon\Carbon;

/**
 * Giấy báo tử: xuất từ hồ sơ giáo dân đã ghi nhận tử vong (+ lịch hậu sự trong DB).
 */
class ParishionerPhieuBaoTuPresenter
{
    public function __construct(
        private Parishioner $parishioner,
    ) {}

    public static function for(Parishioner $parishioner): self
    {
        $parishioner->loadMissing([
            'saint',
            'diocese',
            'parish',
            'parishGroup',
        ]);

        return new self($parishioner);
    }

    public function toPlaceholders(): array
    {
        $p = $this->parishioner;
        $birthday = $p->birthday;
        $deathDate = $p->death_date;

        $diocese = $this->labeledName($p->diocese?->name, 'Giáo phận');
        $parish  = $this->labeledName($p->parish?->name, 'Giáo xứ');
        $group   = $this->labeledName($p->parishGroup?->name, 'Giáo họ');

        return array_merge([
            'diocese'      => $diocese !== '' ? $diocese : '………………',
            'parish'       => $parish !== '' ? $parish : '………………',
            'parish_group' => $group !== '' ? $group : '………………',
            'honorific'    => $this->honorific(),
            'holy_fullname'    => $this->blank(trim((string) ($p->full_name_with_saint ?: $p->full_name))),
            'common_name'  => $this->blank(trim((string) $p->full_name)),
            'birth_place'  => $this->blank(trim((string) ($p->birth_place ?: $p->origin ?: ''))),
            'death_hour'   => $this->blank(trim((string) ($p->death_time ?? ''))),
            'death_place'  => $this->blank(trim((string) ($p->death_place ?? ''))),
            'age'          => $this->ageLabel($birthday, $deathDate),
            'burial_place' => $this->blank(trim((string) ($p->burial_place ?? ''))),
            'sign_place'   => $parish !== '' ? $parish : '………………',
            'day'          => date('d'),
            'month'        => date('m'),
            'year'         => date('Y'),
        ], $this->splitDate('birth_', $birthday),
            $this->splitDate('death_', $deathDate),
            $this->splitDateTime('embalm_', $p->embalm_at),
            $this->splitDateTime('farewell_', $p->farewell_mass_at),
            $this->splitDateTime('burial_mass_', $p->burial_mass_at),
        );
    }

    public function downloadFilename(): string
    {
        // Tên file ASCII để trình duyệt / Word trên Windows không lỗi khi tải.
        $name = \Illuminate\Support\Str::slug($this->parishioner->full_name ?: 'giaodan', '_');
        if ($name === '') {
            $name = 'giaodan_' . (int) $this->parishioner->id;
        }

        return 'GiayBaoTu_' . $name . '.docx';
    }

    private function honorific(): string
    {
        $p = $this->parishioner;
        $role = (string) ($p->family_role ?? '');
        $age = null;
        if ($p->birthday && $p->death_date) {
            $age = $p->birthday->diffInYears($p->death_date);
        }

        if ($p->gender === 'female' || $role === 'wife') {
            if ($role === 'wife' || ($age !== null && $age >= 30)) {
                return 'Bà';
            }

            return 'Chị';
        }

        if ($role === 'husband' || ($age !== null && $age >= 30) || $p->gender === 'male') {
            if ($role === 'husband' || ($age !== null && $age >= 30)) {
                return 'Ông';
            }

            return 'Anh';
        }

        return 'Ông (Bà)';
    }

    private function ageLabel(?Carbon $birthday, ?Carbon $deathDate): string
    {
        if (! $birthday || ! $deathDate) {
            return '……';
        }

        return (string) $birthday->diffInYears($deathDate);
    }

    /**
     * @return array<string, string>
     */
    private function splitDate(string $prefix, ?Carbon $date): array
    {
        if (! $date) {
            return [
                $prefix . 'day'   => '……',
                $prefix . 'month' => '……',
                $prefix . 'year'  => '……',
            ];
        }

        return [
            $prefix . 'day'   => $date->format('d'),
            $prefix . 'month' => $date->format('m'),
            $prefix . 'year'  => $date->format('Y'),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function splitDateTime(string $prefix, $dt): array
    {
        if (! $dt) {
            return [
                $prefix . 'hour'  => '……',
                $prefix . 'day'   => '……',
                $prefix . 'month' => '……',
                $prefix . 'year'  => '……',
            ];
        }

        $carbon = $dt instanceof Carbon ? $dt : Carbon::parse($dt);

        return [
            $prefix . 'hour'  => $carbon->format('H:i'),
            $prefix . 'day'   => $carbon->format('d'),
            $prefix . 'month' => $carbon->format('m'),
            $prefix . 'year'  => $carbon->format('Y'),
        ];
    }

    private function labeledName(?string $name, string $prefix): string
    {
        $name = trim((string) $name);
        if ($name === '') {
            return '';
        }

        if (mb_stripos($name, $prefix) === 0) {
            return $name;
        }

        return $prefix . ' ' . $name;
    }

    private function blank(string $value): string
    {
        return $value !== '' ? $value : '………………';
    }
}
