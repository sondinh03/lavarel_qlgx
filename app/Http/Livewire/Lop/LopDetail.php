<?php

namespace App\Http\Livewire\Lop;

use App\Http\Livewire\Base\BaseComponent;
use App\Models\ClassTeacher;
use App\Models\Lop;
use Illuminate\Support\Facades\Cache;

/**
 * Component chi tiết Lớp học
 * 
 * Features:
 * - Hiển thị thông tin chi tiết lớp
 * - Danh sách giáo lý viên
 * - Thống kê học sinh (cache 5 phút)
 */
class LopDetail extends BaseComponent
{
    // ==================== ROUTE PARAMS ====================

    /** @var int ID của lớp học */
    public $lopId;

    // ==================== PUBLIC DATA ====================

    /** @var array Thông tin cơ bản lớp học (primitive types only) */
    public $lopData = [];

    /** @var array Danh sách giáo lý viên */
    public $teachers = [];

    /** @var array Thống kê học sinh */
    public $statistics = [];

    // ==================== PROTECTED DATA ====================

    /** @var \App\Models\Lop|null Model instance (PROTECTED để tránh serialization) */
    protected $lopModel = null;

    /** @var \App\Models\Block|null Block relation */
    public $block;

    /** @var \App\Models\NamHoc|null Năm học relation */
    protected $namHoc = null;

    // ==================== LISTENERS ====================

    protected $listeners = [
        'refresh' => 'handleRefresh',
        'filtersChanged' => 'handleFiltersChanged',
    ];

    // ==================== LIFECYCLE ====================

    /**
     * Component initialization
     */
    public function mount($id = null)
    {
        $this->lopId = (int) $id;

        parent::mount();
        // Không cần requireManager vì đây là view public
        // Nhưng vẫn validate parish_id nếu cần
        // $this->requireParishId();
    }

    /**
     * Load dữ liệu ban đầu (implement từ BaseComponent)
     */
    protected function loadInitialData(): void
    {
        $this->loadLopDetails();

        if (!$this->lopModel) {
            $this->redirectRoute('ds-lop');
            return;
        }

        $this->loadStatistics();
    }

    /**
     * Override validateUserAccess - Lớp này public nên không cần strict auth
     */
    protected function validateUserAccess(): void
    {
        // Public view - không cần authorization
        // Hoặc nếu cần, có thể check:
        // if (!$this->parish_id) {
        //     abort(403, 'Không xác định được giáo xứ');
        // }
    }


    /**
     * Load chi tiết lớp học
     */
    private function loadLopDetails(): void
    {
        try {
            $this->lopModel = Lop::with([
                'blockRelation',
                'schoolYear',
                'classTeachers' => function ($q) {
                    $q->where('status', 1)
                        ->with('teacher')
                        ->orderBy('role', 'asc');
                }
            ])
                ->withCount('students')
                ->findOrFail($this->lopId);

            // Load teachers data
            $this->loadTeachers();

            // Load relations
            $this->block = $this->lopModel->blockRelation;
            $this->namHoc = $this->lopModel->schoolYear;

            // Expose minimal public data (primitive types only)
            $this->lopData = [
                'id' => $this->lopModel->id,
                'name' => $this->lopModel->name ?? '',
                'symbol' => $this->lopModel->symbol ?? '',
                'students_count' => (int) ($this->lopModel->students_count ?? 0),
                'start_date_one' => $this->lopModel->start_date_one ?? null,
                'end_date_one' => $this->lopModel->end_date_one ?? null,
                'start_date_two' => $this->lopModel->start_date_two ?? null,
                'end_date_two' => $this->lopModel->end_date_two ?? null,
                'note' => $this->lopModel->note ?? null,
                'parish_id' => (int) ($this->lopModel->pid ?? 0),
            ];
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            session()->flash('error', 'Không tìm thấy lớp học này.');
            $this->logError($e, 'Class not found', ['lop_id' => $this->lopId]);
            $this->lopModel = null;
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading class details', ['lop_id' => $this->lopId]);
            session()->flash('error', 'Có lỗi trong lúc tải thông tin lớp.');
            $this->lopModel = null;
        }
    }

