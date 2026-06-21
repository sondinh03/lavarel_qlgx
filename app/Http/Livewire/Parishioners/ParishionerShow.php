<?php

namespace App\Http\Livewire\Parishioners;

use App\Http\Livewire\Parishioners\Concerns\ManagesParishionerForm;
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
    use ManagesParishionerForm;

    public Parishioner $parishioner;
    public string $activeTab = 'basic';

    public bool $showEditBasic     = false;
    public bool $showEditAddress   = false;
    public bool $showEditFamily    = false;
    public bool $showEditParish    = false;
    public bool $showEditMarriage  = false;
    public bool $showEditDeceased  = false;
    public bool $showDeleteConfirm = false;

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

    protected function rulesBasic(): array
    {
        return array_intersect_key($this->parishionerFormRules(), array_flip([
            'last_name', 'first_name', 'gender', 'birthday', 'birth_place', 'birth_order',
            'saint_id', 'cccd', 'phone', 'email', 'note', 'avatar',
            'ethnic', 'career', 'education_level', 'specialist_level', 'catechism_level',
            'catechism_major', 'position', 'language', 'holy_order_status',
            'status', 'is_active', 'is_new_convert', 'is_included_in_stats',
        ]));
    }

    protected function rulesAddress(): array
    {
        return array_intersect_key($this->parishionerFormRules(), array_flip([
            'origin', 'permanent_province', 'permanent_residence',
            'temporary_province', 'temporary_residence',
        ]));
    }

    protected function rulesFamily(): array
    {
        return array_intersect_key($this->parishionerFormRules(), array_flip([
            'father_name', 'mother_name', 'father_id', 'mother_id',
            'family_id', 'family_role', 'married',
        ]));
    }

    protected function rulesParish(): array
    {
        return array_intersect_key($this->parishionerFormRules(), array_flip([
            'parish_area_id', 'level', 'joined_date', 'transferred_from',
            'transferred_date', 'left_reason',
        ]));
    }

    protected function rulesMarriage(): array
    {
        return [
            'spouse_id'            => 'nullable|integer|exists:parishioners_new,id',
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
        return array_intersect_key($this->parishionerFormRules(), array_flip([
            'death_date', 'death_book_number', 'death_place', 'burial_place',
        ]));
    }

    public function mount(Parishioner $parishioner): void
    {
        $this->authorize('view', $parishioner);
        $this->parishioner = $parishioner->load(['saint', 'parishGroup', 'student']);
        $this->is_deceased = $this->parishioner->death_date !== null;
    }

    public function goToTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->loadRelationsForTab($tab);
    }

    private function loadRelationsForTab(string $tab): void
    {
        $map = [
            'basic'     => ['saint', 'student'],
            'parish'    => ['parishGroup', 'parish', 'deanery', 'diocese', 'transferredFromParish'],
            'sacrament' => [],
            'marriage'  => ['marriageAsHusband.wife', 'marriageAsWife.husband'],
            'family'    => ['family.parishGroup', 'family.head', 'father', 'mother'],
            'deceased'  => [],
        ];

        $relations = $map[$tab] ?? [];
        if (!empty($relations)) {
            $this->parishioner->loadMissing($relations);
        }
    }

    protected function loadModalDropdowns(): void
    {
        $this->loadParishionerDropdowns($this->parishioner->parish_id);
        $this->loadParishionerSearchOptions($this->parishioner->parish_id, $this->parishioner->id);
    }

    public function openEditBasic(): void
    {
        $this->authorize('update', $this->parishioner);
        $this->loadModalDropdowns();
        $this->mapParishionerToForm($this->parishioner);
        $this->showEditBasic = true;
    }

    public function saveBasic(): void
    {
        $this->authorize('update', $this->parishioner);
        $this->validate($this->rulesBasic(), $this->parishionerFormMessages());

        try {
            DB::beginTransaction();

            $data = array_intersect_key(
                $this->buildParishionerSaveData($this->parishioner->parish_id),
                array_flip([
                    'last_name', 'first_name', 'gender', 'birthday', 'birth_place', 'birth_order',
                    'saint_id', 'cccd', 'phone', 'email', 'note',
                    'ethnic', 'career', 'education_level', 'specialist_level', 'catechism_level',
                    'catechism_major', 'position', 'language', 'holy_order_status',
                    'status', 'is_active', 'is_new_convert', 'is_included_in_stats',
                ])
            );

            $this->persistParishionerAvatar($data);
            $this->parishioner->update($data);
            $this->parishioner->refresh()->load(['saint', 'parishGroup']);

            DB::commit();
            $this->emit('toast', 'message', 'Cập nhật thông tin cơ bản thành công');
            $this->showEditBasic = false;
            $this->resetValidation();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error(self::class . ': saveBasic - ' . $e->getMessage(), ['id' => $this->parishioner->id]);
            $this->emit('toast', 'error', 'Có lỗi khi lưu. Vui lòng thử lại.');
        }
    }

    public function openEditAddress(): void
    {
        $this->authorize('update', $this->parishioner);
        $this->mapParishionerToForm($this->parishioner);
        $this->showEditAddress = true;
    }

    public function saveAddress(): void
    {
        $this->authorize('update', $this->parishioner);
        $this->validate($this->rulesAddress(), $this->parishionerFormMessages());

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
            $this->emit('toast', 'message', 'Cập nhật địa chỉ thành công');
            $this->showEditAddress = false;
            $this->resetValidation();
        } catch (\Exception $e) {
            Log::error(self::class . ': saveAddress - ' . $e->getMessage(), ['id' => $this->parishioner->id]);
            $this->emit('toast', 'error', 'Có lỗi khi lưu địa chỉ.');
        }
    }

    public function openEditFamily(): void
    {
        $this->authorize('update', $this->parishioner);
        $this->loadModalDropdowns();
        $this->mapParishionerToForm($this->parishioner);
        $this->showEditFamily = true;
    }

    public function saveFamily(): void
    {
        $this->authorize('update', $this->parishioner);
        $this->validate($this->rulesFamily(), $this->parishionerFormMessages());

        try {
            $this->parishioner->update([
                'father_name' => $this->father_name,
                'mother_name' => $this->mother_name,
                'father_id'   => $this->father_id,
                'mother_id'   => $this->mother_id,
                'family_id'   => $this->family_id,
                'family_role' => $this->family_role ?: null,
                'married'     => $this->married,
            ]);

            $this->parishioner->refresh()->load(['family', 'father', 'mother']);
            $this->emit('toast', 'message', 'Cập nhật thông tin gia đình thành công');
            $this->showEditFamily = false;
            $this->resetValidation();
        } catch (\Exception $e) {
            Log::error(self::class . ': saveFamily - ' . $e->getMessage(), ['id' => $this->parishioner->id]);
            $this->emit('toast', 'error', 'Có lỗi khi lưu thông tin gia đình.');
        }
    }

    public function openEditParish(): void
    {
        $this->authorize('update', $this->parishioner);
        $this->loadModalDropdowns();
        $this->mapParishionerToForm($this->parishioner);
        $this->showEditParish = true;
    }

    public function saveParish(): void
    {
        $this->authorize('update', $this->parishioner);
        $this->validate($this->rulesParish(), $this->parishionerFormMessages());

        try {
            $this->parishioner->update([
                'parish_area_id'   => $this->parish_area_id,
                'level'            => $this->level,
                'joined_date'      => $this->joined_date ?: null,
                'transferred_from' => $this->transferred_from,
                'transferred_date' => $this->transferred_date ?: null,
                'left_reason'      => $this->left_reason,
            ]);

            $this->parishioner->refresh()->load(['parishGroup', 'transferredFromParish']);
            $this->emit('toast', 'message', 'Cập nhật sinh hoạt giáo xứ thành công');
            $this->showEditParish = false;
            $this->resetValidation();
        } catch (\Exception $e) {
            Log::error(self::class . ': saveParish - ' . $e->getMessage(), ['id' => $this->parishioner->id]);
            $this->emit('toast', 'error', 'Có lỗi khi lưu.');
        }
    }

    public function openEditMarriage(): void
    {
        $this->authorize('update', $this->parishioner);
        $this->loadModalDropdowns();

        $marriage = $this->parishioner->marriageAsHusband
            ?? $this->parishioner->marriageAsWife;

        if ($marriage) {
            $this->marriage_id          = $marriage->id;
            $this->married_date         = $marriage->married_date?->format('Y-m-d');
            $this->certificate_number   = $marriage->certificate_number;
            $this->marriage_parish_id   = $marriage->parish_id;
            $this->marriage_parish_name = $marriage->parish_name;
            $this->place_province       = $marriage->place_province;
            $this->place_ward_id        = $marriage->place_ward_id;
            $this->priest_witness       = $marriage->priest_witness;
            $this->marriage_status      = $marriage->status;
            $this->witness_1            = $marriage->witness_1;
            $this->witness_2            = $marriage->witness_2;
            $this->marriage_note        = $marriage->note;
            $this->spouse_id            = $this->parishioner->gender === 'male'
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
                'married_date'       => $this->married_date ?: null,
                'certificate_number' => $this->certificate_number,
                'parish_id'          => $this->marriage_parish_id,
                'parish_name'        => $this->marriage_parish_name,
                'place_province'     => $this->place_province,
                'place_ward_id'      => $this->place_ward_id,
                'priest_witness'     => $this->priest_witness,
                'status'             => $this->marriage_status,
                'witness_1'          => $this->witness_1,
                'witness_2'          => $this->witness_2,
                'note'               => $this->marriage_note,
            ];

            if ($this->marriage_id) {
                $marriage = Marriage::findOrFail($this->marriage_id);
                $marriage->update($data);
                if ($this->spouse_id) {
                    if ($this->parishioner->gender === 'male') {
                        $marriage->update(['wife_id' => $this->spouse_id, 'husband_id' => $this->parishioner->id]);
                    } else {
                        $marriage->update(['husband_id' => $this->spouse_id, 'wife_id' => $this->parishioner->id]);
                    }
                }
            } else {
                $data['husband_id'] = $this->parishioner->gender === 'male'
                    ? $this->parishioner->id : $this->spouse_id;
                $data['wife_id'] = $this->parishioner->gender === 'female'
                    ? $this->parishioner->id : $this->spouse_id;
                Marriage::create($data);
            }

            DB::commit();
            $this->parishioner->refresh()->load(['marriageAsHusband.wife', 'marriageAsWife.husband']);

            $this->emit('toast', 'message', 'Cập nhật hôn phối thành công');
            $this->showEditMarriage = false;
            $this->resetValidation();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error(self::class . ': saveMarriage - ' . $e->getMessage(), ['id' => $this->parishioner->id]);
            $this->emit('toast', 'error', 'Có lỗi khi lưu hôn phối.');
        }
    }

    public function deleteMarriage(): void
    {
        $this->authorize('update', $this->parishioner);
        if (!$this->marriage_id) {
            return;
        }

        try {
            Marriage::findOrFail($this->marriage_id)->delete();
            $this->parishioner->refresh()->load(['marriageAsHusband.wife', 'marriageAsWife.husband']);
            $this->emit('toast', 'message', 'Đã xóa hôn phối');
            $this->showEditMarriage = false;
        } catch (\Exception $e) {
            Log::error(self::class . ': deleteMarriage - ' . $e->getMessage(), ['id' => $this->marriage_id]);
            $this->emit('toast', 'error', 'Có lỗi khi xóa hôn phối.');
        }
    }

    public function openEditDeceased(): void
    {
        $this->authorize('update', $this->parishioner);
        $this->mapParishionerToForm($this->parishioner);
        $this->showEditDeceased = true;
    }

    public function saveDeceased(): void
    {
        $this->authorize('update', $this->parishioner);
        $this->validate($this->rulesDeceased(), $this->parishionerFormMessages());

        try {
            $this->parishioner->update([
                'death_date'        => $this->is_deceased ? ($this->death_date ?: null) : null,
                'death_book_number' => $this->is_deceased ? $this->death_book_number : null,
                'death_place'       => $this->is_deceased ? $this->death_place : null,
                'burial_place'      => $this->is_deceased ? $this->burial_place : null,
            ]);

            $this->parishioner->refresh();
            $this->is_deceased = $this->parishioner->death_date !== null;

            $this->emit('toast', 'message', 'Cập nhật thông tin tử vong thành công');
            $this->showEditDeceased = false;
            $this->resetValidation();
        } catch (\Exception $e) {
            Log::error(self::class . ': saveDeceased - ' . $e->getMessage(), ['id' => $this->parishioner->id]);
            $this->emit('toast', 'error', 'Có lỗi khi lưu.');
        }
    }

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
            $this->emit('toast', 'message', 'Đã xóa giáo dân thành công');
            return redirect()->route('parishioners.index');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error(self::class . ': delete - ' . $e->getMessage(), ['id' => $this->parishioner->id]);
            $this->emit('toast', 'error', 'Có lỗi khi xóa giáo dân.');
        }

        return null;
    }

    private function resetMarriageForm(): void
    {
        $this->reset([
            'marriage_id', 'spouse_id', 'married_date', 'certificate_number',
            'marriage_parish_id', 'marriage_parish_name', 'place_province',
            'place_ward_id', 'priest_witness', 'witness_1', 'witness_2', 'marriage_note',
        ]);
        $this->marriage_status = 'valid';
    }

    public function getChildrenProperty()
    {
        if (!$this->parishioner->family_id) {
            return collect();
        }

        return Parishioner::query()
            ->with('saint')
            ->where('family_id', $this->parishioner->family_id)
            ->where('family_role', 'child')
            ->where('id', '!=', $this->parishioner->id)
            ->orderBy('birth_order')
            ->orderBy('birthday')
            ->get();
    }

    public function render()
    {
        return view('livewire.parishioners.parishioner-show', [
            'marriage' => $this->parishioner->marriageAsHusband
                ?? $this->parishioner->marriageAsWife,
        ])->extends('frontend.layout.parishioner')->section('content');
    }
}
