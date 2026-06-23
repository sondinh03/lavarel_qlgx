<?php

namespace App\Http\Livewire\Parishioners;

use App\Http\Livewire\Base\BaseComponent;
use App\Http\Livewire\Parishioners\Concerns\ManagesParishionerForm;
use App\Models\Parishioner;
use Illuminate\Support\Facades\DB;
use Livewire\WithFileUploads;

class ParishionerEdit extends BaseComponent
{
    use WithFileUploads;
    use ManagesParishionerForm;

    public Parishioner $parishioner;
    public string $activeTab = 'basic';
    protected $usePagination = false;

    protected function queryString(): array
    {
        return [
            'activeTab' => ['except' => 'basic', 'as' => 'tab'],
        ];
    }

    public function mount($parishioner = null): void
    {
        $this->parishioner = $parishioner instanceof Parishioner
            ? $parishioner
            : Parishioner::findOrFail($parishioner);
        parent::mount();
    }

    protected function loadInitialData(): void
    {
        $this->authorize('update', $this->parishioner);
        $this->requireParishId();
        $this->loadParishionerDropdowns($this->parishId);
        $this->loadParishionerSearchOptions($this->parishId, $this->parishioner->id);
        $this->mapParishionerToForm($this->parishioner);
    }

    public function switchTab(string $tab): void
    {
        $valid = ['basic', 'address', 'classify', 'parish', 'family', 'deceased'];
        if (in_array($tab, $valid, true)) {
            $this->activeTab = $tab;
        }
    }

    public function save(): void
    {
        $this->normalizeParishionerFormValues();
        $this->validate($this->parishionerFormRules(), $this->parishionerFormMessages());
        $this->authorize('update', $this->parishioner);

        try {
            DB::beginTransaction();

            $data = $this->buildParishionerSaveData($this->parishId);
            $this->persistParishionerAvatar($data);

            $this->parishioner->update($data);

            DB::commit();

            $this->emit('toast', 'message', 'Cập nhật giáo dân thành công');
            $this->redirect(route('parishioners.show', $this->parishioner));
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError($e, 'Failed to update parishioner', ['id' => $this->parishioner->id]);
            $this->emit('toast', 'error', 'Có lỗi khi lưu dữ liệu. Vui lòng thử lại.');
        }
    }

    public function cancel(): void
    {
        $this->redirect(route('parishioners.show', $this->parishioner));
    }

    public function render()
    {
        return view('livewire.parishioners.parishioner-form', [
            'isEdit' => true,
            'parishioner' => $this->parishioner,
        ])->extends('frontend.layout.parishioner')->section('content');
    }
}
