<?php

namespace App\Http\Livewire\Score;

use App\Exports\ScoreExport;
use App\Http\Livewire\Base\BaseComponent;
use App\Models\CatechismClass;
use App\Models\GradeLevel;
use App\Models\NamHoc;
use App\Models\ParishNew;
use App\Models\ScoreEditLog;
use App\Models\ScoreType;
use App\Models\StudentScore;
use App\Models\StudentsClass;
use App\Services\CatechistAccess;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Component quản lý điểm học sinh
 *
 * Features:
 * - Bảng điểm: xem & nhập điểm từng học sinh
 * - Cấu hình loại điểm (thêm/sửa/xoá cột điểm)
 * - Tính điểm trung bình học kỳ tự động (weighted average)
 */
class ScoreManager extends BaseComponent
{
    // ==================== RATING LEVELS ====================

    private const RATING_LEVELS = [
        'XUAT_SAC'   => ['min' => 9.5, 'max' => 10,  'label' => 'Xuất sắc',   'color' => 'emerald'],
        'GIOI'       => ['min' => 8.0, 'max' => 9.5,  'label' => 'Giỏi',       'color' => 'blue'],
        'KHA'        => ['min' => 6.5, 'max' => 8.0,  'label' => 'Khá',        'color' => 'amber'],
        'TRUNG_BINH' => ['min' => 5.0, 'max' => 6.5,  'label' => 'Trung bình', 'color' => 'yellow'],
        'YEU'        => ['min' => 3.5, 'max' => 5.0,  'label' => 'Yếu',        'color' => 'orange'],
        'KEM'        => ['min' => 0,   'max' => 3.5,  'label' => 'Kém',        'color' => 'red'],
    ];

    // ==================== FILTERS ====================

    /** @var int|null Selected năm học ID */
    public $selectedNamHoc = null;

    /** @var int|null Selected khối ID */
    public $selectedKhoi = null;

    /** @var int|null Selected lớp ID */
    public $selectedLop = null;

    /** @var int Selected học kỳ (1 hoặc 2) */
    public $selectedSemester = 1;

    /** @var string|null Selected rating filter */
    public $filterByRating = null;

    // ==================== TABS ====================

    /** @var string Tab hiện tại: 'scores' | 'config' */
    public $activeTab = 'scores';

    /** Giáo xứ đang mở cửa sổ nhập/sửa điểm cho GLV */
    public bool $scoresEntryOpen = false;

    /** User hiện tại được phép sửa điểm (admin luôn; GLV elevated khi mở cửa sổ) */
    public bool $canEditScores = false;

    /** User được xem điểm của lớp đang chọn */
    public bool $canViewScores = false;

    /** Admin: cấu hình loại điểm + bật/tắt cửa sổ nhập điểm */
    public bool $canManageScoreConfig = false;

    /** Admin / elevated: xem mọi lớp; GLV thường: chỉ lớp phân công */
    public bool $canBrowseAllScoreClasses = false;

    /** GLV: đang xem chi tiết điểm của học sinh (students_class.id) */
    public ?int $viewingPivotId = null;

    // ==================== STATISTICS ====================

    /** @var array Thống kê học sinh theo xếp loại */
    public $ratingStats = [];

    // ==================== SCORE TYPE CONFIG FORM ====================

    /** @var bool Hiển thị modal cấu hình loại điểm */
    public $showScoreTypeForm = false;

    /** @var int|null ID ScoreType đang edit (null = create) */
    public $editingScoreTypeId = null;

    /** @var string Tên loại điểm */
    public $typeName = '';

    /** @var int|null Loại (1-5) */
    public $scoreTypeType = null;

    /** @var int Thứ tự hiển thị */
    public $typeOrder = 0;

    /** @var float Hệ số */
    public $typeCoefficient = 1.0;

    /** @var float Điểm tối đa */
    public $typeMaxScore = 10.0;

    /** @var bool Trạng thái */
    public $typeIsActive = true;

    // ==================== CREATE SCOPE (trong form tạo loại điểm) ====================

    /** @var string Phạm vi tạo mới: 'class' | 'grade' | 'parish' */
    public $createScope = 'class';

    /** @var int|null Khối khi createScope = 'grade' */
    public $createScopeGradeId = null;

    // ==================== DATA ====================

    /** @var Collection Danh sách năm học */
    public $availableNamHocs;

    /** @var Collection Danh sách khối */
    public $availableGrades;

    /** @var Collection Danh sách lớp */
    public $availableLops;

    /** @var Collection Loại điểm của lớp hiện tại */
    public $scoreTypes;

    /** @var array Ma trận điểm [student_class_id => [score_type_id => [...]] ] */
    public $scoresMatrix = [];

    /** @var array Điểm trung bình [student_class_id => float|null] */
    public $averages = [];

    /** @var bool Đánh dấu scoresMatrix đã được load trong request hiện tại */
    /** @var bool Đã load matrix điểm cho lớp/kỳ hiện tại (public để Livewire giữ qua request) */
    public bool $scoresLoaded = false;

    /** @var bool Enable pagination */
    protected $usePagination = true;

    /** @var array Draft điểm [student_class_id => [score_type_id => value]] */
    public $draftScores = [];

    /** @var bool Có thay đổi chưa lưu */
    public $hasDraft = false;

