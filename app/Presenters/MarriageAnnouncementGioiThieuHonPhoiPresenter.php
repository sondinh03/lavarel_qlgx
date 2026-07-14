<?php

namespace App\Presenters;

use App\Models\MarriageAnnouncement;
use App\Models\MarriageAnnouncementParishioners;
use App\Models\Parishioner;
use App\Models\ParishNew;
use Carbon\Carbon;

/**
 * Giấy giới thiệu hôn phối — xuất từ hồ sơ Rao hôn phối (đôi chuẩn bị bí tích).
 * Bên A = đương sự (bên được giới thiệu). Bên B = người kết bạn.
 */
class MarriageAnnouncementGioiThieuHonPhoiPresenter
{
    /**
     * @param  array{
     *   honorific:string,holy_name:string,birthday:?Carbon,birth_place:string,
     *   father_name:string,mother_name:string,address:string,
     *   parish_group:string,parish:string
     * }  $sideA
     * @param  array{
     *   honorific:string,holy_name:string,birthday:?Carbon,birth_place:string,
     *   father_name:string,mother_name:string,address:string,
     *   parish_group:string,parish:string
     * }  $sideB
     */
    public function __construct(
        private MarriageAnnouncement $announcement,
        private array $sideA,
        private array $sideB,
        private string $greetingParish = '',
    ) {}

    public static function for(
        MarriageAnnouncement $announcement,
        string $subjectSide = 'groom',
        string $greetingParish = '',
        array $overrides = [],
    ): self {
        $announcement->loadMissing([
            'parishioners.parishioner.saint',
            'parishioners.parishioner.diocese',
            'parishioners.parishioner.parish',
            'parishioners.parishioner.parishGroup',
            'parishioners.parishioner.father.saint',
            'parishioners.parishioner.mother.saint',
        ]);

        $groom = $announcement->groomParticipant();
        $bride = $announcement->brideParticipant();

        $groomData = self::personFromParticipant($groom, 'Anh');
        $brideData = self::personFromParticipant($bride, 'Chị');

        $subjectSide = $subjectSide === 'bride' ? 'bride' : 'groom';
        $sideA = $subjectSide === 'bride' ? $brideData : $groomData;
        $sideB = $subjectSide === 'bride' ? $groomData : $brideData;

        $sideA = self::applyOverrides($sideA, $overrides['a'] ?? []);
        $sideB = self::applyOverrides($sideB, $overrides['b'] ?? []);

        return new self($announcement, $sideA, $sideB, trim($greetingParish));
    }

    public function toPlaceholders(): array
    {
        $header = $this->headerFromAnnouncement();

        $greeting = $this->greetingParish !== ''
            ? $this->labeledName($this->greetingParish, 'Giáo xứ')
            : ($header['parish'] !== '' ? $header['parish'] : '………………');

        return array_merge(
            [
                'diocese'           => $this->upperOrBlank($header['diocese']),
                'parish'            => $this->upperOrBlank($header['parish']),
                'parish_group'      => $this->upperOrBlank($header['parish_group']),
                'greeting_parish'   => $greeting !== '' ? $greeting : '………………',
                'sign_place'        => $header['parish_group'] !== ''
                    ? $header['parish_group']
                    : ($header['parish'] !== '' ? $header['parish'] : '………………'),
                'day'               => date('d'),
                'month'             => date('m'),
                'year'              => date('Y'),
            ],
            $this->sidePlaceholders('a_', $this->sideA),
            $this->sidePlaceholders('b_', $this->sideB),
        );
    }

    public function downloadFilename(): string
    {
        $name = \Illuminate\Support\Str::slug(
            ($this->sideA['holy_name'] ?? '') . '_' . ($this->sideB['holy_name'] ?? ''),
            '_'
        );
        if ($name === '' || $name === '_') {
            $name = 'rao_' . (int) $this->announcement->id;
        }

        return 'GiayGioiThieuHonPhoi_' . $name . '.docx';
    }

    /**
     * @return array{diocese:string,parish:string,parish_group:string}
     */
    private function headerFromAnnouncement(): array
    {
        $parish = ParishNew::query()->with(['diocese'])->find($this->announcement->pid);

        return [
            'diocese'      => $this->labeledName($parish?->diocese?->name, 'Giáo phận'),
            'parish'       => $this->labeledName($parish?->name, 'Giáo xứ'),
            'parish_group' => '',
        ];
    }

