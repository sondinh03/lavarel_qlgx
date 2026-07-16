<?php

namespace App\Support;

use App\Models\NamHoc;

class OperatingSchoolYear
{
    public const PHASE_SEMESTER_1 = 'semester_1';
    public const PHASE_SEMESTER_2 = 'semester_2';
    public const PHASE_BETWEEN = 'between_semesters';
    public const PHASE_SUMMER = 'summer';

    public function __construct(
        public readonly NamHoc $namHoc,
        public readonly string $phase,
        public readonly ?int $semester,
    ) {
    }

    public function id(): int
    {
        return (int) $this->namHoc->id;
    }

    public function label(): string
    {
        return (string) ($this->namHoc->name ?? '');
    }

    public function semesterLabel(): string
    {
        return match ($this->phase) {
            self::PHASE_SEMESTER_1 => 'Học kỳ 1',
            self::PHASE_SEMESTER_2 => 'Học kỳ 2',
            self::PHASE_BETWEEN    => 'Nghỉ giữa kỳ',
            self::PHASE_SUMMER     => 'Kỳ hè',
            default                => 'Chưa xác định học kỳ',
        };
    }

    public function isSummer(): bool
    {
        return $this->phase === self::PHASE_SUMMER;
    }

    public function isBetweenSemesters(): bool
    {
        return $this->phase === self::PHASE_BETWEEN;
    }
}
