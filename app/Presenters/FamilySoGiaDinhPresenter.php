<?php

namespace App\Presenters;

use App\Models\Family;
use App\Models\Marriage;
use App\Models\Parishioner;
use App\Models\Sacrament;
use App\Support\VietnamAddressResolver;
use Illuminate\Support\Collection;

class FamilySoGiaDinhPresenter
{
    public const MAX_MEMBERS = 7;

    public function __construct(private Family $family) {}

    public static function for(Family $family): self
    {
        $family->loadMissing([
            'parish.deanery',
            'parish.diocese',
            'parishGroup',
            'head.saint',
            'members.saint',
            'members.parish.diocese',
            'members.parishGroup',
            'members.diocese',
            'members.baptism.parish',
            'members.communion.parish',
            'members.confirmation.parish',
            'members.marriageAsHusband.parish.diocese',
            'members.marriageAsHusband.parish.deanery.diocese',
            'members.marriageAsWife.parish.diocese',
            'members.marriageAsWife.parish.deanery.diocese',
            'members.father',
            'members.mother',
        ]);

        return new self($family);
    }

    public function toPlaceholders(): array
    {
        $family  = $this->family;
        $parish  = $family->parish;
        $members = $this->orderedMembers();
        $head    = $family->head ?? $members->first();
        $husband = $members->first(fn (Parishioner $p) => $p->family_role === 'husband' || $p->gender === 'male' && $p->family_role !== 'child');
        $wife    = $members->first(fn (Parishioner $p) => $p->family_role === 'wife' || $p->gender === 'female' && $p->family_role !== 'child');

        if (!$husband && $head?->gender === 'male') {
            $husband = $head;
        }
        if (!$wife && $head?->gender === 'female') {
            $wife = $head;
        }

        $marriage = $this->resolveMarriage($husband, $wife);
        $contact  = $this->buildContact($family);

        $data = [
            'diocese'      => $parish?->diocese?->name ?? $parish?->deanery?->diocese?->name ?? '',
            'deanery'      => $parish?->deanery?->name ?? '',
            'parish'       => $parish?->name ?? '',
            'parish_group' => $family->parishGroup?->name ?? '',
            'family_code'  => $family->code ?? '',
            'head_name'    => $this->displayName($head),
            'contact'      => $contact,
            'joined_date'  => $head?->joined_date?->format('d/m/Y') ?? '',
            'member_count' => $members->count() . '/' . max($members->count(), 1),
            'married_date' => $marriage?->married_date?->format('d/m/Y') ?? '',

            'father_birthday'              => $husband?->birthday?->format('d/m/Y') ?? '',
            'mother_birthday'              => $wife?->birthday?->format('d/m/Y') ?? '',
            'father_saint_day'             => '',
            'mother_saint_day'             => '',
            'parents_wedding_day'          => $marriage?->married_date?->format('d/m/Y') ?? '',
            'father_death_date'            => $husband?->death_date?->format('d/m/Y') ?? '',
            'mother_death_date'            => $wife?->death_date?->format('d/m/Y') ?? '',
            'paternal_grandfather_death'   => '',
            'paternal_grandmother_death'   => '',
            'maternal_grandfather_death'   => '',
            'maternal_grandmother_death'   => '',

            'pastor_name' => '',
        ];

        $data = array_merge($data, $this->spousePlaceholders('husband', $husband));
        $data = array_merge($data, $this->spousePlaceholders('wife', $wife));
        $data = array_merge($data, $this->marriagePlaceholders($marriage));

        for ($i = 1; $i <= self::MAX_MEMBERS; $i++) {
            $data = array_merge($data, $this->memberPlaceholders($i, $members->get($i - 1)));
        }

        return $data;
    }

    public function downloadFilename(): string
    {
        $code = $this->family->code ?: $this->family->id;
        $name = preg_replace('/[^\p{L}\p{N}\-_]+/u', '_', $this->family->name ?? 'gia_dinh');

        return 'SoGiaDinh_' . $code . '_' . $name . '.docx';
    }

    private function orderedMembers(): Collection
    {
        $roleOrder = ['husband' => 1, 'wife' => 2, 'child' => 3, 'other' => 4];

        return $this->family->members
            ->sortBy([
                fn (Parishioner $p) => $roleOrder[$p->family_role] ?? 5,
                fn (Parishioner $p) => $p->birthday?->timestamp ?? PHP_INT_MAX,
                fn (Parishioner $p) => $p->id,
            ])
            ->values();
    }

    private function resolveMarriage(?Parishioner $husband, ?Parishioner $wife): ?Marriage
    {
        if ($husband?->marriage) {
            return $husband->marriage;
        }
        if ($wife?->marriage) {
            return $wife->marriage;
        }

        return null;
    }

    private function buildContact(Family $family): string
    {
        $parts = array_filter([
            $family->address,
            VietnamAddressResolver::wardName($family->ward_id),
            VietnamAddressResolver::provinceName($family->province),
            $family->phone,
        ]);

        return implode(' - ', $parts);
    }

    private function displayName(?Parishioner $p): string
    {
        if (!$p) {
            return '';
        }

        return mb_strtoupper($p->full_name_with_saint ?: $p->full_name, 'UTF-8');
    }

    private function roleLabel(Parishioner $p): string
    {
        return match ($p->family_role) {
            'husband' => 'Gia trưởng',
            'wife'    => 'Hiền mẫu',
            'child'   => $p->gender === 'male' ? 'Con trai' : 'Con gái',
            default   => 'Thành viên',
        };
    }

