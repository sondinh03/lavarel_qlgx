<?php

namespace App\Http\Livewire\Parishioners\Concerns;

use App\Models\Holymanagement;
use App\Models\Marriage;
use App\Models\Association;
use App\Models\ParishGroup;
use App\Models\Sacrament;
use App\Support\CacheKeys;
use App\Support\VietnamAddressResolver;

trait ManagesFamilyRegisterSubmission
{
    public string $family_name = '';

    public ?string $family_code = null;

    public $family_parish_area_id = null;

    public ?string $family_address = null;

    public ?string $family_province = null;

    public $family_ward_id = null;

    /** @var array<int, array<string, mixed>> */
    public array $members = [];

    /** @var array<int, array<string, mixed>> */
    public array $familyMarriages = [];

    /** @var array<int, array<string, mixed>> */
    public array $familySacraments = [];

    public string $contact_phone = '';

    public ?string $submitter_ref = null;

    public array $saints = [];

    public array $parishGroups = [];

    public array $associationOptions = [];

    public array $provinces = [];

    public array $familyWardOptions = [];

    public bool $showMemberForm = false;

    public ?int $editingMemberIndex = null;

    public string $member_ref = '';

    public string $member_last_name = '';

    public string $member_first_name = '';

    public string $member_gender = 'male';

    public ?string $member_birthday = null;

    public ?string $member_birth_place = null;

    public $member_birth_order = null;

    public $member_saint_id = null;

    public ?string $member_family_role = null;

    public ?string $member_father_ref = null;

    public ?string $member_mother_ref = null;

    public ?string $member_father_name = null;

    public ?string $member_mother_name = null;

    public ?string $member_cccd = null;

    public ?string $member_note = null;

    public $member_association_id = null;

    public bool $showMarriageForm = false;

    public ?int $editingMarriageIndex = null;

    public ?string $marriage_husband_ref = null;

    public ?string $marriage_wife_ref = null;

    public ?string $marriage_married_date = null;

    public ?string $marriage_certificate_number = null;

    public ?string $marriage_parish_name = null;

    public ?string $marriage_witness_1 = null;

    public ?string $marriage_witness_2 = null;

    public ?string $marriage_priest_witness = null;

    public string $marriage_status = Marriage::STATUS_VALID;

    public ?string $marriage_note = null;

    public bool $showFamilySacramentForm = false;

    public ?int $editingFamilySacramentIndex = null;

    public ?string $fs_member_ref = null;

    public string $fs_type = '';

    public ?string $fs_received_date = null;

    public ?string $fs_certificate_number = null;

    public ?int $fs_book_number = null;

    public ?string $fs_giver = null;

    public ?string $fs_sponsor = null;

    public ?string $fs_parish_name = null;

    public ?string $fs_note = null;

    protected function loadFamilyRegisterDropdowns(int $parishId): void
    {
        $this->saints = Holymanagement::query()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->toArray();

        $this->parishGroups = ParishGroup::where('parish_id', $parishId)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->toArray();

        $this->associationOptions = Association::query()
            ->where('pid', $parishId)
            ->where('status', 1)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($row) => ['id' => (string) $row->id, 'name' => $row->name])
            ->values()
            ->toArray();

