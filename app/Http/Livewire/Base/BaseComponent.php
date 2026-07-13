<?php

namespace App\Http\Livewire\Base;

use App\Models\ClassTeacher;
use App\Models\NamHoc;
use App\Models\Teacher;
use App\Traits\FilterTrait;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Base Livewire Component - Template chuẩn cho tất cả components
 * 
 * Features:
 * - Authentication & Authorization
 * - Pagination with options
 * - Search functionality
 * - Query string support (URL sharing)
 * - Input sanitization & validation
 * - Error handling
 * - Event listeners
 * 
 * @package App\Http\Livewire\Base
 */

abstract class BaseComponent extends Component
{
    use FilterTrait;
    use WithPagination;
    use AuthorizesRequests;

    // ==================== AUTHENTICATION ====================
    /** @var int|null Parish ID hiện tại */
    public ?int $parishId = null;

    public ?int $defaultClassId = null;

    // ==================== PAGINATION & SEARCH ====================

    /** @var string Search query */
    public $search = '';

    /** @var int Items per page */
    public int $perPage = 15;

    /** @var array Allowed per page options */
    protected $perPageOptions = [10, 15, 25, 50, 100];

    /** @var bool Component có dùng pagination không */
    protected $usePagination = true;

    /** @var string Pagination theme */
    protected $paginationTheme = 'tailwind';

    /** @var string Cột đang sort */
    public string $sortField = 'name';

    /** @var string Hướng sort */
    public string $sortDirection = 'asc';

    // ==================== VALIDATION ====================

    /**
     * Validation rules - Override trong child class
     * 
     * @var array
     */
    protected $rules = [
        'perPage' => 'required|integer|in:10,15,25,50,100',
    ];

    /**
     * Custom validation messages - Override nếu cần
     * 
     * @var array
     */
    protected $messages = [];

    // ==================== LISTENERS ====================

    /**
     * Livewire event listeners
     * Override và merge với parent trong child class
     * 
     * @var array
     */
    protected $listeners = [
        'refresh' => '$refresh',

    ];

    // ==================== LIFECYCLE HOOKS ====================

    /**
     * Component initialization
     * Child class nên override và gọi parent::mount()
     */
    public function mount()
    {
        $this->initializeUser();
        $this->sanitizeQueryString();
        $this->validateInitialState();
        $this->loadInitialData();
    }

    // ==================== INITIALIZATION METHODS ====================
    protected function initializeUser(): void
    {
        /** @var \App\Models\User $user */
        $user = auth()->user() ?? abort(401, 'Chưa đăng nhập');

        if (!$user->isSuperAdmin()) {
            $this->parishId = $user->parishId();
        }

        if ($user->isCatechist()) {
            $teacher = Teacher::where('email', $user->email)->first();

            if ($teacher && $this->parishId) {
                $currentNamHocId = NamHoc::where('parish_id', $this->parishId)
                    ->active()
                    ->current()
                    ->value('id');

                $query = ClassTeacher::query()
                    ->where('teacher_id', $teacher->id)
                    ->where('status', true)
                    ->whereHas('catechismClass', function ($q) {
                        $q->where('parish_id', $this->parishId)
                            ->where('is_active', true);
                    });

                if ($currentNamHocId) {
                    $query->where(function ($q) use ($currentNamHocId) {
                        $q->where('namhoc_id', $currentNamHocId)
                            ->orWhereHas(
                                'catechismClass',
                                fn ($c) => $c->where('school_year_id', $currentNamHocId)
                            );
                    });
                }

                $this->defaultClassId = $query->orderByDesc('role')->value('class_id');
            }
        }
    }

    /**
     * Kiểm tra nếu cần parishId, throw 403 nếu không có
     */
    protected function requireParishId(): void
    {
        if (!$this->parishId) {
            abort(403, 'Không xác định được giáo xứ');
        }
    }

    /**
     * Load dữ liệu ban đầu
     * Child class PHẢI implement method này
     */
    abstract protected function loadInitialData(): void;

    /**
     * Get query string parameters động dựa vào usePagination
     */
    protected function queryString()
    {
        $params = [
            'search'        => ['except' => ''],
            'sortField'     => ['except' => 'name', 'as' => 'sort'],
            'sortDirection' => ['except' => 'asc',  'as' => 'dir'],
        ];

        if ($this->usePagination) {
            $params['perPage'] = ['except' => 15];
            $params['page'] = ['except' => 1];
        }

        return $params;
    }

