<?php

namespace App\Http\Livewire\Score;

use App\Exports\ScoreExport;
use App\Http\Livewire\Base\BaseComponent;
use App\Http\Livewire\Score\Concerns\CalculatesSemesterScores;
use App\Http\Livewire\Score\Concerns\ListsClassStudents;
use App\Http\Livewire\Score\Concerns\ManagesGradingWeights;
use App\Http\Livewire\Score\Concerns\ManagesScoreEntry;
use App\Http\Livewire\Score\Concerns\ManagesScoreFilters;
use App\Http\Livewire\Score\Concerns\ManagesScoreTypes;
use App\Http\Livewire\Score\Concerns\RatesStudentScores;
use App\Models\CatechismClass;
use App\Models\ScoreType;
use App\Models\StudentScore;
use App\Services\CatechistAccess;
use App\Services\SchoolYearResolver;

/**
 * Component quản lý điểm học sinh
 *
 * Ba tab, mỗi tab nằm trong một trait riêng:
 * - Bảng điểm: ManagesScoreEntry + ListsClassStudents + CalculatesSemesterScores
 * - Cấu hình loại điểm: ManagesScoreTypes
 * - Cách tính điểm: ManagesGradingWeights
 *
 * Class này chỉ còn phần chung: quyền, bộ lọc ban đầu, chuyển tab, xuất Excel.
 */
class ScoreManager extends BaseComponent
{
    use CalculatesSemesterScores;
    use ListsClassStudents;
    use ManagesGradingWeights;
    use ManagesScoreEntry;
    use ManagesScoreFilters;
    use ManagesScoreTypes;
    use RatesStudentScores;

    // ==================== TABS ====================

    /** @var string Tab hiện tại: 'scores' | 'config' | 'weights' */
    public $activeTab = 'scores';

    protected const TABS = ['scores', 'config', 'weights'];

    /** Tab chỉ dành cho ban giáo lý */
    protected const MANAGER_TABS = ['config', 'weights'];

    // ==================== PERMISSIONS ====================

    /** User hiện tại được phép sửa điểm (admin hoặc GLV có quyền hỗ trợ quản lý điểm) */
    public bool $canEditScores = false;

    /** User được xem điểm của lớp đang chọn */
    public bool $canViewScores = false;

    /** Admin: cấu hình loại điểm và cách tính điểm */
    public bool $canManageScoreConfig = false;

    /** Admin / elevated: xem mọi lớp; GLV thường: chỉ lớp phân công */
    public bool $canBrowseAllScoreClasses = false;

    // ==================== SORT ====================

    protected array $allowedSortFields = ['first_name', 'avg'];

    public string $sortField = 'first_name';

    // ==================== VALIDATION RULES ====================

    protected $rules = [
        'selectedNamHoc'   => 'nullable|integer',
        'selectedLop'      => 'nullable|integer',
        'selectedSemester' => 'required|integer|in:1,2',
        'perPage'          => 'required|integer|in:10,15,25,50',
    ];

    protected $messages = [
        'typeName.required'      => 'Vui lòng nhập tên loại điểm',
        'scoreTypeType.required' => 'Vui lòng chọn loại điểm',
        'scoreTypeType.in'       => 'Loại điểm không hợp lệ',
        'typeCoefficient.min'    => 'Hệ số phải lớn hơn 0',
        'typeMaxScore.min'       => 'Điểm tối đa tối thiểu là 1',
    ];

    // ==================== QUERY STRING ====================

    protected function queryString(): array
    {
        return array_merge([
            'selectedNamHoc'   => ['as' => 'namHoc',   'except' => null],
            'selectedKhoi'     => ['as' => 'khoi',     'except' => null],
            'selectedLop'      => ['as' => 'lop',      'except' => null],
            'selectedSemester' => ['as' => 'semester', 'except' => 1],
            'activeTab'        => ['as' => 'tab',      'except' => 'scores'],
            'filterByRating'   => ['as' => 'rating',   'except' => null],
            'sortField'        => ['except' => 'first_name', 'as' => 'sort'],
            'sortDirection'    => ['except' => 'asc',      'as' => 'dir'],
        ], array_diff_key(parent::queryString(), [
            'sortField'     => true,
            'sortDirection' => true,
        ]));
    }

    // ==================== LISTENERS ====================

    protected $listeners = [
        'refresh'       => 'handleRefresh',
        'filterChanged' => 'handleFilterChanged',
    ];

    // ==================== LIFECYCLE ====================

    public function mount(): void
    {
        $this->availableNamHocs = collect();
        $this->availableGrades  = collect();
        $this->availableLops    = collect();
        $this->scoreTypes       = collect();

        // Không authorize ở đây — guest cũng xem được (phụ huynh tra cứu)
        parent::mount();

        if (in_array($this->activeTab, self::MANAGER_TABS, true)
            && ! auth()->user()?->canManageCatechism()) {
            $this->activeTab = 'scores';
        }
    }

    protected function loadInitialData(): void
    {
        $this->refreshScorePermissions();
        $this->loadNamHocs();
        $this->loadGrades();

        if (! $this->selectedNamHoc) {
            $this->selectedNamHoc = $this->getDefaultNamHocId();
        }

        if (! $this->selectedLop) {
            $this->selectedLop = $this->defaultClassId ?? $this->firstBrowsableClassId();
        }

        $this->assertSelectedScoreClassAllowed();

        if ($this->selectedNamHoc) {
            $this->loadLops();
        }

        if ($this->selectedLop) {
            $this->loadScoreTypes();
            $this->refreshScorePermissions();
        }

        if ($this->canManageScoreConfig) {
            $this->loadGradingSettingsForm();
        }
    }

