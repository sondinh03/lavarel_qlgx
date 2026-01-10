<?php

namespace App\Http\Livewire\Student;

use App\Http\Livewire\Base\BaseComponent;
use App\Models\Block;
use App\Models\Lop;
use App\Models\NamHoc;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

/**
 * Component danh sách học viên trong lớp
 * 
 * Features:
 * - Pagination with options
 * - Search by name, holy name, mahv
 * - Bulk selection
 * - Gender statistics
 * - URL slug generation
 * 
 * @package App\Http\Livewire\Student
 */
class StudentList extends BaseComponent
{
    // ==================== ROUTE PARAMS ====================

    /** @var int Lớp ID */
    public $lopId;

    // ==================== SELECTION ====================

    /** @var array Selected student IDs */
    public $selectedStudents = [];

    /** @var bool Select all checkbox state */
    public $selectAll = false;

    // ==================== PROTECTED DATA ====================

    /** @var \App\Models\Lop|null Lớp model instance */
    protected $lopModel = null;

    // ==================== VALIDATION ====================

    protected $rules = [
        'search' => 'nullable|string|max:255',
        'perPage' => 'required|integer|in:10,15,25,50',
        'selectedStudents' => 'nullable|array',
        'selectedStudents.*' => 'integer',
    ];

    protected $messages = [
        'search.max' => 'Tìm kiếm không được quá 255 ký tự',
        'perPage.in' => 'Số mục trên trang không hợp lệ',
        'selectedStudents.*.integer' => 'ID học viên không hợp lệ',
    ];

    // ==================== QUERY STRING ====================

