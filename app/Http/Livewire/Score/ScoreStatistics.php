<?php

namespace App\Http\Livewire\Score;

use App\Http\Livewire\Base\BaseComponent;
use App\Http\Livewire\Score\Concerns\ExportsScoreDistribution;
use App\Models\CatechismClass;
use App\Models\GradeLevel;
use App\Models\NamHoc;
use App\Services\ScoreDistributionReport;
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
    use ExportsScoreDistribution;

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
        $classIds = $this->resolveClassIds();

        if ($classIds->isEmpty()) {
            $this->totalStudents          = 0;
            $this->totalStudentsWithScore = 0;

            return [];
        }

        $report = app(ScoreDistributionReport::class)->summary(
            $classIds->all(),
            (int) $this->selectedSemester
        );

        $this->totalStudents          = $report['total_students'];
        $this->totalStudentsWithScore = $report['students_with_score'];

        return $report['averages'];
    }

    protected function resolveClassIds(): Collection
    {
        return $this->distributionClassIds();
    }

    /** Lớp đã chọn → lớp; chỉ khối → khối; còn lại → toàn xứ. */
    protected function resolveScope(): string
    {
        return $this->distributionScope();
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
        $this->ratingChartData = app(ScoreDistributionReport::class)
            ->ratingBreakdown($averages);
    }

    protected function buildDistributionChart(array $averages): void
    {
        $this->distributionChartData = app(ScoreDistributionReport::class)
            ->distribution($averages);
    }

    protected function buildSummary(array $averages): void
    {
        if (empty($averages)) {
            $this->summary = [];
            return;
        }

        $this->summary = app(ScoreDistributionReport::class)
            ->overview($averages, $this->totalStudents);
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

    // ==================== EXPORT ====================

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