    /** Ban giáo lý mở trang lần đầu: mặc định vào lớp đầu tiên của năm học. */
    protected function firstBrowsableClassId(): ?int
    {
        if (! $this->canBrowseAllScoreClasses) {
            return null;
        }

        return CatechismClass::where('school_year_id', $this->selectedNamHoc)
            ->when($this->parishId, fn ($q) => $q->where('parish_id', $this->parishId))
            ->orderBy('id')
            ->value('id');
    }

    protected function sanitizeQueryString(): void
    {
        parent::sanitizeQueryString();

        $this->selectedNamHoc = $this->toInt($this->selectedNamHoc);
        $this->selectedKhoi   = $this->toInt($this->selectedKhoi);
        $this->selectedLop    = $this->toInt($this->selectedLop);

        $this->selectedSemester = $this->normalizeSemester($this->selectedSemester);

        if (! in_array($this->activeTab, self::TABS, true)) {
            $this->activeTab = 'scores';
        }

        if (! in_array($this->sortField, $this->allowedSortFields, true)) {
            $this->sortField = 'first_name';
        }

        if (! in_array($this->sortDirection, ['asc', 'desc'], true)) {
            $this->sortDirection = 'asc';
        }
    }

    public function hydrate(): void
    {
        $this->refreshScorePermissions();
    }

    protected function refreshScorePermissions(): void
    {
        $user = auth()->user();
        $access = app(CatechistAccess::class);

        $this->canManageScoreConfig = (bool) $user?->canManageCatechism();
        $this->canBrowseAllScoreClasses = (bool) ($user && (
            $user->canManageCatechism() || $access->canManageParishScores($user)
        ));

        $this->canViewScores = false;
        $this->canEditScores = false;

        if ($user && $this->selectedLop) {
            $class = CatechismClass::query()->find($this->selectedLop);
            if ($class) {
                $this->canViewScores = $user->can('viewScoresForClass', $class);
                $this->canEditScores = $user->can('enterScoresForClass', $class);
            }
        } elseif ($user) {
            // Chưa chọn lớp: chỉ biết khả năng nhập tổng quát (admin / GLV có quyền hỗ trợ).
            $this->canViewScores = $user->canManageCatechism()
                || $user->isCatechist();
            $this->canEditScores = $user->can('enterScores', StudentScore::class);
        }
    }

    // ==================== TABS ====================

    public function switchTab(string $tab): void
    {
        if (! in_array($tab, self::TABS, true)) {
            return;
        }

        if (in_array($tab, self::MANAGER_TABS, true) && ! $this->canManageScoreConfig) {
            $this->emit('toast', 'error', 'Bạn không có quyền cấu hình cách tính điểm');

            return;
        }

        if ($tab === 'weights') {
            $this->loadGradingSettingsForm();
        }

        $this->activeTab = $tab;
    }

    // ==================== EXPORT ====================

    public function exportScores()
    {
        $this->authorize('create', ScoreType::class);

        if (! $this->selectedLop) {
            $this->emit('toast', 'warning', 'Vui lòng chọn lớp trước khi xuất file');
            return;
        }

        $hasScoreTypes = ScoreType::query()
            ->where('class_id', (int) $this->selectedLop)
            ->where('is_active', true)
            ->exists();

        if (! $hasScoreTypes) {
            $this->emit('toast', 'warning', 'Lớp chưa có cấu hình loại điểm');
            return;
        }

        $className = CatechismClass::findOrFail($this->selectedLop)->name;

        return response()->streamDownload(function () {
            echo \Maatwebsite\Excel\Facades\Excel::raw(
                new ScoreExport($this->selectedLop, $this->filterByRating),
                \Maatwebsite\Excel\Excel::XLSX
            );
        }, 'BangDiemCaNam_' . $className . '_' . now()->format('dmY_His') . '.xlsx');
    }

    // ==================== HELPERS ====================

    protected function toInt($value): ?int
    {
        if ($value === '' || $value === null) {
            return null;
        }

        return is_numeric($value) ? (int) $value : null;
    }

    protected function getDefaultNamHocId(): ?int
    {
        return app(SchoolYearResolver::class)
            ->resolveId($this->parishId ? (int) $this->parishId : null);
    }

    // ==================== RENDER ====================

    public function render()
    {
        $students = $this->activeTab === 'scores'
            ? $this->getStudentsPaginated()
            : $this->emptyPaginator();

        $user = auth()->user();

        return view('livewire.score.score-manager', [
            'students'                   => $students,
            'parishId'                   => $this->parishId,
            'viewingStudent'             => $this->resolveViewingStudent($students),
            'scoreFilterAllowedClassIds' => $this->scoreFilterAllowedClassIds(),
            'canBrowseAllScoreClasses'   => $this->canBrowseAllScoreClasses,
            'gradingSettings'            => $this->gradingSettings(),
            'columns'                    => $this->columnVisibility(),
            'activeScoreTypes'           => $this->activeScoreTypes(),
            'gradingPreview'             => $this->activeTab === 'weights'
                ? $this->buildGradingPreview()
                : null,
        ])
            ->extends($user && $user->usesCatechistLayout()
                ? 'frontend.layout.catechist'
                : 'frontend.layout.main')
            ->section('content');
    }

    /**
     * Lớp được phép chọn trong bộ lọc. Mảng rỗng = không hạn chế.
     *
     * @return array<int, int>
     */
    protected function scoreFilterAllowedClassIds(): array
    {
        $user = auth()->user();

        if (! $user || $this->canBrowseAllScoreClasses) {
            return [];
        }

        $ids = app(CatechistAccess::class)->assignedClassIds(
            $user,
            $this->parishId,
            $this->selectedNamHoc ? (int) $this->selectedNamHoc : null
        );

        // GLV không có lớp: sentinel [0] để dropdown lớp trống
        return $ids === [] ? [0] : $ids;
    }
}
