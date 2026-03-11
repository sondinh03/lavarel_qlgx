<?php

namespace App\Http\Livewire\Score;

use App\Http\Livewire\Base\BaseComponent;
use App\Models\CatechismClass;
use App\Models\GradeLevel;
use App\Models\NamHoc;
use App\Models\ScoreType;
use App\Models\StudentsClass;
use App\Models\StudentScore;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

/**
 * Component quản lý điểm học sinh
 *
 * Features:
 * - Bảng điểm: xem & nhập điểm từng học sinh
 * - Cấu hình loại điểm (thêm/sửa/xoá cột điểm)
 * - Apply nhanh cấu hình cho nhiều lớp (cùng khối hoặc toàn xứ)
 * - Tính điểm trung bình học kỳ tự động (weighted average)
 */
class ScoreManager extends BaseComponent
{
    // ==================== FILTERS ====================

    /** @var int|null Selected năm học ID */
    public $selectedNamHoc = null;

    /** @var int|null Selected khối ID */
    public $selectedKhoi = null;

    /** @var int|null Selected lớp ID */
    public $selectedLop = null;

    /** @var int Selected học kỳ (1 hoặc 2) */
    public $selectedSemester = 1;

    // ==================== TABS ====================

    /** @var string Tab hiện tại: 'scores' | 'config' */
    public $activeTab = 'scores';

    // ==================== SCORE ENTRY FORM ====================

    /** @var bool Hiển thị modal nhập điểm */
    public $showScoreForm = false;

    /** @var int|null StudentsClass ID đang nhập */
    public $currentStudentClassId = null;

    /** @var int|null ScoreType ID đang nhập */
    public $currentScoreTypeId = null;

    /** @var float|null Điểm số */
    public $scoreValue = null;

    /** @var int Lần thi */
    public $attempt = 1;

    /** @var string|null Ghi chú */
    public $scoreNote = null;

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

    // ==================== APPLY CONFIG FORM ====================

    /** @var bool Hiển thị modal apply cấu hình */
    public $showApplyForm = false;

    /** @var string Phạm vi apply: 'class' | 'grade' | 'parish' */
    public $applyScope = 'class';

    /** @var int|null ID khối khi applyScope = 'grade' */
    public $applyScopeGradeId = null;

    /** @var bool Ghi đè nếu lớp đã có cấu hình */
    public $applyOverwrite = false;

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

