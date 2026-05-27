<?php

namespace App\Http\Livewire\Attendance;

use App\Http\Livewire\Base\BaseComponent;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\CatechismClass;
use App\Models\GradeLevel;
use App\Models\NamHoc;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

/**
 * Component trang thống kê điểm danh
 *
 * Features:
 * - Donut chart: phân bố trạng thái (có mặt / vắng phép / không phép / chưa điểm danh)
 * - Line chart: tỷ lệ có mặt theo từng buổi
 * - Summary cards: tổng quan toàn lớp/khối/xứ
 * - Phạm vi: lớp / khối / toàn xứ
 * - Hỗ trợ cả đi học (type=1) và đi lễ (type=2)
 */
class AttendanceStatistics extends BaseComponent
{
    // ==================== FILTERS ====================

    public $selectedNamHoc   = null;
    public $selectedKhoi     = null;
    public $selectedClassId  = null;
    public $selectedKy       = null;
    public int $attendanceType = 1; // 1 = đi học, 2 = đi lễ

    /** @var string Phạm vi: 'class' | 'grade' | 'parish' */
    public string $scope = 'class';

    // ==================== DATA ====================

    public $availableNamHocs;
    public $availableGrades;
    public $availableLops;

    /** Donut chart: phân bố trạng thái */
    public array $statusChartData = [];

    /** Line chart: tỷ lệ có mặt theo buổi */
    public array $trendChartData = [];

    /** Summary tổng quan */
    public array $summary = [];

    // ==================== QUERY STRING ====================

    protected function queryString(): array
    {
        return array_merge([
            'selectedNamHoc'  => ['as' => 'namHoc',  'except' => null],
            'selectedKhoi'    => ['as' => 'khoi',    'except' => null],
            'selectedClassId' => ['as' => 'classId', 'except' => null],
            'selectedKy'      => ['as' => 'ky',      'except' => null],
            'attendanceType'  => ['as' => 'type',    'except' => 1],
            'scope'           => ['as' => 'scope',   'except' => 'class'],
        ], parent::queryString());
    }

    // ==================== LISTENERS ====================

    protected $listeners = [
        'filterChanged' => 'handleFilterChanged',
        'refresh'       => 'handleRefresh',
    ];

    // ==================== LIFECYCLE ====================

    public function mount(): void
    {
        $this->availableNamHocs = collect();
        $this->availableGrades  = collect();
        $this->availableLops    = collect();

        parent::mount();
    }

    protected function loadInitialData(): void
    {
        $this->loadNamHocs();
        $this->loadGrades();

        if (!$this->selectedNamHoc) {
            $this->selectedNamHoc = $this->getDefaultNamHocId();
        }

        if ($this->selectedNamHoc) {
            $this->loadLops();
        }

        // Auto-detect scope từ filter ban đầu
        if (!$this->selectedClassId && !$this->selectedKhoi) {
            $this->scope = 'parish';
        } elseif (!$this->selectedClassId && $this->selectedKhoi) {
            $this->scope = 'grade';
        } else {
            $this->scope = 'class';
        }

        $this->reloadChartData();
    }

    protected function sanitizeQueryString(): void
    {
        parent::sanitizeQueryString();

        $this->selectedNamHoc  = $this->toInt($this->selectedNamHoc);
        $this->selectedKhoi    = $this->toInt($this->selectedKhoi);
        $this->selectedClassId = $this->toInt($this->selectedClassId);
        $this->selectedKy      = $this->toInt($this->selectedKy);

        $this->attendanceType = in_array((int) $this->attendanceType, [1, 2])
            ? (int) $this->attendanceType : 1;

        if (!in_array($this->scope, ['class', 'grade', 'parish'])) {
            $this->scope = 'parish';
        }
    }

    // ==================== DATA LOADING ====================

    protected function loadNamHocs(): void
    {
        try {
            $this->availableNamHocs = NamHoc::ofParish($this->parishId)
                ->active()
                ->orderByDesc('start_date_one')
                ->get(['id', 'name']);
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading nam hocs');
            $this->availableNamHocs = collect();
        }
    }

    protected function loadGrades(): void
    {
        try {
            $this->availableGrades = GradeLevel::active()
                ->orderBy('sort_order')
                ->get(['id', 'name']);
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading grades');
            $this->availableGrades = collect();
        }
    }

    protected function loadLops(): void
    {
        if (!$this->selectedNamHoc) {
            $this->availableLops = collect();
            return;
        }

        try {
            $query = CatechismClass::where('school_year_id', $this->selectedNamHoc)
                ->where('parish_id', $this->parishId)
                ->active();

            if ($this->selectedKhoi) {
                $query->where('grade_level_id', $this->selectedKhoi);
            }

            $this->availableLops = $query->orderBy('name')->get(['id', 'name', 'grade_level_id']);
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading lops');
            $this->availableLops = collect();
        }
    }

    // ==================== CHART DATA ====================

