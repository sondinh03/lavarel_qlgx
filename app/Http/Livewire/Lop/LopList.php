<?php

namespace App\Http\Livewire\Lop;

use App\Http\Livewire\Base\BaseComponent;
use App\Models\Block;
use App\Models\Lop;
use App\Models\NamHoc;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Component danh sách Lớp học
 * 
 * Features:
 * - Phân trang với options
 * - Tìm kiếm theo tên/mã lớp
 * - Lọc theo năm học, khối
 * - Query string support (URL sharing)
 * - Auto-select năm học mới nhất
 * - CRUD với modal form (inline actions như Block/NamHoc)
 */

class LopList extends BaseComponent
{
    // ==================== FILTERS ====================

    /** @var int|null Selected năm học ID */
    public $selectedNamHoc = null;

    /** @var int|string Selected khối ('' = all) */
    public $selectedKhoi = '';

    // ==================== FORM STATE ====================

    /** @var bool Hiển thị modal form */
    public $showForm = false;

    /** @var int|null ID lớp đang edit (null = create) */
    public $editingId = null;

    // ==================== FORM FIELDS ====================

    /** @var string Mã lớp */
    public $symbol;

    /** @var string Tên lớp */
    public $name;

    /** @var int|null ID khối */
    public $block;

    /** @var int Trạng thái */
    public $status = 1;

    // ==================== DATA ====================

    /** @var \Illuminate\Support\Collection Danh sách khối có thể chọn */
    public $availableBlocks;

    // ==================== VALIDATION ====================

    protected $rules = [
        'selectedNamHoc' => 'nullable|integer|exists:nam_hoc,id',
        'selectedKhoi' => 'nullable|integer',
        'search' => 'nullable|string|max:255',
        'perPage' => 'required|integer|in:10,15,25,50',
    ];

    /**
     * Rules riêng cho form - chỉ dùng khi save
     */
    protected $formRules = [
        'symbol' => 'required|string|max:50',
        'name' => 'required|string|max:255',
        'block' => 'required|integer|exists:block,id',
        'status' => 'required|boolean',
    ];

    /**
     * Custom validation messages
     */
    protected $messages = [
        'selectedNamHoc.exists' => 'Năm học không tồn tại',
        'selectedKhoi.integer' => 'Khối không hợp lệ',
        'search.max' => 'Tìm kiếm không được quá 255 ký tự',
        'perPage.in' => 'Số mục trên trang không hợp lệ',
        'symbol.required' => 'Vui lòng nhập mã lớp',
        'symbol.max' => 'Mã lớp không được quá 50 ký tự',
        'name.required' => 'Vui lòng nhập tên lớp',
        'name.max' => 'Tên lớp không được quá 255 ký tự',
        'block.required' => 'Vui lòng chọn khối học',
        'block.exists' => 'Khối học không tồn tại',
    ];

    // ==================== QUERY STRING ====================

    protected function queryString()
    {
        return array_merge([
            'selectedNamHoc' => ['as' => 'school-year', 'except' => null],
            'selectedKhoi'   => ['as' => 'grade', 'except' => ''],
        ], parent::queryString());
    }

    // ==================== LISTENERS ====================

    protected $listeners = [
        'refresh' => 'handleRefresh',
        'filterChanged' => 'handleFilterChanged',
        'lopCreated' => '$refresh',
        'lopUpdated' => '$refresh',
    ];

    // ==================== LIFECYCLE ====================

    /**
     * Component initialization
     */
    public function mount()
    {
        $this->authorize('viewAny', Lop::class);
        parent::mount();

        // Bắt buộc phải có parish_id
        // $this->requireParishId();
    }

    /**
     * Load dữ liệu ban đầu (required by BaseComponent)
     */
    protected function loadInitialData(): void
    {
        if (!$this->selectedNamHoc) {
            $this->selectedNamHoc = $this->getDefaultNamHocId();
        }

        if ($this->selectedNamHoc) {
            $this->loadAvailableBlocks();
        }
    }

    /**
     * Override sanitizeQueryString để xử lý thêm filters
     */
    protected function sanitizeQueryString(): void
    {
        parent::sanitizeQueryString();

        // Sanitize selectedNamHoc
        if ($this->selectedNamHoc === '' || $this->selectedNamHoc === null) {
            $this->selectedNamHoc = null;
        } else {
            $this->selectedNamHoc = is_numeric($this->selectedNamHoc)
                ? (int) $this->selectedNamHoc
                : null;
        }

        // Sanitize selectedKhoi: '' or int
        if ($this->selectedKhoi === '' || $this->selectedKhoi === null) {
            $this->selectedKhoi = '';
        } else {
            $this->selectedKhoi = is_numeric($this->selectedKhoi)
                ? (int) $this->selectedKhoi
                : '';
        }
    }

    /**
     * Override resetToDefaults để reset thêm filters
     */
    protected function resetToDefaults(): void
    {
        parent::resetToDefaults();
        $this->selectedKhoi = '';
    }

