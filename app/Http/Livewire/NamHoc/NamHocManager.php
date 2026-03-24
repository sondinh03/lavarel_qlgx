<?php

namespace App\Http\Livewire\NamHoc;

use App\Http\Livewire\Base\BaseComponent;
use App\Models\CatechismClass;
use App\Models\NamHoc;
use Illuminate\Support\Facades\DB;

class NamHocManager extends BaseComponent
{
    // ==================== FORM STATE ====================

    public $showForm = false;
    public $editingId = null;

    // ==================== FORM FIELDS ====================

    public $name;
    public $start_date_one;
    public $end_date_one;
    public $start_date_two;
    public $end_date_two;
    public $status = 1;

    // ==================== CONFIG ====================

    protected $usePagination = false;

    protected array $allowedSortFields = ['name', 'start_date_one', 'status'];

    // Mặc định sort theo start_date_one desc
    public string $sortField = 'start_date_one';
    public string $sortDirection = 'desc';

    // ==================== DATA ====================

    public $namHocs;

    // ==================== VALIDATION ====================

    protected $formRules = [
        'name'           => 'required|string|max:255',
        'start_date_one' => 'nullable|date',
        'end_date_one'   => 'nullable|date|after_or_equal:start_date_one',
        'start_date_two' => 'nullable|date',
        'end_date_two'   => 'nullable|date|after_or_equal:start_date_two',
        'status'         => 'required|boolean',
    ];

    protected $messages = [
        'name.required'                => 'Vui lòng nhập tên năm học',
        'name.max'                     => 'Tên năm học không được quá 255 ký tự',
        'start_date_one.date'          => 'Ngày bắt đầu kỳ 1 không hợp lệ',
        'end_date_one.date'            => 'Ngày kết thúc kỳ 1 không hợp lệ',
        'end_date_one.after_or_equal'  => 'Ngày kết thúc kỳ 1 phải sau hoặc bằng ngày bắt đầu',
        'start_date_two.date'          => 'Ngày bắt đầu kỳ 2 không hợp lệ',
        'end_date_two.date'            => 'Ngày kết thúc kỳ 2 không hợp lệ',
        'end_date_two.after_or_equal'  => 'Ngày kết thúc kỳ 2 phải sau hoặc bằng ngày bắt đầu',
    ];

    // ==================== QUERY STRING ====================

    protected function queryString()
    {
        return [
            'search'        => ['except' => ''],
            'sortField'     => ['except' => 'start_date_one', 'as' => 'sort'],
            'sortDirection' => ['except' => 'desc', 'as' => 'dir'],
        ];
    }

    // ==================== LISTENERS ====================

    protected $listeners = [
        'refresh' => 'handleRefresh',
    ];

    // ==================== LIFECYCLE ====================

    public function mount()
    {
        $this->authorize('viewAny', NamHoc::class);
        parent::mount();
        $this->requireParishId();
    }

    protected function loadInitialData(): void
    {
        $this->loadNamHocs();
    }

    // ==================== DATA LOADING ====================

    public function loadNamHocs(): void
    {
        try {
            $query = NamHoc::ofParish($this->parishId)
                ->when($this->search, fn($q) => $q->where('name', 'like', '%' . $this->search . '%'));

            $this->applySorting($query);

            $this->namHocs = $query->get();
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading nam hocs');
            session()->flash('error', 'Có lỗi khi tải danh sách năm học');
            $this->namHocs = collect();
        }
    }

    // ==================== PROPERTY UPDATERS ====================

    public function updatedSearch(): void
    {
        parent::updatedSearch();
        $this->loadNamHocs();
    }

    // Override sortBy để trigger reload sau khi sort thay đổi
    public function sortBy(string $field): void
    {
        parent::sortBy($field);
        $this->loadNamHocs();
    }

    // ==================== CRUD ACTIONS ====================

    public function create(): void
    {
        $this->authorize('create', NamHoc::class);
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $namHoc = NamHoc::findOrFail($id);
        $this->authorize('update', $namHoc);

        try {
            $this->editingId      = $namHoc->id;
            $this->name           = $namHoc->name;
            $this->start_date_one = $namHoc->start_date_one?->format('Y-m-d');
            $this->end_date_one   = $namHoc->end_date_one?->format('Y-m-d');
            $this->start_date_two = $namHoc->start_date_two?->format('Y-m-d');
            $this->end_date_two   = $namHoc->end_date_two?->format('Y-m-d');
            $this->status         = $namHoc->status;
            $this->showForm       = true;
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading nam hoc for edit', ['id' => $id]);
            session()->flash('error', 'Có lỗi khi tải thông tin năm học');
        }
    }