        $this->provinces = VietnamAddressResolver::provincesForSelect();
        $this->syncFamilyWardOptions();
    }

    protected function syncFamilyWardOptions(): void
    {
        $this->familyWardOptions = VietnamAddressResolver::wardsForSelect(
            $this->family_province ? (string) $this->family_province : null
        );
    }

    public function updatedFamilyProvince(): void
    {
        $this->family_ward_id = null;
        $this->syncFamilyWardOptions();
    }

    protected function seedDefaultMember(): void
    {
        if (! empty($this->members)) {
            return;
        }

        $ref = $this->nextMemberRef();
        $this->members[] = $this->blankMember($ref, true);
        $this->submitter_ref = $ref;
    }

    protected function blankMember(string $ref, bool $isSubmitter = false): array
    {
        return [
            'ref'          => $ref,
            'family_role'  => null,
            'last_name'    => '',
            'first_name'   => '',
            'gender'       => 'male',
            'birthday'     => null,
            'birth_place'  => null,
            'birth_order'  => null,
            'saint_id'     => null,
            'father_ref'   => null,
            'mother_ref'   => null,
            'father_name'  => null,
            'mother_name'  => null,
            'cccd'         => null,
            'note'         => null,
            'association_id' => null,
            'is_submitter' => $isSubmitter,
        ];
    }

    protected function nextMemberRef(): string
    {
        $max = 0;
        foreach ($this->members as $member) {
            if (preg_match('/^m(\d+)$/', $member['ref'] ?? '', $m)) {
                $max = max($max, (int) $m[1]);
            }
        }

        return 'm' . ($max + 1);
    }

    public function openMemberForm(?int $index = null): void
    {
        $this->resetMemberForm();

        if ($index !== null && isset($this->members[$index])) {
            $row = $this->members[$index];
            $this->editingMemberIndex = $index;
            $this->member_ref = $row['ref'];
            $this->member_last_name = $row['last_name'] ?? '';
            $this->member_first_name = $row['first_name'] ?? '';
            $this->member_gender = $row['gender'] ?? 'male';
            $this->member_birthday = $row['birthday'] ?? null;
            $this->member_birth_place = $row['birth_place'] ?? null;
            $this->member_birth_order = $row['birth_order'] ?? null;
            $this->member_saint_id = $row['saint_id'] ?? null;
            $this->member_family_role = $row['family_role'] ?? null;
            $this->member_father_ref = $row['father_ref'] ?? null;
            $this->member_mother_ref = $row['mother_ref'] ?? null;
            $this->member_father_name = $row['father_name'] ?? null;
            $this->member_mother_name = $row['mother_name'] ?? null;
            $this->member_cccd = $row['cccd'] ?? null;
            $this->member_note = $row['note'] ?? null;
            $this->member_association_id = $row['association_id'] ?? null;
        } else {
            $this->member_ref = $this->nextMemberRef();
        }

        $this->showMemberForm = true;
    }

    public function closeMemberForm(): void
    {
        $this->showMemberForm = false;
        $this->resetMemberForm();
    }

    protected function resetMemberForm(): void
    {
        $this->editingMemberIndex = null;
        $this->member_ref = '';
        $this->member_last_name = '';
        $this->member_first_name = '';
        $this->member_gender = 'male';
        $this->member_birthday = null;
        $this->member_birth_place = null;
        $this->member_birth_order = null;
        $this->member_saint_id = null;
        $this->member_family_role = null;
        $this->member_father_ref = null;
        $this->member_mother_ref = null;
        $this->member_father_name = null;
        $this->member_mother_name = null;
        $this->member_cccd = null;
        $this->member_note = null;
        $this->member_association_id = null;
    }

    protected function memberFormRules(): array
    {
        return [
            'member_last_name'   => 'required|string|max:100',
            'member_first_name'  => 'required|string|max:100',
            'member_gender'      => 'required|in:male,female',
            'member_birthday'    => 'nullable|date|before:today',
            'member_birth_place' => 'nullable|string|max:255',
            'member_birth_order' => 'nullable|integer|min:1',
            'member_saint_id'    => 'nullable|integer|exists:holymanagements,id',
            'member_family_role' => 'nullable|in:husband,wife,child,other',
            'member_father_ref'  => 'nullable|string|max:20',
            'member_mother_ref'  => 'nullable|string|max:20',
            'member_father_name' => 'nullable|string|max:255',
            'member_mother_name' => 'nullable|string|max:255',
            'member_cccd'        => 'nullable|string|max:20',
            'member_note'        => 'nullable|string|max:500',
            'member_association_id' => 'nullable|integer|exists:associations,id',
        ];
    }

    public function saveMember(): void
    {
        $this->validate($this->memberFormRules(), [
            'member_last_name.required'  => 'Vui lòng nhập họ',
            'member_first_name.required' => 'Vui lòng nhập tên',
        ]);

        $row = [
            'ref'          => $this->member_ref,
            'family_role'  => $this->member_family_role,
            'last_name'    => $this->member_last_name,
            'first_name'   => $this->member_first_name,
            'gender'       => $this->member_gender,
            'birthday'     => $this->member_birthday ?: null,
            'birth_place'  => $this->member_birth_place ?: null,
            'birth_order'  => $this->member_birth_order !== '' && $this->member_birth_order !== null
                ? (int) $this->member_birth_order : null,
            'saint_id'     => $this->member_saint_id ? (int) $this->member_saint_id : null,
            'father_ref'   => $this->member_father_ref ?: null,
            'mother_ref'   => $this->member_mother_ref ?: null,
            'father_name'  => $this->member_father_name ?: null,
            'mother_name'  => $this->member_mother_name ?: null,
            'cccd'         => $this->member_cccd ?: null,
            'note'         => $this->member_note ?: null,
            'association_id' => $this->member_association_id ? (int) $this->member_association_id : null,
            'is_submitter' => $this->editingMemberIndex !== null
                ? (bool) ($this->members[$this->editingMemberIndex]['is_submitter'] ?? false)
                : false,
        ];

        if ($this->editingMemberIndex !== null) {
            $this->members[$this->editingMemberIndex] = $row;
        } else {
            $this->members[] = $row;
        }

        if (! $this->submitter_ref) {
            $this->submitter_ref = $row['ref'];
        }

        $this->closeMemberForm();
    }

    public function removeMember(int $index): void
    {
        if (! isset($this->members[$index])) {
            return;
        }

        $ref = $this->members[$index]['ref'];
        unset($this->members[$index]);
        $this->members = array_values($this->members);

        if ($this->submitter_ref === $ref) {
            $this->submitter_ref = $this->members[0]['ref'] ?? null;
        }

        $this->familyMarriages = array_values(array_filter(
            $this->familyMarriages,
            fn ($m) => ($m['husband_ref'] ?? '') !== $ref && ($m['wife_ref'] ?? '') !== $ref
        ));
        $this->familySacraments = array_values(array_filter(
            $this->familySacraments,
            fn ($s) => ($s['member_ref'] ?? '') !== $ref
        ));
    }

    public function setSubmitter(string $ref): void
    {
        foreach ($this->members as $i => $member) {
            $this->members[$i]['is_submitter'] = ($member['ref'] ?? '') === $ref;
        }
        $this->submitter_ref = $ref;
    }

    public function memberLabel(?string $ref): string
    {
        if (! $ref) {
            return '—';
        }

        foreach ($this->members as $member) {
            if (($member['ref'] ?? '') === $ref) {
                return trim(($member['last_name'] ?? '') . ' ' . ($member['first_name'] ?? ''));
            }
        }

        return $ref;
    }

    public function openMarriageForm(?int $index = null): void
    {
        $this->resetMarriageForm();

        if ($index !== null && isset($this->familyMarriages[$index])) {
            $row = $this->familyMarriages[$index];
            $this->editingMarriageIndex = $index;
            $this->marriage_husband_ref = $row['husband_ref'] ?? null;
            $this->marriage_wife_ref = $row['wife_ref'] ?? null;
            $this->marriage_married_date = $row['married_date'] ?? null;
            $this->marriage_certificate_number = $row['certificate_number'] ?? null;
            $this->marriage_parish_name = $row['parish_name'] ?? null;
            $this->marriage_witness_1 = $row['witness_1'] ?? null;
            $this->marriage_witness_2 = $row['witness_2'] ?? null;
            $this->marriage_priest_witness = $row['priest_witness'] ?? null;
            $this->marriage_status = $row['status'] ?? Marriage::STATUS_VALID;
            $this->marriage_note = $row['note'] ?? null;
        }

        $this->showMarriageForm = true;
    }

    public function closeMarriageForm(): void
    {
        $this->showMarriageForm = false;
        $this->resetMarriageForm();
    }

    protected function resetMarriageForm(): void
    {
        $this->editingMarriageIndex = null;
        $this->marriage_husband_ref = null;
        $this->marriage_wife_ref = null;
        $this->marriage_married_date = null;
        $this->marriage_certificate_number = null;
        $this->marriage_parish_name = null;
        $this->marriage_witness_1 = null;
        $this->marriage_witness_2 = null;
        $this->marriage_priest_witness = null;
        $this->marriage_status = Marriage::STATUS_VALID;
        $this->marriage_note = null;
    }

    protected function marriageFormRules(): array
    {
        return [
            'marriage_husband_ref'        => 'required|string|max:20',
            'marriage_wife_ref'           => 'required|string|max:20|different:marriage_husband_ref',
            'marriage_married_date'       => 'nullable|date',
            'marriage_certificate_number' => 'nullable|string|max:50',
            'marriage_parish_name'        => 'nullable|string|max:100',
            'marriage_witness_1'          => 'nullable|string|max:100',
            'marriage_witness_2'          => 'nullable|string|max:100',
            'marriage_priest_witness'     => 'nullable|string|max:100',
            'marriage_status'             => 'required|in:valid,invalid,widowed,divorced',
            'marriage_note'               => 'nullable|string|max:500',
        ];
    }

    public function saveMarriage(): void
    {
        $this->validate($this->marriageFormRules(), [
            'marriage_husband_ref.required' => 'Vui lòng chọn chồng',
            'marriage_wife_ref.required'    => 'Vui lòng chọn vợ',
            'marriage_wife_ref.different'   => 'Chồng và vợ phải là hai người khác nhau',
        ]);

        $row = [
            'husband_ref'        => $this->marriage_husband_ref,
            'wife_ref'           => $this->marriage_wife_ref,
            'married_date'       => $this->marriage_married_date ?: null,
            'certificate_number' => $this->marriage_certificate_number ?: null,
            'parish_name'        => $this->marriage_parish_name ?: null,
            'witness_1'          => $this->marriage_witness_1 ?: null,
            'witness_2'          => $this->marriage_witness_2 ?: null,
            'priest_witness'     => $this->marriage_priest_witness ?: null,
            'status'             => $this->marriage_status,
            'note'               => $this->marriage_note ?: null,
        ];

        if ($this->editingMarriageIndex !== null) {
            $this->familyMarriages[$this->editingMarriageIndex] = $row;
        } else {
            $this->familyMarriages[] = $row;
        }

        $this->closeMarriageForm();
    }

    public function removeMarriage(int $index): void
    {
        unset($this->familyMarriages[$index]);
        $this->familyMarriages = array_values($this->familyMarriages);
    }

    public function openFamilySacramentForm(?int $index = null): void
    {
        $this->resetFamilySacramentForm();

        if ($index !== null && isset($this->familySacraments[$index])) {
            $row = $this->familySacraments[$index];
            $this->editingFamilySacramentIndex = $index;
            $this->fs_member_ref = $row['member_ref'] ?? null;
            $this->fs_type = $row['type'] ?? '';
            $this->fs_received_date = $row['received_date'] ?? null;
            $this->fs_certificate_number = $row['certificate_number'] ?? null;
            $this->fs_book_number = $row['book_number'] ?? null;
            $this->fs_giver = $row['giver'] ?? null;
            $this->fs_sponsor = $row['sponsor'] ?? null;
            $this->fs_parish_name = $row['parish_name'] ?? null;
            $this->fs_note = $row['note'] ?? null;
        }

        $this->showFamilySacramentForm = true;
    }

    public function closeFamilySacramentForm(): void
    {
        $this->showFamilySacramentForm = false;
        $this->resetFamilySacramentForm();
    }

    protected function resetFamilySacramentForm(): void
    {
        $this->editingFamilySacramentIndex = null;
        $this->fs_member_ref = null;
        $this->fs_type = '';
        $this->fs_received_date = null;
        $this->fs_certificate_number = null;
        $this->fs_book_number = null;
        $this->fs_giver = null;
        $this->fs_sponsor = null;
        $this->fs_parish_name = null;
        $this->fs_note = null;
    }

    protected function familySacramentFormRules(): array
    {
        return [
            'fs_member_ref'        => 'required|string|max:20',
            'fs_type'                => 'required|in:baptism,communion,confirmation,anointing,holy_orders',
            'fs_received_date'       => 'nullable|date',
            'fs_certificate_number'  => 'nullable|string|max:50',
            'fs_book_number'         => 'nullable|integer|min:1',
            'fs_giver'               => 'nullable|string|max:100',
            'fs_sponsor'             => 'nullable|string|max:100',
            'fs_parish_name'         => 'nullable|string|max:100',
            'fs_note'                => 'nullable|string|max:500',
        ];
    }

    public function saveFamilySacrament(): void
    {
        $this->validate($this->familySacramentFormRules(), [
            'fs_member_ref.required' => 'Vui lòng chọn thành viên',
            'fs_type.required'       => 'Vui lòng chọn loại bí tích',
        ]);

        $row = [
            'member_ref'         => $this->fs_member_ref,
            'type'               => $this->fs_type,
            'received_date'      => $this->fs_received_date ?: null,
            'certificate_number' => $this->fs_certificate_number ?: null,
            'book_number'        => $this->fs_book_number ?: null,
            'giver'              => $this->fs_giver ?: null,
            'sponsor'            => $this->fs_sponsor ?: null,
            'parish_name'        => $this->fs_parish_name ?: null,
            'note'               => $this->fs_note ?: null,
        ];

        if ($this->editingFamilySacramentIndex !== null) {
            $this->familySacraments[$this->editingFamilySacramentIndex] = $row;
        } else {
            $this->familySacraments[] = $row;
        }

        $this->closeFamilySacramentForm();
    }

    public function removeFamilySacrament(int $index): void
    {
        unset($this->familySacraments[$index]);
        $this->familySacraments = array_values($this->familySacraments);
    }

    protected function buildFamilyRegisterPayload(): array
    {
        return [
            'version'        => 2,
            'family'         => [
                'code'            => $this->family_code,
                'name'            => trim($this->family_name) ?: null,
                'parish_area_id'  => $this->family_parish_area_id ? (int) $this->family_parish_area_id : null,
                'address'         => $this->family_address ?: null,
                'province'        => $this->family_province ?: null,
                'ward_id'         => $this->family_ward_id ? (int) $this->family_ward_id : null,
            ],
            'members'        => $this->members,
            'contact_phone'  => $this->contact_phone,
            'submitter_ref'  => $this->submitter_ref,
        ];
    }

    protected function familyRegisterSubmitRules(): array
    {
        return [
            'targetParishId'        => 'required|integer|exists:parishes,id',
            'family_name'           => 'string|max:255',
            'contact_phone'         => 'required|string|max:20',
            'submitter_ref'         => 'required|string|max:20',
            'members'               => 'required|array|min:1',
            'members.*.last_name'   => 'required|string|max:100',
            'members.*.first_name'  => 'required|string|max:100',
            'members.*.gender'      => 'required|in:male,female',
        ];
    }

    protected function familyRegisterSubmitMessages(): array
    {
        return [
            'targetParishId.required' => 'Vui lòng chọn giáo xứ',
            'contact_phone.required'  => 'Vui lòng nhập số điện thoại liên hệ',
            'submitter_ref.required'  => 'Vui lòng chọn người đăng ký',
            'members.required'        => 'Cần ít nhất một thành viên trong hộ',
            'members.min'             => 'Cần ít nhất một thành viên trong hộ',
        ];
    }

    public function saintName(?int $saintId): ?string
    {
        if (! $saintId) {
            return null;
        }

        foreach ($this->saints as $saint) {
            if ((int) ($saint['id'] ?? 0) === (int) $saintId) {
                return $saint['name'] ?? null;
            }
        }

        return null;
    }
}