    protected array $allowedSortFields = ['first_name', 'avg'];

    public string $sortField = 'first_name';

    // ==================== VALIDATION RULES ====================

    protected $rules = [
        'selectedNamHoc'   => 'nullable|integer',
        'selectedLop'      => 'nullable|integer',
        'selectedSemester' => 'required|integer|in:1,2',
        'perPage'          => 'required|integer|in:10,15,25,50',
    ];

    protected $scoreRules = [
        'scoreValue' => 'required|numeric|min:0|max:10',
        'attempt'    => 'required|integer|min:1|max:9',
        'scoreNote'  => 'nullable|string|max:500',
    ];

    protected $scoreTypeRules = [
        'typeName'        => 'required|string|max:100',
        'scoreTypeType'   => 'required|integer|in:1,2,3,4,5',
        'typeOrder'       => 'required|integer|min:0|max:99',
        'typeCoefficient' => 'required|numeric|min:0.1|max:10',
        'typeMaxScore'    => 'required|numeric|min:1|max:100',
        'typeIsActive'    => 'required|boolean',
    ];

    protected $messages = [
        'scoreValue.required'      => 'Vui lòng nhập điểm',
        'scoreValue.max'           => 'Điểm không được quá 10',
        'scoreValue.min'           => 'Điểm không được âm',
        'attempt.min'              => 'Lần thi tối thiểu là 1',
        'typeName.required'        => 'Vui lòng nhập tên loại điểm',
        'scoreTypeType.required'   => 'Vui lòng chọn loại điểm',
        'scoreTypeType.in'         => 'Loại điểm không hợp lệ',
        'typeCoefficient.min'      => 'Hệ số phải lớn hơn 0',
        'typeMaxScore.min'         => 'Điểm tối đa tối thiểu là 1',
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

        if ($this->activeTab === 'config' && ! auth()->user()?->canManageCatechism()) {
            $this->activeTab = 'scores';
        }
    }

    protected function loadInitialData(): void
    {
        $this->refreshScorePermissions();
        $this->loadNamHocs();
        $this->loadGrades();

        if (!$this->selectedNamHoc) {
            $this->selectedNamHoc = $this->getDefaultNamHocId();
        }

        if (!$this->selectedLop) {
            $this->selectedLop = $this->defaultClassId
                ?? ($this->canBrowseAllScoreClasses
                    ? CatechismClass::where('school_year_id', $this->selectedNamHoc)
                        ->when($this->parishId, fn ($q) => $q->where('parish_id', $this->parishId))
                        ->orderBy('id')
                        ->value('id')
                    : null);
        }

        $this->assertSelectedScoreClassAllowed();

        if ($this->selectedNamHoc) {
            $this->loadLops();
        }

        if ($this->selectedLop) {
            $this->loadScoreTypes();
            $this->refreshScorePermissions();
        }
    }

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

    protected function sanitizeQueryString(): void
    {
        parent::sanitizeQueryString();

        $this->selectedNamHoc = $this->toInt($this->selectedNamHoc);
        $this->selectedKhoi   = $this->toInt($this->selectedKhoi);
        $this->selectedLop    = $this->toInt($this->selectedLop);

        $sem = (int) $this->selectedSemester;
        $this->selectedSemester = in_array($sem, [1, 2]) ? $sem : 1;

        if (!in_array($this->activeTab, ['scores', 'config'])) {
            $this->activeTab = 'scores';
        }

        if (!in_array($this->sortField, $this->allowedSortFields, true)) {
            $this->sortField = 'first_name';
        }

        if (!in_array($this->sortDirection, ['asc', 'desc'], true)) {
            $this->sortDirection = 'asc';
        }
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

    protected function loadScoreTypes(): void
    {
        if (!$this->selectedLop) {
            $this->scoreTypes = collect();
            return;
        }

        try {
            $this->scoreTypes = ScoreType::where('class_id', $this->selectedLop)
                ->where('semester', $this->selectedSemester)
                ->orderBy('order')
                ->orderBy('type')
                ->get();
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading score types');
            $this->scoreTypes = collect();
        }
    }

    // ==================== PROPERTY UPDATERS ====================

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

        $this->selectedLop  = $this->toInt($this->selectedLop);
        $this->assertSelectedScoreClassAllowed();
        $this->scoresMatrix = [];
        $this->averages     = [];
        $this->draftScores  = [];
        $this->hasDraft     = false;
        $this->scoresLoaded = false;
        $this->viewingPivotId = null;
        $this->resetPage();
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

        $sem = (int) $this->selectedSemester;
        $this->selectedSemester = in_array($sem, [1, 2]) ? $sem : 1;
        $this->scoresMatrix = [];
        $this->averages     = [];
        $this->draftScores  = [];
        $this->hasDraft     = false;
        $this->scoresLoaded = false;
        $this->viewingPivotId = null;
        $this->resetPage();
        $this->loadScoreTypes();
    }

    public function updatedFilterByRating(): void
    {
        $this->resetPage();
        $this->recalculateRatingStats();
    }