    public function save(): void
    {
        if ($this->editingId) {
            $namHoc = NamHoc::findOrFail($this->editingId);
            $this->authorize('update', $namHoc);
        } else {
            $this->authorize('create', NamHoc::class);
        }

        $this->validate($this->formRules, $this->messages);

        if ($this->start_date_two && $this->end_date_one) {
            if (strtotime($this->start_date_two) <= strtotime($this->end_date_one)) {
                $this->addError('start_date_two', 'Kỳ 2 phải bắt đầu sau khi kỳ 1 kết thúc');
                return;
            }
        }

        try {
            DB::beginTransaction();

            $exists = NamHoc::ofParish($this->parishId)
                ->where('name', $this->name)
                ->when($this->editingId, fn($q) => $q->where('id', '!=', $this->editingId))
                ->exists();

            if ($exists) {
                DB::rollBack();
                $this->addError('name', 'Tên năm học đã tồn tại');
                return;
            }

            NamHoc::updateOrCreate(
                ['id' => $this->editingId],
                [
                    'name'           => $this->name,
                    'parish_id'      => $this->parishId,
                    'start_date_one' => $this->start_date_one ?: null,
                    'end_date_one'   => $this->end_date_one ?: null,
                    'start_date_two' => $this->start_date_two ?: null,
                    'end_date_two'   => $this->end_date_two ?: null,
                    'status'         => $this->status,
                ]
            );

            DB::commit();

            session()->flash(
                'message',
                $this->editingId
                    ? 'Cập nhật năm học thành công'
                    : 'Tạo năm học mới thành công'
            );

            $this->resetForm();
            $this->loadNamHocs();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError($e, 'Error saving nam hoc', [
                'editing_id' => $this->editingId,
                'name'       => $this->name,
            ]);
            session()->flash('error', 'Có lỗi khi lưu năm học. Vui lòng thử lại.');
        }
    }

    public function toggleStatus(int $id): void
    {
        $this->requireManager();

        try {
            $namHoc = NamHoc::ofParish($this->parishId)->findOrFail($id);
            $namHoc->update(['status' => !$namHoc->status]);

            session()->flash(
                'message',
                $namHoc->status
                    ? 'Đã kích hoạt năm học'
                    : 'Đã lưu trữ năm học'
            );

            $this->loadNamHocs();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            session()->flash('error', 'Không tìm thấy năm học này');
        } catch (\Exception $e) {
            $this->logError($e, 'Error toggling nam hoc status', ['id' => $id]);
            session()->flash('error', 'Có lỗi khi thay đổi trạng thái năm học');
        }
    }

    public function delete(int $id): void
    {
        $this->authorize('delete', NamHoc::class);

        try {
            DB::beginTransaction();

            $namHoc = NamHoc::ofParish($this->parishId)->findOrFail($id);

            if (CatechismClass::where('school_year_id', $namHoc->id)->exists()) {
                session()->flash('error', 'Không thể xóa năm học đang có lớp học');
                return;
            }

            $namHoc->delete();
            DB::commit();

            session()->flash('message', 'Đã xóa năm học thành công');
            $this->loadNamHocs();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            DB::rollBack();
            session()->flash('error', 'Không tìm thấy năm học này');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError($e, 'Error deleting nam hoc', ['id' => $id]);
            session()->flash('error', 'Có lỗi khi xóa năm học');
        }
    }

    // ==================== FORM HELPERS ====================

    public function closeModal(): void
    {
        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->reset([
            'editingId',
            'name',
            'start_date_one',
            'end_date_one',
            'start_date_two',
            'end_date_two',
            'status',
        ]);
        $this->status   = 1;
        $this->showForm = false;
        $this->resetValidation();
    }

    // ==================== RENDER ====================

    public function render()
    {
        return view('livewire.nam-hoc.nam-hoc-manager', [
            'namHocs' => $this->namHocs,
        ])
            ->extends('frontend.layout.main')
            ->section('content');
    }
}
