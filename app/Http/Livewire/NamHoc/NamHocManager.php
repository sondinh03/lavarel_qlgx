<?php

namespace App\Http\Livewire\NamHoc;

use App\Http\Livewire\Base\BaseComponent;
use App\Models\NamHoc;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Component quản lý Năm học (CRUD)
 * 
 * Features:
 * - List năm học với pagination
 * - Create/Edit năm học
 * - Toggle status
 * - Search năm học
 * - Validation dates (kỳ 1, kỳ 2)
 */

class NamHocManager extends BaseComponent
{
    // ==================== FORM STATE ====================

    /** @var bool Hiển thị form create/edit */
    public $showForm = false;

    /** @var int|null ID của năm học đang edit (null = create mode) */
    public $editingId = null;

    // ==================== FORM FIELDS ====================

    /** @var string Tên năm học */
    public $name;

    /** @var string|null Ngày bắt đầu kỳ 1 */
    public $start_date_one;

    /** @var string|null Ngày kết thúc kỳ 1 */
    public $end_date_one;

    /** @var string|null Ngày bắt đầu kỳ 2 */
    public $start_date_two;

    /** @var string|null Ngày kết thúc kỳ 2 */
    public $end_date_two;

    /** @var int Trạng thái (1 = active, 0 = inactive) */
    public $status = 1;

    /** @var bool Không dùng pagination */
    protected $usePagination = false;

    // ==================== DATA ====================

    /** @var \Illuminate\Support\Collection Danh sách năm học */
    public $namHocs;

    // ==================== VALIDATION ====================

    /**
     * Rules riêng cho form - chỉ dùng khi save
     */
    protected $formRules = [
        'name' => 'required|string|max:255',
        'start_date_one' => 'nullable|date',
        'end_date_one' => 'nullable|date|after_or_equal:start_date_one',
        'start_date_two' => 'nullable|date',
        'end_date_two' => 'nullable|date|after_or_equal:start_date_two',
        'status' => 'required|boolean',
    ];

    /**
     * Custom validation messages
     */
    protected $messages = [
        'name.required' => 'Vui lòng nhập tên năm học',
        'name.max' => 'Tên năm học không được quá 255 ký tự',
        'start_date_one.date' => 'Ngày bắt đầu kỳ 1 không hợp lệ',
        'end_date_one.date' => 'Ngày kết thúc kỳ 1 không hợp lệ',
        'end_date_one.after_or_equal' => 'Ngày kết thúc kỳ 1 phải sau hoặc bằng ngày bắt đầu',
        'start_date_two.date' => 'Ngày bắt đầu kỳ 2 không hợp lệ',
        'end_date_two.date' => 'Ngày kết thúc kỳ 2 không hợp lệ',
        'end_date_two.after_or_equal' => 'Ngày kết thúc kỳ 2 phải sau hoặc bằng ngày bắt đầu',
    ];

    // ==================== QUERY STRING ====================
    protected function queryString()
    {
        return [
            'search' => ['except' => ''],
            'showForm' => ['except' => false],
        ];
    }

    // ==================== LISTENERS ====================

    /**
     * Listeners cho Livewire events
     */
    protected $listeners = [
        'refresh' => 'handleRefresh',
        'namHocCreated' => 'loadNamHocs',
        'namHocUpdated' => 'loadNamHocs',
    ];

    // ==================== LIFECYCLE ====================

    /**
     * Component initialization
     */
    public function mount()
    {
        parent::mount();

        // Yêu cầu quyền quản trị (Admin hoặc Decen)
        $this->requireManager();

        // Bắt buộc phải có parishId
        $this->requireParishId();
    }

    /**
     * Load dữ liệu ban đầu (implement từ BaseComponent)
     */
    protected function loadInitialData(): void
    {
        $this->loadNamHocs();
    }

    /**
     * Load danh sách năm học với pagination và search
     */
    public function loadNamHocs(): void
    {
        try {
            $query = NamHoc::ofParish($this->parishId)
                ->orderByDesc('start_date_one');

            // Apply search filter
            if (!empty($this->search)) {
                $query->where('name', 'like', '%' . $this->search . '%');
            }

            $this->namHocs = $query->get();
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading nam hocs');
            session()->flash('error', 'Có lỗi khi tải danh sách năm học');
            $this->namHocs = collect();
        }
    }

    // ==================== PROPERTY UPDATERS ====================

    /**
     * Khi search thay đổi, reload data
     */
    public function updatedSearch(): void
    {
        parent::updatedSearch();
        $this->loadNamHocs();
    }

    // ==================== CRUD ACTIONS ====================

    /**
     * Mở form tạo mới
     */
    public function create(): void
    {
        $this->requireManager();

        $this->resetForm();
        $this->showForm = true;
    }

