<?php

namespace App\Http\Livewire\Block;

use App\Http\Livewire\Base\BaseComponent;
use App\Models\Block;
use App\Models\NamHoc;
use Illuminate\Support\Facades\DB;

/**
 * Component quản lý Khối học (CRUD)
 * 
 * Features:
 * - Chọn năm học
 * - List khối theo năm học với pagination
 * - Create/Edit/Delete khối
 * - Toggle status
 * - Sắp xếp thứ tự (weight)
 * - Search khối
 */

class BlockManager extends BaseComponent
{
    // ==================== FILTERS ====================

    /** @var int|null Selected năm học ID */
    public $selectedNamHoc;

    // ==================== FORM STATE ====================

    /** @var bool Hiển thị form create/edit */
    public $showForm = false;

    /** @var int|null ID của khối đang edit (null = create mode) */
    public $editingId = null;

    // ==================== FORM FIELDS ====================

    /** @var string Tên khối */
    public $name;

    /** @var int Trạng thái (1 = active, 0 = inactive) */
    public $status = 1;

    /** @var int Weight - Thứ tự sắp xếp */
    public $weight = 0;

    /** @var bool Không dùng pagination */
    protected $usePagination = false;

    // ==================== DATA ====================

    /** @var \Illuminate\Support\Collection Danh sách khối */
    public $blocks;

    // ==================== VALIDATION ====================

    // Rules riêng cho form – chỉ dùng khi save
    protected $formRules = [
        'name' => 'required|string|max:255',
        'weight' => 'nullable|integer|min:0',
        'status' => 'required|boolean',
    ];

    protected function validateUniqueName(): bool
    {
        return !Block::ofParish($this->parish_id)
            ->where('namhoc', $this->selectedNamHoc)
            ->where('name', $this->name)
            ->when($this->editingId, fn($q) => $q->where('id', '!=', $this->editingId))
            ->exists();
    }

    /**
     * Custom validation messages
     */
    protected $messages = [
        'selectedNamHoc.required' => 'Vui lòng chọn năm học',
        'selectedNamHocexists' => 'Năm học không hợp lệ',
        'name.required' => 'Vui lòng nhập tên khối',
        'name.max' => 'Tên khối không được quá 255 ký tự',
        'weight.numeric'  => 'Thứ tự phải là một số hợp lệ',
        'weight.integer' => 'Thứ tự phải là số nguyên',
        'weight.min' => 'Thứ tự phải lớn hơn hoặc bằng 0',
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
        'blockCreated' => 'loadBlocks',
        'blockUpdated' => 'loadBlocks',
        'filterChanged' => 'handleFilterChanged',
    ];

    /**
     * Xử lý khi filter thay đổi từ FilterBar
     */
    public function handleFilterChanged($filters)
    {
        $this->selectedNamHoc = is_numeric($filters['namHoc'] ?? null)
            ? (int) $filters['namHoc']
            : null;

        $this->search = '';
        $this->resetPage();
        $this->resetForm();

        $this->loadBlocks();
    }

    // ==================== LIFECYCLE ====================

    /**
     * Component initialization
     */
    public function mount()
    {
        parent::mount();

        // Yêu cầu quyền quản trị (Admin hoặc Decen)
        $this->requireManager();

        // Bắt buộc phải có parish_id
        $this->requireParishId();
    }

    /**
     * Load dữ liệu ban đầu (implement từ BaseComponent)
     */
    protected function loadInitialData(): void
    {
        if (!$this->selectedNamHoc) {
            $this->selectedNamHoc = $this->getDefaultNamHocId();
        }

        // Nếu đã có selectedNamHoc, load blocks
        if ($this->selectedNamHoc) {
            $this->loadBlocks();
        } else {
            $this->blocks = collect();
        }
    }

    protected function getDefaultNamHocId(): ?int
    {
        return NamHoc::ofParish($this->parish_id)
            ->active()
            ->orderByDesc('name')
            ->value('id');
    }


    /**
     * Override sanitizeQueryString để xử lý selectedNamHoc
     */
    protected function sanitizeQueryString(): void
    {
        parent::sanitizeQueryString();

        if ($this->selectedNamHoc === '' || $this->selectedNamHoc === null) {
            $this->selectedNamHoc = null;
        } else {
            $this->selectedNamHoc = is_numeric($this->selectedNamHoc)
                ? (int) $this->selectedNamHoc
                : null;
        }
    }

