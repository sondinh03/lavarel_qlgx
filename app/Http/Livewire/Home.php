<?php

namespace App\Http\Livewire;

use App\Http\Livewire\Base\BaseComponent;
use App\Models\Block;
use App\Models\Lop;
use App\Models\NamHoc;
use App\Models\Student;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Trang chủ / Dashboard cho admin giáo lý một xứ
 *
 * Thiết kế theo hướng "Action-first":
 * - Ưu tiên hiển thị việc cần làm
 * - Số liệu tổng quan nhanh
 * - Điểm danh hôm nay
 * - Truy cập nhanh các chức năng
 *
 * Users: Cha xứ / Ban quản trị, Trưởng ban giáo lý
 * Tần suất: Mỗi tuần
 */
class Home extends BaseComponent
{
    // ==================== CONFIG ====================

    /** Không dùng pagination ở trang chủ */
    protected $usePagination = false;

    /** Cache duration: 10 phút */
    const CACHE_TTL = 600;

    // ==================== DATA ====================

    /** @var NamHoc|null Năm học đang active */
    public $activeSchoolYear = null;

    /** @var array Số liệu tổng quan */
    public $stats = [
        'students'   => 0,
        'classes'    => 0,
        'teachers'   => 0,
        'attendance' => null, // % điểm danh tuần này
    ];

    /** @var array Danh sách việc cần làm */
    public $todos = [];

    /** @var array Điểm danh hôm nay theo lớp */
    public $todayAttendance = [];

    /** @var array Học sinh theo khối (cho Cha xứ) */
    public $studentsByGrade = [];

    /** @var array Thống kê giới tính */
    public $genderStats = ['male' => 0, 'female' => 0];

    // ==================== LIFECYCLE ====================

    public function mount(): void
    {
        parent::mount();
        $this->requireParishId();
    }

    protected function loadInitialData(): void
    {
        $this->loadDashboard();
    }

    // ==================== ACTIONS ====================

    /**
     * Refresh thủ công — xóa cache và load lại
     */
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
                $schoolYear = NamHoc::ofParish($parishId)
                    ->active()
                    ->orderByDesc('id')
                    ->first();

                if (!$schoolYear) {
                    return $this->emptyData();
                }