    protected $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 10],
        'page' => ['except' => 1],
    ];

    // ==================== LISTENERS ====================

    protected $listeners = [
        'refresh' => 'handleRefresh',
        'refreshStudents' => 'handleRefresh',
    ];

    // ==================== LIFECYCLE ====================

    /**
     * Component initialization
     */
    public function mount($id = null): void
    {
        $this->lopId = (int) $id;

        parent::mount();
    }

    /**
     * Load dữ liệu ban đầu (required by BaseComponent)
     */
    protected function loadInitialData(): void
    {
        $this->loadLopData();
    }

    /**
     * Override validateUserAccess
     */
    protected function validateUserAccess(): void
    {
        parent::validateUserAccess();

        // Component này BẮT BUỘC phải có parishId
        if (!$this->parishId) {
            abort(403, 'Không xác định được giáo xứ');
        }
    }

    // ==================== DATA LOADING ====================

    /**
     * Load thông tin lớp học
     */
    private function loadLopData(): void
    {
        try {
            $this->lopModel = Lop::with(['blockRelation', 'schoolYear', 'classTeachers.teacher'])
                ->findOrFail($this->lopId);

            // Validate ownership
            $this->validateLopOwnership();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            session()->flash('error', 'Không tìm thấy lớp học này.');
            $this->logError($e, 'Lop not found', ['lop_id' => $this->lopId]);
            $this->redirectRoute('classes.index');
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading lop data', ['lop_id' => $this->lopId]);
            session()->flash('error', 'Có lỗi khi tải thông tin lớp học.');
            $this->redirectRoute('classes.index');
        }
    }

    /**
     * Validate user can access this lop
     */
    private function validateLopOwnership(): void
    {
        if (!$this->lopModel) {
            return;
        }

        // Admin có thể xem mọi lớp
        if ($this->isAdmin) {
            return;
        }

        // Decen chỉ xem lớp của parish mình
        if ($this->isDecen) {
            if ($this->lopModel->pid != $this->parishId) {
                abort(403, 'Bạn không có quyền xem lớp học này.');
            }
            return;
        }

        abort(403, 'Không có quyền xem lớp học này.');
    }

    // ==================== PROPERTY UPDATERS ====================

    /**
     * Override updatedSearch để validate
     */
    public function updatedSearch(): void
    {
        $this->search = trim($this->search);

        try {
            $this->validateOnly('search');
        } catch (ValidationException $e) {
            $this->search = '';
            session()->flash('warning', 'Từ khóa tìm kiếm không hợp lệ.');
        }

        $this->resetPage();
        $this->resetSelection();
    }

    /**
     * When select all checkbox changes
     */
    public function updatedSelectAll($value): void
    {
        if ($value) {
            // Select all students on current page
            $ids = $this->getCurrentStudentsQuery()
                ->pluck('id')
                ->map(fn($id) => (int) $id)
                ->toArray();

            $this->selectedStudents = $ids;
        } else {
            // Deselect all
            $this->selectedStudents = [];
        }
    }

    /**
     * When individual checkboxes change
     */
    public function updatedSelectedStudents(): void
    {
        // Sanitize: ensure array of integers
        $this->selectedStudents = array_values(
            array_unique(
                array_map('intval', array_filter($this->selectedStudents, 'is_numeric'))
            )
        );

        // Update select all checkbox state
        $currentIds = $this->getCurrentStudentsQuery()->pluck('id')->toArray();
        $selectedCount = count(array_intersect($this->selectedStudents, $currentIds));
        $totalCount = count($currentIds);

        $this->selectAll = $totalCount > 0 && $selectedCount === $totalCount;
    }

    // ==================== QUERY HELPERS ====================

    /**
     * Get base query for students in this lop
     */
    private function getCurrentStudentsQuery()
    {
        if (!$this->lopModel) {
            return Student::whereRaw('1 = 0'); // Empty query
        }

        return $this->lopModel->students()
            ->wherePivot('status', 1)
            ->when($this->search, function ($q) {
                $search = trim($this->search);
                $q->where(function ($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('holy', 'like', "%{$search}%")
                        ->orWhere('mahv', 'like', "%{$search}%");
                });
            })
            ->orderBy('name', 'asc');
    }

    /**
     * Get paginated students
     */
    private function getStudentsPaginated(): LengthAwarePaginator
    {
        try {
            $students = $this->getCurrentStudentsQuery()->paginate($this->perPage);

            // Transform each student
            $students->getCollection()->transform(function ($student, $index) use ($students) {
                return $this->transformStudent($student, $students->firstItem() + $index);
            });

            return $students;
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading students', [
                'lop_id' => $this->lopId,
                'search' => $this->search,
            ]);

            session()->flash('error', 'Có lỗi khi tải danh sách học viên.');

            return new LengthAwarePaginator([], 0, $this->perPage, $this->page ?? 1);
        }
    }

    /**
     * Transform student data for display
     */
    private function transformStudent($student, int $stt)
    {
        $student->stt = $stt;

        // Generate URLs
        $baseSlug = slug($student) . config('app.url_prefix', '');
        $student->slug = url($baseSlug);
        $student->thugioithieu = url($baseSlug . '/thugioithieu=' . $student->id);
        $student->edit = config('app.url') . '/admin/student/' . $student->id . '/edit';

        // Load relationships
        $student->holy = $student->holyRelation->name ?? '';
        $student->paid = $student->paidRelation->name ?? '';

        // Address
        $student->ward = $this->getXaPhuongName($student->ward);
        $student->province = $this->getTinhThanhName($student->province);

        return $student;
    }

    // ==================== STATISTICS ====================

    /**
     * Get gender statistics
     */
    private function getGenderStats(): array
    {
        if (!$this->lopModel) {
            return [
                'total' => 0,
                'countnam' => 0,
                'countnu' => 0,
            ];
        }

        try {
            $activeStudents = $this->lopModel->students()->wherePivot('status', 1);

            $countnam = (clone $activeStudents)->where('sex', 1)->count();
            $countnu = (clone $activeStudents)->where('sex', 0)->count();

            return [
                'total' => $countnam + $countnu,
                'countnam' => $countnam,
                'countnu' => $countnu,
            ];
        } catch (\Exception $e) {
            $this->logError($e, 'Error calculating gender stats', ['lop_id' => $this->lopId]);

            return [
                'total' => 0,
                'countnam' => 0,
                'countnu' => 0,
            ];
        }
    }

    // ==================== LOP INFO HELPERS ====================

    /**
     * Get formatted lop info for view
     */
    private function getLopInfo(): object
    {
        if (!$this->lopModel) {
            return (object) [
                'name' => 'N/A',
                'symbol' => 'N/A',
                'block' => '',
                'schoolyear' => '',
                'teachers' => [],
                'slug' => '#',
            ];
        }

        $lop = clone $this->lopModel;

        // Generate slug
        $lop->slug = url(slug($lop) . config('app.url_prefix', ''));

        // Block name
        $lop->block = $lop->blockRelation->name ?? '';

        // School year name
        $lop->schoolyear = $lop->schoolYear->name ?? '';

        // Teachers (using relationship instead of parsing JSON)
        $lop->teachers = $this->getLopTeachers();

        return $lop;
    }

    /**
     * Get teachers for this lop
     */
    private function getLopTeachers(): array
    {
        if (!$this->lopModel || !$this->lopModel->relationLoaded('classTeachers')) {
            return [];
        }

        return $this->lopModel->classTeachers
            ->filter(fn($ct) => $ct->teacher && $ct->teacher->status == 1)
            ->sortBy(fn($ct) => $ct->role == 'chu_nhiem' ? 0 : 1) // Chủ nhiệm first
            ->pluck('teacher.name')
            ->toArray();
    }

    // ==================== ADDRESS HELPERS ====================

    /**
     * Get tỉnh/thành phố name
     */
    private function getTinhThanhName($provinceId): string
    {
        if (!$provinceId) {
            return '';
        }

        // Cache 1 hour
        return Cache::remember("tinh_thanh_{$provinceId}", 3600, function () use ($provinceId) {
            $filePath = resource_path('cities/tinh_thanhpho.php');

            if (!file_exists($filePath)) {
                return '';
            }

            include $filePath;

            return isset($tinh_thanhpho[$provinceId])
                ? ', ' . $tinh_thanhpho[$provinceId]
                : '';
        });
    }

    /**
     * Get xã/phường name
     */
    private function getXaPhuongName($wardId): string
    {
        if (!$wardId) {
            return '';
        }

        // Cache 1 hour
        return Cache::remember("xa_phuong_{$wardId}", 3600, function () use ($wardId) {
            $filePath = resource_path('cities/xa_phuong_thitran.php');

            if (!file_exists($filePath)) {
                return '';
            }

            include $filePath;

            foreach ($xa_phuong_thitran as $xaphuong) {
                if ($xaphuong['xaid'] == $wardId) {
                    return $xaphuong['name'] ?? '';
                }
            }

            return '';
        });
    }

    // ==================== ACTIONS ====================

    /**
     * Reset selection
     */
    private function resetSelection(): void
    {
        $this->selectedStudents = [];
        $this->selectAll = false;
    }

    /**
     * Refresh danh sách
     */
    public function handleRefresh(): void
    {
        $this->loadLopData();
        $this->resetPage();
        $this->resetSelection();
        session()->flash('message', 'Đã làm mới danh sách học viên');
    }

    /**
     * Reset filters
     */
    public function resetFilters(): void
    {
        $this->search = '';
        $this->resetPage();
        $this->resetSelection();
        session()->flash('message', 'Đã đặt lại bộ lọc');
    }

    // ==================== RENDER ====================

    /**
     * Render component
     */
    public function render()
    {
        $students = $this->getStudentsPaginated();
        $stats = $this->getGenderStats();
        $lop = $this->getLopInfo();

        return view('livewire.student.student-list', [
            'lop' => $lop,
            'students' => $students,
            'total' => $stats['total'],
            'countnam' => $stats['countnam'],
            'countnu' => $stats['countnu'],
            'parishId' => $this->parishId,
        ])
            ->extends('frontend.layout.main')
            ->section('content');
    }
}