    public function confirmDiscard(string $action, $value): void
    {
        $this->draftScores = [];
        $this->hasDraft    = false;
        $this->viewingPivotId = null;

        match ($action) {
            'changeLop' => (function () use ($value) {
                $this->selectedLop  = $this->toInt($value);
                $this->scoresMatrix = [];
                $this->averages     = [];
                $this->resetPage();
                $this->loadScoreTypes();
            })(),

            'changeSemester' => (function () use ($value) {
                $sem = (int) $value;
                $this->selectedSemester = in_array($sem, [1, 2]) ? $sem : 1;
                $this->scoresMatrix = [];
                $this->averages     = [];
                $this->resetPage();
                $this->loadScoreTypes();
            })(),

            default => null,
        };
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

        if ($this->parishId) {
            $this->scoresEntryOpen = (bool) ParishNew::query()
                ->whereKey($this->parishId)
                ->value('scores_entry_open');
        } else {
            $this->scoresEntryOpen = false;
        }

        $this->canViewScores = false;
        $this->canEditScores = false;

        if ($user && $this->selectedLop) {
            $class = CatechismClass::query()->find($this->selectedLop);
            if ($class) {
                $this->canViewScores = $user->can('viewScoresForClass', $class);
                $this->canEditScores = $user->can('enterScoresForClass', $class);
            }
        } elseif ($user) {
            // Chưa chọn lớp: chỉ biết khả năng nhập tổng quát (admin / elevated + window)
            $this->canViewScores = $user->canManageCatechism()
                || $user->isCatechist();
            $this->canEditScores = $user->can('enterScores', StudentScore::class);
        }
    }

    public function openStudentScoreDetail(int $pivotId): void
    {
        $this->viewingPivotId = $pivotId;
    }

    public function closeStudentScoreDetail(): void
    {
        $this->viewingPivotId = null;
    }

    public function toggleScoresEntryOpen(): void
    {
        $this->authorize('create', ScoreType::class);

        if (! $this->parishId) {
            $this->emit('toast', 'error', 'Không xác định được giáo xứ');

            return;
        }

        $parish = ParishNew::query()->findOrFail($this->parishId);
        $parish->scores_entry_open = ! $parish->scores_entry_open;
        $parish->save();

        $this->refreshScorePermissions();

        $this->emit(
            'toast',
            'message',
            $this->scoresEntryOpen
                ? 'Đã mở nhập/sửa điểm cho giáo lý viên'
                : 'Đã khóa nhập/sửa điểm'
        );
    }

    public function updatedDraftScores(): void
    {
        if (! $this->canEditScores) {
            $this->hasDraft = false;

            return;
        }

        $this->hasDraft = $this->hasAnyDraftChange();
    }

    protected function hasAnyDraftChange(): bool
    {
        foreach ($this->draftScores as $studentClassId => $types) {
            foreach ($types as $scoreTypeId => $value) {
                $original = $this->scoresMatrix[$studentClassId][$scoreTypeId]['value'] ?? null;
                $draft    = ($value === '' || $value === null) ? null : (float) $value;

                if ($draft !== $original) {
                    return true;
                }
            }
        }
        return false;
    }

