<?php

namespace App\Http\Livewire\Score;

use App\Http\Livewire\Base\BaseComponent;
use App\Models\Lop;
use App\Models\NamHoc;
use App\Models\ScoreType;
use App\Models\Student;
use App\Models\StudentScore;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Component quản lý điểm học sinh
 * 
 * Features:
 * - Xem bảng điểm theo lớp & học kỳ
 * - Nhập/sửa điểm từng học sinh
 * - Cấu hình loại điểm (ScoreType)
 * - Tính điểm trung bình tự động
 * - Export/Import điểm (future)
 */

class ScoreManager extends BaseComponent
{
    // ==================== FILTERS ====================

    /** @var int|null Selected năm học ID */
    public $selectedNamHoc = null;

    /** @var int|null Selected lớp ID */
    public $selectedLop = null;

    /** @var int Selected học kỳ (1 hoặc 2) */
    public $selectedSemester = 1;

    // ==================== FORM STATE ====================

    /** @var bool Hiển thị modal nhập điểm */
    public $showScoreForm = false;

    /** @var bool Hiển thị modal cấu hình loại điểm */
    public $showScoreTypeForm = false;

    /** @var int|null ID ScoreType đang edit */
    public $editingScoreTypeId = null;

    // ==================== FORM FIELDS - Score Entry ====================

    /** @var int|null Student ID đang nhập điểm */
    public $currentStudentId = null;

    /** @var int|null ScoreType ID đang nhập */
    public $currentScoreTypeId = null;

    /** @var float|null Điểm số */
    public $scoreValue = null;

    /** @var int Lần thi */
    public $attempt = 1;

    /** @var string|null Ghi chú */
    public $scoreNote = null;

    // ==================== FORM FIELDS - ScoreType Config ====================

    /** @var string Tên loại điểm */
    public $typeName;

    /** @var int Loại điểm (1-5) */
    public $scoreTypeType;

    /** @var int Thứ tự */
    public $typeOrder = 0;

    /** @var float Hệ số */
    public $typeCoefficient = 1.0;

    /** @var float Điểm tối đa */
    public $typeMaxScore = 10.0;

    /** @var bool Trạng thái */
    public $typeIsActive = true;

    // ==================== DATA ====================

    /** @var \Illuminate\Support\Collection Danh sách năm học */
    public $availableNamHocs;

    /** @var \Illuminate\Support\Collection Danh sách lớp */
    public $availableLops;

    /** @var \Illuminate\Support\Collection Danh sách loại điểm */
    public $scoreTypes;

    /** @var \Illuminate\Support\Collection Danh sách học sinh */
    public $students;

    /** @var array Điểm của các học sinh [student_id => [score_type_id => score]] */
    public $scoresMatrix = [];

    // ==================== VALIDATION ====================

    protected $rules = [
        'selectedNamHoc' => 'nullable|integer|exists:nam_hoc,id',
        'selectedLop' => 'nullable|integer|exists:lop,id',
        'selectedSemester' => 'required|integer|in:1,2',
    ];

    protected $scoreRules = [
        'scoreValue' => 'required|numeric|min:0|max:10',
        'attempt' => 'required|integer|min:1',
        'scoreNote' => 'nullable|string|max:500',
    ];

    protected $scoreTypeRules = [
        'typeName' => 'required|string|max:100',
        'scoreTypeType' => 'required|integer|in:1,2,3,4,5',
        'typeOrder' => 'required|integer|min:0',
        'typeCoefficient' => 'required|numeric|min:0.1|max:10',
        'typeMaxScore' => 'required|numeric|min:1|max:100',
        'typeIsActive' => 'required|boolean',
    ];

    protected $messages = [
        'selectedNamHoc.exists' => 'Năm học không tồn tại',
        'selectedLop.exists' => 'Lớp không tồn tại',
        'selectedSemester.in' => 'Học kỳ không hợp lệ',
        'scoreValue.required' => 'Vui lòng nhập điểm',
        'scoreValue.max' => 'Điểm không được quá 10',
        'typeName.required' => 'Vui lòng nhập tên loại điểm',
        'scoreTypeType.required' => 'Vui lòng chọn loại điểm',
        'typeCoefficient.min' => 'Hệ số phải lớn hơn 0',
    ];

    // ==================== QUERY STRING ====================

    protected function queryString()
    {
        return array_merge([
            'selectedNamHoc' => ['as' => 'namHoc', 'except' => null],
            'selectedLop' => ['as' => 'lop', 'except' => null],
            'selectedSemester' => ['as' => 'semester', 'except' => 1],
        ], parent::queryString());
    }

    // ==================== LISTENERS ====================

    protected $listeners = [
        'refresh' => 'handleRefresh',
        'filterChanged' => 'handleFilterChanged',
        'scoreUpdated' => 'loadScoresData',
    ];

    // ==================== LIFECYCLE ====================

