<?php

namespace App\Http\Livewire\Student;

use App\Http\Livewire\Base\BaseComponent;
use App\Models\CatechismClass;
use App\Models\NamHoc;
use App\Models\StudentNew;
use App\Models\StudentsClass;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Trang thống kê học sinh toàn xứ theo năm học
 *
 * - Donut: phân bố giới tính
 * - Bar: số học sinh đang học từng lớp
 */
class StudentStatistics extends BaseComponent
{
    public $selectedNamHoc = null;

    public $availableNamHocs;

    public array $statusChartData = [];
    public array $classChartData = [];
    public array $summary = [];

    protected function queryString(): array
    {
        return array_merge([
            'selectedNamHoc' => ['as' => 'namHoc', 'except' => null],
        ], parent::queryString());
    }

    protected $listeners = [
        'filterChanged' => 'handleFilterChanged',
        'refresh'       => 'handleRefresh',
    ];

    public function mount(): void
    {
        $this->authorize('viewAny', StudentNew::class);
        $this->availableNamHocs = collect();
        parent::mount();
    }

    protected function loadInitialData(): void
    {
        if ($this->assignmentBlocked) {
            $this->clearChartData();

            return;
        }

        $this->loadNamHocs();

        if (!$this->selectedNamHoc) {
            $this->selectedNamHoc = $this->getDefaultNamHocId();
        }

        $this->reloadChartData();
    }

    protected function sanitizeQueryString(): void
    {
        parent::sanitizeQueryString();
        $this->selectedNamHoc = $this->toInt($this->selectedNamHoc);
    }

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

    public function reloadChartData(): void
    {
        if ($this->assignmentBlocked || !$this->selectedNamHoc) {
            $this->clearChartData();
            return;
        }

        try {
            $classIds = $this->resolveClassIds();

            if ($classIds->isEmpty()) {
                $this->clearChartData();
                return;
            }

            $students = $this->fetchActiveStudents($classIds);

            $this->buildClassChart($classIds);

            if ($students->isNotEmpty()) {
                $this->buildStatusChart($students);
            } else {
                $this->statusChartData = [];
            }

            $this->buildSummary($students, $classIds);
        } catch (\Exception $e) {
            $this->logError($e, 'Error building student chart data');
            $this->clearChartData();
        }
    }

    /** Tất cả lớp active của xứ trong năm học đã chọn. */
    protected function resolveClassIds(): Collection
    {
        if (!$this->selectedNamHoc) {
            return collect();
        }

        return CatechismClass::where('school_year_id', $this->selectedNamHoc)
            ->where('parish_id', $this->parishId)
            ->active()
            ->pluck('id');
    }

    protected function fetchActiveStudents(Collection $classIds): Collection
    {
        return DB::table('students_class as sc')
            ->join('students as s', 's.id', '=', 'sc.student_id')
            ->whereIn('sc.class_id', $classIds)
            ->where('sc.status', StudentsClass::STATUS_ENROLLED)
            ->where('s.parish_id', $this->parishId)
            ->select('s.id', 's.gender')
            ->distinct()
            ->get();
    }

    protected function buildStatusChart(Collection $students): void
    {
        $male   = $students->where('gender', 'male')->count();
        $female = $students->where('gender', 'female')->count();
        $other  = $students->count() - $male - $female;

        $total = $students->count();
        $pct   = fn(int $n) => $total > 0 ? round($n / $total * 100, 1) : 0;

        $this->statusChartData = [
            ['label' => 'Nam',           'count' => $male,   'color' => '#3b82f6', 'percentage' => $pct($male)],
            ['label' => 'Nữ',            'count' => $female, 'color' => '#ec4899', 'percentage' => $pct($female)],
            ['label' => 'Chưa xác định', 'count' => $other,  'color' => '#cbd5e1', 'percentage' => $pct($other)],
        ];
    }

    protected function buildClassChart(Collection $classIds): void
    {
        $countsByClass = DB::table('students_class as sc')
            ->join('students as s', 's.id', '=', 'sc.student_id')
            ->whereIn('sc.class_id', $classIds)
            ->where('sc.status', StudentsClass::STATUS_ENROLLED)
            ->where('s.parish_id', $this->parishId)
            ->selectRaw('sc.class_id, COUNT(DISTINCT s.id) as student_count')
            ->groupBy('sc.class_id')
            ->pluck('student_count', 'class_id');

        $this->classChartData = CatechismClass::whereIn('id', $classIds)
            ->orderBy('grade_level_id')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn($class) => [
                'label' => $class->name,
                'count' => (int) ($countsByClass[$class->id] ?? 0),
            ])
            ->values()
            ->all();
    }

    protected function buildSummary(Collection $students, Collection $classIds): void
    {
        $totalStudents = $students->count();
        $maleCount     = $students->where('gender', 'male')->count();
        $maleRate      = $totalStudents > 0
            ? round($maleCount / $totalStudents * 100, 1)
            : 0;

        $classCounts = collect($this->classChartData);

        $this->summary = [
            'total_students' => $totalStudents,
            'male_rate'      => $maleRate,
            'male_count'     => $maleCount,
            'female_count'   => $students->where('gender', 'female')->count(),
            'classes_count'  => $classIds->count(),
            'avg_per_class'  => $classCounts->count() > 0
                ? round($classCounts->sum('count') / $classCounts->count(), 1)
                : 0,
            'max_class'      => $classCounts->sortByDesc('count')->first(),
            'min_class'      => $classCounts->sortBy('count')->first(),
        ];
    }

    protected function clearChartData(): void
    {
        $this->statusChartData = [];
        $this->classChartData  = [];
        $this->summary         = [];
    }

    public function updatedSelectedNamHoc(): void
    {
        $this->selectedNamHoc = $this->toInt($this->selectedNamHoc);
        $this->reloadChartData();
    }

    public function handleFilterChanged(array $filters): void
    {
        if (!is_array($filters) || !array_key_exists('namHoc', $filters)) {
            return;
        }

        $this->selectedNamHoc = $this->toInt($filters['namHoc']);
        $this->reloadChartData();
    }

    private function toInt($value): ?int
    {
        if ($value === '' || $value === null) {
            return null;
        }

        return is_numeric($value) ? (int) $value : null;
    }

    protected function getDefaultNamHocId(): ?int
    {
        return app(\App\Services\SchoolYearResolver::class)
            ->resolveId($this->parishId ? (int) $this->parishId : null);
    }

    public function render()
    {
        return view('livewire.student.student-statistics', [
            'parishId' => $this->parishId,
        ])
            ->extends('frontend.layout.main')
            ->section('content');
    }
}
