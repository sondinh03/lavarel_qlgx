<?php

namespace App\Http\Livewire\Parishioners\Concerns;

use App\Models\Association;
use App\Models\Deanery;
use App\Models\Diocese;
use App\Models\Holymanagement;
use App\Models\ParishGroup;
use App\Models\Parishioner;
use App\Models\ParishNew;
use App\Support\CacheKeys;
use App\Support\VietnamAddressResolver;
use Illuminate\Support\Facades\Storage;

trait ManagesParishionerForm
{
    public string  $last_name   = '';
    public string  $first_name  = '';
    public string  $gender      = 'male';
    public ?string $birthday    = null;
    public ?string $birth_place = null;
    public         $birth_order = null;
    public         $saint_id    = null;
    public ?string $cccd        = null;
    public ?string $phone       = null;
    public ?string $email       = null;
    public ?string $note        = null;
    public         $avatar      = null;
    public ?string $currentAvatarPath = null;

    public ?string $origin              = null;
    public ?string $permanent_province  = null;
    public         $permanent_ward_id   = null;
    public ?string $permanent_residence = null;
    public ?string $temporary_province  = null;
    public         $temporary_ward_id   = null;
    public ?string $temporary_residence = null;

    public ?string $father_name = null;
    public ?string $mother_name = null;
    public         $father_id   = null;
    public         $mother_id   = null;
    public         $family_id   = null;
    public ?string $family_role = null;
    public int     $married     = 0;

    public         $ethnic            = null;
    public         $career            = null;
    public         $education_level   = null;
    public         $specialist_level  = null;
    public         $catechism_level   = null;
    public ?string $catechism_major   = null;
    public         $position          = null;
    public         $language          = null;
    public         $holy_order_status = null;

    public bool $status               = true;
    public bool $is_active            = true;
    public bool $is_new_convert       = false;
    public bool $is_included_in_stats = true;

    public         $parish_area_id   = null;
    public         $association_id   = null;
    public         $diocese_id       = null;
    public         $deanery_id       = null;
    public         $parish_id        = null;
    public         $level            = null;
    public ?string $joined_date      = null;
    public         $transferred_from = null;
    public ?string $transferred_date = null;
    public ?string $left_reason      = null;

    public bool    $is_deceased       = false;
    public ?string $death_date        = null;
    public ?string $death_book_number = null;
    public ?string $death_place       = null;
    public ?string $burial_place      = null;

    public array $saints        = [];
    public array $parishGroups              = [];
    public array $associationOptions        = [];
    public array $parishionerSearchOptions  = [];
    public array $provinces                 = [];
    public array $permanentWardOptions      = [];
    public array $temporaryWardOptions      = [];
    public array $dioceses                  = [];
    public array $deaneryOptions            = [];
    public array $parishOptions             = [];

    protected function parishionerFormRules(): array
    {
        return [
            'last_name'             => 'required|string|max:100',
            'first_name'            => 'required|string|max:100',
            'gender'                => 'required|in:male,female',
            'birthday'              => 'nullable|date|before:today',
            'birth_place'           => 'nullable|string|max:255',
            'birth_order'           => 'nullable|integer|min:0',
            'saint_id'              => 'nullable|integer|exists:holymanagements,id',
            'cccd'                  => 'nullable|string|max:20',
            'phone'                 => 'nullable|string|max:20',
            'email'                 => 'nullable|email|max:255',
            'note'                  => 'nullable|string|max:1000',
            'avatar'                => 'nullable|image|max:2048',
            'ethnic'                => 'nullable|integer',
            'career'                => 'nullable|integer',
            'education_level'       => 'nullable|integer',
            'specialist_level'      => 'nullable|integer',
            'catechism_level'       => 'nullable|integer',
            'catechism_major'       => 'nullable|string|max:255',
            'position'              => 'nullable|integer',
            'language'              => 'nullable|integer',
            'holy_order_status'     => 'nullable|integer',
            'origin'                => 'nullable|string|max:255',
            'permanent_province'    => 'nullable|string|max:255',
            'permanent_ward_id'     => 'nullable|integer',
            'permanent_residence'   => 'nullable|string|max:255',
            'temporary_province'    => 'nullable|string|max:255',
            'temporary_ward_id'     => 'nullable|integer',
            'temporary_residence'   => 'nullable|string|max:255',
            'father_name'           => 'nullable|string|max:255',
            'mother_name'           => 'nullable|string|max:255',
            'married'               => 'required|integer|in:0,1,2,3',
            'parish_area_id'        => 'nullable|integer|exists:parish_groups,id',
            'association_id'        => 'nullable|integer|exists:associations,id',
            'diocese_id'            => 'nullable|integer|exists:dioceses,id',
            'deanery_id'            => 'nullable|integer|exists:deanerys,id',
            'parish_id'             => 'nullable|integer|exists:parishes,id',
            'level'                 => 'nullable|integer',
            'joined_date'           => 'nullable|date',
            'transferred_from'      => 'nullable|integer|exists:parishes,id',
            'transferred_date'      => 'nullable|date',
            'left_reason'           => 'nullable|string|max:255',
            'status'                => 'required|boolean',
            'is_active'             => 'required|boolean',
            'is_new_convert'        => 'required|boolean',
            'is_included_in_stats'  => 'required|boolean',
            'death_date'            => 'nullable|date|required_if:is_deceased,true',
            'death_book_number'     => 'nullable|string|max:20',
            'death_place'           => 'nullable|string|max:255',
            'burial_place'          => 'nullable|string|max:255',
        ];
    }