    public function saveAllScores(): void
    {
        $this->refreshScorePermissions();

        if (! $this->selectedLop) {
            $this->emit('toast', 'error', 'Vui lòng chọn lớp');
            return;
        }

        $class = CatechismClass::query()
            ->when($this->parishId, fn ($q) => $q->where('parish_id', $this->parishId))
            ->find($this->selectedLop);

        if (! $class) {
            $this->emit('toast', 'error', 'Lớp không hợp lệ');
            return;
        }

        if (! auth()->user()?->can('enterScoresForClass', $class) || ! $this->canEditScores) {
            $this->emit('toast', 'error', 'Hiện chưa mở cửa sổ nhập/sửa điểm hoặc bạn không có quyền');
            return;
        }

        // Luôn lấy lại từ DB — không tin scoreTypes đã serialize qua Livewire.
        $scoreTypes = ScoreType::query()
            ->where('class_id', (int) $this->selectedLop)
            ->get()
            ->keyBy('id');
        $this->scoreTypes = $scoreTypes->values();

        $allowedTypeIds = $scoreTypes->keys()->map(fn ($id) => (int) $id)->all();
        $allowedPivotIds = StudentsClass::query()
            ->where('class_id', (int) $this->selectedLop)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        // Validate
        foreach ($this->draftScores as $studentClassId => $types) {
            if (! in_array((int) $studentClassId, $allowedPivotIds, true)) {
                $this->emit('toast', 'error', 'Phát hiện dữ liệu điểm không thuộc lớp đang chọn');
                return;
            }

            foreach ($types as $scoreTypeId => $value) {
                if (! in_array((int) $scoreTypeId, $allowedTypeIds, true)) {
                    $this->emit('toast', 'error', 'Loại điểm không thuộc lớp đang chọn');
                    return;
                }

                if ($value === '' || $value === null) continue;

                if (!is_numeric($value)) {
                    $this->emit('toast', 'error', 'Điểm không hợp lệ');
                    return;
                }

                $scoreType = $scoreTypes->get((int) $scoreTypeId);
                $max       = $scoreType?->max_score ?? 10;
                $val       = (float) $value;

                if ($val < 0 || $val > $max) {
                    $this->emit(
                        'toast',
                        'error',
                        "Điểm {$scoreType?->name} phải từ 0 đến {$max}"
                    );
                    return;
                }
            }
        }

        try {
            DB::beginTransaction();

            $saved   = 0;
            $deleted = 0;
            $userId  = auth()->id();

            foreach ($this->draftScores as $studentClassId => $types) {
                foreach ($types as $scoreTypeId => $value) {
                    $hasOriginal = isset($this->scoresMatrix[$studentClassId][$scoreTypeId]);
                    if (($value === '' || $value === null) && !$hasOriginal) {
                        continue;
                    }

                    $original = $hasOriginal
                        ? (float) $this->scoresMatrix[$studentClassId][$scoreTypeId]['value']
                        : null;

                    $draft = ($value === '' || $value === null) ? null : (float) $value;

                    if ($draft === $original) continue;

                    if ($draft === null) {
                        $existing = StudentScore::query()
                            ->where('student_class_id', $studentClassId)
                            ->where('score_type_id', $scoreTypeId)
                            ->first();

                        if ($this->parishId) {
                            ScoreEditLog::create([
                                'parish_id'        => $this->parishId,
                                'student_class_id' => (int) $studentClassId,
                                'score_type_id'    => (int) $scoreTypeId,
                                'student_score_id' => $existing?->id,
                                'old_value'        => $original,
                                'new_value'        => null,
                                'action'           => ScoreEditLog::ACTION_DELETED,
                                'user_id'          => $userId,
                            ]);
                        }

                        $existing?->delete();

                        unset($this->scoresMatrix[$studentClassId][$scoreTypeId]);
                        $deleted++;
                    } else {
                        $action = $hasOriginal
                            ? ScoreEditLog::ACTION_UPDATED
                            : ScoreEditLog::ACTION_CREATED;

                        $score = StudentScore::updateOrCreate(
                            [
                                'student_class_id' => (int) $studentClassId,
                                'score_type_id'    => (int) $scoreTypeId,
                                'attempt'          => 1,
                            ],
                            ['score_value' => $draft]
                        );

                        if ($this->parishId) {
                            ScoreEditLog::create([
                                'parish_id'        => $this->parishId,
                                'student_class_id' => (int) $studentClassId,
                                'score_type_id'    => (int) $scoreTypeId,
                                'student_score_id' => $score->id,
                                'old_value'        => $original,
                                'new_value'        => $draft,
                                'action'           => $action,
                                'user_id'          => $userId,
                            ]);
                        }

                        $this->scoresMatrix[$studentClassId][$scoreTypeId] = [
                            'value'   => $draft,
                            'attempt' => 1,
                            'note'    => null,
                        ];
                        $saved++;
                    }

                    $this->recalculateAverage((int) $studentClassId);
                }
            }

            DB::commit();

            $this->scoresLoaded = true;
            $this->hasDraft     = false;
            $draft = [];
            foreach ($this->draftScores as $studentClassId => $types) {
                foreach (array_keys($types) as $scoreTypeId) {
                    $draft[$studentClassId][$scoreTypeId] =
                        $this->scoresMatrix[$studentClassId][$scoreTypeId]['value'] ?? '';
                }
            }
            $this->draftScores = $draft;

            $msg = "Đã lưu {$saved} điểm";
            if ($deleted > 0) $msg .= ", xóa {$deleted} điểm";
            $this->emit('toast', 'message', $msg);
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError($e, 'Error saving scores');
            $this->emit('toast', 'error', 'Có lỗi khi lưu điểm');
        }
    }

    // ==================== TABS ====================

    public function switchTab(string $tab): void
    {
        if (!in_array($tab, ['scores', 'config'])) {
            return;
        }

        if ($tab === 'config' && ! $this->canManageScoreConfig) {
            $this->emit('toast', 'error', 'Bạn không có quyền cấu hình loại điểm');

            return;
        }

        $this->activeTab = $tab;
    }

    // ==================== SCORE TYPE CONFIG ====================

    public function createScoreType(): void
    {
        $this->authorize('create', ScoreType::class);

        if (!$this->selectedNamHoc) {
            $this->emit('toast', 'warning', 'Vui lòng chọn năm học trước');
            return;
        }

        $this->resetScoreTypeForm();

        // Pre-fill scope dựa vào filter đang chọn
        if ($this->selectedLop) {
            $this->createScope        = 'class';
            $this->createScopeGradeId = $this->selectedKhoi;
        } elseif ($this->selectedKhoi) {
            $this->createScope        = 'grade';
            $this->createScopeGradeId = $this->selectedKhoi;
        } else {
            $this->createScope        = 'parish';
            $this->createScopeGradeId = null;
        }

        $this->showScoreTypeForm = true;
    }

    public function editScoreType(int $id): void
    {
        $this->authorize('update', ScoreType::class);

        try {
            $st = ScoreType::findOrFail($id);

            $this->editingScoreTypeId = $st->id;
            $this->typeName           = $st->name;
            $this->scoreTypeType      = $st->type;
            $this->typeOrder          = $st->order;
            $this->typeCoefficient    = $st->coefficient;
            $this->typeMaxScore       = $st->max_score;
            $this->typeIsActive       = $st->is_active;

            $this->resetValidation();
            $this->showScoreTypeForm = true;
        } catch (ModelNotFoundException $e) {
            $this->emit('toast', 'error', 'Không tìm thấy loại điểm này');
        }
    }