                return [
                    'schoolYear'     => $schoolYear,
                    'stats'          => $this->buildStats($schoolYear, $parishId),
                    'todos'          => $this->buildTodos($schoolYear, $parishId),
                    'studentsByGrade' => $this->buildStudentsByGrade($schoolYear, $parishId),
                    'genderStats'    => $this->buildGenderStats($schoolYear, $parishId),
                ];
            });

            $this->activeSchoolYear = $data['schoolYear'];
            $this->stats            = $data['stats'];
            $this->todos            = $data['todos'];
            $this->studentsByGrade  = $data['studentsByGrade'];
            $this->genderStats      = $data['genderStats'];

            // Điểm danh hôm nay KHÔNG cache (luôn cần mới nhất)
            if ($this->activeSchoolYear) {
                $this->todayAttendance = $this->buildTodayAttendance(
                    $this->activeSchoolYear,
                    $parishId
                );
            }
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading dashboard');
            session()->flash('error', 'Có lỗi khi tải dữ liệu. Vui lòng thử lại.');
        }
    }

    // ==================== BUILDERS ====================

    /**
     * Số liệu tổng quan
     */
    private function buildStats(NamHoc $schoolYear, int $parishId): array
    {
        $classCount = Lop::where('schoolyear', $schoolYear->id)
            ->where('pid', $parishId)
            ->where('status', 1)
            ->count();

        $studentCount = DB::table('students_class')
            ->join('lop', 'students_class.class_id', '=', 'lop.id')
            ->where('lop.schoolyear', $schoolYear->id)
            ->where('lop.pid', $parishId)
            ->where('students_class.status', 1)
            ->distinct('students_class.student_id')
            ->count('students_class.student_id');

        $teacherCount = DB::table('class_teachers')
            ->join('lop', 'class_teachers.class_id', '=', 'lop.id')
            ->where('lop.schoolyear', $schoolYear->id)
            ->where('lop.pid', $parishId)
            ->where('class_teachers.status', 1)
            ->distinct('class_teachers.teacher_id')
            ->count('class_teachers.teacher_id');

        // Tỷ lệ điểm danh tuần này (nếu có bảng attendance)
        $attendanceRate = $this->getWeeklyAttendanceRate($schoolYear, $parishId);

        return [
            'students'   => $studentCount,
            'classes'    => $classCount,
            'teachers'   => $teacherCount,
            'attendance' => $attendanceRate,
        ];
    }

    /**
     * Danh sách việc cần làm — phần quan trọng nhất
     * Mỗi todo có: type, message, count, route, priority
     */
    private function buildTodos(NamHoc $schoolYear, int $parishId): array
    {
        $todos = [];

        // 1. Lớp chưa có GLV
        $classesWithoutTeacher = Lop::where('schoolyear', $schoolYear->id)
            ->where('pid', $parishId)
            ->where('status', 1)
            ->whereDoesntHave('classTeachers', fn($q) => $q->where('status', 1))
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

        // 2. Lớp chưa đủ sĩ số (dưới 5 học sinh)
        $classesUnderstaffed = DB::table('lop')
            ->leftJoin('students_class', function ($join) {
                $join->on('lop.id', '=', 'students_class.class_id')
                    ->where('students_class.status', 1);
            })
            ->where('lop.schoolyear', $schoolYear->id)
            ->where('lop.pid', $parishId)
            ->where('lop.status', 1)
            ->select('lop.id', DB::raw('COUNT(students_class.student_id) as student_count'))
            ->groupBy('lop.id')
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

        // 4. Chưa có năm học nào active
        // (trường hợp này không xảy ra vì đã check schoolYear ở trên,
        //  nhưng giữ lại để dễ mở rộng sau)

        // Sắp xếp theo priority
        usort($todos, fn($a, $b) => $a['priority'] <=> $b['priority']);

        return $todos;
    }

    /**
     * Học sinh theo khối — cho Cha xứ xem tổng quan
     */
    private function buildStudentsByGrade(NamHoc $schoolYear, int $parishId): array
    {
        $rows = DB::table('lop')
            ->join('students_class', 'lop.id', '=', 'students_class.class_id')
            ->join('block', 'lop.block', '=', 'block.id')   // tên bảng đúng là 'block'
            ->where('lop.schoolyear', $schoolYear->id)
            ->where('lop.pid', $parishId)
            ->where('students_class.status', 1)
            ->select(
                'block.name as grade',
                'block.weight',
                DB::raw('COUNT(DISTINCT students_class.student_id) as count')
            )
            ->groupBy('block.id', 'block.name', 'block.weight')
            ->orderBy('block.weight')
            ->get();

        return $rows->map(fn($r) => [
            'grade' => $r->grade,
            'count' => (int) $r->count,
        ])->toArray();
    }

    /**
     * Thống kê giới tính
     */
    private function buildGenderStats(NamHoc $schoolYear, int $parishId): array
    {
        $data = DB::table('students_class')
            ->join('lop', 'students_class.class_id', '=', 'lop.id')
            ->join('student', 'students_class.student_id', '=', 'student.id')
            ->where('lop.schoolyear', $schoolYear->id)
            ->where('lop.pid', $parishId)
            ->where('students_class.status', 1)
            ->selectRaw('
                SUM(CASE WHEN student.sex = 1 THEN 1 ELSE 0 END) as male,
                SUM(CASE WHEN student.sex = 0 THEN 1 ELSE 0 END) as female
            ')
            ->first();

        return [
            'male'   => (int) ($data->male ?? 0),
            'female' => (int) ($data->female ?? 0),
        ];
    }

    /**
     * Điểm danh hôm nay — không cache, luôn fresh
     * Trả về danh sách lớp học hôm nay với trạng thái điểm danh
     *
     * NOTE: Cấu trúc này phụ thuộc vào bảng attendance của bạn.
     * Hiện tại trả về danh sách lớp active, có thể mở rộng sau.
     */
    private function buildTodayAttendance(NamHoc $schoolYear, int $parishId): array
    {
        // Lấy tất cả lớp active trong năm học
        // TODO: Lọc theo lịch học hôm nay nếu có bảng schedule
        $classes = Lop::with(['blockRelation', 'classTeachers'])
            ->where('schoolyear', $schoolYear->id)
            ->where('pid', $parishId)
            ->where('status', 1)
            ->withCount(['activeStudents as students_count'])
            ->orderBy('name')
            ->limit(10) // Giới hạn hiển thị trên dashboard
            ->get();

        return $classes->map(function (Lop $lop) {
            // TODO: Tích hợp với bảng attendance thực tế
            // Hiện tại trả về placeholder
            return [
                'id'            => $lop->id,
                'name'          => $lop->name,
                'block'         => $lop->blockRelation?->name ?? '',
                'students_count' => $lop->students_count ?? 0,
                'has_attendance' => false,   // TODO: check bảng attendance
                'attended'      => 0,       // TODO: count từ bảng attendance
                'url'           => route('classes.show', $lop->id),
            ];
        })->toArray();
    }

    /**
     * Tỷ lệ điểm danh tuần này
     * TODO: Tích hợp khi có bảng attendance
     */
    private function getWeeklyAttendanceRate(NamHoc $schoolYear, int $parishId): ?float
    {
        // Placeholder — tích hợp sau khi có bảng attendance
        return null;
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

    /**
     * Tổng số việc cần làm — dùng trong badge
     */
    public function getTodoCountProperty(): int
    {
        return count($this->todos);
    }

    /**
     * Tên ngày hôm nay tiếng Việt
     */
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

    /**
     * Thông tin học kỳ hiện tại
     */
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
        return view('livewire.home', [
            'todoCount'       => $this->todoCount,
            'todayLabel'      => $this->todayLabel,
            'semesterLabel'   => $this->currentSemesterLabel,
        ])
            ->extends('frontend.layout.main')
            ->section('content');
    }
}