    /**
     * Load danh sách khối với pagination
     */
    public function loadBlocks(): void
    {
        if (!$this->selectedNamHoc) {
            $this->blocks = collect();
            return;
        }

        try {
            $query = Block::with('namHoc')
                ->ofParish($this->parish_id)
                ->where('namhoc', $this->selectedNamHoc)
                ->orderBy('weight', 'asc');

            // Apply search filter
            if (!empty($this->search)) {
                $query->where('name', 'like', '%' . $this->search . '%');
            }

            $this->blocks = $query->get();
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading blocks');
            session()->flash('error', 'Có lỗi khi tải danh sách khối');
            $this->blocks = collect();
            return;
        }
    }

    // ==================== PROPERTY UPDATERS ====================

    /**
     * Khi search thay đổi, reload data
     */
    public function updatedSearch(): void
    {
        parent::updatedSearch();
        $this->loadBlocks();
    }

    // ==================== CRUD ACTIONS ====================

    /**
     * Mở form tạo mới
     */
    public function create(): void
    {
        $this->requireManager();

        if (!$this->selectedNamHoc) {
            session()->flash('warning', 'Vui lòng chọn năm học trước');
            return;
        }

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
            $block = Block::ofParish($this->parish_id)
                ->where('namhoc', $this->selectedNamHoc)
                ->findOrFail($id);

            $this->editingId = $block->id;
            $this->name = $block->name;
            $this->weight = $block->weight ?? 0;
            $this->status = $block->status;

            $this->showForm = true;
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            session()->flash('error', 'Không tìm thấy khối học này');
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading block for edit', ['id' => $id]);
            session()->flash('error', 'Có lỗi khi tải thông tin khối học');
        }
    }

    /**
     * Lưu (create hoặc update)
     */
    public function save(): void
    {
        $this->requireManager();

        if (!$this->selectedNamHoc) {
            session()->flash('error', 'Vui lòng chọn năm học');
            return;
        }

        $this->validate($this->formRules, $this->messages);

        if (!$this->validateUniqueName()) {
            session()->flash('error', 'Tên khối đã tồn tại');
            return;
        }

        // Save data

        try {
            DB::beginTransaction();

            Block::updateOrCreate(
                ['id' => $this->editingId],
                [
                    'name' => $this->name,
                    'weight' => $this->weight ?? 0,
                    'status' => $this->status,
                    'namhoc' => $this->selectedNamHoc,
                    'pid' => $this->parish_id,
                    'did' => 0, // Default value
                    'deid' => 0, // Default value
                    'paid' => 0, // Default value
                ]
            );

            DB::commit();

            $message = $this->editingId
                ? 'Cập nhật khối học thành công'
                : 'Tạo khối học mới thành công';

            session()->flash('message', $message);

            $this->resetForm();
            $this->loadBlocks();

            // Emit event
            $this->emit($this->editingId ? 'blockUpdated' : 'blockCreated');
        } catch (\Exception $e) {
            DB::rollBack();

            $this->logError($e, 'Error saving block', [
                'editing_id' => $this->editingId,
                'name' => $this->name,
                'namhoc' => $this->selectedNamHoc,
            ]);

            session()->flash('error', 'Có lỗi khi lưu khối học. Vui lòng thử lại.');
        }
    }

    /**
     * Toggle status khối học
     */
    public function toggleStatus(int $id): void
    {
        $this->requireManager();

        try {
            $block = Block::ofParish($this->parish_id)
                ->where('namhoc', $this->selectedNamHoc)
                ->findOrFail($id);

            $block->update(['status' => !$block->status]);

            $message = $block->status
                ? 'Đã kích hoạt khối học'
                : 'Đã vô hiệu hóa khối học';

            session()->flash('message', $message);

            $this->loadBlocks();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            session()->flash('error', 'Không tìm thấy khối học này');
        } catch (\Exception $e) {
            $this->logError($e, 'Error toggling block status', ['id' => $id]);
            session()->flash('error', 'Có lỗi khi thay đổi trạng thái khối học');
        }
    }

    /**
     * Xóa khối học
     */
    public function delete(int $id): void
    {
        // Chỉ Admin mới được xóa
        $this->requireAdmin();

        try {
            DB::beginTransaction();

            $block = Block::ofParish($this->parish_id)
                ->where('namhoc', $this->selectedNamHoc)
                ->findOrFail($id);

            // Check nếu khối đang được sử dụng (có lớp học)
            $hasClasses = \App\Models\Lop::where('block', $block->id)->exists();

            if ($hasClasses) {
                session()->flash('error', 'Không thể xóa khối học đang có lớp học');
                return;
            }

            $block->delete();

            DB::commit();

            session()->flash('message', 'Đã xóa khối học thành công');

            $this->loadBlocks();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            session()->flash('error', 'Không tìm thấy khối học này');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError($e, 'Error deleting block', ['id' => $id]);
            session()->flash('error', 'Có lỗi khi xóa khối học');
        }
    }

    /**
     * Sắp xếp thứ tự khối (move up)
     */
    public function moveUp(int $id): void
    {
        $this->requireManager();

        try {
            DB::beginTransaction();

            $block = Block::ofParish($this->parish_id)
                ->where('namhoc', $this->selectedNamHoc)
                ->findOrFail($id);

            // Find block với weight nhỏ hơn gần nhất
            $prevBlock = Block::ofParish($this->parish_id)
                ->where('namhoc', $this->selectedNamHoc)
                ->where('weight', '<', $block->weight)
                ->orderBy('weight', 'desc')
                ->first();

            if ($prevBlock) {
                // Swap weights
                $tempWeight = $block->weight;
                $block->update(['weight' => $prevBlock->weight]);
                $prevBlock->update(['weight' => $tempWeight]);

                session()->flash('message', 'Đã di chuyển khối học lên');
            } else {
                session()->flash('info', 'Khối học đã ở vị trí đầu tiên');
            }

            DB::commit();
            $this->loadBlocks();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError($e, 'Error moving block up', ['id' => $id]);
            session()->flash('error', 'Có lỗi khi di chuyển khối học');
        }
    }

    /**
     * Sắp xếp thứ tự khối (move down)
     */
    public function moveDown(int $id): void
    {
        $this->requireManager();

        try {
            DB::beginTransaction();

            $block = Block::ofParish($this->parish_id)
                ->where('namhoc', $this->selectedNamHoc)
                ->findOrFail($id);

            // Find block với weight lớn hơn gần nhất
            $nextBlock = Block::ofParish($this->parish_id)
                ->where('namhoc', $this->selectedNamHoc)
                ->where('weight', '>', $block->weight)
                ->orderBy('weight', 'asc')
                ->first();

            if ($nextBlock) {
                // Swap weights
                $tempWeight = $block->weight;
                $block->update(['weight' => $nextBlock->weight]);
                $nextBlock->update(['weight' => $tempWeight]);

                session()->flash('message', 'Đã di chuyển khối học xuống');
            } else {
                session()->flash('info', 'Khối học đã ở vị trí cuối cùng');
            }

            DB::commit();
            $this->loadBlocks();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError($e, 'Error moving block down', ['id' => $id]);
            session()->flash('error', 'Có lỗi khi di chuyển khối học');
        }
    }

    // ==================== FORM HELPERS ====================

    /**
     * Reset form về trạng thái mặc định
     */
    public function resetForm(): void
    {
        $this->reset([
            'editingId',
            'name',
            'weight',
            'status',
        ]);

        $this->status = 1; // Default active
        $this->weight = 0; // Default weight
        $this->showForm = false;

        // Clear validation errors
        $this->resetValidation();
    }

    /**
     * Cancel và đóng form
     */
    public function cancel(): void
    {
        $this->resetForm();
    }

    /**
     * Override resetFilters để reset năm học
     */
    public function resetFilters(): void
    {
        $this->search = '';
        // Không reset selectedNamHoc vì cần giữ năm học đang chọn
        $this->resetPage();

        session()->flash('message', 'Đã đặt lại bộ lọc');
    }

    // ==================== RENDER ====================

    /**
     * Render component
     */
    public function render()
    {
        return view('livewire.block.block-manager', [
            'blocks' => $this->blocks,
        ])
            ->extends('frontend.layout.main')
            ->section('content');
    }
}
