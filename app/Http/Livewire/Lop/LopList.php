<?php

namespace App\Http\Livewire\Lop;

use App\Http\Livewire\Base\BaseComponent;
use App\Models\Lop;
use App\Models\NamHoc;
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
 */

class LopList extends BaseComponent
{
    // ==================== FILTERS ====================

    /** @var int|null Selected năm học ID */
    public $selectedNamHoc = null;

    /** @var int|string Selected khối ('' = all) */
    public $selectedKhoi = '';

    // ==================== VALIDATION ====================

    protected $rules = [
        'selectedNamHoc' => 'nullable|integer|exists:nam_hoc,id',
        'selectedKhoi' => 'nullable|integer',
        'search' => 'nullable|string|max:255',
        'perPage' => 'required|integer|in:10,15,25,50',
    ];

    protected $messages = [
        'selectedNamHoc.exists' => 'Năm học không tồn tại.',
        'selectedKhoi.integer' => 'Khối không hợp lệ',
        'search.max' => 'Tìm kiếm không được quá 255 ký tự',
        'perPage.in' => 'Số mục trên trang không hợp lệ.',
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
    ];

    // ==================== LIFECYCLE ====================

    /**
     * Override mount để set perPage default
     */
    public function mount()
    {
        parent::mount();
    }

    /**
     * Load dữ liệu ban đầu (required by BaseComponent)
     */
    protected function loadInitialData(): void
    {
        if (!$this->selectedNamHoc) {
            $this->selectedNamHoc = $this->getDefaultNamHocId();
        }
    }

    /**
     * Override validateUserAccess - Component này cần parish_id
     */
    protected function validateUserAccess(): void
    {
        // Gọi parent để check admin/decen
        parent::validateUserAccess();

        // Component này BẮT BUỘC phải có parish_id
        if (!$this->parish_id) {
            abort(403, 'Không xác định được giáo xứ');
        }
    }

    /**
     * Override sanitizeQueryString để xử lý thêm filters
     */
    protected function sanitizeQueryString(): void
    {
        parent::sanitizeQueryString();

        // Sanitize selectedNamHoc: null or int
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

    // ==================== HELPER METHODS ====================

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
                ->ofParish($this->parish_id)
                ->where('schoolyear', $this->selectedNamHoc)
                ->withCount('students');

            // Filter by khối
            if ($this->selectedKhoi !== '') {
                $query->where('block', $this->selectedKhoi);
            }

            // Search
            if (!empty($this->search)) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('symbol', 'like', '%' . $this->search . '%');
                });
            }

            $paginator = $query->paginate($this->perPage);

            $paginator->setCollection(
                $paginator->getCollection()->sortBy([
                    fn($a, $b) => ($a->blockRelation->weight ?? 999) <=> ($b->blockRelation->weight ?? 999),
                    fn($a, $b) => $a->name <=> $b->name,
                ])
            );

            return $paginator;
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
}
