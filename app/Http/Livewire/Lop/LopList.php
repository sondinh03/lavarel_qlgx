<?php

namespace App\Http\Livewire\Lop;

use App\Http\Livewire\Base\BaseComponent;
use App\Services\LopService;
use App\Traits\FilterTrait;
use Faker\Provider\Base;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Pagination\LengthAwarePaginator;

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

    // ==================== PUBLIC DATA ====================

    /** @var array Danh sách năm học */
    public $namHocs = [];

    /** @var array Danh sách khối */
    public $khois = [];

    public $isAdmin = false;

    public $search = '';
    public $perPage = 15;
    protected $perPageOptions = [10, 15, 25, 50];

    // ==================== VALIDATION ====================

    protected $rules = [
        'selectedNamHoc' => 'nullable|integer|exists:nam_hoc,id',
        'selectedKhoi' => 'nullable|integer',
        'search' => 'nullable|string|max:255',
    ];

    protected $messages = [
        'selectedNamHoc.exists' => 'Năm học không tồn tại.',
        'perPage.in' => 'Số mục trên trang không hợp lệ.',
    ];

    // ==================== QUERY STRING ====================

    protected $queryString = [
        'selectedNamHoc' => ['except' => null],
        'selectedKhoi' => ['except' => ''],
        'search' => ['except' => ''],
        'perPage' => ['except' => 15],
        'page' => ['except' => 1],
    ];

    // ==================== LISTENERS ====================

    protected $listeners = [
        'refresh' => 'handleRefresh',
        'refreshLops' => 'handleRefresh',
        'filtersChanged' => 'handleFiltersChanged',
    ];

    // ==================== LIFECYCLE ====================

    /**
     * Override mount để set perPage default
     */
    public function mount()
    {
        // Set default perPage if not set
        // if (!$this->perPage || $this->perPage === 10) {
        //     $this->perPage = 15;
        // }

        parent::mount();
    }

    /**
     * Load dữ liệu ban đầu (required by BaseComponent)
     */
    protected function loadInitialData(): void
    {
        $this->loadNamHocs();

        if ($this->selectedNamHoc) {
            $this->loadKhois();
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
        // Không reset selectedNamHoc vì cần giữ năm học hiện tại
    }

    // ==================== DATA LOADING ====================

    /**
     * Load danh sách năm học
     */
    private function loadNamHocs(): void
    {
        try {
            $data = $this->getNamHocs($this->parish_id);
            $this->namHocs = $data['namHocs'] ?? [];

            // Auto-select năm học mới nhất nếu chưa chọn
            if (!$this->selectedNamHoc && !empty($data['selectedId'])) {
                $this->selectedNamHoc = $data['selectedId'];
            }
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading nam hocs');
            $this->namHocs = [];
            session()->flash('error', 'Không thể tải danh sách năm học.');
        }
    }

    /**
     * Load danh sách khối theo năm học đã chọn
     */
    private function loadKhois(): void
    {
        if (!$this->selectedNamHoc) {
            $this->khois = [];
            return;
        }

        try {
            $this->khois = $this->getKhois($this->selectedNamHoc);
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading khois', [
                'namhoc_id' => $this->selectedNamHoc
            ]);
            $this->khois = [];
            session()->flash('warning', 'Không thể tải danh sách khối.');
        }
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
        $this->khois = [];

        // Reload data
        if ($this->selectedNamHoc) {
            $this->loadKhois();
        }

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
    public function handleFiltersChanged($filters): void
    {
        if (!is_array($filters)) {
            return;
        }

        // Update filters from event
        $hasChanges = false;

        if (isset($filters['namHoc']) && $filters['namHoc'] != $this->selectedNamHoc) {
            $this->selectedNamHoc = $filters['namHoc'];
            $hasChanges = true;
        }

        if (isset($filters['khoi']) && $filters['khoi'] != $this->selectedKhoi) {
            $this->selectedKhoi = $filters['khoi'];
            $hasChanges = true;
        }

        // Clear search when filters change
        if ($hasChanges) {
            $this->search = '';
            $this->resetPage();

            // Reload khois if namHoc changed
            if (isset($filters['namHoc'])) {
                $this->loadKhois();
            }
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

    /**
     * Refresh danh sách
     * Override từ BaseComponent
     */
    public function handleRefresh(): void
    {
        $this->loadNamHocs();

        if ($this->selectedNamHoc) {
            $this->loadKhois();
        }

        $this->resetPage();
        session()->flash('message', 'Đã làm mới danh sách lớp học');
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
            $lops = app(LopService::class)->paginateLops(
                $this->selectedNamHoc,
                $this->selectedKhoi,
                $this->perPage,
                $this->search,
                $this->page ?? 1
            );

            // Ensure we always return a paginator
            if (!$lops instanceof \Illuminate\Pagination\LengthAwarePaginator) {
                $items = is_countable($lops) ? (array) $lops : [];
                $total = count($items);

                return new \Illuminate\Pagination\LengthAwarePaginator(
                    $items,
                    $total,
                    $this->perPage,
                    $this->page ?? 1
                );
            }

            return $lops;
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
    
    // ==================== HELPER METHODS ====================

    /**
     * Get per page options cho dropdown
     * Override để change default
     */
    public function getPerPageOptions(): array
    {
        return [10, 15, 25, 50];
    }
}
