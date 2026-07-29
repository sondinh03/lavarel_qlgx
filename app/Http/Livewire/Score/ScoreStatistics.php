<?php

namespace App\Http\Livewire\Score;

use App\Http\Livewire\Base\BaseComponent;
use App\Models\CatechismClass;
use App\Models\GradeLevel;
use App\Models\NamHoc;
use App\Services\SemesterScoreCalculator;
use Illuminate\Support\Collection;

/**
 * Component trang thống kê điểm
 *
 * Features:
 * - Thống kê xếp loại: biểu đồ tròn (donut)
 * - Phân phối điểm TB: biểu đồ cột (histogram)
 * - So sánh TB giữa các lớp/khối: biểu đồ cột ngang
 * - Phạm vi tự động theo filter: lớp → khối → toàn xứ
 *
 * TB lấy từ SemesterScoreCalculator để khớp đúng bảng điểm và file Excel.
 */
class ScoreStatistics extends BaseComponent
{
    // ==================== RATING LEVELS ====================

    private const RATING_LEVELS = [
        'XUAT_SAC'   => ['min' => 9.5, 'max' => 10,   'label' => 'Xuất sắc',   'color' => '#10b981'],
        'GIOI'       => ['min' => 8.0, 'max' => 9.5,  'label' => 'Giỏi',       'color' => '#3b82f6'],
        'KHA'        => ['min' => 6.5, 'max' => 8.0,  'label' => 'Khá',        'color' => '#f59e0b'],
        'TRUNG_BINH' => ['min' => 5.0, 'max' => 6.5,  'label' => 'Trung bình', 'color' => '#eab308'],
        'YEU'        => ['min' => 3.5, 'max' => 5.0,  'label' => 'Yếu',        'color' => '#f97316'],
        'KEM'        => ['min' => 0,   'max' => 3.5,  'label' => 'Kém',        'color' => '#ef4444'],
    ];

    // ==================== FILTERS ====================

    public $selectedNamHoc   = null;
    public $selectedKhoi     = null;
    public $selectedLop      = null;
    /** 0 = cả năm, 1|2 = theo kỳ */
    public $selectedSemester = 1;

    // ==================== DATA ====================

    public $availableNamHocs;
    public $availableGrades;
    public $availableLops;

    /** Dữ liệu biểu đồ tròn xếp loại */
    public array $ratingChartData = [];

    /** Dữ liệu biểu đồ cột phân phối điểm */
    public array $distributionChartData = [];

    /** Dữ liệu biểu đồ so sánh TB các lớp */
    public array $classComparisonData = [];

    /** Tóm tắt thống kê tổng quan */
    public array $summary = [];

    /** Số học sinh đã có điểm / tổng */
    public int $totalStudentsWithScore = 0;
    public int $totalStudents          = 0;

    // ==================== QUERY STRING ====================

