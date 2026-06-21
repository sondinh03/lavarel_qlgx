<?php

namespace App\Http\Livewire\Parishioners\Concerns;

use App\Models\Holymanagement;
use App\Models\ParishGroup;
use App\Models\Parishioner;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

trait ManagesParishionerForm
{
    public string  $last_name   = '';
    public string  $first_name  = '';
    public string  $gender      = 'male';
    public ?string $birthday    = null;
    public ?string $birth_place = null;
    public ?int    $birth_order = null;
    public ?int    $saint_id    = null;
    public ?string $cccd        = null;
    public ?string $phone       = null;
    public ?string $email       = null;
    public ?string $note        = null;
    public         $avatar      = null;
    public ?string $currentAvatarPath = null;

    public ?string $origin              = null;
    public ?string $permanent_province  = null;
    public ?int    $permanent_ward_id   = null;
    public ?string $permanent_residence = null;
    public ?string $temporary_province  = null;
    public ?int    $temporary_ward_id   = null;
    public ?string $temporary_residence = null;

    public ?string $father_name = null;
    public ?string $mother_name = null;
    public ?int    $father_id   = null;
    public ?int    $mother_id   = null;
    public ?int    $family_id   = null;
    public ?string $family_role = null;
    public int     $married     = 0;

    public ?int    $ethnic            = null;
    public ?int    $career            = null;
    public ?int    $education_level   = null;
    public ?int    $specialist_level  = null;
    public ?int    $catechism_level   = null;
    public ?string $catechism_major   = null;
    public ?int    $position          = null;
    public ?int    $language          = null;
    public ?int    $holy_order_status = null;

    public bool $status               = true;
    public bool $is_active            = true;
    public bool $is_new_convert       = false;
    public bool $is_included_in_stats = true;

    public ?int    $parish_area_id   = null;
    public ?int    $level            = null;
    public ?string $joined_date      = null;
    public ?int    $transferred_from = null;
    public ?string $transferred_date = null;
    public ?string $left_reason      = null;

    public bool    $is_deceased       = false;
    public ?string $death_date        = null;
    public ?string $death_book_number = null;
    public ?string $death_place       = null;
    public ?string $burial_place      = null;

    public array $saints        = [];
    public array $parishGroups  = [];
    public array $parishionerSearchOptions = [];

    protected function parishionerFormRules(): array
    {
        return [
            'last_name'             => 'required|string|max:100',
            'first_name'            => 'required|string|max:100',
            'gender'                => 'required|in:male,female',
            'birthday'              => 'nullable|date|before:today',
            'birth_place'           => 'nullable|string|max:255',
            'birth_order'           => 'nullable|integer|min:1',
            'saint_id'              => 'nullable|integer|exists:holymanagements,id',
            'cccd'                  => 'nullable|string|max:20',
            'phone'                 => 'nullable|string|max:20',
            'email'                 => 'nullable|email|max:255',
            'note'                  => 'nullable|string|max:1000',
            'avatar'                => 'nullable|image|max:2048',
            'origin'                => 'nullable|string|max:255',
            'permanent_province'    => 'nullable|string|max:255',
            'permanent_residence'   => 'nullable|string|max:255',
            'temporary_province'    => 'nullable|string|max:255',
            'temporary_residence'   => 'nullable|string|max:255',
            'father_name'           => 'nullable|string|max:255',
            'mother_name'           => 'nullable|string|max:255',
            'father_id'             => 'nullable|integer|exists:parishioners_new,id',
            'mother_id'             => 'nullable|integer|exists:parishioners_new,id',
            'family_id'             => 'nullable|integer|exists:families,id',
            'family_role'           => 'nullable|in:husband,wife,child,other',
            'married'               => 'required|integer|in:0,1,2,3',
            'parish_area_id'        => 'nullable|integer|exists:parish_groups,id',
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

    protected function loadParishionerDropdowns(int $parishId): void
    {
        $this->saints = Holymanagement::orderBy('name')
            ->pluck('name', 'id')
            ->toArray();

        $this->parishGroups = ParishGroup::where('parish_id', $parishId)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
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
        $this->permanent_province  = $p->permanent_province;
        $this->permanent_ward_id   = $p->permanent_ward_id;
        $this->permanent_residence = $p->permanent_residence;
        $this->temporary_province  = $p->temporary_province;
        $this->temporary_ward_id   = $p->temporary_ward_id;
        $this->temporary_residence = $p->temporary_residence;

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
    }

    protected function buildParishionerSaveData(int $parishId): array
    {
        return [
            'last_name'             => $this->last_name,
            'first_name'            => $this->first_name,
            'gender'                => $this->gender,
            'birthday'              => $this->birthday ?: null,
            'birth_place'           => $this->birth_place ?: null,
            'birth_order'           => $this->birth_order,
            'saint_id'              => $this->saint_id,
            'cccd'                  => $this->cccd,
            'phone'                 => $this->phone,
            'email'                 => $this->email,
            'note'                  => $this->note,
            'parish_id'             => $parishId,
            'origin'                => $this->origin,
            'permanent_province'    => $this->permanent_province,
            'permanent_ward_id'     => $this->permanent_ward_id,
            'permanent_residence'   => $this->permanent_residence,
            'temporary_province'    => $this->temporary_province,
            'temporary_ward_id'     => $this->temporary_ward_id,
            'temporary_residence'   => $this->temporary_residence,
            'father_name'           => $this->father_name,
            'mother_name'           => $this->mother_name,
            'father_id'             => $this->father_id,
            'mother_id'             => $this->mother_id,
            'family_id'             => $this->family_id,
            'family_role'           => $this->family_role ?: null,
            'married'               => $this->married,
            'ethnic'                => $this->ethnic,
            'career'                => $this->career,
            'education_level'       => $this->education_level,
            'specialist_level'      => $this->specialist_level,
            'catechism_level'       => $this->catechism_level,
            'catechism_major'       => $this->catechism_major,
            'position'              => $this->position,
            'language'              => $this->language,
            'holy_order_status'     => $this->holy_order_status,
            'parish_area_id'        => $this->parish_area_id,
            'level'                 => $this->level,
            'joined_date'           => $this->joined_date ?: null,
            'transferred_from'      => $this->transferred_from,
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
            'parish_area_id', 'level', 'joined_date', 'transferred_from', 'transferred_date', 'left_reason',
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
