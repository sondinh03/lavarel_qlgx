<?php

namespace App\Http\Livewire\Score\Concerns;

use App\Models\CatechismClass;
use App\Models\GradeLevel;
use App\Models\NamHoc;
use App\Services\CatechistAccess;

/**
 * Bộ lọc năm học / khối / lớp / học kỳ của trang điểm.
 *
 * Đổi bộ lọc là đổi ngữ cảnh của cả trang, nên mỗi updater đều phải xoá điểm
 * đã nạp và TB đã tính của lớp cũ. Trait cần các thành viên sau từ component:
 * toInt(), resetPage(), loadScoreTypes(), forgetCalculatedScores(),
 * refreshGradingContext(), refreshScorePermissions(), logError().
 */
trait ManagesScoreFilters
{
    /** @var int|null Selected năm học ID */
    public $selectedNamHoc = null;

    /** @var int|null Selected khối ID */
    public $selectedKhoi = null;

    /** @var int|null Selected lớp ID */
    public $selectedLop = null;

    /** @var int Selected học kỳ (1 hoặc 2) */
    public $selectedSemester = 1;

    /** @var \Illuminate\Support\Collection Danh sách năm học */
    public $availableNamHocs;

    /** @var \Illuminate\Support\Collection Danh sách khối */
    public $availableGrades;

    /** @var \Illuminate\Support\Collection Danh sách lớp */
    public $availableLops;

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

            $user = auth()->user();
            if ($user && ! $this->canBrowseAllScoreClasses) {
                $ids = app(CatechistAccess::class)->assignedClassIds(
                    $user,
                    $this->parishId,
                    (int) $this->selectedNamHoc
                );
                $query->whereIn('id', $ids !== [] ? $ids : [0]);
            }

            $this->availableLops = $query->orderBy('name')->get(['id', 'name', 'grade_level_id']);
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading lops');
            $this->availableLops = collect();
        }
    }

    /** GLV chỉ được xem lớp mình phụ trách — lớp ngoài phạm vi bị đưa về lớp mặc định. */
    protected function assertSelectedScoreClassAllowed(): void
    {
        if (! $this->selectedLop) {
            return;
        }

        $user = auth()->user();
        if (! $user) {
            $this->selectedLop = null;
            return;
        }

        $class = CatechismClass::query()
            ->when($this->parishId, fn ($q) => $q->where('parish_id', $this->parishId))
            ->find($this->selectedLop);

        if (! $class || ! app(CatechistAccess::class)->canViewScoresForClass(
            $user,
            (int) $class->id,
            $this->parishId
        )) {
            $allowed = app(CatechistAccess::class)->assignedClassIds(
                $user,
                $this->parishId,
                $this->selectedNamHoc ? (int) $this->selectedNamHoc : null
            );
            $this->selectedLop = $this->defaultClassId
                ?? ($allowed[0] ?? null);
        }
    }

    public function updatedSelectedNamHoc(): void
    {
        $this->selectedNamHoc = $this->toInt($this->selectedNamHoc);
        $this->selectedKhoi   = null;
        $this->selectedLop    = null;
        $this->scoreTypes     = collect();
        $this->scoresMatrix   = [];
        $this->averages       = [];
        $this->resetPage();
        $this->loadLops();
        $this->refreshGradingContext();
    }

    public function updatedSelectedKhoi(): void
    {
        $this->selectedKhoi = $this->toInt($this->selectedKhoi);
        $this->selectedLop  = null;
        $this->scoreTypes   = collect();
        $this->scoresMatrix = [];
        $this->averages     = [];
        $this->resetPage();
        $this->loadLops();
        $this->refreshGradingContext();
    }

    public function updatedSelectedLop(): void
    {
        if ($this->hasDraft) {
            // Revert về giá trị cũ, báo user confirm
            $this->emit('confirmDiscardDraft', [
                'action' => 'changeLop',
                'value'  => $this->selectedLop
            ]);
            return;
        }

        $this->selectedLop = $this->toInt($this->selectedLop);
        $this->assertSelectedScoreClassAllowed();
        $this->resetScoreContext();
        $this->loadScoreTypes();
        $this->refreshScorePermissions();
    }

    public function updatedSelectedSemester(): void
    {
        if ($this->hasDraft) {
            $this->emit('confirmDiscardDraft', [
                'action' => 'changeSemester',
                'value'  => $this->selectedSemester
            ]);
            return;
        }

        $this->selectedSemester = $this->normalizeSemester($this->selectedSemester);
        $this->resetScoreContext();
        $this->loadScoreTypes();
    }

    /**
     * Nhận bộ lọc từ component filter-bar dùng chung.
     */
    public function handleFilterChanged(array $filters): void
    {
        $namHocChanged = false;

        if (array_key_exists('namHoc', $filters)) {
            $new = $this->toInt($filters['namHoc']);
            if ($new !== $this->selectedNamHoc) {
                $this->selectedNamHoc = $new;
                $this->selectedKhoi   = null;
                $this->selectedLop    = null;
                $namHocChanged        = true;
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
            $this->selectedSemester = $this->normalizeSemester($filters['ky']);
        }

        $this->scoreTypes = collect();
        $this->resetScoreContext();
        $this->refreshGradingContext();

        if ($namHocChanged || array_key_exists('khoi', $filters)) {
            $this->loadLops();
        }

        if ($this->selectedLop) {
            $this->loadScoreTypes();
        }
    }

    protected function normalizeSemester($value): int
    {
        $semester = (int) $value;

        return in_array($semester, [1, 2], true) ? $semester : 1;
    }
}