    private function residenceStatus(Parishioner $p): string
    {
        return $p->is_active ? 'Hiện ở xứ' : 'Đã chuyển';
    }

    private function parishLabel(?Sacrament $s): string
    {
        if (!$s) {
            return '';
        }
        $name = $s->parish?->name ?? $s->parish_name ?? '';
        return $name !== '' ? 'Gx. ' . $name : '';
    }

    private function spousePlaceholders(string $prefix, ?Parishioner $p): array
    {
        $baptism      = $p?->baptism;
        $confirmation = $p?->confirmation;

        return [
            "{$prefix}_name"                 => $this->displayName($p),
            "{$prefix}_birthday"             => $p?->birthday?->format('d/m/Y') ?? '',
            "{$prefix}_birth_place"          => $p?->birth_place ?? '',
            "{$prefix}_baptism_date"         => $baptism?->received_date?->format('d/m/Y') ?? '',
            "{$prefix}_baptism_parish"       => $this->parishLabel($baptism),
            "{$prefix}_confirmation_date"    => $confirmation?->received_date?->format('d/m/Y') ?? '',
            "{$prefix}_confirmation_parish"  => $this->parishLabel($confirmation),
            "{$prefix}_father"               => $p?->father_name ?? $p?->father?->full_name_with_saint ?? '',
            "{$prefix}_mother"               => $p?->mother_name ?? $p?->mother?->full_name_with_saint ?? '',
            "{$prefix}_origin_parish"        => $this->originParishLine($p),
        ];
    }

    private function originParishLine(?Parishioner $p): string
    {
        if (!$p) {
            return '';
        }

        $parts = array_filter([
            $p->parishGroup?->name ? 'Gh. ' . $p->parishGroup->name : null,
            $p->parish?->name ? 'Gx. ' . $p->parish->name : null,
            $p->diocese?->name ? 'Gp. ' . $p->diocese->name : null,
        ]);

        return implode('; ', $parts);
    }

    private function marriagePlaceholders(?Marriage $marriage): array
    {
        return [
            'marriage_priest'              => $marriage?->priest_witness ?? '',
            'marriage_witness_1'           => $marriage?->witness_1 ?? '',
            'marriage_witness_2'           => $marriage?->witness_2 ?? '',
            'marriage_certificate_number'  => $marriage?->certificate_number ?? '',
            'married_parish'               => $marriage?->parish?->name ?? $marriage?->parish_name ?? '',
            'married_diocese'              => $marriage?->parish?->diocese?->name
                ?? $marriage?->parish?->deanery?->diocese?->name
                ?? '',
        ];
    }

    private function memberPlaceholders(int $index, ?Parishioner $p): array
    {
        $prefix = "m{$index}";

        if (!$p) {
            return $this->emptyMember($prefix);
        }

        $baptism      = $p->baptism;
        $communion    = $p->communion;
        $confirmation = $p->confirmation;

        return [
            "{$prefix}_index"                 => (string) $index,
            "{$prefix}_role"                  => $this->roleLabel($p),
            "{$prefix}_name"                  => $this->displayName($p),
            "{$prefix}_birthday"              => $p->birthday?->format('d/m/Y') ?? '',
            "{$prefix}_birth_place"           => $p->birth_place ?? '',
            "{$prefix}_father"                => $p->father_name ?? $p->father?->full_name_with_saint ?? '',
            "{$prefix}_mother"                => $p->mother_name ?? $p->mother?->full_name_with_saint ?? '',
            "{$prefix}_residence_status"      => $this->residenceStatus($p),
            "{$prefix}_baptism_date"          => $baptism?->received_date?->format('d/m/Y') ?? '',
            "{$prefix}_baptism_parish"        => $this->parishLabel($baptism),
            "{$prefix}_baptism_giver"         => $baptism?->giver ?? '',
            "{$prefix}_baptism_sponsor"       => $baptism?->sponsor ?? '',
            "{$prefix}_baptism_number"        => $baptism?->certificate_number ?? $baptism?->book_number ?? '',
            "{$prefix}_communion_date"        => $communion?->received_date?->format('d/m/Y') ?? '',
            "{$prefix}_communion_parish"      => $this->parishLabel($communion),
            "{$prefix}_confirmation_date"     => $confirmation?->received_date?->format('d/m/Y') ?? '',
            "{$prefix}_confirmation_parish"   => $this->parishLabel($confirmation),
            "{$prefix}_confirmation_giver"    => $confirmation?->giver ?? '',
            "{$prefix}_confirmation_sponsor"  => $confirmation?->sponsor ?? '',
            "{$prefix}_confirmation_number"   => $confirmation?->certificate_number ?? $confirmation?->book_number ?? '',
        ];
    }

    private function emptyMember(string $prefix): array
    {
        $keys = [
            'index', 'role', 'name', 'birthday', 'birth_place', 'father', 'mother', 'residence_status',
            'baptism_date', 'baptism_parish', 'baptism_giver', 'baptism_sponsor', 'baptism_number',
            'communion_date', 'communion_parish',
            'confirmation_date', 'confirmation_parish', 'confirmation_giver', 'confirmation_sponsor', 'confirmation_number',
        ];

        $out = [];
        foreach ($keys as $key) {
            $out["{$prefix}_{$key}"] = $key === 'index' ? '' : '';
        }

        return $out;
    }
}