    // ==================== PROPERTY UPDATERS ====================

    /**
     * ✅ FIX: Khi search thay đổi - Livewire tự động re-render
     */
    public function updatedSearch(): void
    {
        parent::updatedSearch(); // Reset page
        // Livewire sẽ tự động gọi lại render() và getLopsPaginated()
    }

    /**
     * Khi thay đổi năm học
     */
    public function updatedSelectedNamHoc(): void
    {
        $this->selectedNamHoc = is_numeric($this->selectedNamHoc)
            ? (int) $this->selectedNamHoc
            : null;

        try {
            $this->validateOnly('selectedNamHoc');
        } catch (ValidationException $e) {
            $this->selectedNamHoc = null;
            session()->flash('warning', 'Năm học không hợp lệ.');
            $this->logError($e, 'Invalid selectedNamHoc');
        }

        // Reset dependent filters
        $this->selectedKhoi = '';
        $this->search = '';
        $this->resetPage();

        $this->loadAvailableBlocks();
    }

    /**
     * Khi thay đổi khối
     */
    public function updatedSelectedKhoi(): void
    {
        if ($this->selectedKhoi === '' || $this->selectedKhoi === null) {
            $this->selectedKhoi = '';
        } else {
            $this->selectedKhoi = is_numeric($this->selectedKhoi)
                ? (int) $this->selectedKhoi
                : '';
        }

        if ($this->selectedKhoi !== '') {
            try {
                $this->validateOnly('selectedKhoi');
            } catch (ValidationException $e) {
                $this->selectedKhoi = '';
                session()->flash('warning', 'Khối không hợp lệ.');
            }
        }

        $this->resetPage();
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

        // Load blocks cho form
        $this->loadAvailableBlocks();

        if ($this->availableBlocks->isEmpty()) {
            session()->flash('warning', 'Chưa có khối học nào. Vui lòng tạo khối học trước');
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
            $lop = Lop::ofParish($this->parishId)
                ->where('schoolyear', $this->selectedNamHoc)
                ->findOrFail($id);

            // Load blocks cho form
            $this->loadAvailableBlocks();

            $this->editingId = $lop->id;
            $this->symbol = $lop->symbol ?? '';
            $this->name = $lop->name ?? '';
            $this->block = $lop->block;
            $this->status = $lop->status;

            $this->showForm = true;
        } catch (ModelNotFoundException $e) {
            session()->flash('error', 'Không tìm thấy lớp học này');
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading class for edit', ['id' => $id]);
            session()->flash('error', 'Có lỗi khi tải thông tin lớp học');
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

        if (!$this->validateUniqueSymbol()) {
            session()->flash('error', 'Mã lớp đã tồn tại trong năm học này');
            return;
        }

        // Validate tên lớp không trùng
        if (!$this->validateUniqueName()) {
            session()->flash('error', 'Tên lớp đã tồn tại trong năm học này');
            return;
        }

        try {
            DB::beginTransaction();

            Lop::updateOrCreate(
                ['id' => $this->editingId],
                [
                    'symbol' => $this->symbol,
                    'name' => $this->name,
                    'block' => $this->block,
                    'schoolyear' => $this->selectedNamHoc,
                    'pid' => $this->parishId,
                    'status' => $this->status,
                    'did' => 0, // Default value
                    'deid' => 0, // Default value
                    'paid' => 0, // Default value
                ]
            );

            DB::commit();

            $message = $this->editingId
                ? 'Cập nhật lớp học thành công'
                : 'Tạo lớp học mới thành công';

            session()->flash('message', $message);

            $this->resetForm();
            $this->closeModal();

            $this->emit($this->editingId ? 'lopUpdated' : 'lopCreated');
        } catch (\Exception $e) {
            DB::rollBack();

            $this->logError($e, 'Error saving class', [
                'editing_id' => $this->editingId,
                'name' => $this->name,
                'block' => $this->block,
            ]);

            session()->flash('error', 'Có lỗi khi lưu dữ liệu. Vui lòng thử lại.');
        }
    }

    /**
     * Toggle status lớp học
     */
    public function toggleStatus(int $id): void
    {
        $this->requireManager();

        try {
            $lop = Lop::ofParish($this->parishId)
                ->where('schoolyear', $this->selectedNamHoc)
                ->findOrFail($id);

            $lop->update(['status' => !$lop->status]);

            $message = $lop->status
                ? 'Đã kích hoạt lớp học'
                : 'Đã tắt lớp học';

            session()->flash('message', $message);
        } catch (ModelNotFoundException $e) {
            session()->flash('error', 'Không tìm thấy lớp học này');
        } catch (\Exception $e) {
            $this->logError($e, 'Error toggling class status', ['id' => $id]);
            session()->flash('error', 'Có lỗi khi thay đổi trạng thái lớp học');
        }
    }

    // ==================== DATA LOADING ====================

    /**
     * ✅ FIX: Load blocks theo năm học đã chọn
     */
    protected function loadAvailableBlocks(): void
    {
        if (!$this->selectedNamHoc) {
            $this->availableBlocks = collect();
            return;
        }

        try {
            $this->availableBlocks = Block::ofParish($this->parishId)
                ->where('namhoc', $this->selectedNamHoc)
                ->active()
                ->orderBy('weight')
                ->get(['id', 'name']);
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading available blocks');
            $this->availableBlocks = collect();
        }
    }

    /**
     * ✅ IMPROVED: Get paginated lops với better error handling
     */
    private function getLopsPaginated()
    {
        if (!$this->selectedNamHoc) {
            return new \Illuminate\Pagination\LengthAwarePaginator(
                [],
                0,
                $this->perPage,
                $this->page ?? 1
            );
        }

        try {
            $query = Lop::with(['blockRelation', 'schoolYear'])
                ->where('lop.pid', $this->parishId)
                ->where('schoolyear', $this->selectedNamHoc)
                ->withCount('students');

            // Filter by khối
            if ($this->selectedKhoi !== '') {
                $query->where('block', $this->selectedKhoi);
            }

            // ✅ IMPROVED: Search với trim và sanitize
            if (!empty(trim($this->search))) {
                $searchTerm = '%' . trim($this->search) . '%';
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('lop.name', 'like', $searchTerm)
                        ->orWhere('lop.symbol', 'like', $searchTerm);
                });
            }

            // Order by block weight then name
            $query->join('block', 'lop.block', '=', 'block.id')
                ->orderBy('block.weight', 'asc')
                ->orderBy('lop.name', 'asc')
                ->select('lop.*');

            return $query->paginate($this->perPage);
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading lops', [
                'namhoc_id' => $this->selectedNamHoc,
                'khoi' => $this->selectedKhoi,
                'search' => $this->search,
            ]);

            session()->flash('error', 'Có lỗi khi tải danh sách lớp học.');

            return new \Illuminate\Pagination\LengthAwarePaginator(
                [],
                0,
                $this->perPage,
                $this->page ?? 1
            );
        }
    }

    // ==================== EVENT HANDLERS ====================

    /**
     * Handle filters changed event từ FilterBar
     */
    public function handleFilterChanged($filters)
    {
        if (!is_array($filters)) {
            return;
        }

        $namHocChanged = false;

        // Năm học
        if (array_key_exists('namHoc', $filters)) {
            $newNamHoc = is_numeric($filters['namHoc'])
                ? (int) $filters['namHoc']
                : null;

            if ($newNamHoc !== $this->selectedNamHoc) {
                $this->selectedNamHoc = $newNamHoc;
                $namHocChanged = true;
            }
        }

        if (!$namHocChanged && array_key_exists('khoi', $filters)) {
            $this->selectedKhoi = is_numeric($filters['khoi'])
                ? (int) $filters['khoi']
                : '';
        } else if ($namHocChanged) {
            // Nếu năm học thay đổi, reset khối
            $this->selectedKhoi = '';
            $this->loadAvailableBlocks();
        }

        // Reset phụ
        $this->search = '';
        $this->resetPage();
    }

    /**
     * Reset filters về giá trị mặc định
     */
    public function resetFilters(): void
    {
        $this->selectedKhoi = '';
        $this->search = '';
        $this->resetPage();

        session()->flash('message', 'Đã đặt lại bộ lọc');
    }

    // ==================== FORM HELPERS ====================

    /**
     * Đóng modal
     */
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
            'symbol',
            'name',
            'block',
            'status',
        ]);

        $this->status = 1; // Default active
        $this->resetValidation();
    }

    // ==================== HELPER METHODS ====================

    /**
     * Validate tên lớp không trùng trong cùng khối
     */
    protected function validateUniqueName(): bool
    {
        return !Lop::ofParish($this->parishId)
            ->where('schoolyear', $this->selectedNamHoc)
            ->where('block', $this->block)
            ->where('name', $this->name)
            ->when($this->editingId, fn($q) => $q->where('id', '!=', $this->editingId))
            ->exists();
    }

    protected function validateUniqueSymbol(): bool
    {
        return !Lop::ofParish($this->parishId)
            ->where('schoolyear', $this->selectedNamHoc)
            ->where('symbol', $this->symbol)
            ->when($this->editingId, fn($q) => $q->where('id', '!=', $this->editingId))
            ->exists();
    }

    /**
     * Get năm học mặc định (năm active mới nhất)
     */
    protected function getDefaultNamHocId(): ?int
    {
        return NamHoc::ofParish($this->parishId)
            ->active()
            ->orderByDesc('name')
            ->value('id');
    }

    // ==================== RENDER ====================

    /**
     * Render component
     */
    public function render()
    {
        // Load lops using pagination
        $lops = $this->getLopsPaginated();

        return view('livewire.lop.lop-list', [
            'lops' => $lops,
            'parishId' => $this->parishId,
        ])
            ->extends('frontend.layout.main')
            ->section('content');
    }
}