    public function reloadChartData(): void
    {
        if (!$this->selectedNamHoc) {
            $this->clearChartData();
            return;
        }

        try {
            $classIds = $this->resolveClassIds();

            if ($classIds->isEmpty()) {
                $this->clearChartData();
                return;
            }

            $sessions = $this->fetchSessions($classIds);

            if ($sessions->isEmpty()) {
                $this->clearChartData();
                return;
            }

            $records = $this->fetchRecords($sessions->pluck('id'));

            $this->buildStatusChart($records, $sessions, $classIds);
            $this->buildTrendChart($records, $sessions, $classIds);
            $this->buildSummary($records, $sessions, $classIds);
        } catch (\Exception $e) {
            $this->logError($e, 'Error building attendance chart data');
            $this->clearChartData();
        }
    }

    protected function resolveClassIds(): Collection
    {
        if (!$this->selectedNamHoc) return collect();

        $query = CatechismClass::where('school_year_id', $this->selectedNamHoc)
            ->where('parish_id', $this->parishId)
            ->active();

        return match ($this->scope) {
            'class'  => $this->selectedClassId ? collect([$this->selectedClassId]) : collect(),
            'grade'  => $this->selectedKhoi
                ? $query->where('grade_level_id', $this->selectedKhoi)->pluck('id')
                : collect(),
            'parish' => $query->pluck('id'),
            default  => collect(),
        };
    }

    protected function fetchSessions(Collection $classIds): Collection
    {
        $query = AttendanceSession::whereIn('class_id', $classIds)
            ->where('type', $this->attendanceType)
            ->orderBy('date');

        if ($this->selectedKy) {
            $query->where('semester', $this->selectedKy);
        }

        return $query->get(['id', 'date', 'class_id', 'status']);
    }

    protected function fetchRecords(Collection $sessionIds): Collection
    {
        return AttendanceRecord::whereIn('session_id', $sessionIds)
            ->select('session_id', 'student_id', 'status')
            ->get();
    }

    /**
     * Donut: tổng hợp trạng thái toàn bộ slot (học sinh × buổi)
     */
    protected function buildStatusChart(Collection $records, Collection $sessions, Collection $classIds): void
    {
        $totalStudents = DB::table('students_class')
            ->whereIn('class_id', $classIds)
            ->where('status', 1)
            ->count();

        $totalSlots = $totalStudents * $sessions->count();

        $present   = $records->where('status', AttendanceRecord::STATUS_PRESENT)->count();
        $excused   = $records->where('status', AttendanceRecord::STATUS_ABSENT_EXCUSED)->count();
        $unexcused = $records->where('status', AttendanceRecord::STATUS_ABSENT_UNEXCUSED)->count();
        $notMarked = max(0, $totalSlots - $present - $excused - $unexcused);

        $total = $present + $excused + $unexcused + $notMarked;

        $pct = fn(int $n) => $total > 0 ? round($n / $total * 100, 1) : 0;

        $this->statusChartData = [
            ['label' => 'Có mặt',          'count' => $present,   'color' => '#10b981', 'percentage' => $pct($present)],
            ['label' => 'Vắng có phép',     'count' => $excused,   'color' => '#f59e0b', 'percentage' => $pct($excused)],
            ['label' => 'Vắng không phép',  'count' => $unexcused, 'color' => '#ef4444', 'percentage' => $pct($unexcused)],
            ['label' => 'Chưa điểm danh',  'count' => $notMarked, 'color' => '#cbd5e1', 'percentage' => $pct($notMarked)],
        ];
    }

    /**
     * Line chart: tỷ lệ có mặt (%) theo từng buổi.
     * Khi scope > class: gộp các buổi cùng ngày → trung bình.
     */
    protected function buildTrendChart(Collection $records, Collection $sessions, Collection $classIds): void
    {
        $studentCountByClass = DB::table('students_class')
            ->whereIn('class_id', $classIds)
            ->where('status', 1)
            ->select('class_id', DB::raw('COUNT(*) as cnt'))
            ->groupBy('class_id')
            ->pluck('cnt', 'class_id');

        $recordsBySession = $records->groupBy('session_id');

        // Gộp theo ngày: nhiều lớp cùng ngày → lấy trung bình có trọng số
        $byDate = [];

        foreach ($sessions as $session) {
            $sessionRecords = $recordsBySession->get($session->id, collect());
            $presentCount   = $sessionRecords->where('status', AttendanceRecord::STATUS_PRESENT)->count();
            $totalStudents  = $studentCountByClass[$session->class_id] ?? 0;

            if ($totalStudents === 0) continue;

            $dateStr = Carbon::parse($session->date)->format('Y-m-d');
            $label   = Carbon::parse($session->date)->format('d/m');

            if (!isset($byDate[$dateStr])) {
                $byDate[$dateStr] = ['label' => $label, 'present' => 0, 'total' => 0];
            }

            $byDate[$dateStr]['present'] += $presentCount;
            $byDate[$dateStr]['total']   += $totalStudents;
        }

        ksort($byDate);

        $this->trendChartData = array_values(array_map(function ($data) {
            return [
                'label' => $data['label'],
                'rate'  => $data['total'] > 0
                    ? round($data['present'] / $data['total'] * 100, 1)
                    : 0,
            ];
        }, $byDate));
    }