    /**
     * Mở form edit
     */
    public function edit(int $id): void
    {
        $this->requireManager();

        try {
            $namHoc = NamHoc::ofParish($this->parishId)
                ->findOrFail($id);

            $this->editingId = $namHoc->id;
            $this->name = $namHoc->name;
            $this->start_date_one = $namHoc->start_date_one?->format('Y-m-d');
            $this->end_date_one = $namHoc->end_date_one?->format('Y-m-d');
            $this->start_date_two = $namHoc->start_date_two?->format('Y-m-d');
            $this->end_date_two = $namHoc->end_date_two?->format('Y-m-d');
            $this->status = $namHoc->status;

            $this->showForm = true;
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            session()->flash('error', 'Không tìm thấy năm học này');
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading nam hoc for edit', ['id' => $id]);
            session()->flash('error', 'Có lỗi khi tải thông tin năm học');
        }
    }

    /**
     * Lưu (create hoặc update)
     */
    public function save(): void
    {
        $this->requireManager();
        $this->validate($this->formRules, $this->messages);

        // Custom validation: Kỳ 2 phải sau kỳ 1
        if ($this->start_date_two && $this->end_date_one) {
            if (strtotime($this->start_date_two) <= strtotime($this->end_date_one)) {
                $this->addError('start_date_two', 'Kỳ 2 phải bắt đầu sau khi kỳ 1 kết thúc');
                return;
            }
        }

        try {
            DB::beginTransaction();

            // Check trùng tên trong cùng xứ
            $exists = NamHoc::ofParish($this->parishId)
                ->where('name', $this->name)
                ->when($this->editingId, function ($q) {
                    $q->where('id', '!=', $this->editingId);
                })
                ->exists();

            if ($exists) {
                DB::rollBack();
                session()->flash('error', 'Tên năm học đã tồn tại');
                return;
            }

            NamHoc::updateOrCreate(
                ['id' => $this->editingId],
                [
                    'name' => $this->name,
                    'parish_id' => $this->parishId,
                    'start_date_one' => $this->start_date_one ?: null,
                    'end_date_one' => $this->end_date_one ?: null,
                    'start_date_two' => $this->start_date_two ?: null,
                    'end_date_two' => $this->end_date_two ?: null,
                    'status' => $this->status,
                ]
            );

            DB::commit();

            $message = $this->editingId
                ? 'Cập nhật năm học thành công'
                : 'Tạo năm học mới thành công';

            session()->flash('message', $message);

            $this->resetForm();
            $this->loadNamHocs();

            // Emit event
            $this->emit($this->editingId ? 'namHocUpdated' : 'namHocCreated');
        } catch (\Exception $e) {
            DB::rollBack();

            $this->logError($e, 'Error saving nam hoc', [
                'editing_id' => $this->editingId,
                'name' => $this->name,
            ]);

            session()->flash('error', 'Có lỗi khi lưu năm học. Vui lòng thử lại.');
        }
    }

    /**
     * Toggle status năm học
     */
    public function toggleStatus(int $id): void
    {
        $this->requireManager();

        try {
            $namHoc = NamHoc::ofParish($this->parishId)
                ->findOrFail($id);

            $namHoc->update(['status' => !$namHoc->status]);

            $message = $namHoc->status
                ? 'Đã kích hoạt năm học'
                : 'Đã lưu trữ năm học';

            session()->flash('message', $message);

            $this->loadNamHocs();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            session()->flash('error', 'Không tìm thấy năm học này');
        } catch (\Exception $e) {
            $this->logError($e, 'Error toggling nam hoc status', ['id' => $id]);
            session()->flash('error', 'Có lỗi khi thay đổi trạng thái năm học');
        }
    }

    /**
     * Xóa năm học
     */
    public function delete(int $id): void
    {
        // Chỉ Admin mới được xóa
        $this->requireAdmin();

        try {
            DB::beginTransaction();

            $namHoc = NamHoc::ofParish($this->parishId)
                ->findOrFail($id);

            // Check nếu năm học đang được sử dụng (có khối học hoặc lớp học)
            $hasBlocks = \App\Models\Block::where('namhoc', $namHoc->id)->exists();

            if ($hasBlocks) {
                session()->flash('error', 'Không thể xóa năm học đang có khối học hoặc lớp học');
                return;
            }

            $namHoc->delete();

            DB::commit();

            session()->flash('message', 'Đã xóa năm học thành công');

            $this->loadNamHocs();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            session()->flash('error', 'Không tìm thấy năm học này');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError($e, 'Error deleting nam hoc', ['id' => $id]);
            session()->flash('error', 'Có lỗi khi xóa năm học');
        }
    }

    // ==================== FORM HELPERS ====================

    public function closeModal()
    {
        $this->showForm = false;
        $this->resetForm();
        $this->resetValidation();
    }

    /**
     * Reset form về trạng thái mặc định
     */
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

        $this->status = 1; // Default active
        $this->showForm = false;

        // Clear validation errors
        $this->resetValidation();
    }

    // ==================== RENDER ====================

    /**
     * Render component
     */

    public function render()
    {
        return view('livewire.nam-hoc.nam-hoc-manager', [
            'namHocs' => $this->namHocs,
        ])
            ->extends('frontend.layout.main')
            ->section('content');
    }
}