    public function mount()
    {
        $this->authorize('viewAny', Lop::class);
        parent::mount();
        $this->requireParishId();

        $this->availableNamHocs = collect();
        $this->availableLops = collect();
        $this->scoreTypes = collect();
        $this->students = collect();
    }

    protected function loadInitialData(): void
    {
        $this->loadNamHocs();

        if (!$this->selectedNamHoc) {
            $this->selectedNamHoc = $this->getDefaultNamHocId();
        }

        if ($this->selectedNamHoc) {
            $this->loadLops();
        }

        if ($this->selectedLop && $this->selectedSemester) {
            $this->loadScoresData();
        }
    }

    // ==================== DATA LOADING ====================

    protected function loadNamHocs(): void
    {
        try {
            $this->availableNamHocs = NamHoc::ofParish($this->parishId)
                ->active()
                ->orderByDesc('name')
                ->get(['id', 'name']);
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading nam hocs');
            $this->availableNamHocs = collect();
        }
    }

    protected function loadLops(): void
    {
        if (!$this->selectedNamHoc) {
            $this->availableLops = collect();
            return;
        }

        try {
            $this->availableLops = Lop::with('blockRelation')
                ->where('pid', $this->parishId)
                ->where('schoolyear', $this->selectedNamHoc)
                ->active()
                ->get(['id', 'name', 'block'])
                ->map(function ($lop) {
                    return [
                        'id' => $lop->id,
                        'name' => $lop->name,
                        'block_name' => $lop->blockRelation->name ?? '',
                    ];
                });
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading lops');
            $this->availableLops = collect();
        }
    }

    protected function loadScoresData(): void
    {
        if (!$this->selectedLop || !$this->selectedSemester) {
            return;
        }

        try {
            // Load ScoreTypes
            $this->scoreTypes = ScoreType::ofClass($this->selectedLop)
                ->ofSemester($this->selectedSemester)
                ->active()
                ->ordered()
                ->get();

            // Load Students
            $this->students = Student::whereHas('enrollments', function ($q) {
                $q->where('class_id', $this->selectedLop);
            })
                ->orderBy('name')
                ->get();

            // Load Scores Matrix
            $this->loadScoresMatrix();
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading scores data');
            session()->flash('error', 'Có lỗi khi tải dữ liệu điểm');
        }
    }

    protected function loadScoresMatrix(): void
    {
        if ($this->students->isEmpty() || $this->scoreTypes->isEmpty()) {
            $this->scoresMatrix = [];
            return;
        }

        $studentIds = $this->students->pluck('id')->toArray();
        $scoreTypeIds = $this->scoreTypes->pluck('id')->toArray();

        $scores = StudentScore::whereIn('student_id', $studentIds)
            ->whereIn('score_type_id', $scoreTypeIds)
            ->where('class_id', $this->selectedLop)
            ->get()
            ->groupBy('student_id');

        $matrix = [];
        foreach ($scores as $studentId => $studentScores) {
            foreach ($studentScores as $score) {
                $matrix[$studentId][$score->score_type_id] = [
                    'id' => $score->id,
                    'value' => $score->score_value,
                    'attempt' => $score->attempt,
                ];
            }
        }

        $this->scoresMatrix = $matrix;
    }

    // ==================== PROPERTY UPDATERS ====================

    public function updatedSelectedNamHoc(): void
    {
        $this->selectedNamHoc = is_numeric($this->selectedNamHoc)
            ? (int) $this->selectedNamHoc
            : null;

        $this->selectedLop = null;
        $this->resetPage();
        $this->loadLops();
    }

    public function updatedSelectedLop(): void
    {
        $this->selectedLop = is_numeric($this->selectedLop)
            ? (int) $this->selectedLop
            : null;

        $this->resetPage();
        $this->loadScoresData();
    }

    public function updatedSelectedSemester(): void
    {
        $this->selectedSemester = is_numeric($this->selectedSemester)
            ? (int) $this->selectedSemester
            : 1;

        $this->loadScoresData();
    }

    // ==================== SCORE ENTRY ACTIONS ====================

    public function openScoreForm(int $studentId, int $scoreTypeId): void
    {
        $this->requireManager();

        $this->currentStudentId = $studentId;
        $this->currentScoreTypeId = $scoreTypeId;

        // Load existing score if any
        $existingScore = $this->scoresMatrix[$studentId][$scoreTypeId] ?? null;

        if ($existingScore) {
            $this->scoreValue = $existingScore['value'];
            $this->attempt = $existingScore['attempt'];
        } else {
            $this->scoreValue = null;
            $this->attempt = 1;
        }

        $this->scoreNote = null;
        $this->showScoreForm = true;
    }