    protected function buildSummary(Collection $records, Collection $sessions, Collection $classIds): void
    {
        $totalStudents = DB::table('students_class')
            ->whereIn('class_id', $classIds)
            ->where('status', 1)
            ->count();

        $totalSessions = $sessions->count();
        $totalSlots    = $totalStudents * $totalSessions;

        $present   = $records->where('status', AttendanceRecord::STATUS_PRESENT)->count();
        $excused   = $records->where('status', AttendanceRecord::STATUS_ABSENT_EXCUSED)->count();
        $unexcused = $records->where('status', AttendanceRecord::STATUS_ABSENT_UNEXCUSED)->count();

        $avgRate = !empty($this->trendChartData)
            ? round(collect($this->trendChartData)->avg('rate'), 1)
            : ($totalSlots > 0 ? round($present / $totalSlots * 100, 1) : 0);

        $minSession = !empty($this->trendChartData)
            ? collect($this->trendChartData)->sortBy('rate')->first()
            : null;

        $maxSession = !empty($this->trendChartData)
            ? collect($this->trendChartData)->sortByDesc('rate')->first()
            : null;

        $this->summary = [
            'total_students' => $totalStudents,
            'total_sessions' => $totalSessions,
            'total_slots'    => $totalSlots,
            'present'        => $present,
            'excused'        => $excused,
            'unexcused'      => $unexcused,
            'avg_rate'       => $avgRate,
            'min_session'    => $minSession,
            'max_session'    => $maxSession,
            'classes_count'  => $classIds->count(),
        ];
    }

    protected function clearChartData(): void
    {
        $this->statusChartData = [];
        $this->trendChartData  = [];
        $this->summary         = [];
    }

    // ==================== UPDATERS ====================

    public function updatedSelectedNamHoc(): void
    {
        $this->selectedNamHoc  = $this->toInt($this->selectedNamHoc);
        $this->selectedKhoi    = null;
        $this->selectedClassId = null;
        $this->loadLops();
        $this->reloadChartData();
    }

    public function updatedSelectedKhoi(): void
    {
        $this->selectedKhoi    = $this->toInt($this->selectedKhoi);
        $this->selectedClassId = null;
        $this->loadLops();
        $this->reloadChartData();
    }

    public function updatedSelectedClassId(): void
    {
        $this->selectedClassId = $this->toInt($this->selectedClassId);
        $this->reloadChartData();
    }

    public function updatedSelectedKy(): void   { $this->reloadChartData(); }
    public function updatedAttendanceType(): void { $this->reloadChartData(); }
    public function updatedScope(): void          { $this->reloadChartData(); }

    public function setScope(string $scope): void
    {
        if (!in_array($scope, ['class', 'grade', 'parish'])) return;
        $this->scope = $scope;
        $this->reloadChartData();
    }

    public function setType(int $type): void
    {
        if (!in_array($type, [1, 2])) return;
        $this->attendanceType = $type;
        $this->reloadChartData();
    }

    // ==================== EVENT HANDLERS ====================

    public function handleFilterChanged(array $filters): void
    {
        if (!is_array($filters)) return;

        if (array_key_exists('namHoc', $filters)) {
            $new = $this->toInt($filters['namHoc']);
            if ($new !== $this->selectedNamHoc) {
                $this->selectedNamHoc  = $new;
                $this->selectedKhoi    = null;
                $this->selectedClassId = null;
                $this->loadLops();
            }
        }

        if (array_key_exists('khoi', $filters)) {
            $new = $this->toInt($filters['khoi']);
            if ($new !== $this->selectedKhoi) {
                $this->selectedKhoi    = $new;
                $this->selectedClassId = null;
            }
        }

        if (array_key_exists('lop', $filters)) {
            $this->selectedClassId = $this->toInt($filters['lop']);
        }

        if (array_key_exists('ky', $filters)) {
            $this->selectedKy = $this->toInt($filters['ky']);
        }

        $this->reloadChartData();
    }

    // ==================== HELPERS ====================

    private function toInt($value): ?int
    {
        if ($value === '' || $value === null) return null;
        return is_numeric($value) ? (int) $value : null;
    }

    protected function getDefaultNamHocId(): ?int
    {
        return NamHoc::ofParish($this->parishId)
            ->active()
            ->orderByDesc('start_date_one')
            ->value('id');
    }

    // ==================== RENDER ====================

    public function render()
    {
        return view('livewire.attendance.attendance-statistics', [
            'parishId' => $this->parishId,
        ])
            ->extends('frontend.layout.main')
            ->section('content');
    }
}