<?php

namespace App\Presenters;

use App\Models\Marriage;
use App\Models\Parishioner;
use App\Models\Sacrament;
use App\Support\ParishionerEnumResolver;
use App\Support\VietnamAddressResolver;

class ParishionerLyLichPresenter
{
    public function __construct(private Parishioner $parishioner) {}

    public static function for(Parishioner $parishioner): self
    {
        $parishioner->loadMissing([
            'saint', 'diocese', 'deanery', 'parish', 'parishGroup',
            'father', 'mother',
            'baptism.diocese', 'baptism.deanery', 'baptism.parish',
            'confirmation.diocese', 'confirmation.deanery', 'confirmation.parish',
            'communion.diocese', 'communion.deanery', 'communion.parish',
            'anointing',
            'marriageAsHusband', 'marriageAsWife',
        ]);

        return new self($parishioner);
    }

    public function toPlaceholders(): array
    {
        $p = $this->parishioner;
        $marriage = $p->marriage;

        $baptism      = $p->baptism;
        $confirmation = $p->confirmation;
        $communion    = $p->communion;
        $anointing    = $p->anointing;

        $phone = $p->phone ? 'Điện Thoại: ' . $p->phone : '';
        $email = $p->email ? 'Email: ' . $p->email : '';

        return [
            'did'   => $p->diocese?->name ?? '',
            'deid'  => $p->deanery?->name ? ', ' . $p->deanery->name : '',
            'pid'   => $p->parish?->name ? ', ' . $p->parish->name : '',
            'giaoxu'=> $p->parish?->name ?? '',
            'paid'  => $p->parishGroup?->name ? ', Giáo họ ' . $p->parishGroup->name : '',
            'holy'  => $p->saint?->name ?? '',
            'id'    => (string) $p->id,
            'name'  => $p->full_name,
            'birthday' => $p->birthday?->format('d-m-Y') ?? '',
            'sex'   => $p->gender_name,
            'email' => $email,
            'phone' => $phone,

            'origin'        => $p->origin ? $p->origin . ',' : '',
            'ward'          => ($w = $p->permanent_ward_name) ? $w . ',' : '',
            'province'      => VietnamAddressResolver::provinceName($p->permanent_province),
            'residence'     => $p->permanent_residence ? $p->permanent_residence . ',' : '',
            'resi_ward'     => ($tw = $p->temporary_ward_name) ? $tw . ',' : '',
            'resi_province' => VietnamAddressResolver::provinceName($p->temporary_province),

            'father'  => $p->father_name ?? $p->father?->full_name ?? '',
            'mother'  => $p->mother_name ?? $p->mother?->full_name ?? '',
            'career'  => $p->career_name ?? '',
            'level'   => $p->level_name ?? '',
            'married' => ParishionerEnumResolver::marriedToLegacyLabel((int) ($p->married ?? 0)),

            ...$this->sacramentPlaceholders($baptism, 'baptism'),
            ...$this->sacramentPlaceholders($confirmation, 'more_power'),
            ...$this->sacramentPlaceholders($communion, 'communion', false),
            ...$this->anointingPlaceholders($anointing),

            'die_time'    => $p->death_date ? 'Qua đời: ' . $p->death_date->format('d-m-Y') : '',
            'die_burial'  => $p->burial_place ? 'Nơi an táng: ' . $p->burial_place : '',
            'die_lottery' => $p->death_book_number ? 'Số sổ: ' . $p->death_book_number : '',

            ...$this->marriagePlaceholders($marriage),

            'day'   => date('d'),
            'month' => date('m'),
            'year'  => date('Y'),
        ];
    }

    public function downloadFilename(): string
    {
        $name = preg_replace('/[^a-zA-Z0-9_\-\p{L}]/u', '_', $this->parishioner->full_name_with_saint);

        return 'LyLich_' . $name . '.docx';
    }

    private function sacramentPlaceholders(?Sacrament $sacrament, string $prefix, bool $includeSponsor = true): array
    {
        if (!$sacrament) {
            $empty = [
                "{$prefix}_date"      => '',
                "{$prefix}_number"    => '',
                "{$prefix}_giver"     => '',
                "{$prefix}_dioceses"  => '',
                "{$prefix}_deanerys"  => '',
                "{$prefix}_parish"    => '',
            ];
            if ($includeSponsor) {
                $empty["{$prefix}_sponsor"] = '';
            }

            return $empty;
        }

        $data = [
            "{$prefix}_date"     => $sacrament->received_date?->format('d-m-Y') ?? '',
            "{$prefix}_number"   => $sacrament->certificate_number ?? $sacrament->book_number ?? '',
            "{$prefix}_giver"    => $sacrament->giver ?? '',
            "{$prefix}_dioceses" => $sacrament->diocese?->name ?? '',
            "{$prefix}_deanerys" => $sacrament->deanery?->name ? $sacrament->deanery->name . ', ' : '',
            "{$prefix}_parish"   => ($sacrament->parish?->name ?? $sacrament->parish_name)
                ? ($sacrament->parish?->name ?? $sacrament->parish_name) . ', '
                : '',
        ];

        if ($includeSponsor) {
            $data["{$prefix}_sponsor"] = $sacrament->sponsor ?? '';
        }

        return $data;
    }

    private function anointingPlaceholders(?Sacrament $anointing): array
    {
        if (!$anointing) {
            return [
                'anoint_date'   => '',
                'anoint_status' => '',
                'anoint_giver'  => '',
            ];
        }

        return [
            'anoint_date'   => $anointing->received_date?->format('d-m-Y') ?? '',
            'anoint_status' => $anointing->anointing_condition ?? '',
            'anoint_giver'  => $anointing->giver ?? '',
        ];
    }

    private function marriagePlaceholders(?Marriage $marriage): array
    {
        if (!$marriage) {
            return [
                'date'              => '',
                'sohonphoi'         => '',
                'marriage_address'  => '',
                'marriage_ward'     => '',
                'marriage_province' => '',
                'priest'            => '',
                'peopleone'         => '',
                'peopletwo'         => '',
                'tinhtrang'         => '',
            ];
        }

        return [
            'date'              => $marriage->married_date?->format('d-m-Y') ?? '',
            'sohonphoi'         => $marriage->certificate_number ?? '',
            'marriage_address'  => $marriage->parish_name ? $marriage->parish_name . ', ' : '',
            'marriage_ward'     => VietnamAddressResolver::wardName($marriage->place_ward_id)
                ? VietnamAddressResolver::wardName($marriage->place_ward_id) . ', '
                : '',
            'marriage_province' => VietnamAddressResolver::provinceName($marriage->place_province),
            'priest'            => $marriage->priest_witness ?? '',
            'peopleone'         => $marriage->witness_1 ?? '',
            'peopletwo'         => $marriage->witness_2 ?? '',
            'tinhtrang'         => ParishionerEnumResolver::marriageStatusToLegacyLabel($marriage->status),
        ];
    }
}