    /**
     * Load danh sách giáo lý viên
     */
    private function loadTeachers(): void
    {
        $teachersData = [];

        if ($this->lopModel->relationLoaded('classTeachers') && $this->lopModel->classTeachers->isNotEmpty()) {
            $schoolYearId = $this->lopModel->schoolYear?->id ?? null;

            foreach ($this->lopModel->classTeachers as $ct) {
                // Filter by school year & ensure teacher is active
                if (
                    $ct->teacher &&
                    $ct->teacher->status == 1 &&
                    (is_null($schoolYearId) || $ct->namhoc_id == $schoolYearId)
                ) {

                    $teacher = $ct->teacher;
                    $isChuNhiem = ($ct->role == ClassTeacher::ROLE_CHU_NHIEM);

                    $teachersData[] = [
                        'id' => $teacher->id,
                        'name' => $teacher->name,
                        'birthday' => $teacher->birthday ?? null,
                        'phone' => $teacher->phone ?? '',
                        'is_chu_nhiem' => $isChuNhiem,
                    ];
                }
            }
        }

        $this->teachers = $teachersData;
    }

    /**
     * Load thống kê học sinh (với cache 5 phút)
     */
    private function loadStatistics(): void
    {
        if (!$this->lopModel) {
            $this->statistics = ['total' => 0, 'male' => 0, 'female' => 0];
            return;
        }

        try {
            $schoolYearId = $this->lopModel->schoolYear?->id ?? 'none';
            $cacheKey = "class_stats:{$this->lopModel->id}:{$schoolYearId}";
            $ttlSeconds = 300; // 5 minutes

            $this->statistics = Cache::remember($cacheKey, $ttlSeconds, function () {
                $result = $this->lopModel->students()
                    ->wherePivot('status', 1)
                    ->withoutGlobalScopes()
                    ->selectRaw("
                        COUNT(*) as total,
                        SUM(CASE WHEN sex = 1 THEN 1 ELSE 0 END) as male,
                        SUM(CASE WHEN sex = 0 THEN 1 ELSE 0 END) as female
                    ")
                    ->getQuery()
                    ->first();

                return [
                    'total'  => (int) ($result->total ?? 0),
                    'male'   => (int) ($result->male ?? 0),
                    'female' => (int) ($result->female ?? 0),
                ];
            });
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading statistics', ['lop_id' => $this->lopId]);
            $this->statistics = ['total' => 0, 'male' => 0, 'female' => 0];
        }
    }

    // ==================== EVENT HANDLERS ====================

    /**
     * Handle filters changed event
     */
    public function handleFiltersChanged($filters): void
    {
        if (!is_array($filters)) {
            return;
        }

        $newLopId = $filters['lop'] ?? null;

        if ($newLopId && (int) $newLopId !== (int) $this->lopId) {
            $this->lopId = (int) $newLopId;
            $this->loadLopDetails();
            $this->loadStatistics();
            $this->redirect(route('lop.show', $newLopId));
        }
    }

    /**
     * Refresh dữ liệu
     */
    public function handleRefresh(): void
    {
        // Clear cache
        if ($this->lopModel && $this->lopModel->schoolYear) {
            $schoolYearId = $this->lopModel->schoolYear->id ?? 'none';
            $cacheKey = "class_stats:{$this->lopModel->id}:{$schoolYearId}";
            Cache::forget($cacheKey);
        }

        // Reload data
        $this->loadLopDetails();
        $this->loadStatistics();

        session()->flash('message', 'Đã làm mới thông tin lớp học');
    }

    // ==================== RENDER ====================

    /**
     * Render component
     */
    public function render()
    {
        return view('livewire.lop.lop-detail', [
            // Pass protected data to view
            'parishId' => $this->parish_id,
            'block' => $this->block,
            'namHoc' => $this->namHoc,
        ])
            ->extends('frontend.layout.main')
            ->section('content');
    }

}