    /**
     * Sanitize và coerce query string inputs về đúng type
     */
    protected function sanitizeQueryString(): void
    {
        if ($this->usePagination) {
            $this->perPage = is_numeric($this->perPage) ? (int) $this->perPage : 15;
            if (!in_array($this->perPage, $this->perPageOptions)) {
                $this->perPage = 15;
            }
        }

        // Sanitize search: trim whitespace
        $this->search = trim($this->search);
    }

    /**
     * Validate initial state sau mount
     * Nếu validation fails, reset về defaults
     */
    protected function validateInitialState(): void
    {
        if (!$this->usePagination) {
            return;
        }

        try {
            $this->validateOnly('perPage');
        } catch (ValidationException $e) {
            $this->resetToDefaults();
            session()->flash('warning', 'Một số tham số không hợp lệ, đã đặt lại về mặc định.');
        }
    }

    /**
     * Reset về giá trị mặc định
     * Child class có thể override để reset thêm properties
     */
    protected function resetToDefaults(): void
    {
        $this->search = '';
        $this->perPage = 15;
        $this->resetPage();
    }

    protected array $allowedSortFields = ['name'];

    public function sortBy(string $field): void
    {
        if (!in_array($field, $this->allowedSortFields)) return;

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField     = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    /**
     * Apply sort vào query — dùng trong render() của child class
     */
    protected function applySorting($query)
    {
        return $query->orderBy($this->sortField, $this->sortDirection);
    }

    // ==================== PROPERTY UPDATERS ====================

    /**
     * Auto reset page khi search thay đổi
     */
    public function updatedSearch(): void
    {
        $this->search = trim($this->search);
        $this->resetPage();
    }

    /**
     * Auto reset page và validate khi perPage thay đổi
     */
    public function updatedPerPage(): void
    {
        // Sanitize incoming perPage value
        $this->perPage = is_numeric($this->perPage) ? (int) $this->perPage : 15;
        if (!in_array($this->perPage, $this->perPageOptions)) {
            $this->perPage = 15;
        }

        $this->validateOnly('perPage');
        $this->resetPage();
    }

    // ==================== COMMON ACTIONS ====================

    /**
     * Reset filters và pagination
     */
    public function resetFilters(): void
    {
        $this->search = '';
        $this->resetPage();

        // Child class có thể override để reset thêm filters

        session()->flash('message', 'Đã đặt lại bộ lọc');
    }

    /**
     * Refresh dữ liệu
     */
    public function handleRefresh(): void
    {
        $this->resetPage();
        session()->flash('message', 'Đã làm mới danh sách');
    }

    // ==================== HELPER METHODS ====================

    /**
     * Log error với context đầy đủ
     * 
     * @param \Exception $e Exception instance
     * @param string $context Mô tả ngữ cảnh
     * @param array $extra Thông tin thêm
     */
    protected function logError(\Exception $e, string $context, array $extra = []): void
    {
        Log::error(static::class . ": $context - " . $e->getMessage(), array_merge([
            'parishId' => $this->parishId,
            'search' => $this->search,
            'perPage' => $this->perPage,
        ], $extra));
    }

    /**
     * Check quyền quản trị (Super Admin hoặc Parish Admin), throw 403 nếu không phải
     */
    protected function requireManager(): void
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $user->canManage()
            || abort(403, 'Chỉ quản trị viên mới có quyền');
    }

    /**
     * Check quyền Decen của xứ hiện tại
     */
    protected function requireDecenOfParish(): void
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $user->canManage()
            || abort(403, 'Chỉ quản trị xứ mới có quyền');

        $this->parishId
            || abort(403, 'Không xác định được giáo xứ');
    }

    /**
     * Get per page options cho dropdown
     * 
     * @return array
     */
    public function getPerPageOptions(): array
    {
        return $this->perPageOptions;
    }

    // ==================== RENDER ====================

    /**
     * Render component
     * Child class PHẢI implement method này
     * 
     * @return \Illuminate\View\View
     */
    abstract public function render();
}