    /** @var bool Enable pagination */
    protected $usePagination = true;

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
        ], parent::queryString());
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

        if ($this->selectedLop) {
            $this->loadScoreTypes();
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

    protected function loadScoreTypes(): void
    {
        if (!$this->selectedLop) {
            $this->scoreTypes = collect();
            return;
        }

        try {
            $this->scoreTypes = ScoreType::where('class_id', $this->selectedLop)
                ->where('semester', $this->selectedSemester)
                ->active()
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
        $this->selectedLop  = $this->toInt($this->selectedLop);
        $this->scoresMatrix = [];
        $this->averages     = [];
        $this->resetPage();
        $this->loadScoreTypes();
    }

    public function updatedSelectedSemester(): void
    {
        $sem = (int) $this->selectedSemester;
        $this->selectedSemester = in_array($sem, [1, 2]) ? $sem : 1;
        $this->scoresMatrix = [];
        $this->averages     = [];
        $this->resetPage();
        $this->loadScoreTypes();
    }

    // ==================== TABS ====================

    public function switchTab(string $tab): void
    {
        if (!in_array($tab, ['scores', 'config'])) {
            return;
        }

        $this->activeTab = $tab;
    }

    // ==================== SCORE ENTRY ====================

    public function openScoreForm(int $studentClassId, int $scoreTypeId): void
    {
        $this->currentStudentClassId = $studentClassId;
        $this->currentScoreTypeId    = $scoreTypeId;

        // Load existing score if any
        $existing = $this->scoresMatrix[$studentClassId][$scoreTypeId] ?? null;

        if ($existing) {
            $this->scoreValue = $existing['value'];
            $this->attempt    = $existing['attempt'];
            $this->scoreNote  = $existing['note'];
        } else {
            $this->scoreValue = null;
            $this->attempt    = 1;
            $this->scoreNote  = null;
        }

        $this->resetValidation();
        $this->showScoreForm = true;
    }

    public function saveScore(): void
    {
        $this->authorize('update', ScoreType::class);
        $this->validate($this->scoreRules, $this->messages);

        try {
            DB::beginTransaction();

            StudentScore::updateOrCreate(
                [
                    'student_class_id' => $this->currentStudentClassId,
                    'score_type_id'    => $this->currentScoreTypeId,
                    'attempt'          => $this->attempt,
                ],
                [
                    'score_value' => $this->scoreValue,
                    'note'        => $this->scoreNote,
                ]
            );

            DB::commit();

            // Cập nhật matrix local — không cần reload toàn bộ
            $this->scoresMatrix[$this->currentStudentClassId][$this->currentScoreTypeId] = [
                'value'   => (float) $this->scoreValue,
                'attempt' => $this->attempt,
                'note'    => $this->scoreNote,
            ];

            // Recalculate average cho student này
            $this->recalculateAverage($this->currentStudentClassId);

            session()->flash('message', 'Lưu điểm thành công');
            $this->closeScoreForm();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError($e, 'Error saving score');
            session()->flash('error', 'Có lỗi khi lưu điểm');
        }
    }

    public function deleteScore(int $studentClassId, int $scoreTypeId): void
    {
        $this->authorize('update', ScoreType::class);

        try {
            StudentScore::where('student_class_id', $studentClassId)
                ->where('score_type_id', $scoreTypeId)
                ->delete();

            unset($this->scoresMatrix[$studentClassId][$scoreTypeId]);
            $this->recalculateAverage($studentClassId);

            session()->flash('message', 'Đã xoá điểm');
        } catch (\Exception $e) {
            $this->logError($e, 'Error deleting score');
            session()->flash('error', 'Có lỗi khi xoá điểm');
        }
    }

    public function closeScoreForm(): void
    {
        $this->showScoreForm         = false;
        $this->currentStudentClassId = null;
        $this->currentScoreTypeId    = null;
        $this->scoreValue            = null;
        $this->attempt               = 1;
        $this->scoreNote             = null;
        $this->resetValidation();
    }

    // ==================== SCORE TYPE CONFIG ====================

    public function createScoreType(): void
    {
        $this->authorize('create', ScoreType::class);

        if (!$this->selectedNamHoc) {
            session()->flash('warning', 'Vui lòng chọn năm học trước');
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
            session()->flash('error', 'Không tìm thấy loại điểm này');
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

                session()->flash('message', 'Cập nhật loại điểm thành công');
                $this->closeScoreTypeForm();
                $this->loadScoreTypes();
                return;
            } catch (\Exception $e) {
                $this->logError($e, 'Error updating score type');
                session()->flash('error', 'Có lỗi khi cập nhật loại điểm');
                return;
            }
        }

        // Create mode → resolve danh sách lớp theo scope
        $classIds = $this->resolveCreateTargetClassIds();

        if ($classIds->isEmpty()) {
            session()->flash('warning', 'Không tìm thấy lớp nào để áp dụng');
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

            session()->flash('message', $msg);
            $this->closeScoreTypeForm();
            $this->loadScoreTypes();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError($e, 'Error saving score type');
            session()->flash('error', 'Có lỗi khi lưu loại điểm');
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

        $query = CatechismClass::where('school_year_id', $this->selectedNamHoc)
            ->where('parish_id', $this->parishId)
            ->active();

        return match ($this->createScope) {
            'grade'  => $query->where('grade_level_id', $this->createScopeGradeId)->pluck('id'),
            'parish' => $query->pluck('id'),
            default  => collect(),
        };
    }

    public function deleteScoreType(int $id): void
    {
        $this->authorize('delete', ScoreType::class);

        try {
            DB::beginTransaction();

            $hasScores = StudentScore::where('score_type_id', $id)->exists();

            if ($hasScores) {
                session()->flash('error', 'Không thể xoá loại điểm đã có dữ liệu điểm');
                return;
            }

            ScoreType::findOrFail($id)->delete();

            DB::commit();

            session()->flash('message', 'Đã xoá loại điểm');
            $this->loadScoreTypes();
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            session()->flash('error', 'Không tìm thấy loại điểm');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError($e, 'Error deleting score type');
            session()->flash('error', 'Có lỗi khi xoá loại điểm');
        }
    }

    public function toggleScoreTypeStatus(int $id): void
    {
        $this->authorize('update', ScoreType::class);

        try {
            $st = ScoreType::findOrFail($id);
            $st->update(['is_active' => !$st->is_active]);

            session()->flash('message', $st->is_active ? 'Đã kích hoạt' : 'Đã tắt loại điểm');
            $this->loadScoreTypes();
        } catch (\Exception $e) {
            $this->logError($e, 'Error toggling score type');
            session()->flash('error', 'Có lỗi khi thay đổi trạng thái');
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

    // ==================== APPLY CONFIG ====================

    /**
     * Mở modal apply cấu hình lớp hiện tại sang lớp/khối/toàn xứ khác
     */
    public function openApplyForm(): void
    {
        $this->authorize('create', ScoreType::class);

        if (!$this->selectedLop) {
            session()->flash('warning', 'Vui lòng chọn lớp nguồn trước');
            return;
        }

        if ($this->scoreTypes->isEmpty()) {
            session()->flash('warning', 'Lớp này chưa có cấu hình loại điểm nào');
            return;
        }

        $this->applyScope        = 'class';
        $this->applyScopeGradeId = null;
        $this->applyOverwrite    = false;

        $this->showApplyForm = true;
    }

    /**
     * Thực hiện apply cấu hình từ lớp hiện tại sang lớp/khối/toàn xứ
     */
    public function applyConfig(): void
    {
        $this->authorize('create', ScoreType::class);

        if (!$this->selectedLop || $this->scoreTypes->isEmpty()) {
            session()->flash('error', 'Không có cấu hình để apply');
            return;
        }

        // Resolve danh sách class_id đích
        $targetClassIds = $this->resolveTargetClassIds();

        if ($targetClassIds->isEmpty()) {
            session()->flash('warning', 'Không tìm thấy lớp nào phù hợp');
            return;
        }

        // Loại bỏ lớp nguồn khỏi danh sách đích
        $targetClassIds = $targetClassIds->reject(fn($id) => $id === (int) $this->selectedLop)->values();

        if ($targetClassIds->isEmpty()) {
            session()->flash('warning', 'Không có lớp nào khác để apply');
            return;
        }

        try {
            DB::beginTransaction();

            $applied  = 0;
            $skipped  = 0;

            foreach ($targetClassIds as $classId) {
                foreach ($this->scoreTypes as $st) {
                    $exists = ScoreType::where('class_id', $classId)
                        ->where('semester', $this->selectedSemester)
                        ->where('type', $st->type)
                        ->exists();

                    if ($exists && !$this->applyOverwrite) {
                        $skipped++;
                        continue;
                    }

                    ScoreType::updateOrCreate(
                        [
                            'class_id' => $classId,
                            'semester' => $this->selectedSemester,
                            'type'     => $st->type,
                        ],
                        [
                            'name'        => $st->name,
                            'order'       => $st->order,
                            'coefficient' => $st->coefficient,
                            'max_score'   => $st->max_score,
                            'is_active'   => $st->is_active,
                        ]
                    );

                    $applied++;
                }
            }

            DB::commit();

            $msg = "Đã apply cho {$targetClassIds->count()} lớp ({$applied} cấu hình).";
            if ($skipped > 0) {
                $msg .= " Bỏ qua {$skipped} cấu hình đã tồn tại.";
            }

            session()->flash('message', $msg);
            $this->showApplyForm = false;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError($e, 'Error applying score config');
            session()->flash('error', 'Có lỗi khi apply cấu hình');
        }
    }

    /**
     * Resolve danh sách class_id dựa theo applyScope
     */
    protected function resolveTargetClassIds(): Collection
    {
        $query = CatechismClass::where('school_year_id', $this->selectedNamHoc)
            ->where('parish_id', $this->parishId)
            ->active();

        return match ($this->applyScope) {
            'grade'  => $query->where('grade_level_id', $this->applyScopeGradeId)->pluck('id'),
            'parish' => $query->pluck('id'),
            default  => collect([$this->selectedLop]), // 'class' = chỉ lớp hiện tại (edge case)
        };
    }

    public function closeApplyForm(): void
    {
        $this->showApplyForm     = false;
        $this->applyScope        = 'class';
        $this->applyScopeGradeId = null;
        $this->applyOverwrite    = false;
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

    private function getStudentsPaginated()
    {
        if (!$this->selectedLop) {
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $this->perPage, 1);
        }

        try {
            // Dùng bảng pivot trực tiếp, join sang bảng students
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

            $query->orderByRaw("CONCAT(students.last_name, ' ', students.first_name)");

            $students = $query->paginate($this->perPage);

            // pivot_id là students_class.id — dùng cho score matrix
            $this->loadScoresMatrix($students->pluck('pivot_id')->toArray());
            $this->recalculateAllAverages($students->pluck('pivot_id')->toArray());

            return $students;
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading students');
            session()->flash('error', 'Có lỗi khi tải danh sách học sinh');

            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $this->perPage, 1);
        }
    }

    protected function loadScoresMatrix(array $studentClassIds): void
    {
        if (empty($studentClassIds) || $this->scoreTypes->isEmpty()) {
            $this->scoresMatrix = [];
            return;
        }

        try {
            $scoreTypeIds = $this->scoreTypes->pluck('id')->toArray();

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
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading scores matrix');
            $this->scoresMatrix = [];
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
        return NamHoc::ofParish($this->parishId)
            ->active()
            ->orderByDesc('start_date_one')
            ->value('id');
    }

    public function getScoreValue(int $studentClassId, int $scoreTypeId): ?float
    {
        return $this->scoresMatrix[$studentClassId][$scoreTypeId]['value'] ?? null;
    }

    public function getAverage(int $studentClassId): ?float
    {
        return $this->averages[$studentClassId] ?? null;
    }

    // ==================== RENDER ====================

    public function render()
    {
        $students = $this->getStudentsPaginated();

        return view('livewire.score.score-manager', [
            'students'       => $students,
            'parishId'       => $this->parishId,
        ])
            ->extends('frontend.layout.main')
            ->section('content');
    }
}