    /**
     * @return array{
     *   honorific:string,holy_name:string,birthday:?Carbon,birth_place:string,
     *   father_name:string,mother_name:string,address:string,
     *   parish_group:string,parish:string
     * }
     */
    public static function personFromParticipant(
        ?MarriageAnnouncementParishioners $row,
        string $defaultHonorific
    ): array {
        $empty = [
            'honorific'    => $defaultHonorific,
            'holy_name'    => '',
            'birthday'     => null,
            'birth_place'  => '',
            'father_name'  => '',
            'mother_name'  => '',
            'address'      => '',
            'parish_group' => '',
            'parish'       => '',
        ];

        if (! $row) {
            return $empty;
        }

        $labels = $row->parishGroupLabels('current');
        $empty['parish_group'] = trim((string) ($labels['parish'] ?? $labels['management'] ?? ''));
        $empty['parish'] = trim((string) ($labels['management'] ?? $labels['parish'] ?? ''));

        // parishs on announcement often = giáo họ; parishmanagements = giáo xứ
        if (($labels['parish'] ?? '') !== '') {
            $empty['parish_group'] = (string) $labels['parish'];
        }
        if (($labels['management'] ?? '') !== '') {
            $empty['parish'] = (string) $labels['management'];
        }

        $p = $row->parishioner;
        if ($p instanceof Parishioner) {
            $p->loadMissing(['saint', 'parish', 'parishGroup', 'father.saint', 'mother.saint']);

            return [
                'honorific'    => $p->gender === 'female' ? 'Chị' : ($p->gender === 'male' ? 'Anh' : $defaultHonorific),
                'holy_name'    => trim((string) ($p->full_name_with_saint ?: $p->full_name)),
                'birthday'     => $p->birthday,
                'birth_place'  => trim((string) ($p->birth_place ?: $p->origin ?: '')),
                'father_name'  => self::parentName($p, 'father'),
                'mother_name'  => self::parentName($p, 'mother'),
                'address'      => trim((string) ($p->full_address_permanent ?: $p->full_address_temporary ?: '')),
                'parish_group' => trim((string) ($p->parishGroup?->name ?: $empty['parish_group'])),
                'parish'       => trim((string) ($p->parish?->name ?: $empty['parish'])),
            ];
        }

        $empty['holy_name'] = trim((string) ($row->displayName() ?? ''));

        return $empty;
    }

    private static function parentName(Parishioner $p, string $role): string
    {
        $related = $p->{$role} ?? null;
        if ($related) {
            $related->loadMissing('saint');
            $name = trim((string) ($related->full_name_with_saint ?: $related->full_name));
            if ($name !== '') {
                return $name;
            }
        }

        return trim((string) ($p->{$role . '_name'} ?? ''));
    }

    /**
     * @param  array<string, mixed>  $base
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private static function applyOverrides(array $base, array $overrides): array
    {
        foreach ($overrides as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }
            if ($key === 'birthday' && ! $value instanceof Carbon) {
                $base[$key] = Carbon::parse((string) $value);
                continue;
            }
            $base[$key] = is_string($value) ? trim($value) : $value;
        }

        return $base;
    }

    /**
     * @param  array<string, mixed>  $side
     * @return array<string, string>
     */
    private function sidePlaceholders(string $prefix, array $side): array
    {
        /** @var Carbon|null $birthday */
        $birthday = $side['birthday'] ?? null;

        return [
            $prefix . 'honorific'    => $this->blank((string) ($side['honorific'] ?? '')),
            $prefix . 'holy_name'    => $this->blank((string) ($side['holy_name'] ?? '')),
            $prefix . 'birth_day'    => $birthday ? $birthday->format('d') : '……',
            $prefix . 'birth_month'  => $birthday ? $birthday->format('m') : '……',
            $prefix . 'birth_year'   => $birthday ? $birthday->format('Y') : '……',
            $prefix . 'birth_place'  => $this->blank((string) ($side['birth_place'] ?? '')),
            $prefix . 'father_name'  => $this->blank((string) ($side['father_name'] ?? '')),
            $prefix . 'mother_name'  => $this->blank((string) ($side['mother_name'] ?? '')),
            $prefix . 'address'      => $this->blank((string) ($side['address'] ?? '')),
            $prefix . 'parish_group' => $this->blank(
                ($side['parish_group'] ?? '') !== ''
                    ? $this->labeledName((string) $side['parish_group'], 'Giáo họ')
                    : ''
            ),
            $prefix . 'parish'       => $this->blank(
                ($side['parish'] ?? '') !== ''
                    ? $this->labeledName((string) $side['parish'], 'Giáo xứ')
                    : ''
            ),
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

    private function upperOrBlank(string $value): string
    {
        return $value !== '' ? mb_strtoupper($value, 'UTF-8') : '………………';
    }

    private function blank(string $value): string
    {
        return $value !== '' ? $value : '………………';
    }
}
