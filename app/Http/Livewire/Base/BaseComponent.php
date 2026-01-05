<?php

namespace App\Http\Livewire\Base;

use App\Traits\FilterTrait;
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

    // ==================== AUTHENTICATION ====================

    /** @var int|null Parish ID từ session */
    public $parish_id;

    /** @var bool Quyền admin từ session */
    public $isAdmin = false;

    /** @var bool Kiểm tra quyền quản trị xứ */
    public $isDecen = false;

    // ==================== PAGINATION & SEARCH ====================

    /** @var string Search query */
    public $search = '';

    /** @var int Items per page */
    public $perPage = 15;

    /** @var array Allowed per page options */
    protected $perPageOptions = [10, 15, 25, 50];

    /** @var bool Component có dùng pagination không */
    protected $usePagination = true;

    /** @var string Pagination theme */
    protected $paginationTheme = 'tailwind';

    // ==================== VALIDATION ====================

    /**
     * Validation rules - Override trong child class
     * 
     * @var array
     */
    protected $rules = [
        'perPage' => 'required|integer|in:10,15,25,50',
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

    /**
     * Khởi tạo thông tin user từ session
     * Child class có thể override để custom logic
     */
    protected function initializeUser(): void
    {
        $this->parish_id = session('parish_id');
        $this->isAdmin = session('isAdmin', false);
        $this->isDecen = session('isDecen', false);

        // Validate user có quyền truy cập không
        $this->validateUserAccess();
    }

    /**
     * Validate user có quyền truy cập component này không
     * Override method này trong child class để custom authorization logic
     */
    protected function validateUserAccess(): void
    {
        // Admin tổng: Có quyền trên toàn hệ thống
        if ($this->isAdmin) {
            return;
        }

        // Decen (quản trị xứ): Phải có parish_id
        if ($this->isDecen) {
            if (!$this->parish_id) {
                abort(403, 'Không xác định được giáo xứ của bạn.');
            }
            return;
        }

        // Không phải admin cũng không phải decen
        abort(403, 'Không có quyền truy cập');
    }

    /**
     * Yêu cầu phải có parish_id (cho cả Admin và Decen)
     * Gọi method này trong child component nếu bắt buộc phải có parish_id
     */
    protected function requireParishId(): void
    {
        if (!$this->parish_id) {
            if ($this->isAdmin) {
                // Redirect admin đến trang chọn xứ hoặc hiển thị thông báo
                session()->flash('warning', 'Vui lòng chọn giáo xứ để tiếp tục');
                $this->redirectRoute('admin.select-parish'); // Hoặc route phù hợp
            } else {
                abort(403, 'Không xác định được giáo xứ');
            }
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
            'search' => ['except' => ''],
        ];

        if ($this->usePagination) {
            $params['perPage'] = ['except' => 10];
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
        try {
            $this->validate();
        } catch (ValidationException $e) {
            $this->resetToDefaults();
            session()->flash('warning', 'Một số tham số không hợp lệ, đã đặt lại về mặc định.');

            Log::warning(static::class . ': Validation failed on mount', [
                'errors' => $e->errors(),
                'parish_id' => $this->parish_id,
            ]);
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
        Log::error(static::class . ": {$context}", array_merge([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'parish_id' => $this->parish_id,
            'is_admin' => $this->isAdmin,
        ], $extra));
    }

    /**
     * Check quyền admin tổng, throw 403 nếu không phải
     */
    protected function requireAdmin(): void
    {
        if (!$this->isAdmin) {
            abort(403, 'Chỉ quản trị tổng mới có quyền thực hiện thao tác này');
        }
    }

    /**
     * Check quyền quản trị (Admin hoặc Decen), throw 403 nếu không phải
     */
    protected function requireManager(): void
    {
        if (!$this->isAdmin && !$this->isDecen) {
            abort(403, 'Chỉ quản trị viên mới có quyền thực hiện thao tác này');
        }
    }

    /**
     * Check quyền Decen của xứ hiện tại
     */
    protected function requireDecenOfParish(): void
    {
        if (!$this->isDecen) {
            abort(403, 'Chỉ quản trị xứ mới có quyền thực hiện thao tác này');
        }

        if (!$this->parish_id) {
            abort(403, 'Không xác định được giáo xứ');
        }
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