    protected function parishionerFormMessages(): array
    {
        return [
            'last_name.required'     => 'Vui lòng nhập họ',
            'first_name.required'    => 'Vui lòng nhập tên',
            'gender.required'        => 'Vui lòng chọn giới tính',
            'birthday.before'        => 'Ngày sinh phải trước hôm nay',
            'email.email'            => 'Email không hợp lệ',
            'avatar.image'           => 'File phải là ảnh',
            'death_date.required_if' => 'Vui lòng nhập ngày mất',
        ];
    }

    protected function normalizeParishionerFormValues(): void
    {
        foreach ($this->nullableIntegerFormFields() as $field) {
            $value = $this->{$field};
            if ($value === '' || $value === null) {
                $this->{$field} = null;
            } elseif (is_numeric($value)) {
                $this->{$field} = (int) $value;
            }
        }
    }

    protected function nullableIntegerFormFields(): array
    {
        return [
            'birth_order', 'saint_id', 'permanent_ward_id', 'temporary_ward_id',
            'father_id', 'mother_id', 'family_id',
            'ethnic', 'career', 'education_level', 'specialist_level', 'catechism_level',
            'position', 'language', 'holy_order_status',
            'parish_area_id', 'association_id', 'diocese_id', 'deanery_id', 'parish_id', 'level', 'transferred_from',
        ];
    }

    protected function nullableFormInt($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return is_numeric($value) ? (int) $value : null;
    }

    protected function parishionerFormSectionFields(): array
    {
        return [
            'basic' => [
                'last_name', 'first_name', 'gender', 'birthday', 'birth_place', 'birth_order',
                'saint_id', 'cccd', 'phone', 'email', 'note', 'avatar',
                'ethnic', 'career', 'education_level', 'specialist_level', 'catechism_level',
                'catechism_major', 'position', 'language', 'holy_order_status',
                'status', 'is_active', 'is_new_convert', 'is_included_in_stats',
            ],
            'address' => [
                'origin', 'permanent_province', 'permanent_ward_id', 'permanent_residence',
                'temporary_province', 'temporary_ward_id', 'temporary_residence',
            ],
            'parish' => [
                'diocese_id', 'deanery_id', 'parish_id', 'parish_area_id', 'association_id', 'level', 'joined_date',
                'transferred_from', 'transferred_date', 'left_reason',
            ],
            'family' => [
                'father_name', 'mother_name', 'married',
            ],
            'deceased' => [
                'death_date', 'death_book_number', 'death_place', 'burial_place',
            ],
        ];
    }

    protected function parishionerFormRulesForSection(string $section): array
    {
        $fields = $this->parishionerFormSectionFields()[$section] ?? [];

        return array_intersect_key($this->parishionerFormRules(), array_flip($fields));
    }

    protected function buildParishionerSaveDataForSection(string $section, int $parishId): array
    {
        $fields = $this->parishionerFormSectionFields()[$section] ?? [];

        return array_intersect_key($this->buildParishionerSaveData($parishId), array_flip($fields));
    }

    protected function applyParishionerSectionSave(Parishioner $parishioner, string $section): array
    {
        $this->normalizeParishionerFormValues();
        $this->validate($this->parishionerFormRulesForSection($section), $this->parishionerFormMessages());

        $data = $this->buildParishionerSaveDataForSection(
            $section,
            $this->nullableFormInt($this->parish_id) ?? $parishioner->parish_id
        );

        if ($section === 'basic') {
            $this->persistParishionerAvatar($data);
        }

        return $data;
    }