    public function saveScoreType(): void
    {
        $this->authorize('create', ScoreType::class);
        $this->validate($this->scoreTypeRules, $this->messages);

        // Edit mode → chỉ update record cụ thể, không cần scope
        if ($this->editingScoreTypeId) {
            $duplicate = ScoreType::where(
                'class_id',
                ScoreType::find($this->editingScoreTypeId)?->class_id
            )
                ->where('semester', $this->selectedSemester)
                ->where('type', $this->scoreTypeType)
                ->where('name', $this->typeName)
                ->where('id', '!=', $this->editingScoreTypeId)
                ->exists();

            if ($duplicate) {
                $this->addError('scoreTypeType', 'Loại điểm này đã tồn tại trong học kỳ');
                return;
            }

            try {
                ScoreType::where('id', $this->editingScoreTypeId)->update([
                    'type'        => $this->scoreTypeType,
                    'name'        => $this->typeName,
                    'order'       => $this->typeOrder,
                    'coefficient' => $this->typeCoefficient,
                    'max_score'   => $this->typeMaxScore,
                    'is_active'   => $this->typeIsActive,
                ]);

                $this->emit('toast', 'message', 'Cập nhật loại điểm thành công');
                $this->closeScoreTypeForm();
                $this->loadScoreTypes();
                return;
            } catch (\Exception $e) {
                $this->logError($e, 'Error updating score type');
                $this->emit('toast', 'error', 'Có lỗi khi cập nhật loại điểm');
                return;
            }
        }

        // Create mode → resolve danh sách lớp theo scope
        $classIds = $this->resolveCreateTargetClassIds();

        if ($classIds->isEmpty()) {
            $this->emit('toast', 'warning', 'Không tìm thấy lớp nào để áp dụng');
            return;
        }

        try {
            DB::beginTransaction();

            $created = 0;
            $skipped = 0;

            foreach ($classIds as $classId) {
                $duplicate = ScoreType::where('class_id', $classId)
                    ->where('semester', $this->selectedSemester)
                    ->where('type', $this->scoreTypeType)
                    ->where('name', $this->typeName)
                    ->exists();

                if ($duplicate) {
                    $skipped++;
                    continue;
                }

                ScoreType::create([
                    'class_id'    => $classId,
                    'semester'    => $this->selectedSemester,
                    'type'        => $this->scoreTypeType,
                    'name'        => $this->typeName,
                    'order'       => $this->typeOrder,
                    'coefficient' => $this->typeCoefficient,
                    'max_score'   => $this->typeMaxScore,
                    'is_active'   => $this->typeIsActive,
                ]);

                $created++;
            }

            DB::commit();

            $msg = "Đã tạo cho {$created} lớp";
            if ($skipped > 0) {
                $msg .= ", bỏ qua {$skipped} lớp đã tồn tại loại điểm này";
            }

            $this->emit('toast', 'message', $msg);
            $this->closeScoreTypeForm();
            $this->loadScoreTypes();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError($e, 'Error saving score type');
            $this->emit('toast', 'error', 'Có lỗi khi lưu loại điểm');
        }
    }

    /**
     * Resolve danh sách class_id cho create mode
     */
    protected function resolveCreateTargetClassIds(): Collection
    {
        if ($this->createScope === 'class' && $this->selectedLop) {
            return collect([$this->selectedLop]);
        }

        if ($this->createScope === 'grade' && !$this->createScopeGradeId) {
            $this->addError('createScopeGradeId', 'Vui lòng chọn khối');
            return collect();
        }

        $query = CatechismClass::where('school_year_id', $this->selectedNamHoc)
            ->where('parish_id', $this->parishId)
            ->active();

        return match ($this->createScope) {
            'grade'  => $query->where('grade_level_id', $this->createScopeGradeId)->pluck('id'),
            'parish' => $query->pluck('id'),
            default  => collect(),
        };
    }

    public function delete(int $id): void
    {
        $this->authorize('delete', ScoreType::class);

        try {
            DB::beginTransaction();

            $hasScores = StudentScore::where('score_type_id', $id)->exists();

            if ($hasScores) {
                $this->emit('toast', 'error', 'Không thể xoá loại điểm đã có dữ liệu điểm');
                return;
            }

            ScoreType::findOrFail($id)->delete();

            DB::commit();

            $this->emit('toast', 'message', 'Đã xoá loại điểm');
            $this->loadScoreTypes();
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            $this->emit('toast', 'error', 'Không tìm thấy loại điểm');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError($e, 'Error deleting score type');
            $this->emit('toast', 'error', 'Có lỗi khi xoá loại điểm');
        }
    }

    public function toggleScoreTypeStatus(int $id): void
    {
        $this->authorize('update', ScoreType::class);

        try {
            $st = ScoreType::findOrFail($id);
            $st->update(['is_active' => !$st->is_active]);

            $this->emit('toast', 'message', $st->is_active ? 'Đã kích hoạt' : 'Đã tắt loại điểm');
            $this->loadScoreTypes();
        } catch (\Exception $e) {
            $this->logError($e, 'Error toggling score type');
            $this->emit('toast', 'error', 'Có lỗi khi thay đổi trạng thái');
        }
    }

