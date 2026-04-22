<?php

namespace App\Http\Livewire\Dashboard;

use App\Http\Livewire\Base\BaseComponent;
use App\Models\CatechismClass;   // ← thay Lop (bảng: lop → classes)
use App\Models\NamHoc;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;


class AdminDashboard extends BaseComponent
{
    protected $usePagination = false;
    const CACHE_TTL = 600;

    public $activeSchoolYear = null;
    public $stats = ['students' => 0, 'classes' => 0, 'teachers' => 0, 'attendance' => null];
    public $todos = [];
    public $todayAttendance = [];
    public $studentsByGrade = [];
    public $genderStats = ['male' => 0, 'female' => 0];

    // ==================== LIFECYCLE ====================

    public function mount(): void
    {
        if (auth()->user()?->isCatechist()) {
            redirect()->route('catechist.dashboard');
        }

        parent::mount();
        $this->requireParishId();
    }

    protected function loadInitialData(): void
    {
        $this->loadDashboard();
    }

    // ==================== ACTIONS ====================

    public function refresh(): void
    {
        Cache::forget($this->cacheKey());
        $this->loadDashboard();
        session()->flash('message', 'Đã làm mới dữ liệu');
    }

    // ==================== DATA LOADING ====================

    private function loadDashboard(): void
    {
        try {
            $parishId = $this->parishId;


            $data = Cache::remember($this->cacheKey(), self::CACHE_TTL, function () use ($parishId) {
                $schoolYear = NamHoc::active()
                    ->orderByDesc('id')
                    ->first();

                if (!$schoolYear) {
                    return $this->emptyData();
                }

                return [
                    'schoolYear'      => $schoolYear,
                    'stats'           => $this->buildStats($schoolYear, $parishId),
                    'todos'           => $this->buildTodos($schoolYear, $parishId),
                    'studentsByGrade' => $this->buildStudentsByGrade($schoolYear, $parishId),
                    'genderStats'     => $this->buildGenderStats($schoolYear, $parishId),
                ];
            });

            $this->activeSchoolYear = $data['schoolYear'];
            $this->stats            = $data['stats'];
            $this->todos            = $data['todos'];
            $this->studentsByGrade  = $data['studentsByGrade'];
            $this->genderStats      = $data['genderStats'];

            if ($this->activeSchoolYear) {
                $this->todayAttendance = $this->buildTodayAttendance(
                    $this->activeSchoolYear,
                    $parishId
                );
            }
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading dashboard');
            dd($e);
            session()->flash('error', 'Có lỗi khi tải dữ liệu. Vui lòng thử lại.');
        }
    }

    // ==================== BUILDERS ====================

    private function buildStats(NamHoc $schoolYear, int $parishId): array
    {
        // ✅ CatechismClass: parish_id + school_year_id + is_active (boolean)
        $classCount = CatechismClass::where('parish_id', $parishId)
            ->where('school_year_id', $schoolYear->id)
            ->where('is_active', true)
            ->count();

        // ✅ Bảng: classes | cột: school_year_id, parish_id
        $studentCount = DB::table('students_class')
            ->join('classes', 'students_class.class_id', '=', 'classes.id')
            ->where('classes.school_year_id', $schoolYear->id)
            ->where('classes.parish_id', $parishId)
            ->where('students_class.status', 1)
            ->distinct('students_class.student_id')
            ->count('students_class.student_id');

        // ✅ Bảng: classes (không phải catechism_classes hay lop)
        $teacherCount = DB::table('class_teachers')
            ->join('classes', 'class_teachers.class_id', '=', 'classes.id')
            ->where('classes.school_year_id', $schoolYear->id)
            ->where('classes.parish_id', $parishId)
            ->where('class_teachers.status', 1)
            ->distinct('class_teachers.teacher_id')
            ->count('class_teachers.teacher_id');

        return [
            'students'   => $studentCount,
            'classes'    => $classCount,
            'teachers'   => $teacherCount,
            'attendance' => $this->getWeeklyAttendanceRate($schoolYear, $parishId),
        ];
    }

    private function buildTodos(NamHoc $schoolYear, int $parishId): array
    {
        $todos = [];

        // 1. Lớp chưa có GLV
        // ✅ teachers() thay classTeachers() | is_active thay status=1
        $classesWithoutTeacher = CatechismClass::where('parish_id', $parishId)
            ->where('school_year_id', $schoolYear->id)
            ->where('is_active', true)
            ->whereDoesntHave('teachers', fn($q) => $q->where('class_teachers.status', 1))
            ->count();

        if ($classesWithoutTeacher > 0) {
            $todos[] = [
                'type'     => 'warning',
                'icon'     => 'teacher',
                'message'  => "{$classesWithoutTeacher} lớp chưa có Giáo lý viên",
                'count'    => $classesWithoutTeacher,
                'route'    => 'classes.index',
                'priority' => 1,
            ];
        }

        // 2. Lớp chưa đủ sĩ số
        // ✅ Bảng: classes | is_active thay status | thêm parish_id
        $classesUnderstaffed = DB::table('classes')
            ->leftJoin('students_class', function ($join) {
                $join->on('classes.id', '=', 'students_class.class_id')
                    ->where('students_class.status', 1);
            })
            ->where('classes.school_year_id', $schoolYear->id)
            ->where('classes.parish_id', $parishId)
            ->where('classes.is_active', true)
            ->select('classes.id', DB::raw('COUNT(students_class.student_id) as student_count'))
            ->groupBy('classes.id')
            ->having('student_count', '<', 5)
            ->count();

        if ($classesUnderstaffed > 0) {
            $todos[] = [
                'type'     => 'warning',
                'icon'     => 'students',
                'message'  => "{$classesUnderstaffed} lớp có ít hơn 5 học sinh",
                'count'    => $classesUnderstaffed,
                'route'    => 'classes.index',
                'priority' => 2,
            ];
        }

        // 3. Năm học chưa thiết lập học kỳ
        if (!$schoolYear->start_date_one || !$schoolYear->end_date_one) {
            $todos[] = [
                'type'     => 'info',
                'icon'     => 'calendar',
                'message'  => 'Năm học chưa thiết lập thời gian học kỳ',
                'count'    => 0,
                'route'    => 'school-years.index',
                'priority' => 3,
            ];
        }

        usort($todos, fn($a, $b) => $a['priority'] <=> $b['priority']);

        return $todos;
    }