    protected function queryString(): array
    {
        return array_merge([
            'selectedNamHoc'   => ['as' => 'namHoc',   'except' => null],
            'selectedKhoi'     => ['as' => 'khoi',     'except' => null],
            'selectedLop'      => ['as' => 'lop',      'except' => null],
            'selectedSemester' => ['as' => 'semester', 'except' => 1],
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

        $this->reloadChartData();
    }

    protected function sanitizeQueryString(): void
    {
        parent::sanitizeQueryString();

        $this->selectedNamHoc = $this->toInt($this->selectedNamHoc);
        $this->selectedKhoi   = $this->toInt($this->selectedKhoi);
        $this->selectedLop    = $this->toInt($this->selectedLop);

        $sem = (int) $this->selectedSemester;
        $this->selectedSemester = in_array($sem, [0, 1, 2], true) ? $sem : 1;
    }

    // ==================== DATA LOADING ====================

    protected function loadNamHocs(): void
    {
        try {
            $this->availableNamHocs = NamHoc::query()
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
            $query = CatechismClass::with('gradeLevel')
                ->where('school_year_id', $this->selectedNamHoc)
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

    /**
     * Reload toàn bộ dữ liệu biểu đồ theo scope hiện tại
     */
    public function reloadChartData(): void
    {
        if (!$this->selectedNamHoc) {
            $this->clearChartData();
            return;
        }

        try {
            $averages = $this->fetchAverages();

            if (empty($averages)) {
                $this->clearChartData();
                return;
            }

            $this->buildRatingChart($averages);
            $this->buildDistributionChart($averages);
            $this->buildSummary($averages);
            $this->buildClassComparison($averages);
        } catch (\Exception $e) {
            $this->logError($e, 'Error building chart data');
            $this->clearChartData();
        }
    }

    /**
     * Lấy danh sách điểm TB theo scope
     * Returns: [ ['avg' => float, 'class_name' => string, 'student_class_id' => int], ... ]
     */
    protected function fetchAverages(): array
    {
        $this->totalStudents          = 0;
        $this->totalStudentsWithScore = 0;

        $classIds = $this->resolveClassIds();

        if ($classIds->isEmpty()) {
            return [];
        }

        $calculator = app(SemesterScoreCalculator::class);

        $byClass = $this->isFullYear()
            ? $calculator->forClassesYear($classIds->all())
            : $calculator->forClassesSemester($classIds->all(), (int) $this->selectedSemester);

        $averages = [];

        foreach ($byClass as $classId => $students) {
            foreach ($students as $pivotId => $row) {
                $this->totalStudents++;

                $avg = $this->isFullYear() ? $row['year'] : $row['total'];

                if ($avg === null) {
                    continue;
                }

                $averages[] = [
                    'avg'              => (float) $avg,
                    'class_id'         => (int) $classId,
                    'student_class_id' => (int) $pivotId,
                ];
                $this->totalStudentsWithScore++;
            }
        }

        return $averages;
    }

    protected function resolveClassIds(): Collection
    {
        if (!$this->selectedNamHoc) {
            return collect();
        }

        $query = CatechismClass::where('school_year_id', $this->selectedNamHoc)
            ->where('parish_id', $this->parishId)
            ->active();

        return match ($this->resolveScope()) {
            'class'  => $this->selectedLop
                ? collect([$this->selectedLop])
                : collect(),
            'grade'  => $this->selectedKhoi
                ? $query->where('grade_level_id', $this->selectedKhoi)->pluck('id')
                : collect(),
            'parish' => $query->pluck('id'),
            default  => collect(),
        };
    }

    /** Lớp đã chọn → lớp; chỉ khối → khối; còn lại → toàn xứ. */
    protected function resolveScope(): string
    {
        if ($this->selectedLop) {
            return 'class';
        }

        if ($this->selectedKhoi) {
            return 'grade';
        }

        return 'parish';
    }

    protected function isFullYear(): bool
    {
        return (int) $this->selectedSemester === 0;
    }

    protected function getSemesterLabel(): string
    {
        return $this->isFullYear() ? 'cả năm' : ('kỳ ' . $this->selectedSemester);
    }

    protected function getScopeLabel(): string
    {
        return match ($this->resolveScope()) {
            'class'  => 'theo lớp',
            'grade'  => 'theo khối',
            default  => 'toàn xứ',
        };
    }

    protected function buildRatingChart(array $averages): void
    {
        $counts = [];
        foreach (self::RATING_LEVELS as $key => $info) {
            $counts[$key] = 0;
        }

        foreach ($averages as $row) {
            $rating = $this->getRatingKey($row['avg']);
            if ($rating) {
                $counts[$rating]++;
            }
        }

        $total = array_sum($counts);
        $data  = [];

        foreach (self::RATING_LEVELS as $key => $info) {
            $count = $counts[$key];
            $data[] = [
                'key'        => $key,
                'label'      => $info['label'],
                'color'      => $info['color'],
                'count'      => $count,
                'percentage' => $total > 0 ? round(($count / $total) * 100, 1) : 0,
            ];
        }

        $this->ratingChartData = $data;
    }

    protected function buildDistributionChart(array $averages): void
    {
        // Chia thành 10 khoảng: 0-1, 1-2, ..., 9-10
        $buckets = array_fill(0, 10, 0);

        foreach ($averages as $row) {
            $avg    = min(9.99, max(0, $row['avg']));
            $bucket = (int) floor($avg);
            $buckets[$bucket]++;
        }

        $data = [];
        for ($i = 0; $i < 10; $i++) {
            $data[] = [
                'label' => $i . '-' . ($i + 1),
                'count' => $buckets[$i],
                'color' => $this->getColorForRange($i),
            ];
        }

        $this->distributionChartData = $data;
    }

    protected function buildSummary(array $averages): void
    {
        if (empty($averages)) {
            $this->summary = [];
            return;
        }

        $avgs  = array_column($averages, 'avg');
        $count = count($avgs);

        $this->summary = [
            'avg'    => round(array_sum($avgs) / $count, 2),
            'max'    => round(max($avgs), 2),
            'min'    => round(min($avgs), 2),
            'count'  => $count,
            'pass'   => count(array_filter($avgs, fn($v) => $v >= 5.0)),
        ];
    }

    /**
     * Tính TB từng lớp để so sánh (scope = grade / parish)
     */
    protected function buildClassComparison(array $averages): void
    {
        if ($this->resolveScope() === 'class' || $averages === []) {
            $this->classComparisonData = [];
            return;
        }

        $grouped = collect($averages)->groupBy('class_id');

        $classes = CatechismClass::whereIn('id', $grouped->keys()->all())
            ->orderBy('grade_level_id')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->keyBy('id');

        $data = [];

        foreach ($grouped as $classId => $rows) {
            $class = $classes->get($classId);

            if (! $class) {
                continue;
            }

            $values = collect($rows)->pluck('avg');

            $data[] = [
                'class_name' => $class->name,
                'avg'        => round($values->avg(), 2),
                'count'      => $values->count(),
                'pass_rate'  => round(($values->filter(fn ($v) => $v >= 5)->count() / $values->count()) * 100, 1),
            ];
        }

        // Sort by avg desc
        usort($data, fn($a, $b) => $b['avg'] <=> $a['avg']);
        $this->classComparisonData = $data;
    }

    protected function clearChartData(): void
    {
        $this->ratingChartData        = [];
        $this->distributionChartData  = [];
        $this->classComparisonData    = [];
        $this->summary                = [];
        $this->totalStudents          = 0;
        $this->totalStudentsWithScore = 0;
    }

    // ==================== PROPERTY UPDATERS ====================

    public function updatedSelectedNamHoc(): void
    {
        $this->selectedNamHoc = $this->toInt($this->selectedNamHoc);
        $this->selectedKhoi   = null;
        $this->selectedLop    = null;
        $this->loadLops();
        $this->reloadChartData();
    }

    public function updatedSelectedKhoi(): void
    {
        $this->selectedKhoi = $this->toInt($this->selectedKhoi);
        $this->selectedLop  = null;
        $this->loadLops();
        $this->reloadChartData();
    }

    public function updatedSelectedLop(): void
    {
        $this->selectedLop = $this->toInt($this->selectedLop);
        $this->reloadChartData();
    }

    public function updatedSelectedSemester(): void
    {
        $sem = (int) $this->selectedSemester;
        $this->selectedSemester = in_array($sem, [0, 1, 2], true) ? $sem : 1;
        $this->reloadChartData();
    }

    // ==================== EVENT HANDLERS ====================

    public function handleFilterChanged(array $filters): void
    {
        if (!is_array($filters)) return;

        if (array_key_exists('namHoc', $filters)) {
            $new = $this->toInt($filters['namHoc']);
            if ($new !== $this->selectedNamHoc) {
                $this->selectedNamHoc = $new;
                $this->selectedKhoi   = null;
                $this->selectedLop    = null;
                $this->loadLops();
            }
        }

        if (array_key_exists('khoi', $filters)) {
            $new = $this->toInt($filters['khoi']);
            if ($new !== $this->selectedKhoi) {
                $this->selectedKhoi = $new;
                $this->selectedLop  = null;
            }
        }

        if (array_key_exists('lop', $filters)) {
            $this->selectedLop = $this->toInt($filters['lop']);
        }

        if (array_key_exists('ky', $filters)) {
            $ky = $filters['ky'];
            if ($ky !== '' && $ky !== null) {
                $sem = (int) $ky;
                $this->selectedSemester = in_array($sem, [0, 1, 2], true) ? $sem : 1;
            }
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
        return app(\App\Services\SchoolYearResolver::class)
            ->resolveId($this->parishId ? (int) $this->parishId : null);
    }

    private function getRatingKey(?float $avg): ?string
    {
        if ($avg === null) return null;

        foreach (self::RATING_LEVELS as $key => $info) {
            if ($avg >= $info['min'] && $avg < $info['max']) {
                return $key;
            }
        }
        // Trường hợp đặc biệt: avg = 10 rơi vào XUAT_SAC
        if ($avg >= 10) return 'XUAT_SAC';

        return null;
    }

    private function getColorForRange(int $bucket): string
    {
        return match (true) {
            $bucket >= 9  => '#10b981', // emerald
            $bucket >= 8  => '#3b82f6', // blue
            $bucket >= 6  => '#f59e0b', // amber
            $bucket >= 5  => '#eab308', // yellow
            $bucket >= 3  => '#f97316', // orange
            default       => '#ef4444', // red
        };
    }

    // ==================== RENDER ====================

    public function render()
    {
        return view('livewire.score.score-statistics', [
            'parishId'      => $this->parishId,
            'effectiveScope' => $this->resolveScope(),
            'scopeLabel'    => $this->getScopeLabel(),
            'semesterLabel' => $this->getSemesterLabel(),
        ])
            ->extends('frontend.layout.main')
            ->section('content');
    }
}