    public function closeScoreTypeForm(): void
    {
        $this->showScoreTypeForm = false;
        $this->resetScoreTypeForm();
        $this->resetValidation();
    }

    protected function resetScoreTypeForm(): void
    {
        $this->editingScoreTypeId = null;
        $this->typeName           = '';
        $this->scoreTypeType      = null;
        $this->typeOrder          = 0;
        $this->typeCoefficient    = 1.0;
        $this->typeMaxScore       = 10.0;
        $this->typeIsActive       = true;
        $this->createScope        = 'class';
        $this->createScopeGradeId = null;
    }

    // ==================== AVERAGE CALCULATION ====================

    /**
     * Tính điểm trung bình học kỳ cho 1 học sinh
     * Công thức: Σ(score × coefficient) / Σ(coefficient)
     */
    protected function recalculateAverage(int $studentClassId): void
    {
        if ($this->scoreTypes->isEmpty()) {
            $this->averages[$studentClassId] = null;
            return;
        }

        $totalWeight = 0.0;
        $totalScore  = 0.0;

        foreach ($this->scoreTypes as $st) {
            $score = $this->scoresMatrix[$studentClassId][$st->id]['value'] ?? null;

            if ($score === null) {
                // Nếu thiếu điểm cuối kỳ/giữa kỳ thì chưa tính được TB
                if (in_array($st->type, [ScoreType::TYPE_GIUA_KY, ScoreType::TYPE_CUOI_KY])) {
                    $this->averages[$studentClassId] = null;
                    return;
                }
                continue;
            }

            $totalScore  += $score * $st->coefficient;
            $totalWeight += $st->coefficient;
        }

        $this->averages[$studentClassId] = $totalWeight > 0
            ? round($totalScore / $totalWeight, 1)
            : null;
    }

    /**
     * Tính lại TB cho tất cả học sinh trong trang
     */
    protected function recalculateAllAverages(array $studentClassIds): void
    {
        foreach ($studentClassIds as $id) {
            $this->recalculateAverage($id);
        }
    }

    // ==================== PAGINATED DATA ====================

    protected function applySorting($query)
    {
        return $query->orderByRaw(
            "students.first_name {$this->sortDirection},
             students.last_name {$this->sortDirection}"
        );
    }