    public function saveScore(): void
    {
        $this->requireManager();

        $this->validate($this->scoreRules, $this->messages);

        try {
            DB::beginTransaction();

            StudentScore::updateOrCreate(
                [
                    'student_id' => $this->currentStudentId,
                    'class_id' => $this->selectedLop,
                    'score_type_id' => $this->currentScoreTypeId,
                    'attempt' => $this->attempt,
                ],
                [
                    'score_value' => $this->scoreValue,
                    'note' => $this->scoreNote,
                ]
            );

            DB::commit();

            session()->flash('message', 'Lưu điểm thành công');

            $this->closeScoreForm();
            $this->loadScoresMatrix();

            $this->emit('scoreUpdated');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError($e, 'Error saving score');
            session()->flash('error', 'Có lỗi khi lưu điểm');
        }
    }

    public function closeScoreForm(): void
    {
        $this->showScoreForm = false;
        $this->resetScoreForm();
        $this->resetValidation();
    }

    protected function resetScoreForm(): void
    {
        $this->reset([
            'currentStudentId',
            'currentScoreTypeId',
            'scoreValue',
            'attempt',
            'scoreNote',
        ]);
    }

    // ==================== SCORE TYPE CONFIG ACTIONS ====================

    public function createScoreType(): void
    {
        $this->requireManager();

        if (!$this->selectedLop || !$this->selectedSemester) {
            session()->flash('warning', 'Vui lòng chọn lớp và học kỳ');
            return;
        }

        $this->resetScoreTypeForm();
        $this->showScoreTypeForm = true;
    }

    public function editScoreType(int $id): void
    {
        $this->requireManager();

        try {
            $scoreType = ScoreType::findOrFail($id);

            $this->editingScoreTypeId = $scoreType->id;
            $this->typeName = $scoreType->name;
            $this->scoreTypeType = $scoreType->type;
            $this->typeOrder = $scoreType->order;
            $this->typeCoefficient = $scoreType->coefficient;
            $this->typeMaxScore = $scoreType->max_score;
            $this->typeIsActive = $scoreType->is_active;

            $this->showScoreTypeForm = true;
        } catch (ModelNotFoundException $e) {
            session()->flash('error', 'Không tìm thấy loại điểm này');
        }
    }

    public function saveScoreType(): void
    {
        $this->requireManager();

        $this->validate($this->scoreTypeRules, $this->messages);

        try {
            DB::beginTransaction();

            // Check unique: class + semester + type
            $exists = ScoreType::where('class_id', $this->selectedLop)
                ->where('semester', $this->selectedSemester)
                ->where('type', $this->scoreTypeType)
                ->when($this->editingScoreTypeId, fn($q) => $q->where('id', '!=', $this->editingScoreTypeId))
                ->exists();

            if ($exists) {
                session()->flash('error', 'Loại điểm này đã tồn tại trong học kỳ');
                return;
            }

            ScoreType::updateOrCreate(
                ['id' => $this->editingScoreTypeId],
                [
                    'class_id' => $this->selectedLop,
                    'semester' => $this->selectedSemester,
                    'type' => $this->scoreTypeType,
                    'name' => $this->typeName,
                    'order' => $this->typeOrder,
                    'coefficient' => $this->typeCoefficient,
                    'max_score' => $this->typeMaxScore,
                    'is_active' => $this->typeIsActive,
                ]
            );

            DB::commit();

            $message = $this->editingScoreTypeId
                ? 'Cập nhật loại điểm thành công'
                : 'Tạo loại điểm mới thành công';

            session()->flash('message', $message);

            $this->closeScoreTypeForm();
            $this->loadScoresData();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError($e, 'Error saving score type');
            session()->flash('error', 'Có lỗi khi lưu loại điểm');
        }
    }

    public function deleteScoreType(int $id): void
    {
        $this->requireManager();

        try {
            DB::beginTransaction();

            $scoreType = ScoreType::findOrFail($id);

            // Check if has scores
            $hasScores = StudentScore::where('score_type_id', $id)->exists();

            if ($hasScores) {
                session()->flash('error', 'Không thể xóa loại điểm đã có dữ liệu');
                return;
            }

            $scoreType->delete();

            DB::commit();

            session()->flash('message', 'Đã xóa loại điểm');
            $this->loadScoresData();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError($e, 'Error deleting score type');
            session()->flash('error', 'Có lỗi khi xóa loại điểm');
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
        $this->reset([
            'editingScoreTypeId',
            'typeName',
            'scoreTypeType',
            'typeOrder',
            'typeCoefficient',
            'typeMaxScore',
            'typeIsActive',
        ]);

        $this->typeCoefficient = 1.0;
        $this->typeMaxScore = 10.0;
        $this->typeIsActive = true;
    }

    // ==================== HELPERS ====================

    protected function getDefaultNamHocId(): ?int
    {
        return NamHoc::ofParish($this->parishId)
            ->active()
            ->orderByDesc('name')
            ->value('id');
    }

    public function getScoreValue(int $studentId, int $scoreTypeId): ?float
    {
        return $this->scoresMatrix[$studentId][$scoreTypeId]['value'] ?? null;
    }

    // ==================== RENDER ====================

    public function render()
    {
        return view('livewire.score.score-manager', [
            'parishId' => $this->parishId,
        ])
            ->extends('frontend.layout.main')
            ->section('content');
    }
}
