<?php

namespace App\Presenters;

use App\Models\Parishioner;
use Carbon\Carbon;

/**
 * Giấy giới thiệu học giáo lý dự tòng: xuất độc lập.
 * Người được giới thiệu chưa có trong hệ thống — họ tên/ngày sinh/địa chỉ/bố/mẹ nhập tay.
 * Giáo phận/hạt/xứ và cha xứ lấy từ hồ sơ đang mở (bối cảnh xuất).
 */
class ParishionerGioiThieuGiaoLyDuTongPresenter
{
    public function __construct(
        private Parishioner $parishioner,
        private string $fullName,
        private Carbon $birthday,
        private string $address,
        private string $fatherName,
        private string $motherName,
        private string $coursePlace = '',
        private string $greetingTo = '',
    ) {}

    public static function for(
        Parishioner $parishioner,
        string $fullName,
        string $birthday,
        string $address = '',
        string $fatherName = '',
        string $motherName = '',
        string $coursePlace = '',
        string $greetingTo = '',
    ): self {
        $parishioner->loadMissing([
            'diocese',
            'deanery',
            'parish',
        ]);

        return new self(
            $parishioner,
            trim($fullName),
            Carbon::parse($birthday),
            trim($address),
            trim($fatherName),
            trim($motherName),
            trim($coursePlace),
            trim($greetingTo),
        );
    }

    public function toPlaceholders(): array
    {
        $p = $this->parishioner;

        $diocese = $this->labeledName($p->diocese?->name, 'Giáo phận');
        $deanery = $this->labeledName($p->deanery?->name, 'Giáo hạt');
        $parish  = $this->labeledName($p->parish?->name, 'Giáo xứ');

        $priestName = trim((string) ($p->parish?->parish_priest_name ?? ''));

        $coursePlace = $this->coursePlace !== ''
            ? $this->coursePlace
            : ($parish !== '' ? $parish : '………………');

        $greetingTo = $this->greetingTo !== ''
            ? $this->greetingTo
            : ($deanery !== '' ? $deanery : '………………');

        return [
            'diocese'        => $this->upperOrBlank($diocese),
            'deanery'        => $this->upperOrBlank($deanery),
            'parish'         => $this->upperOrBlank($parish),
            'greeting_to'    => $greetingTo,
            'priest_name'    => $this->blank($priestName),
            'priest_parish'  => $parish !== '' ? $parish : '………………',
            'priest_diocese' => $diocese !== '' ? $diocese : '………………',
            'honorific'      => 'Anh (Chị)',
            'holy_name'      => $this->blank(mb_strtoupper($this->fullName, 'UTF-8')),
            'birth_day'      => $this->birthday->format('d'),
            'birth_month'    => $this->birthday->format('m'),
            'birth_year'     => $this->birthday->format('Y'),
            'father_name'    => $this->blank($this->fatherName),
            'mother_name'    => $this->blank($this->motherName),
            'address'        => $this->blank($this->address),
            'belong_parish'  => $parish !== '' ? $parish : '………………',
            'belong_diocese' => $diocese !== '' ? $diocese : '………………',
            'course_place'   => $coursePlace,
            'sign_place'     => $parish !== '' ? $parish : '………………',
            'day'            => date('d'),
            'month'          => date('m'),
            'year'           => date('Y'),
            'signer_name'    => $this->blank($priestName),
        ];
    }

    public function downloadFilename(): string
    {
        $name = \Illuminate\Support\Str::slug($this->fullName ?: 'du_tong', '_');
        if ($name === '') {
            $name = 'du_tong';
        }

        return 'GiayGioiThieuGiaoLyDuTong_' . $name . '.docx';
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

    private function upperOrBlank(string $value): string
    {
        return $value !== '' ? mb_strtoupper($value, 'UTF-8') : '………………';
    }

    private function blank(string $value): string
    {
        return $value !== '' ? $value : '………………';
    }
}