    protected function normalizeDropdownList(mixed $value): array
    {
        if ($value instanceof \Illuminate\Support\Collection) {
            return $value->values()->toArray();
        }

        return is_array($value) ? array_values($value) : [];
    }

    protected function loadParishionerDropdowns(int $parishId): void
    {
        $this->saints = $this->normalizeDropdownList(cache()->remember(
            CacheKeys::SAINTS_LIST,
            now()->addHours(24),
            fn () => Holymanagement::orderBy('name')->get(['id', 'name'])->toArray()
        ));

        $this->parishGroups = $this->normalizeDropdownList(cache()->remember(
            CacheKeys::parishGroups($parishId),
            now()->addHours(24),
            fn () => ParishGroup::where('parish_id', $parishId)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->toArray()
        ));

        $this->loadAddressDropdowns();
        $this->loadHierarchyDropdowns();
        $this->syncAssociationOptions($parishId);
    }

    protected function loadHierarchyDropdowns(): void
    {
        $this->dioceses = $this->normalizeDropdownList(
            Diocese::query()
                ->where('status', 1)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn ($row) => ['id' => (string) $row->id, 'name' => $row->name])
                ->values()
                ->toArray()
        );

        $this->syncDeaneryOptions();
        $this->syncParishOptions();
        $this->syncParishGroupsFromFormParish();
    }

    protected function syncDeaneryOptions(): void
    {
        if (! $this->diocese_id) {
            $this->deaneryOptions = [];

            return;
        }

        $this->deaneryOptions = $this->normalizeDropdownList(
            Deanery::query()
                ->where('did', $this->diocese_id)
                ->where('status', 1)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn ($row) => ['id' => (string) $row->id, 'name' => $row->name])
                ->values()
                ->toArray()
        );
    }

    protected function syncParishOptions(): void
    {
        if (! $this->deanery_id) {
            $this->parishOptions = [];

            return;
        }

        $this->parishOptions = $this->normalizeDropdownList(
            ParishNew::query()
                ->where('deanery_id', $this->deanery_id)
                ->where('status', 1)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn ($row) => ['id' => (string) $row->id, 'name' => $row->name])
                ->values()
                ->toArray()
        );
    }

    protected function syncParishGroupsFromFormParish(): void
    {
        $parishId = $this->nullableFormInt($this->parish_id);
        if (! $parishId) {
            return;
        }

        $this->parishGroups = $this->normalizeDropdownList(
            ParishGroup::where('parish_id', $parishId)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->toArray()
        );
    }

    public function updatedDioceseId(): void
    {
        $this->deanery_id = null;
        $this->parish_id = null;
        $this->parish_area_id = null;
        $this->association_id = null;
        $this->syncDeaneryOptions();
        $this->parishOptions = [];
        $this->parishGroups = [];
        $this->associationOptions = [];
    }

    public function updatedDeaneryId(): void
    {
        $this->parish_id = null;
        $this->parish_area_id = null;
        $this->association_id = null;
        $this->syncParishOptions();
        $this->parishGroups = [];
        $this->associationOptions = [];
    }

    public function updatedParishId(): void
    {
        $this->parish_area_id = null;
        $this->association_id = null;

        if ($this->parish_id) {
            $parish = ParishNew::find($this->parish_id);
            if ($parish) {
                $this->diocese_id = $parish->diocese_id;
                $this->deanery_id = $parish->deanery_id;
                $this->syncDeaneryOptions();
                $this->syncParishOptions();
            }
        }

        $this->syncParishGroupsFromFormParish();
        $this->syncAssociationOptions();
    }

    protected function syncAssociationOptions(?int $parishId = null): void
    {
        $parishId = $parishId ?? $this->nullableFormInt($this->parish_id);
        if (! $parishId) {
            $this->associationOptions = [];

            return;
        }

        $this->associationOptions = $this->normalizeDropdownList(
            Association::query()
                ->where('pid', $parishId)
                ->where('status', 1)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn ($row) => ['id' => (string) $row->id, 'name' => $row->name])
                ->values()
                ->toArray()
        );
    }

    protected function loadAddressDropdowns(): void
    {
        $this->provinces = VietnamAddressResolver::provincesForSelect();
        $this->syncWardOptionsFromProvinces();
    }

    protected function syncWardOptionsFromProvinces(): void
    {
        $this->permanentWardOptions = VietnamAddressResolver::wardsForSelect(
            $this->permanent_province ? (string) $this->permanent_province : null
        );
        $this->temporaryWardOptions = VietnamAddressResolver::wardsForSelect(
            $this->temporary_province ? (string) $this->temporary_province : null
        );
    }

    public function updatedPermanentProvince(): void
    {
        $this->permanent_ward_id = null;
        $this->permanentWardOptions = VietnamAddressResolver::wardsForSelect(
            $this->permanent_province ? (string) $this->permanent_province : null
        );
    }

    public function updatedTemporaryProvince(): void
    {
        $this->temporary_ward_id = null;
        $this->temporaryWardOptions = VietnamAddressResolver::wardsForSelect(
            $this->temporary_province ? (string) $this->temporary_province : null
        );
    }

    protected function loadParishionerSearchOptions(int $parishId, ?int $excludeId = null): void
    {
        $query = Parishioner::query()
            ->with('saint')
            ->where('parish_id', $parishId)
            ->orderBy('last_name')
            ->orderBy('first_name');

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $this->parishionerSearchOptions = $query->limit(500)->get()
            ->map(fn ($p) => [
                'id'   => $p->id,
                'name' => $p->full_name_with_saint,
            ])
            ->toArray();
    }

    protected function mapParishionerToForm(Parishioner $p): void
    {
        $this->last_name         = $p->last_name;
        $this->first_name        = $p->first_name;
        $this->gender            = $p->gender ?? 'male';
        $this->birthday          = $p->birthday?->format('Y-m-d');
        $this->birth_place       = $p->birth_place;
        $this->birth_order       = $p->birth_order;
        $this->saint_id          = $p->saint_id;
        $this->cccd              = $p->cccd;
        $this->phone             = $p->phone;
        $this->email             = $p->email;
        $this->note              = $p->note;
        $this->currentAvatarPath = $p->avatar_path;

        $this->origin              = $p->origin;
        $this->permanent_province  = VietnamAddressResolver::resolveProvinceKey($p->permanent_province)
            ?? $p->permanent_province;
        $this->permanent_ward_id   = $p->permanent_ward_id;
        $this->permanent_residence = $p->permanent_residence;
        $this->temporary_province  = VietnamAddressResolver::resolveProvinceKey($p->temporary_province)
            ?? $p->temporary_province;
        $this->temporary_ward_id   = $p->temporary_ward_id;
        $this->temporary_residence = $p->temporary_residence;
        $this->syncWardOptionsFromProvinces();

        $this->father_name = $p->father_name;
        $this->mother_name = $p->mother_name;
        $this->father_id   = $p->father_id;
        $this->mother_id   = $p->mother_id;
        $this->family_id   = $p->family_id;
        $this->family_role = $p->family_role;
        $this->married     = $p->married ?? 0;

        $this->ethnic            = $p->ethnic;
        $this->career            = $p->career;
        $this->education_level   = $p->education_level;
        $this->specialist_level  = $p->specialist_level;
        $this->catechism_level   = $p->catechism_level;
        $this->catechism_major   = $p->catechism_major;
        $this->position          = $p->position;
        $this->language          = $p->language;
        $this->holy_order_status = $p->holy_order_status;

        $this->status               = (bool) $p->status;
        $this->is_active            = (bool) $p->is_active;
        $this->is_new_convert       = (bool) $p->is_new_convert;
        $this->is_included_in_stats = (bool) $p->is_included_in_stats;

        $this->parish_area_id   = $p->parish_area_id;
        $this->association_id   = $p->association_id;
        $this->diocese_id       = $p->diocese_id;
        $this->deanery_id       = $p->deanery_id;
        $this->parish_id        = $p->parish_id;
        $this->level            = $p->level;
        $this->joined_date      = $p->joined_date?->format('Y-m-d');
        $this->transferred_from = $p->transferred_from;
        $this->transferred_date = $p->transferred_date?->format('Y-m-d');
        $this->left_reason      = $p->left_reason;

        $this->is_deceased       = $p->death_date !== null;
        $this->death_date        = $p->death_date?->format('Y-m-d');
        $this->death_book_number = $p->death_book_number;
        $this->death_place       = $p->death_place;
        $this->burial_place      = $p->burial_place;

        $this->loadHierarchyDropdowns();
    }

    protected function buildParishionerSaveData(int $parishId): array
    {
        $this->normalizeParishionerFormValues();

        return [
            'last_name'             => $this->last_name,
            'first_name'            => $this->first_name,
            'gender'                => $this->gender,
            'birthday'              => $this->birthday ?: null,
            'birth_place'           => $this->birth_place ?: null,
            'birth_order'           => $this->nullableFormInt($this->birth_order),
            'saint_id'              => $this->nullableFormInt($this->saint_id),
            'cccd'                  => $this->cccd,
            'phone'                 => $this->phone,
            'email'                 => $this->email,
            'note'                  => $this->note,
            'diocese_id'            => $this->nullableFormInt($this->diocese_id),
            'deanery_id'            => $this->nullableFormInt($this->deanery_id),
            'parish_id'             => $this->nullableFormInt($this->parish_id) ?? $parishId,
            'origin'                => $this->origin,
            'permanent_province'    => $this->permanent_province,
            'permanent_ward_id'     => $this->nullableFormInt($this->permanent_ward_id),
            'permanent_residence'   => $this->permanent_residence,
            'temporary_province'    => $this->temporary_province,
            'temporary_ward_id'     => $this->nullableFormInt($this->temporary_ward_id),
            'temporary_residence'   => $this->temporary_residence,
            'father_name'           => $this->father_name,
            'mother_name'           => $this->mother_name,
            'married'               => $this->married,
            'ethnic'                => $this->nullableFormInt($this->ethnic),
            'career'                => $this->nullableFormInt($this->career),
            'education_level'       => $this->nullableFormInt($this->education_level),
            'specialist_level'      => $this->nullableFormInt($this->specialist_level),
            'catechism_level'       => $this->nullableFormInt($this->catechism_level),
            'catechism_major'       => $this->catechism_major,
            'position'              => $this->nullableFormInt($this->position),
            'language'              => $this->nullableFormInt($this->language),
            'holy_order_status'     => $this->nullableFormInt($this->holy_order_status),
            'parish_area_id'        => $this->nullableFormInt($this->parish_area_id),
            'association_id'        => $this->nullableFormInt($this->association_id),
            'level'                 => $this->nullableFormInt($this->level),
            'joined_date'           => $this->joined_date ?: null,
            'transferred_from'      => $this->nullableFormInt($this->transferred_from),
            'transferred_date'      => $this->transferred_date ?: null,
            'left_reason'           => $this->left_reason,
            'status'                => $this->status,
            'is_active'             => $this->is_active,
            'is_new_convert'        => $this->is_new_convert,
            'is_included_in_stats'  => $this->is_included_in_stats,
            'death_date'            => $this->is_deceased ? ($this->death_date ?: null) : null,
            'death_book_number'     => $this->is_deceased ? $this->death_book_number : null,
            'death_place'           => $this->is_deceased ? $this->death_place : null,
            'burial_place'          => $this->is_deceased ? $this->burial_place : null,
        ];
    }

    protected function persistParishionerAvatar(array &$data): void
    {
        if ($this->avatar) {
            if ($this->currentAvatarPath) {
                Storage::disk('public')->delete($this->currentAvatarPath);
            }
            $data['avatar_path'] = $this->avatar->store('parishioners', 'public');
        }
    }

    protected function resetParishionerForm(): void
    {
        $this->reset([
            'last_name', 'first_name', 'birthday', 'birth_place', 'birth_order', 'saint_id',
            'cccd', 'phone', 'email', 'note', 'avatar', 'currentAvatarPath',
            'origin', 'permanent_province', 'permanent_ward_id', 'permanent_residence',
            'temporary_province', 'temporary_ward_id', 'temporary_residence',
            'father_name', 'mother_name', 'father_id', 'mother_id', 'family_id', 'family_role',
            'ethnic', 'career', 'education_level', 'specialist_level', 'catechism_level',
            'catechism_major', 'position', 'language', 'holy_order_status',
            'parish_area_id', 'association_id', 'diocese_id', 'deanery_id', 'parish_id', 'level', 'joined_date', 'transferred_from', 'transferred_date', 'left_reason',
            'death_date', 'death_book_number', 'death_place', 'burial_place',
        ]);
        $this->gender               = 'male';
        $this->married              = 0;
        $this->status               = true;
        $this->is_active            = true;
        $this->is_new_convert       = false;
        $this->is_included_in_stats = true;
        $this->is_deceased          = false;
    }

    public function updatedIsDeceased(): void
    {
        if (!$this->is_deceased) {
            $this->death_date        = null;
            $this->death_book_number = null;
            $this->death_place       = null;
            $this->burial_place      = null;
        }
    }
}
