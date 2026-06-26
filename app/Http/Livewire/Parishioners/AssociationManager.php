<?php

namespace App\Http\Livewire\Parishioners;

use App\Http\Livewire\Base\BaseComponent;
use App\Models\Association;
use App\Models\ParishNew;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class AssociationManager extends BaseComponent
{
    public $showForm = false;
    public $editingId = null;

    public $name = '';
    public $ngaybonmang = '';
    public $ngaythanhlap = '';
    public $thanhbonmang = '';
    public $note = '';
    public $status = true;

    protected array $allowedSortFields = [
        'name',
        'ngaythanhlap',
        'ngaybonmang',
        'parishioners_count',
        'status',
    ];

    protected $formRules = [
        'name'           => 'required|string|max:255',
        'ngaybonmang'    => 'nullable|date',
        'ngaythanhlap'   => 'nullable|date',
        'thanhbonmang'   => 'nullable|string|max:255',
        'note'           => 'nullable|string|max:2000',
        'status'         => 'required|boolean',
    ];

    protected $messages = [
        'name.required' => 'Vui lòng nhập tên hội đoàn',
        'name.max'      => 'Tên hội đoàn không được quá 255 ký tự',
        'ngaybonmang.date'  => 'Ngày bổn mạng không hợp lệ',
        'ngaythanhlap.date' => 'Ngày thành lập không hợp lệ',
    ];

    public function mount(): void
    {
        parent::mount();
        $this->authorize('viewAny', Association::class);
        $this->requireParishId();
    }

    protected function loadInitialData(): void {}

    public function create(): void
    {
        $this->authorize('create', Association::class);
        $this->resetForm();
        $this->emit('openModal');
    }

    public function edit(int $id): void
    {
        try {
            $association = $this->scopedQuery()->findOrFail($id);
            $this->authorize('update', $association);

            $this->editingId     = $association->id;
            $this->name          = $association->name;
            $this->ngaybonmang   = $association->ngaybonmang?->format('Y-m-d') ?? '';
            $this->ngaythanhlap  = $association->ngaythanhlap?->format('Y-m-d') ?? '';
            $this->thanhbonmang  = $association->thanhbonmang ?? '';
            $this->note          = $association->note ?? '';
            $this->status        = (bool) $association->status;

            $this->emit('openModal');
        } catch (ModelNotFoundException) {
            $this->emit('toast', 'error', 'Không tìm thấy hội đoàn');
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading association for edit', ['id' => $id]);
            $this->emit('toast', 'error', 'Có lỗi khi tải thông tin hội đoàn');
        }
    }

    public function save(): void
    {
        if ($this->editingId) {
            $association = $this->scopedQuery()->findOrFail($this->editingId);
            $this->authorize('update', $association);
        } else {
            $this->authorize('create', Association::class);
        }

        $this->validate($this->formRules, $this->messages);

        try {
            DB::beginTransaction();

            $exists = $this->scopedQuery()
                ->where('name', $this->name)
                ->when($this->editingId, fn ($q) => $q->where('id', '!=', $this->editingId))
                ->exists();

            if ($exists) {
                DB::rollBack();
                $this->addError('name', 'Tên hội đoàn đã tồn tại trong giáo xứ');
                return;
            }

            $parish = ParishNew::find($this->parishId);

            Association::updateOrCreate(
                ['id' => $this->editingId],
                [
                    'pid'            => $this->parishId,
                    'deid'           => $parish?->deanery_id ?? 0,
                    'did'            => $parish?->diocese_id ?? 0,
                    'name'           => $this->name,
                    'ngaybonmang'    => $this->ngaybonmang ?: null,
                    'ngaythanhlap'   => $this->ngaythanhlap ?: null,
                    'thanhbonmang'   => $this->thanhbonmang ?: null,
                    'note'           => $this->note ?: null,
                    'status'         => $this->status ? 1 : 0,
                ]
            );

            DB::commit();

            $this->emit(
                'toast',
                'success',
                $this->editingId ? 'Cập nhật hội đoàn thành công' : 'Thêm hội đoàn mới thành công'
            );

            $this->resetForm();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError($e, 'Error saving association', [
                'editing_id' => $this->editingId,
                'name'       => $this->name,
            ]);
            $this->emit('toast', 'error', 'Có lỗi khi lưu hội đoàn. Vui lòng thử lại.');
        }
    }

    public function toggleStatus(int $id): void
    {
        try {
            $association = $this->scopedQuery()->findOrFail($id);
            $this->authorize('update', $association);

            $association->update(['status' => $association->status ? 0 : 1]);
            $association->refresh();

            $this->emit(
                'toast',
                'success',
                $association->status ? 'Đã kích hoạt hội đoàn' : 'Đã lưu trữ hội đoàn'
            );
        } catch (ModelNotFoundException) {
            $this->emit('toast', 'error', 'Không tìm thấy hội đoàn');
        } catch (\Exception $e) {
            $this->logError($e, 'Error toggling association status', ['id' => $id]);
            $this->emit('toast', 'error', 'Có lỗi khi thay đổi trạng thái');
        }
    }

    public function delete(int $id): void
    {
        try {
            $association = $this->scopedQuery()->findOrFail($id);
            $this->authorize('delete', $association);

            if ($association->parishionersNew()->exists()) {
                $this->emit('toast', 'error', 'Không thể xóa hội đoàn đang có giáo dân');
                return;
            }

            $association->delete();
            $this->emit('toast', 'success', 'Đã xóa hội đoàn');
        } catch (ModelNotFoundException) {
            $this->emit('toast', 'error', 'Không tìm thấy hội đoàn');
        } catch (\Exception $e) {
            $this->logError($e, 'Error deleting association', ['id' => $id]);
            $this->emit('toast', 'error', 'Có lỗi khi xóa hội đoàn');
        }
    }

    public function closeModal(): void
    {
        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->reset([
            'editingId',
            'name',
            'ngaybonmang',
            'ngaythanhlap',
            'thanhbonmang',
            'note',
        ]);
        $this->status = true;
        $this->showForm = false;
        $this->resetValidation();
        $this->emit('closeModal');
    }

    private function scopedQuery()
    {
        return Association::query()->ofParish((int) $this->parishId);
    }

    public function render()
    {
        $query = $this->scopedQuery()
            ->withCount('parishionersNew as parishioners_count')
            ->when($this->search, fn ($q) => $q->where('name', 'like', '%' . $this->search . '%'));

        $this->applySorting($query);

        return view('livewire.parishioners.association-manager', [
            'associations' => $query->paginate($this->perPage),
        ])
            ->extends('frontend.layout.parishioner')
            ->section('content');
    }
}
