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
 * - Tìm kiếm theo tên
 * - Lọc theo năm học, khối
 * - Query string support (URL sharing)
 * - Auto-select năm học mới nhất
 * - CRUD với modal form
 */

class LopList extends BaseComponent
{
    // ==================== FILTERS ====================

    /** @var int|null Selected năm học ID */
    public $selectedNamHoc = null;

    /** @var int|string Selected khối ('' = all) */
    public $selectedKhoi = '';

    // ==================== FORM STATE ====================
    /** @var bool */
    public $showForm = false;

    /** @var int|null */
    public $editingId = null;

    // ==================== FORM FIELDS ====================

    /** @var string Mã lớp */
    public $symbol;

    /** @var string */
    public $name;

    /** @var int|null */
    public $block;

    /** @var string */
    public $schoolyear;

    /** @var int */
    public $status = 1;

    // ==================== DATA ====================

    /** @var \Illuminate\Support\Collection Danh sách khối để chọn trong form */
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
            'selectedNamHoc' => ['as' => 'namHoc', 'except' => null],
            'selectedKhoi'   => ['as' => 'khoi', 'except' => ''],
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
     * Override mount để set perPage default
     */
    public function mount()
    {
        parent::mount();

        // Yêu cầu quyền quản trị (Admin hoặc Decen)
        $this->requireManager();

        // Bắt buộc phải có parish_id
        $this->requireParishId();

        // Initialize available blocks
        $this->availableBlocks = collect();
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

    public function updatedSearch(): void
    {
        parent::updatedSearch();
    }

    /**
     * Khi thay đổi năm học
     */
    public function updatedSelectedNamHoc(): void
    {
        // Sanitize
        $this->selectedNamHoc = is_numeric($this->selectedNamHoc)
            ? (int) $this->selectedNamHoc
            : null;

        // Validate
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
        // Sanitize: allow empty string (all) or int
        if ($this->selectedKhoi === '' || $this->selectedKhoi === null) {
            $this->selectedKhoi = '';
        } else {
            $this->selectedKhoi = is_numeric($this->selectedKhoi)
                ? (int) $this->selectedKhoi
                : '';
        }

        // Validate only if not empty
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
            $lop = Lop::ofParish($this->parish_id)
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


        if (!$this->validateUniqueName()) {
            session()->flash('error', 'Tên lớp đã tồn tại');
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
                    'pid' => $this->parish_id,
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
     * Load danh sách khối có thể chọn
     */
    protected function loadAvailableBlocks(): void
    {
        if (!$this->selectedNamHoc) {
            $this->availableBlocks = collect();
            return;
        }

        try {
            $this->availableBlocks = Block::ofParish($this->parish_id)
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
     * Toggle status lớp học
     */
    public function toggleStatus(int $id): void
    {
        $this->requireManager();

        try {
            $lop = Lop::ofParish($this->parish_id)
                ->where('schoolyear', $this->selectedNamHoc)
                ->findOrFail($id);

            $lop->update(['status' => !$lop->status]);

            $message = $lop->status
                ? 'Đã kích hoạt lớp học'
                : 'Đã vô hiệu hóa lớp học';

            session()->flash('message', $message);
        } catch (ModelNotFoundException $e) {
            session()->flash('error', 'Không tìm thấy lớp học này');
        } catch (\Exception $e) {
            $this->logError($e, 'Error toggling class status', ['id' => $id]);
            session()->flash('error', 'Có lỗi khi thay đổi trạng thái lớp học');
        }
    }

    // ==================== EVENT HANDLERS ====================

    /**
     * Handle filters changed event từ ClassFilterSelector
     */
    public function handleFilterChanged($filters)
    {
        if (!is_array($filters)) {
            return;
        }

        $namHocChanged = false;

        /** ===================== NĂM HỌC ===================== */
        if (array_key_exists('namHoc', $filters)) {
            $newNamHoc = is_numeric($filters['namHoc'])
                ? (int) $filters['namHoc']
                : null;

            if ($newNamHoc !== $this->selectedNamHoc) {
                $this->selectedNamHoc = $newNamHoc;
                $namHocChanged = true;
            }
        }

        /** ===================== KHỐI ===================== */
        if (array_key_exists('khoi', $filters)) {
            $this->selectedKhoi = is_numeric($filters['khoi'])
                ? (int) $filters['khoi']
                : '';
        }

        /** ===================== RESET PHỤ ===================== */
        $this->search = '';
        $this->resetPage();

        /** ===================== LOAD LẠI KHỐI ===================== */
        if ($namHocChanged) {
            $this->selectedKhoi = '';
            $this->loadAvailableBlocks();
        }
    }

    /**
     * Reset filters về giá trị mặc định
     * Override từ BaseComponent để reset thêm filters
     */
    public function resetFilters(): void
    {
        $this->selectedKhoi = '';
        $this->search = '';
        $this->resetPage();

        session()->flash('message', 'Đã đặt lại bộ lọc');
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
            'symbol',
            'name',
            'block',
            'status',
        ]);

        $this->status = 1; // Default active
        // $this->showForm = false;

        // Clear validation errors
        $this->resetValidation();
    }

    // ==================== HELPER METHODS ====================

    protected function validateUniqueName(): bool
    {
        return !Lop::ofParish($this->parish_id)
            ->where('schoolyear', $this->selectedNamHoc)
            ->where('block', $this->selectedKhoi)
            ->where('name', $this->name)
            ->when($this->editingId, fn($q) => $q->where('id', '!=', $this->editingId))
            ->exists();
    }

    /**
     * Get năm học mặc định (năm active đầu tiên)
     */
    protected function getDefaultNamHocId(): ?int
    {
        return NamHoc::ofParish($this->parish_id)
            ->active()
            ->orderByDesc('name')
            ->value('id');
    }

    /**
     * Get paginated lops using LopService
     * 
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    private function getLopsPaginated()
    {
        // Nếu chưa chọn năm học, return empty paginator
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
                ->where('lop.pid', $this->parish_id)
                ->where('schoolyear', $this->selectedNamHoc)
                ->withCount('activeStudents as students_count');

            // Filter by khối
            if ($this->selectedKhoi !== '') {
                $query->where('block', $this->selectedKhoi);
            }

            // Search
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

    /**
     * Render component
     */
    public function render()
    {
        // Load lops using service
        $lops = $this->getLopsPaginated();

        return view('livewire.lop.lop-list', [
            'lops' => $lops,
            'parishId' => $this->parish_id,
        ])
            ->extends('frontend.layout.main')
            ->section('content');
    }
}
