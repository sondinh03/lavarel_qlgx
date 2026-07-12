<?php

namespace App\Http\Livewire\Parishioners;

use App\Http\Livewire\Parishioners\Concerns\ManagesParishionerForm;
use App\Models\Marriage;
use App\Models\Parishioner;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
    public bool $showDonXinRuaToiModal = false;

    public string $baptism_candidate_name = '';
    public string $godparent_name = '';
    public ?string $baptism_candidate_birthday = null;
    public string $baptism_candidate_birth_place = '';
    public $baptism_candidate_birth_order = null;

    public ?int    $marriage_id          = null;
    public         $spouse_id            = null;
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
        return $this->parishionerFormRulesForSection('basic');
    }

    protected function rulesAddress(): array
    {
        return $this->parishionerFormRulesForSection('address');
    }

    protected function rulesFamily(): array
    {
        return $this->parishionerFormRulesForSection('family');
    }

    protected function rulesParish(): array
    {
        return $this->parishionerFormRulesForSection('parish');
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
        return $this->parishionerFormRulesForSection('deceased');
    }

    public function mount(Parishioner $parishioner): void
    {
        $this->authorize('view', $parishioner);
        $this->parishioner = $parishioner->load(['saint', 'parishGroup', 'association', 'student']);
        $this->is_deceased = $this->parishioner->death_date !== null;

        $this->openEditModalFromQuery(request()->query('edit'));
    }

    protected function openEditModalFromQuery(?string $edit): void
    {
        if (!$edit || !auth()->user()?->can('update', $this->parishioner)) {
            return;
        }

        $map = [
            'basic'    => 'openEditBasic',
            'address'  => 'openEditAddress',
            'parish'   => 'openEditParish',
            'family'   => 'openEditFamily',
            'marriage' => 'openEditMarriage',
            'deceased' => 'openEditDeceased',
        ];

        if (isset($map[$edit]) && method_exists($this, $map[$edit])) {
            $this->{$map[$edit]}();
        }
    }

    public function goToTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->loadRelationsForTab($tab);
    }

    public function openDonXinRuaToiModal(): void
    {
        $this->authorize('view', $this->parishioner);

        $this->baptism_candidate_name        = '';
        $this->godparent_name                = '';
        $this->baptism_candidate_birthday    = null;
        $this->baptism_candidate_birth_place = '';
        $this->baptism_candidate_birth_order = null;
        $this->resetErrorBag([
            'baptism_candidate_name',
            'godparent_name',
            'baptism_candidate_birthday',
            'baptism_candidate_birth_place',
            'baptism_candidate_birth_order',
        ]);
        $this->showDonXinRuaToiModal = true;
    }

    public function exportDonXinRuaToi()
    {
        $this->authorize('view', $this->parishioner);

        $this->validate([
            'baptism_candidate_name'        => 'required|string|max:200',
            'godparent_name'                => 'required|string|max:200',
            'baptism_candidate_birthday'    => 'required|date',
            'baptism_candidate_birth_place' => 'required|string|max:255',
            'baptism_candidate_birth_order' => 'required|integer|min:1|max:99',
        ], [
            'baptism_candidate_name.required'        => 'Vui lòng nhập tên thánh, họ tên người được rửa tội',
            'godparent_name.required'                => 'Vui lòng nhập tên thánh, họ tên người đỡ đầu',
            'baptism_candidate_birthday.required'    => 'Vui lòng nhập ngày sinh',
            'baptism_candidate_birthday.date'        => 'Ngày sinh không hợp lệ',
            'baptism_candidate_birth_place.required' => 'Vui lòng nhập nơi sinh',
            'baptism_candidate_birth_order.required' => 'Vui lòng nhập con thứ',
            'baptism_candidate_birth_order.integer'  => 'Con thứ phải là số',
            'baptism_candidate_birth_order.min'      => 'Con thứ phải từ 1 trở lên',
        ]);

        $url = route('parishioners.export-don-xin-rua-toi', [
            'parishioner'    => $this->parishioner,
            'holy_fullname'       => $this->baptism_candidate_name,
            'godparent_name' => $this->godparent_name,
            'birthday'       => $this->baptism_candidate_birthday,
            'birth_place'    => $this->baptism_candidate_birth_place,
            'birth_order'    => (int) $this->baptism_candidate_birth_order,
        ]);

        $this->showDonXinRuaToiModal = false;

        return redirect()->to($url);
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

        $data = $this->applyParishionerSectionSave($this->parishioner, 'basic');

        try {
            DB::beginTransaction();
            $this->parishioner->update($data);
            $this->parishioner->refresh()->load(['saint', 'parishGroup', 'association']);
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
        if (empty($this->provinces)) {
            $this->loadAddressDropdowns();
        }
        $this->mapParishionerToForm($this->parishioner);
        $this->showEditAddress = true;
    }

    public function saveAddress(): void
    {
        $this->authorize('update', $this->parishioner);

        try {
            $data = $this->applyParishionerSectionSave($this->parishioner, 'address');
            $this->parishioner->update($data);
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

        try {
            $data = $this->applyParishionerSectionSave($this->parishioner, 'family');
            $this->parishioner->update($data);
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

        try {
            $data = $this->applyParishionerSectionSave($this->parishioner, 'parish');
            $this->parishioner->update($data);
            $this->parishioner->refresh()->load(['parishGroup', 'association', 'diocese', 'deanery', 'parish', 'transferredFromParish']);
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
        if ($this->spouse_id === '') {
            $this->spouse_id = null;
        }
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

        try {
            $data = $this->applyParishionerSectionSave($this->parishioner, 'deceased');
            $this->parishioner->update($data);
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
                delete_stored_media($this->parishioner->avatar_path);
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
        if ($this->parishioner->family_role === 'child' || !$this->parishioner->family_id) {
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