    /**
     * Query học sinh trong lớp (chưa phân trang).
     */
    private function buildStudentsQuery()
    {
        $query = \App\Models\StudentNew::query()
            ->with('saint')
            ->join('students_class', 'students.id', '=', 'students_class.student_id')
            ->where('students_class.class_id', $this->selectedLop)
            ->select(
                'students_class.id as pivot_id',
                'students_class.student_id',
                'students.*',
            );

        if (!empty(trim($this->search ?? ''))) {
            $term = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('students.first_name', 'like', $term)
                    ->orWhere('students.last_name', 'like', $term)
                    ->orWhere('students.student_code', 'like', $term);
            });
        }

        return $query;
    }

    /**
     * Phân trang thủ công sau khi filter/sort trên collection.
     */
    private function paginateCollection(Collection $items): \Illuminate\Pagination\LengthAwarePaginator
    {
        $page    = max(1, (int) ($this->page ?? 1));
        $perPage = $this->perPage;
        $total   = $items->count();
        $slice   = $items->slice(($page - 1) * $perPage, $perPage)->values();

        return new \Illuminate\Pagination\LengthAwarePaginator(
            $slice,
            $total,
            $perPage,
            $page,
            [
                'path'  => \Illuminate\Pagination\Paginator::resolveCurrentPath(),
                'query' => request()->query(),
            ]
        );
    }

    /**
     * Load điểm + TB cho toàn bộ học sinh (phục vụ sort/filter theo điểm).
     */
    private function loadScoresForStudents(Collection $students): void
    {
        $pivotIds = $students->pluck('pivot_id')->toArray();
        $this->loadScoresMatrix($pivotIds);
        $this->recalculateAllAverages($pivotIds);
    }

    private function getStudentsPaginated()
    {
        if (!$this->selectedLop) {
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $this->perPage, 1);
        }

        try {
            $needsFullList = $this->sortField === 'avg' || $this->filterByRating;

            if ($needsFullList) {
                $query = $this->buildStudentsQuery();
                $this->applySorting($query);
                $allStudents = $query->get();

                $this->loadScoresForStudents($allStudents);

                $collection = $allStudents;

                if ($this->filterByRating) {
                    $collection = $collection->filter(function ($student) {
                        $avg = $this->averages[$student->pivot_id] ?? null;
                        if ($avg === null) {
                            return false;
                        }

                        return $this->getStudentRating($avg) === $this->filterByRating;
                    })->values();
                }

                if ($this->sortField === 'avg') {
                    $collection = $collection->sortBy(
                        fn ($s) => $this->averages[$s->pivot_id] ?? -1,
                        SORT_REGULAR,
                        $this->sortDirection === 'desc'
                    )->values();
                }

                $this->recalculateRatingStatsFromClass();

                return $this->paginateCollection($collection);
            }

            $query = $this->buildStudentsQuery();
            $this->applySorting($query);
            $students = $query->paginate($this->perPage);

            $pivotIds = $students->pluck('pivot_id')->toArray();
            $this->loadScoresMatrix($pivotIds);
            $this->recalculateAllAverages($pivotIds);

            $this->recalculateRatingStatsFromClass();

            return $students;
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading students');
            $this->emit('toast', 'error', 'Có lỗi khi tải danh sách học sinh');

            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $this->perPage, 1);
        }
    }

    protected function loadScoresMatrix(array $studentClassIds): void
    {
        $scoreTypes = collect($this->scoreTypes);

        if (empty($studentClassIds) || $scoreTypes->isEmpty()) {
            if (! $this->hasDraft) {
                $this->scoresMatrix = [];
                $this->draftScores  = [];
            }
            return;
        }

        if ($this->scoresLoaded) {
            return;
        }

        try {
            $scoreTypeIds = $scoreTypes->pluck('id')->toArray();

            $scores = StudentScore::whereIn('student_class_id', $studentClassIds)
                ->whereIn('score_type_id', $scoreTypeIds)
                ->get();

            $matrix = [];
            foreach ($scores as $score) {
                $matrix[$score->student_class_id][$score->score_type_id] = [
                    'id'      => $score->id,
                    'value'   => (float) $score->score_value,
                    'attempt' => $score->attempt,
                    'note'    => $score->note,
                ];
            }

            $this->scoresMatrix = $matrix;

            // Không ghi đè draft đang nhập (hasDraft) — tránh mất dữ liệu mỗi lần render.
            if (! $this->hasDraft) {
                $draft = [];
                foreach ($studentClassIds as $scId) {
                    foreach ($scoreTypeIds as $stId) {
                        $draft[$scId][$stId] = isset($matrix[$scId][$stId])
                            ? $matrix[$scId][$stId]['value']
                            : '';
                    }
                }
                $this->draftScores = $draft;
            }

            $this->scoresLoaded = true;
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading scores matrix');
            $this->scoresMatrix = [];
            if (! $this->hasDraft) {
                $this->draftScores = [];
            }
        }
    }

    // ==================== EVENT HANDLERS ====================

    public function handleFilterChanged(array $filters): void
    {
        if (!is_array($filters)) {
            return;
        }

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
            $sem = (int) $filters['ky'];
            $this->selectedSemester = in_array($sem, [1, 2]) ? $sem : 1;
        }

        $this->scoreTypes   = collect();
        $this->scoresMatrix = [];
        $this->averages     = [];
        $this->resetPage();

        if ($namHocChanged) {
            $this->loadLops();
        } elseif (array_key_exists('khoi', $filters)) {
            $this->loadLops();
        }

        if ($this->selectedLop) {
            $this->loadScoreTypes();
        }
    }

    // ==================== HELPERS ====================

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

    public function getScoreValue(int $studentClassId, int $scoreTypeId): ?float
    {
        return $this->scoresMatrix[$studentClassId][$scoreTypeId]['value'] ?? null;
    }

    public function getAverage(int $studentClassId): ?float
    {
        return $this->averages[$studentClassId] ?? null;
    }

    public function computedStudents()
    {
        return $this->getStudentsPaginated();
    }

    public function exportScores()
    {
        $this->authorize('create', ScoreType::class);

        if (!$this->selectedLop) {
            $this->emit('toast', 'warning', 'Vui lòng chọn lớp trước khi xuất file');
            return;
        }

        if ($this->scoreTypes->isEmpty()) {
            $this->emit('toast', 'warning', 'Lớp chưa có cấu hình loại điểm');
            return;
        }

        $selectedNameClass = CatechismClass::findOrFail($this->selectedLop)->name;

        return response()->streamDownload(function () {
            echo \Maatwebsite\Excel\Facades\Excel::raw(
                new ScoreExport($this->selectedLop, $this->selectedSemester, $this->filterByRating),
                \Maatwebsite\Excel\Excel::XLSX
            );
        }, 'BangDiem_' . $selectedNameClass . '_HK' . $this->selectedSemester . '_' . now()->format('dmY_His') . '.xlsx');
    }

    // ==================== RATING & STATISTICS ====================

    private function getStudentRating(?float $average): ?string
    {
        if ($average === null || $average < 0) {
            return null;
        }

        foreach (self::RATING_LEVELS as $key => $rating) {
            if ($average >= $rating['min'] && $average < $rating['max']) {
                return $key;
            }
        }

        return null;
    }

    /**
     * Tính thống kê xếp loại cho cả lớp (không chỉ trang hiện tại).
     */
    private function recalculateRatingStatsFromClass(): void
    {
        if (!$this->selectedLop || $this->scoreTypes->isEmpty()) {
            $this->ratingStats = [];
            return;
        }

        try {
            $allPivotIds = \App\Models\StudentsClass::query()
                ->where('class_id', $this->selectedLop)
                ->pluck('id')
                ->toArray();

            if (empty($allPivotIds)) {
                $this->ratingStats = [];
                return;
            }

            $scoreTypeIds = $this->scoreTypes->pluck('id')->toArray();
            $scores = StudentScore::whereIn('student_class_id', $allPivotIds)
                ->whereIn('score_type_id', $scoreTypeIds)
                ->get();

            $matrix = [];
            foreach ($scores as $score) {
                $matrix[$score->student_class_id][$score->score_type_id] = [
                    'value' => (float) $score->score_value,
                ];
            }

            $classAverages = [];
            foreach ($allPivotIds as $pivotId) {
                $classAverages[$pivotId] = $this->calculateAverageFromMatrix($matrix, $pivotId);
            }

            $this->ratingStats = [];
            $totalStudents   = 0;
            $statsByRating   = [];

            foreach ($classAverages as $avg) {
                if ($avg === null) {
                    continue;
                }

                $rating = $this->getStudentRating($avg);
                if ($rating) {
                    $statsByRating[$rating] = ($statsByRating[$rating] ?? 0) + 1;
                    $totalStudents++;
                }
            }

            foreach (self::RATING_LEVELS as $key => $ratingInfo) {
                $count      = $statsByRating[$key] ?? 0;
                $percentage = $totalStudents > 0
                    ? round(($count / $totalStudents) * 100, 1)
                    : 0;

                $this->ratingStats[$key] = [
                    'label'      => $ratingInfo['label'],
                    'color'      => $ratingInfo['color'],
                    'range'      => "{$ratingInfo['min']} - {$ratingInfo['max']}",
                    'count'      => $count,
                    'percentage' => $percentage,
                ];
            }
        } catch (\Exception $e) {
            $this->logError($e, 'Error calculating rating stats');
            $this->ratingStats = [];
        }
    }

    /**
     * Tính TB từ ma trận điểm tạm (không ghi vào $this->averages).
     */
    private function calculateAverageFromMatrix(array $matrix, int $studentClassId): ?float
    {
        if ($this->scoreTypes->isEmpty()) {
            return null;
        }

        $totalWeight = 0.0;
        $totalScore  = 0.0;

        foreach ($this->scoreTypes as $st) {
            $score = $matrix[$studentClassId][$st->id]['value'] ?? null;

            if ($score === null) {
                if (in_array($st->type, [ScoreType::TYPE_GIUA_KY, ScoreType::TYPE_CUOI_KY])) {
                    return null;
                }
                continue;
            }

            $totalScore  += $score * $st->coefficient;
            $totalWeight += $st->coefficient;
        }

        return $totalWeight > 0 ? round($totalScore / $totalWeight, 1) : null;
    }

    /**
     * @deprecated Dùng recalculateRatingStatsFromClass() — stats theo cả lớp
     */
    private function recalculateRatingStats(): void
    {
        $this->recalculateRatingStatsFromClass();
    }

    public function getRatingStats()
    {
        return $this->ratingStats;
    }

    public function setFilterByRating(?string $rating): void
    {
        $this->filterByRating = $rating;
    }

    public function clearRatingFilter(): void
    {
        $this->filterByRating = null;
    }

    // ==================== RENDER ====================

    public function render()
    {
        $students = ($this->activeTab === 'scores')
            ? $this->getStudentsPaginated()
            : new \Illuminate\Pagination\LengthAwarePaginator([], 0, $this->perPage, 1);

        $user = auth()->user();
        $layout = $user && $user->usesCatechistLayout()
            ? 'frontend.layout.catechist'
            : 'frontend.layout.main';

        $scoreFilterAllowedClassIds = [];
        if ($user && ! $this->canBrowseAllScoreClasses) {
            $scoreFilterAllowedClassIds = app(CatechistAccess::class)->assignedClassIds(
                $user,
                $this->parishId,
                $this->selectedNamHoc ? (int) $this->selectedNamHoc : null
            );

            // GLV không có lớp: sentinel [0] để dropdown lớp trống (mảng rỗng = không hạn chế)
            if ($scoreFilterAllowedClassIds === []) {
                $scoreFilterAllowedClassIds = [0];
            }
        }

        return view('livewire.score.score-manager', [
            'students'                   => $students,
            'parishId'                   => $this->parishId,
            'viewingStudent'             => $this->resolveViewingStudent($students),
            'scoreFilterAllowedClassIds' => $scoreFilterAllowedClassIds,
            'canBrowseAllScoreClasses'   => $this->canBrowseAllScoreClasses,
        ])
            ->extends($layout)
            ->section('content');
    }

    /**
     * @param  \Illuminate\Contracts\Pagination\Paginator|\Illuminate\Support\Collection  $students
     */
    protected function resolveViewingStudent($students)
    {
        if (! $this->viewingPivotId || ! $this->selectedLop) {
            return null;
        }

        $fromPage = collect($students->items())->firstWhere('pivot_id', $this->viewingPivotId);
        if ($fromPage) {
            return $fromPage;
        }

        $student = \App\Models\StudentNew::query()
            ->with('saint')
            ->join('students_class', 'students.id', '=', 'students_class.student_id')
            ->where('students_class.id', $this->viewingPivotId)
            ->where('students_class.class_id', $this->selectedLop)
            ->select(
                'students_class.id as pivot_id',
                'students_class.student_id',
                'students.*',
            )
            ->first();

        if (! $student) {
            $this->viewingPivotId = null;

            return null;
        }

        if (! isset($this->scoresMatrix[$student->pivot_id])) {
            $this->loadScoresMatrix([(int) $student->pivot_id]);
            $this->recalculateAverage((int) $student->pivot_id);
        }

        return $student;
    }
}