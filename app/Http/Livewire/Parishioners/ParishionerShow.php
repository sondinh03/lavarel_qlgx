<?php

namespace App\Http\Livewire\Parishioners;

use App\Models\Marriage;
use App\Models\Parishioner;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class ParishionerShow extends Component
{
    use WithFileUploads;
    use AuthorizesRequests;

    // ==================== PROPS ====================

    public Parishioner $parishioner;
    public string $activeTab = 'basic';

    // ==================== UI STATE ====================

    public bool $showEditBasic     = false;
    public bool $showEditAddress   = false;
    public bool $showEditFamily    = false;
    public bool $showEditParish    = false;
    public bool $showEditMarriage  = false;
    public bool $showEditDeceased  = false;
    public bool $showDeleteConfirm = false;

    // ==================== FORM: CƠ BẢN ====================

    public string  $last_name   = '';
    public string  $first_name  = '';
    public string  $gender      = 'male';
    public ?string $birthday    = null;
    public ?int    $birth_order = null;
    public ?int    $saint_id    = null;
    public ?string $cccd        = null;
    public ?string $phone       = null;
    public ?string $email       = null;
    public ?string $note        = null;
    public         $avatar      = null;
    public ?string $currentAvatarPath = null;

    // Phân loại cá nhân - xã hội
    public ?int    $ethnic            = null;
    public ?int    $career            = null;
    public ?int    $education_level   = null;
    public ?int    $specialist_level  = null;
    public ?int    $catechism_level   = null;
    public ?string $catechism_major   = null;
    public ?int    $position          = null;
    public ?int    $language          = null;
    public ?int    $holy_order_status = null;

    // Trạng thái — dùng chung giữa form cơ bản và form giáo xứ
    public bool $status               = true;
    public bool $is_active            = true;
    public bool $is_new_convert       = false;
    public bool $is_included_in_stats = true;

    // ==================== FORM: ĐỊA CHỈ ====================

    public ?string $origin              = null;
    public ?string $permanent_province  = null;
    public ?int    $permanent_ward_id   = null;
    public ?string $permanent_residence = null;
    public ?string $temporary_province  = null;
    public ?int    $temporary_ward_id   = null;
    public ?string $temporary_residence = null;

    // ==================== FORM: GIA ĐÌNH ====================

    public ?string $father_name = null;
    public ?string $mother_name = null;
    public ?int    $father_id   = null;
    public ?int    $mother_id   = null;
    public ?int    $family_id   = null;
    public int     $married     = 0;

    // ==================== FORM: SINH HOẠT GIÁO XỨ ====================

    public ?int    $parish_area_id   = null;   // Giáo họ
    public ?int    $level            = null;   // Cấp bậc
    public ?string $joined_date      = null;   // Ngày gia nhập xứ
    public ?int    $transferred_from = null;   // Chuyển từ xứ (FK parishes)
    public ?string $transferred_date = null;   // Ngày chuyển đến
    public ?string $left_reason      = null;   // Lý do rời xứ

    // ==================== FORM: HÔN PHỐI ====================

    public ?int    $marriage_id          = null;
    public ?int    $spouse_id            = null;
    public ?string $married_date         = null;
    public ?string $certificate_number   = null;
    public ?int    $marriage_parish_id   = null;
    public ?string $marriage_parish_name = null;
    public ?string $place_province       = null;
    public ?int    $place_ward_id        = null;
    public ?string $priest_witness       = null;
    public string  $marriage_status      = 'valid';
    public ?string $witness_1            = null;
    public ?string $witness_2            = null;
    public ?string $marriage_note        = null;

    // ==================== FORM: TỬ VONG ====================

    public bool    $is_deceased       = false;
    public ?string $death_date        = null;
    public ?string $death_book_number = null;
    public ?string $death_place       = null;
    public ?string $burial_place      = null;

    // ==================== VALIDATION ====================

    protected function rulesBasic(): array
    {
        return [
            'last_name'         => 'required|string|max:100',
            'first_name'        => 'required|string|max:100',
            'gender'            => 'required|in:male,female',
            'birthday'          => 'nullable|date|before:today',
            'birth_order'       => 'nullable|integer|min:1',
            'saint_id'          => 'nullable|integer|exists:holymanagements,id',
            'cccd'              => 'nullable|string|max:20',
            'phone'             => 'nullable|string|max:20',
            'email'             => 'nullable|email|max:255',
            'note'              => 'nullable|string|max:1000',
            'avatar'            => 'nullable|image|max:2048',
            'specialist_level'  => 'nullable|integer',
            'catechism_major'   => 'nullable|string|max:100',
            'language'          => 'nullable|integer',
            'holy_order_status' => 'nullable|integer',
        ];
    }

    protected function rulesAddress(): array
    {
        return [
            'origin'              => 'nullable|string|max:255',
            'permanent_province'  => 'nullable|string|max:255',
            'permanent_residence' => 'nullable|string|max:255',
            'temporary_province'  => 'nullable|string|max:255',
            'temporary_residence' => 'nullable|string|max:255',
        ];
    }

    protected function rulesFamily(): array
    {
        return [
            'father_name' => 'nullable|string|max:255',
            'mother_name' => 'nullable|string|max:255',
            'father_id'   => 'nullable|integer|exists:parishioners_new,id',
            'mother_id'   => 'nullable|integer|exists:parishioners_new,id',
            'family_id'   => 'nullable|integer|exists:families,id',
            'married'     => 'required|integer|in:0,1,2,3',
        ];
    }

    protected function rulesParish(): array
    {
        return [
            'parish_area_id'       => 'nullable|integer|exists:parish_groups,id',
            'level'                => 'nullable|integer',
            'joined_date'          => 'nullable|date',
            'transferred_from'     => 'nullable|integer|exists:parishes,id',
            'transferred_date'     => 'nullable|date',
            'left_reason'          => 'nullable|string|max:255',
            'status'               => 'required|boolean',
            'is_active'            => 'required|boolean',
            'is_new_convert'       => 'required|boolean',
            'is_included_in_stats' => 'required|boolean',
        ];
    }

    protected function rulesMarriage(): array
    {
        return [
            'married_date'         => 'nullable|date',
            'certificate_number'   => 'nullable|string|max:50',
            'marriage_parish_id'   => 'nullable|integer|exists:parishes,id',
            'marriage_parish_name' => 'nullable|string|max:100',
            'place_province'       => 'nullable|string|max:100',
            'place_ward_id'        => 'nullable|integer',
            'priest_witness'       => 'nullable|string|max:100',
            'marriage_status'      => 'required|in:valid,invalid,widowed,divorced',
            'witness_1'            => 'nullable|string|max:100',
            'witness_2'            => 'nullable|string|max:100',
            'marriage_note'        => 'nullable|string|max:500',
        ];
    }

    protected function rulesDeceased(): array
    {
        return [
            'death_date'        => 'required_if:is_deceased,true|nullable|date',
            'death_book_number' => 'nullable|string|max:20',
            'death_place'       => 'nullable|string|max:255',
            'burial_place'      => 'nullable|string|max:255',
        ];
    }

    // ==================== LIFECYCLE ====================

    public function mount(Parishioner $parishioner): void
    {
        $this->authorize('view', $parishioner);
        $this->parishioner = $parishioner->load([
            'saint', 'parishGroup', 'parish', 'deanery', 'diocese',
            'family', 'father', 'mother',
            'baptism', 'communion', 'confirmation', 'holyOrders', 'anointing',
            'marriageAsHusband.wife', 'marriageAsWife.husband',
            'sacraments', 'transferredFromParish',
        ]);

        $this->is_deceased = $this->parishioner->death_date !== null;
    }

    public function goToTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    // ==================== EDIT: CƠ BẢN ====================

    public function openEditBasic(): void
    {
        $this->authorize('update', $this->parishioner);
        $p = $this->parishioner;

        $this->last_name         = $p->last_name;
        $this->first_name        = $p->first_name;
        $this->gender            = $p->gender ?? 'male';
        $this->birthday          = $p->birthday?->format('Y-m-d');
        $this->birth_order       = $p->birth_order;
        $this->saint_id          = $p->saint_id;
        $this->cccd              = $p->cccd;
        $this->phone             = $p->phone;
        $this->email             = $p->email;
        $this->note              = $p->note;
        $this->currentAvatarPath = $p->avatar_path;

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

        $this->showEditBasic = true;
    }

    public function saveBasic(): void
    {
        $this->authorize('update', $this->parishioner);
        $this->validate($this->rulesBasic());

        try {
            DB::beginTransaction();

            $data = [
                'last_name'             => $this->last_name,
                'first_name'            => $this->first_name,
                'gender'                => $this->gender,
                'birthday'              => $this->birthday ?: null,
                'birth_order'           => $this->birth_order,
                'saint_id'              => $this->saint_id,
                'cccd'                  => $this->cccd,
                'phone'                 => $this->phone,
                'email'                 => $this->email,
                'note'                  => $this->note,
                'ethnic'                => $this->ethnic,
                'career'                => $this->career,
                'education_level'       => $this->education_level,
                'specialist_level'      => $this->specialist_level,
                'catechism_level'       => $this->catechism_level,
                'catechism_major'       => $this->catechism_major,
                'position'              => $this->position,
                'language'              => $this->language,
                'holy_order_status'     => $this->holy_order_status,
                'status'                => $this->status,
                'is_active'             => $this->is_active,
                'is_new_convert'        => $this->is_new_convert,
                'is_included_in_stats'  => $this->is_included_in_stats,
            ];

            if ($this->avatar) {
                if ($this->currentAvatarPath) {
                    Storage::disk('public')->delete($this->currentAvatarPath);
                }
                $data['avatar_path'] = $this->avatar->store('parishioners', 'public');
            }

            $this->parishioner->update($data);
            $this->parishioner->refresh()->load(['saint', 'parishGroup']);

            DB::commit();
            session()->flash('message', 'Cập nhật thông tin cơ bản thành công');
            $this->showEditBasic = false;
            $this->resetValidation();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error(self::class . ': saveBasic - ' . $e->getMessage(), ['id' => $this->parishioner->id]);
            session()->flash('error', 'Có lỗi khi lưu. Vui lòng thử lại.');
        }
    }

    // ==================== EDIT: ĐỊA CHỈ ====================

    public function openEditAddress(): void
    {
        $this->authorize('update', $this->parishioner);
        $p = $this->parishioner;

        $this->origin              = $p->origin;
        $this->permanent_province  = $p->permanent_province;
        $this->permanent_ward_id   = $p->permanent_ward_id;
        $this->permanent_residence = $p->permanent_residence;
        $this->temporary_province  = $p->temporary_province;
        $this->temporary_ward_id   = $p->temporary_ward_id;
        $this->temporary_residence = $p->temporary_residence;

        $this->showEditAddress = true;
    }

    public function saveAddress(): void
    {
        $this->authorize('update', $this->parishioner);
        $this->validate($this->rulesAddress());

        try {
            $this->parishioner->update([
                'origin'              => $this->origin,
                'permanent_province'  => $this->permanent_province,
                'permanent_ward_id'   => $this->permanent_ward_id,
                'permanent_residence' => $this->permanent_residence,
                'temporary_province'  => $this->temporary_province,
                'temporary_ward_id'   => $this->temporary_ward_id,
                'temporary_residence' => $this->temporary_residence,
            ]);

            $this->parishioner->refresh();
            session()->flash('message', 'Cập nhật địa chỉ thành công');
            $this->showEditAddress = false;
            $this->resetValidation();
        } catch (\Exception $e) {
            Log::error(self::class . ': saveAddress - ' . $e->getMessage(), ['id' => $this->parishioner->id]);
            session()->flash('error', 'Có lỗi khi lưu địa chỉ.');
        }
    }

    // ==================== EDIT: GIA ĐÌNH ====================

    public function openEditFamily(): void
    {
        $this->authorize('update', $this->parishioner);
        $p = $this->parishioner;

        $this->father_name = $p->father_name;
        $this->mother_name = $p->mother_name;
        $this->father_id   = $p->father_id;
        $this->mother_id   = $p->mother_id;
        $this->family_id   = $p->family_id;
        $this->married     = $p->married ?? 0;

        $this->showEditFamily = true;
    }

    public function saveFamily(): void
    {
        $this->authorize('update', $this->parishioner);
        $this->validate($this->rulesFamily());

        try {
            $this->parishioner->update([
                'father_name' => $this->father_name,
                'mother_name' => $this->mother_name,
                'father_id'   => $this->father_id,
                'mother_id'   => $this->mother_id,
                'family_id'   => $this->family_id,
                'married'     => $this->married,
            ]);

            $this->parishioner->refresh()->load(['family', 'father', 'mother']);
            session()->flash('message', 'Cập nhật thông tin gia đình thành công');
            $this->showEditFamily = false;
            $this->resetValidation();
        } catch (\Exception $e) {
            Log::error(self::class . ': saveFamily - ' . $e->getMessage(), ['id' => $this->parishioner->id]);
            session()->flash('error', 'Có lỗi khi lưu thông tin gia đình.');
        }
    }

    // ==================== EDIT: SINH HOẠT GIÁO XỨ ====================

    public function openEditParish(): void
    {
        $this->authorize('update', $this->parishioner);
        $p = $this->parishioner;

        $this->parish_area_id   = $p->parish_area_id;
        $this->level            = $p->level;
        $this->joined_date      = $p->joined_date?->format('Y-m-d');
        $this->transferred_from = $p->transferred_from;
        $this->transferred_date = $p->transferred_date?->format('Y-m-d');
        $this->left_reason      = $p->left_reason;
        $this->status           = (bool) $p->status;
        $this->is_active        = (bool) $p->is_active;
        $this->is_new_convert   = (bool) $p->is_new_convert;
        $this->is_included_in_stats = (bool) $p->is_included_in_stats;

        $this->showEditParish = true;
    }

    public function saveParish(): void
    {
        $this->authorize('update', $this->parishioner);
        $this->validate($this->rulesParish());

        try {
            $this->parishioner->update([
                'parish_area_id'       => $this->parish_area_id,
                'level'                => $this->level,
                'joined_date'          => $this->joined_date ?: null,
                'transferred_from'     => $this->transferred_from,
                'transferred_date'     => $this->transferred_date ?: null,
                'left_reason'          => $this->left_reason,
                'status'               => $this->status,
                'is_active'            => $this->is_active,
                'is_new_convert'       => $this->is_new_convert,
                'is_included_in_stats' => $this->is_included_in_stats,
            ]);

            $this->parishioner->refresh()->load(['parishGroup', 'transferredFromParish']);
            session()->flash('message', 'Cập nhật sinh hoạt giáo xứ thành công');
            $this->showEditParish = false;
            $this->resetValidation();
        } catch (\Exception $e) {
            Log::error(self::class . ': saveParish - ' . $e->getMessage(), ['id' => $this->parishioner->id]);
            session()->flash('error', 'Có lỗi khi lưu.');
        }
    }

    // ==================== EDIT: HÔN PHỐI ====================

    public function openEditMarriage(): void
    {
        $this->authorize('update', $this->parishioner);

        $marriage = $this->parishioner->marriageAsHusband
            ?? $this->parishioner->marriageAsWife;

        if ($marriage) {
            $this->marriage_id           = $marriage->id;
            $this->married_date          = $marriage->married_date?->format('Y-m-d');
            $this->certificate_number    = $marriage->certificate_number;
            $this->marriage_parish_id    = $marriage->parish_id;
            $this->marriage_parish_name  = $marriage->parish_name;
            $this->place_province        = $marriage->place_province;
            $this->place_ward_id         = $marriage->place_ward_id;
            $this->priest_witness        = $marriage->priest_witness;
            $this->marriage_status       = $marriage->status;
            $this->witness_1             = $marriage->witness_1;
            $this->witness_2             = $marriage->witness_2;
            $this->marriage_note         = $marriage->note;
            $this->spouse_id             = $this->parishioner->gender === 'male'
                ? $marriage->wife_id
                : $marriage->husband_id;
        } else {
            $this->resetMarriageForm();
        }

        $this->showEditMarriage = true;
    }

    public function saveMarriage(): void
    {
        $this->authorize('update', $this->parishioner);
        $this->validate($this->rulesMarriage());

        try {
            DB::beginTransaction();

            $data = [
                'married_date'        => $this->married_date ?: null,
                'certificate_number'  => $this->certificate_number,
                'parish_id'           => $this->marriage_parish_id,
                'parish_name'         => $this->marriage_parish_name,
                'place_province'      => $this->place_province,
                'place_ward_id'       => $this->place_ward_id,
                'priest_witness'      => $this->priest_witness,
                'status'              => $this->marriage_status,
                'witness_1'           => $this->witness_1,
                'witness_2'           => $this->witness_2,
                'note'                => $this->marriage_note,
            ];

            if ($this->marriage_id) {
                Marriage::findOrFail($this->marriage_id)->update($data);
            } else {
                $data['husband_id'] = $this->parishioner->gender === 'male'
                    ? $this->parishioner->id : $this->spouse_id;
                $data['wife_id'] = $this->parishioner->gender === 'female'
                    ? $this->parishioner->id : $this->spouse_id;
                Marriage::create($data);
            }

            DB::commit();
            $this->parishioner->refresh()->load(['marriageAsHusband.wife', 'marriageAsWife.husband']);

            session()->flash('message', 'Cập nhật hôn phối thành công');
            $this->showEditMarriage = false;
            $this->resetValidation();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error(self::class . ': saveMarriage - ' . $e->getMessage(), ['id' => $this->parishioner->id]);
            session()->flash('error', 'Có lỗi khi lưu hôn phối.');
        }
    }

    public function deleteMarriage(): void
    {
        $this->authorize('update', $this->parishioner);
        if (!$this->marriage_id) return;

        try {
            Marriage::findOrFail($this->marriage_id)->delete();
            $this->parishioner->refresh()->load(['marriageAsHusband.wife', 'marriageAsWife.husband']);
            session()->flash('message', 'Đã xóa hôn phối');
            $this->showEditMarriage = false;
        } catch (\Exception $e) {
            Log::error(self::class . ': deleteMarriage - ' . $e->getMessage(), ['id' => $this->marriage_id]);
            session()->flash('error', 'Có lỗi khi xóa hôn phối.');
        }
    }

    // ==================== EDIT: TỬ VONG ====================

    public function openEditDeceased(): void
    {
        $this->authorize('update', $this->parishioner);
        $p = $this->parishioner;

        $this->is_deceased       = $p->death_date !== null;
        $this->death_date        = $p->death_date?->format('Y-m-d');
        $this->death_book_number = $p->death_book_number;
        $this->death_place       = $p->death_place;
        $this->burial_place      = $p->burial_place;

        $this->showEditDeceased = true;
    }

    public function saveDeceased(): void
    {
        $this->authorize('update', $this->parishioner);
        $this->validate($this->rulesDeceased());

        try {
            $this->parishioner->update([
                'death_date'        => $this->is_deceased ? ($this->death_date ?: null) : null,
                'death_book_number' => $this->is_deceased ? $this->death_book_number : null,
                'death_place'       => $this->is_deceased ? $this->death_place : null,
                'burial_place'      => $this->is_deceased ? $this->burial_place : null,
            ]);

            $this->parishioner->refresh();
            $this->is_deceased = $this->parishioner->death_date !== null;

            session()->flash('message', 'Cập nhật thông tin tử vong thành công');
            $this->showEditDeceased = false;
            $this->resetValidation();
        } catch (\Exception $e) {
            Log::error(self::class . ': saveDeceased - ' . $e->getMessage(), ['id' => $this->parishioner->id]);
            session()->flash('error', 'Có lỗi khi lưu.');
        }
    }

    // ==================== XÓA GIÁO DÂN ====================

    public function delete(): mixed
    {
        $this->authorize('delete', $this->parishioner);

        try {
            DB::beginTransaction();
            if ($this->parishioner->avatar_path) {
                Storage::disk('public')->delete($this->parishioner->avatar_path);
            }
            $this->parishioner->delete();
            DB::commit();
            session()->flash('message', 'Đã xóa giáo dân thành công');
            return redirect()->route('parishioners.index');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error(self::class . ': delete - ' . $e->getMessage(), ['id' => $this->parishioner->id]);
            session()->flash('error', 'Có lỗi khi xóa giáo dân.');
        }

        return null;
    }

    // ==================== HELPERS ====================

    private function resetMarriageForm(): void
    {
        $this->reset([
            'marriage_id', 'spouse_id', 'married_date', 'certificate_number',
            'marriage_parish_id', 'marriage_parish_name',
            'place_province', 'place_ward_id', 'priest_witness',
            'witness_1', 'witness_2', 'marriage_note',
        ]);
        $this->marriage_status = 'valid';
    }

    public function closeAllModals(): void
    {
        $this->showEditBasic     = false;
        $this->showEditAddress   = false;
        $this->showEditFamily    = false;
        $this->showEditParish    = false;
        $this->showEditMarriage  = false;
        $this->showEditDeceased  = false;
        $this->showDeleteConfirm = false;
        $this->resetValidation();
    }

    // ==================== RENDER ====================

    public function render()
    {
        return view('livewire.parishioners.parishioner-show', [
            'marriage' => $this->parishioner->marriageAsHusband
                ?? $this->parishioner->marriageAsWife,
        ])->extends('frontend.layout.main')->section('content');
    }
}