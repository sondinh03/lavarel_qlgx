<?php

namespace App\Presenters;

use App\Models\Parishioner;
use App\Support\VietnamAddressResolver;
use Carbon\Carbon;

class ParishionerDonXinRuaToiPresenter
{
    public function __construct(
        private Parishioner $parishioner,
        private string $holyFullName,
        private string $godparentName,
        private Carbon $birthday,
        private string $birthPlace,
        private ?int $birthOrder = null,
    ) {}

    public static function for(
        Parishioner $parishioner,
        string $holyFullName,
        string $godparentName,
        string $birthday,
        string $birthPlace = '',
        ?int $birthOrder = null
    ): self {
        $parishioner->loadMissing([
            'saint',
            'diocese',
            'parish',
            'parishGroup',
            'father.saint',
            'mother.saint',
            'family.members.saint',
            'marriageAsHusband.wife.saint',
            'marriageAsWife.husband.saint',
        ]);

        return new self(
            $parishioner,
            trim($holyFullName),
            trim($godparentName),
            Carbon::parse($birthday),
            trim($birthPlace),
            $birthOrder
        );
    }

    public function toPlaceholders(): array
    {
        $p = $this->parishioner;

        $diocese = $this->labeledName($p->diocese?->name, 'Giáo phận');
        $parish  = $this->labeledName($p->parish?->name, 'Giáo xứ');
        $group   = $this->labeledName($p->parishGroup?->name, 'Giáo họ');
        $parents = $this->resolveParents();
        $order   = $this->birthOrder ?? (
            ($p->birth_order && (int) $p->birth_order > 0) ? (int) $p->birth_order : null
        );

        return [
            'diocese'              => $diocese,
            'parish'               => $parish,
            'parish_group'         => $group,
            'birth_order'          => $order ? (string) $order : '…..',
            'holy_fullname'            => $this->holyFullName,
            'birth_day'            => $this->birthday->format('d'),
            'birth_month'          => $this->birthday->format('m'),
            'birth_year'           => $this->birthday->format('Y'),
            'birth_place'          => $this->birthPlace !== '' ? $this->birthPlace : $this->defaultBirthPlace(),
            'father_name'          => $parents['father'],
            'mother_name'          => $parents['mother'],
            'address'              => $this->address(),
            'current_parish_group' => $group !== '' ? $group : '………………',
            'current_parish'       => $parish !== '' ? $parish : '………………',
            'godparent_name'       => $this->godparentName,
            'sign_place'           => $parish !== '' ? $parish : '………………',
            'day'                  => date('d'),
            'month'                => date('m'),
            'year'                 => date('Y'),
        ];
    }

    public function downloadFilename(): string
    {
        $name = preg_replace('/[^a-zA-Z0-9_\-\p{L}]/u', '_', $this->holyFullName ?: $this->parishioner->full_name);

        return 'DonXinRuaToi_' . $name . '.docx';
    }

    /**
     * Thêm tiền tố nếu tên trong DB chưa có (tránh "Giáo xứ Giáo xứ A").
     */
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

    /**
     * Con Ông / Và Bà:
     * - Xuất từ hồ sơ cha/mẹ (husband/wife hoặc có hôn phối) → người xuất + vợ/chồng.
     * - Xuất từ hồ sơ con → cha/mẹ liên kết / tên chữ / chồng-vợ trong hộ.
     *
     * @return array{father: string, mother: string}
     */
    private function resolveParents(): array
    {
        $p = $this->parishioner;
        $role = (string) ($p->family_role ?? '');

        if ($this->isParentExporter()) {
            $self   = $this->displayName($p);
            $spouse = $this->spouseDisplayName();

            $isFather = $role === 'husband'
                || ($role !== 'wife' && $p->gender === 'male');

            if ($isFather) {
                return [
                    'father' => $self !== '' ? $self : '………………',
                    'mother' => $spouse !== '' ? $spouse : '………………',
                ];
            }

            return [
                'father' => $spouse !== '' ? $spouse : '………………',
                'mother' => $self !== '' ? $self : '………………',
            ];
        }

        return [
            'father' => $this->parentName('father'),
            'mother' => $this->parentName('mother'),
        ];
    }