    private function buildStudentsByGrade(NamHoc $schoolYear, int $parishId): array
    {
        // ✅ Bảng: classes + grade_levels | sort_order thay weight
        $rows = DB::table('classes')
            ->join('students_class', 'classes.id', '=', 'students_class.class_id')
            ->join('grade_levels', 'classes.grade_level_id', '=', 'grade_levels.id')
            ->where('classes.school_year_id', $schoolYear->id)
            ->where('classes.parish_id', $parishId)
            ->where('students_class.status', 1)
            ->select(
                'grade_levels.name as grade',
                'grade_levels.sort_order',
                DB::raw('COUNT(DISTINCT students_class.student_id) as count')
            )
            ->groupBy('grade_levels.id', 'grade_levels.name', 'grade_levels.sort_order')
            ->orderBy('grade_levels.sort_order')
            ->get();

        return $rows->map(fn($r) => [
            'grade' => $r->grade,
            'count' => (int) $r->count,
        ])->toArray();
    }

    private function buildGenderStats(NamHoc $schoolYear, int $parishId): array
    {
        // ✅ Bảng: students (thay student) | cột: gender (thay sex)
        $data = DB::table('students_class')
            ->join('classes', 'students_class.class_id', '=', 'classes.id')
            ->join('students', 'students_class.student_id', '=', 'students.id')
            ->where('classes.school_year_id', $schoolYear->id)
            ->where('classes.parish_id', $parishId)
            ->where('students_class.status', 1)
            ->selectRaw('
                COUNT(DISTINCT CASE WHEN students.gender = "male" THEN students.id END) as male,
                COUNT(DISTINCT CASE WHEN students.gender = "female" THEN students.id END) as female
            ')
            ->first();

        return [
            'male'   => (int) ($data->male ?? 0),
            'female' => (int) ($data->female ?? 0),
        ];
    }

    private function buildTodayAttendance(NamHoc $schoolYear, int $parishId): array
    {
        $classes = CatechismClass::with(['gradeLevel', 'teachers'])
            ->where('school_year_id', $schoolYear->id)
            ->where('parish_id', $parishId)
            ->where('is_active', true)
            ->withCount([
                'students as students_count' => fn($q) => $q->where('students_class.status', 1),
            ])
            ->orderBy('name')
            ->limit(10)
            ->get();

        return $classes->map(function (CatechismClass $class) {
            return [
                'id'             => $class->id,
                'name'           => $class->name,
                'block'          => $class->gradeLevel?->name ?? '',
                'students_count' => $class->students_count ?? 0,
                'has_attendance' => false,
                'attended'       => 0,
                'url'            => route('classes.show', $class->id),
            ];
        })->toArray();
    }

    private function getWeeklyAttendanceRate(NamHoc $schoolYear, int $parishId): ?float
    {
        return null; // TODO: tích hợp khi có bảng attendance
    }

    // ==================== HELPERS ====================

    private function cacheKey(): string
    {
        return "home_dashboard_{$this->parishId}";
    }

    private function emptyData(): array
    {
        return [
            'schoolYear'      => null,
            'stats'           => ['students' => 0, 'classes' => 0, 'teachers' => 0, 'attendance' => null],
            'todos'           => [],
            'studentsByGrade' => [],
            'genderStats'     => ['male' => 0, 'female' => 0],
        ];
    }

    // ==================== COMPUTED ====================

    public function getTodoCountProperty(): int
    {
        return count($this->todos);
    }

    public function getTodayLabelProperty(): string
    {
        $days = [
            0 => 'Chủ nhật',
            1 => 'Thứ hai',
            2 => 'Thứ ba',
            3 => 'Thứ tư',
            4 => 'Thứ năm',
            5 => 'Thứ sáu',
            6 => 'Thứ bảy',
        ];

        return $days[now()->dayOfWeek] . ', ' . now()->format('d/m/Y');
    }

    public function getCurrentSemesterLabelProperty(): string
    {
        if (!$this->activeSchoolYear) {
            return '';
        }

        $semester = $this->activeSchoolYear->current_semester;

        return $semester ? "Học kỳ {$semester}" : 'Chưa xác định học kỳ';
    }

    // ==================== RENDER ====================

    public function render()
    {
        return view('livewire.dashboard.admin-dashboard', [
            'todoCount'     => $this->todoCount,
            'todayLabel'    => $this->todayLabel,
            'semesterLabel' => $this->currentSemesterLabel,
        ])
            ->extends('frontend.layout.main')
            ->section('content');
    }
}
