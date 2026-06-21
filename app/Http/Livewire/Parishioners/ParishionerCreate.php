<?php

namespace App\Http\Livewire\Parishioners;

use App\Http\Livewire\Base\BaseComponent;
use App\Http\Livewire\Parishioners\Concerns\ManagesParishionerForm;
use App\Models\Parishioner;
use Illuminate\Support\Facades\DB;
use Livewire\WithFileUploads;

class ParishionerCreate extends BaseComponent
{
    use WithFileUploads;
    use ManagesParishionerForm;

    public string $activeTab = 'basic';
    protected $usePagination = false;

    protected function queryString(): array
    {
        return [
            'activeTab' => ['except' => 'basic', 'as' => 'tab'],
        ];
    }

    protected function loadInitialData(): void
    {
        $this->authorize('create', Parishioner::class);
        $this->requireParishId();
        $this->loadParishionerDropdowns($this->parishId);
        $this->loadParishionerSearchOptions($this->parishId);
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
        $this->validate($this->parishionerFormRules(), $this->parishionerFormMessages());
        $this->authorize('create', Parishioner::class);

        try {
            DB::beginTransaction();

            $data = $this->buildParishionerSaveData($this->parishId);
            $this->persistParishionerAvatar($data);

            $parishioner = Parishioner::create($data);

            DB::commit();

            $this->emit('toast', 'message', 'Thêm giáo dân thành công');
            $this->redirect(route('parishioners.show', $parishioner));
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError($e, 'Failed to create parishioner');
            $this->emit('toast', 'error', 'Có lỗi khi lưu dữ liệu. Vui lòng thử lại.');
        }
    }

    public function cancel(): void
    {
        $this->redirect(route('parishioners.index'));
    }

    public function render()
    {
        return view('livewire.parishioners.parishioner-form', [
            'isEdit' => false,
            'parishioner' => null,
        ])->extends('frontend.layout.parishioner')->section('content');
    }
}