    /** Xuất từ hồ sơ cha/mẹ (vai trò chồng/vợ hoặc có hôn phối). */
    private function isParentExporter(): bool
    {
        $role = (string) ($this->parishioner->family_role ?? '');
        if (in_array($role, ['husband', 'wife'], true)) {
            return true;
        }

        return (bool) (
            $this->parishioner->marriageAsHusband
            || $this->parishioner->marriageAsWife
        );
    }

    /**
     * Ưu tiên: hồ sơ cha/mẹ đã liên kết → tên chữ trên hồ sơ → chồng/vợ trong hộ GĐ.
     */
    private function parentName(string $role): string
    {
        $p = $this->parishioner;

        $related = $p->{$role} ?? null;
        if ($related) {
            $related->loadMissing('saint');
            $name = $this->displayName($related);
            if ($name !== '') {
                return $name;
            }
        }

        $text = trim((string) ($p->{$role . '_name'} ?? ''));
        if ($text !== '') {
            return $text;
        }

        $fromFamily = $this->familyMemberDisplayName($role === 'father' ? 'husband' : 'wife');
        if ($fromFamily !== '') {
            return $fromFamily;
        }

        return '………………';
    }

    private function spouseDisplayName(): string
    {
        $p = $this->parishioner;
        $role = (string) ($p->family_role ?? '');

        if ($role === 'husband') {
            $name = $this->familyMemberDisplayName('wife');
            if ($name !== '') {
                return $name;
            }
        }

        if ($role === 'wife') {
            $name = $this->familyMemberDisplayName('husband');
            if ($name !== '') {
                return $name;
            }
        }

        // Không gắn family_role: lấy role còn lại trong hộ, hoặc hôn phối
        $wife = $this->familyMemberDisplayName('wife');
        if ($wife !== '' && (int) ($this->familyMemberByRole('wife')?->id) !== (int) $p->id) {
            return $wife;
        }

        $husband = $this->familyMemberDisplayName('husband');
        if ($husband !== '' && (int) ($this->familyMemberByRole('husband')?->id) !== (int) $p->id) {
            return $husband;
        }

        $marriageWife = $p->marriageAsHusband?->wife;
        if ($marriageWife) {
            return $this->displayName($marriageWife);
        }

        $marriageHusband = $p->marriageAsWife?->husband;
        if ($marriageHusband) {
            return $this->displayName($marriageHusband);
        }

        return '';
    }

    private function familyMemberByRole(string $familyRole): ?Parishioner
    {
        $p = $this->parishioner;
        if (!$p->family_id) {
            return null;
        }

        $members = $p->relationLoaded('family') && $p->family
            ? $p->family->members
            : Parishioner::query()
                ->where('family_id', $p->family_id)
                ->with('saint')
                ->get();

        return collect($members)->first(
            fn ($m) => (string) ($m->family_role ?? '') === $familyRole
        );
    }

    private function familyMemberDisplayName(string $familyRole): string
    {
        $member = $this->familyMemberByRole($familyRole);
        if (!$member) {
            return '';
        }

        // Khi tìm vợ/chồng: bỏ qua chính người xuất
        if ((int) $member->id === (int) $this->parishioner->id) {
            return '';
        }

        $member->loadMissing('saint');

        return $this->displayName($member);
    }

    private function displayName(Parishioner $person): string
    {
        $person->loadMissing('saint');

        return trim((string) ($person->full_name_with_saint ?: $person->full_name));
    }

    private function defaultBirthPlace(): string
    {
        $p = $this->parishioner;

        if (filled($p->birth_place ?? null)) {
            return (string) $p->birth_place;
        }

        if (filled($p->origin)) {
            return (string) $p->origin;
        }

        $line = $p->full_address_permanent;
        if (filled($line)) {
            return $line;
        }

        return '………………';
    }

    private function address(): string
    {
        $p = $this->parishioner;

        $temp = $p->full_address_temporary;
        if (filled($temp)) {
            return $temp;
        }

        $perm = $p->full_address_permanent;
        if (filled($perm)) {
            return $perm;
        }

        $parts = array_filter([
            $p->permanent_residence,
            VietnamAddressResolver::wardName($p->permanent_ward_id),
            VietnamAddressResolver::provinceName($p->permanent_province),
        ]);

        return $parts ? implode(', ', $parts) : '………………';
    }
}
