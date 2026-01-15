<?php

namespace App\Http\Livewire\Score;

use App\Http\Livewire\Base\BaseComponent;
use App\Models\Lop;
use App\Models\NamHoc;
use App\Models\ScoreType;
use App\Models\Student;
use App\Models\StudentsClass;
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

    /** @var int|null Selected khối ID */
    public $selectedKhoi = null;

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
    public $currentStudentClassId = null;

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

    /** @var array Điểm của các học sinh [student_id => [score_type_id => score]] */
    public $scoresMatrix = [];

    /** @var bool Enable pagination */
    protected $usePagination = true;

    // ==================== VALIDATION ====================

    protected $rules = [
        'selectedNamHoc' => 'nullable|integer|exists:nam_hoc,id',
        'selectedLop' => 'nullable|integer|exists:lop,id',
        'selectedSemester' => 'required|integer|in:1,2',
        'perPage' => 'required|integer|in:10,15,25,50',
    ];

    /**
     * Rules riêng cho form nhập điểm
     */
    protected $scoreRules = [
        'scoreValue' => 'required|numeric|min:0|max:10',
        'attempt' => 'required|integer|min:1',
        'scoreNote' => 'nullable|string|max:500',
    ];

    /**
     * Rules riêng cho form cấu hình loại điểm
     */
    protected $scoreTypeRules = [
        'typeName' => 'required|string|max:100',
        'scoreTypeType' => 'required|integer|in:1,2,3,4,5',
        'typeOrder' => 'required|integer|min:0',
        'typeCoefficient' => 'required|numeric|min:0.1|max:10',
        'typeMaxScore' => 'required|numeric|min:1|max:100',
        'typeIsActive' => 'required|boolean',
    ];

    /**
     * Custom validation messages
     */
    protected $messages = [
        'selectedNamHoc.exists' => 'Năm học không tồn tại',
        'selectedLop.exists' => 'Lớp không tồn tại',
        'selectedSemester.in' => 'Học kỳ không hợp lệ',
        'scoreValue.required' => 'Vui lòng nhập điểm',
        'scoreValue.max' => 'Điểm không được quá 10',
        'attempt.min' => 'Lần thi phải lớn hơn 0',
        'typeName.required' => 'Vui lòng nhập tên loại điểm',
        'scoreTypeType.required' => 'Vui lòng chọn loại điểm',
        'typeCoefficient.min' => 'Hệ số phải lớn hơn 0',
        'perPage.in' => 'Số mục trên trang không hợp lệ',
    ];

    // ==================== QUERY STRING ====================

    protected function queryString()
    {
        return array_merge([
            'selectedNamHoc' => ['as' => 'namHoc', 'except' => null],
            'selectedLop' => ['as' => 'lop', 'except' => null],
            'selectedSemester' => ['as' => 'semester', 'except' => 1],
            'showScoreForm' => ['except' => false],
            'showScoreTypeForm' => ['except' => false],
        ], parent::queryString());
    }

    // ==================== LISTENERS ====================

    protected $listeners = [
        'refresh' => 'handleRefresh',
        'filterChanged' => 'handleFilterChanged',
        'scoreUpdated' => 'loadScoresData',
        'scoreTypeUpdated' => 'loadScoresData',
    ];

    // ==================== LIFECYCLE ====================

    public function mount()
    {
        parent::mount();

        // Yêu cầu quyền quản trị
        $this->requireManager();

        // Bắt buộc phải có parishId
        $this->requireParishId();

        // Initialize collections
        $this->availableNamHocs = collect();
        $this->availableLops = collect();
        $this->scoreTypes = collect();
    }

    /**
     * Load dữ liệu ban đầu (required by BaseComponent)
     */
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
            $this->loadScoreTypes();
        }
    }

    /**
     * Override sanitizeQueryString để xử lý thêm filters
     */
    protected function sanitizeQueryString(): void
    {
        parent::sanitizeQueryString();

        // Sanitize selectedNamHoc
        $this->selectedNamHoc = $this->sanitizeInteger($this->selectedNamHoc);

        // Sanitize selectedLop
        $this->selectedLop = $this->sanitizeInteger($this->selectedLop);

        // Sanitize selectedSemester (default to 1)
        if ($this->selectedSemester === '' || $this->selectedSemester === null) {
            $this->selectedSemester = 1;
        } else {
            $this->selectedSemester = is_numeric($this->selectedSemester)
                ? (int) $this->selectedSemester
                : 1;
        }

        // Validate semester is 1 or 2
        if (!in_array($this->selectedSemester, [1, 2])) {
            $this->selectedSemester = 1;
        }
    }

    /**
     * Override resetToDefaults để reset thêm filters
     */
    protected function resetToDefaults(): void
    {
        parent::resetToDefaults();
        $this->selectedLop = null;
        $this->selectedSemester = 1;
    }

    // ==================== DATA LOADING ====================

    /**
     * Load danh sách năm học
     */
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
            session()->flash('error', 'Có lỗi khi tải danh sách năm học');
        }
    }

    /**
     * Load danh sách lớp theo năm học
     */
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
                        'block_name' => $lop->blockRelation->name ?? 'N/A',
                    ];
                })
                ->sortBy('name')
                ->values();
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading lops');
            $this->availableLops = collect();
            session()->flash('error', 'Có lỗi khi tải danh sách lớp');
        }
    }

    /**
     * Load danh sách ScoreTypes cho lớp và học kỳ
     */
    protected function loadScoreTypes(): void
    {
        if (!$this->selectedLop || !$this->selectedSemester) {
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
            $this->logError($e, 'Error loading score types', [
                'lop_id' => $this->selectedLop,
                'semester' => $this->selectedSemester,
            ]);

            $this->scoreTypes = collect();
            session()->flash('error', 'Có lỗi khi tải danh sách loại điểm');
        }
    }

    /**
     * Load toàn bộ dữ liệu điểm
     */
    // protected function loadScoresData(): void
    public function loadScoresData(): void
    {
        $this->loadScoreTypes();
        $this->resetPage(); // Reset về trang 1 khi load lại data
    }

    /**
     * Get paginated students với điểm
     */
    private function getStudentsPaginated()
    {
        if (!$this->selectedLop) {
            return new \Illuminate\Pagination\LengthAwarePaginator(
                [],
                0,
                $this->perPage,
                $this->page ?? 1
            );
        }

        try {
            $query = StudentsClass::with('student')
                ->where('class_id', $this->selectedLop);
            // ->active();

            // Apply search filter
            if (!empty(trim($this->search))) {
                $searchTerm = '%' . trim($this->search) . '%';
                $query->whereHas('student', function ($q) use ($searchTerm) {
                    $q->where('name', 'like', $searchTerm);
                });
            }

            // Order by student name
            $query->join('student', 'students_class.student_id', '=', 'student.id')
                ->orderBy('student.name', 'asc')
                ->select('students_class.*');

            $students = $query->paginate($this->perPage);

            // Load scores matrix cho students trong trang hiện tại
            $this->loadScoresMatrixForPage($students);

            return $students;
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading students', [
                'lop_id' => $this->selectedLop,
                'search' => $this->search,
            ]);

            session()->flash('error', 'Có lỗi khi tải danh sách học sinh');

            return new \Illuminate\Pagination\LengthAwarePaginator(
                [],
                0,
                $this->perPage,
                $this->page ?? 1
            );
        }
    }

    /**
     * Load ma trận điểm cho students trong trang hiện tại
     */
    protected function loadScoresMatrixForPage($students): void
    {
        if ($students->isEmpty() || $this->scoreTypes->isEmpty()) {
            $this->scoresMatrix = [];
            return;
        }

        try {
            $studentClassIds = $students->pluck('id')->toArray();
            $scoreTypeIds = $this->scoreTypes->pluck('id')->toArray();

            $scores = StudentScore::with(['studentClass', 'scoreType'])
                ->whereIn('student_class_id', $studentClassIds)
                ->whereIn('score_type_id', $scoreTypeIds)
                ->get()
                ->groupBy('student_class_id');

            $matrix = [];
            foreach ($scores as $studentClassId => $studentScores) {
                foreach ($studentScores as $score) {
                    $matrix[$studentClassId][$score->score_type_id] = [
                        'id' => $score->id,
                        'value' => $score->score_value,
                        'attempt' => $score->attempt,
                        'note' => $score->note,
                    ];
                }
            }

            $this->scoresMatrix = $matrix;
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading scores matrix');
            $this->scoresMatrix = [];
        }
    }

    // ==================== PROPERTY UPDATERS ====================

    /**
     * Khi thay đổi năm học
     */
    public function updatedSelectedNamHoc(): void
    {
        $this->selectedNamHoc = $this->sanitizeInteger($this->selectedNamHoc);

        try {
            $this->validateOnly('selectedNamHoc');
        } catch (ValidationException $e) {
            $this->selectedNamHoc = null;
            session()->flash('warning', 'Năm học không hợp lệ');
            $this->logError($e, 'Invalid selectedNamHoc');
        }

        // Reset dependent filters
        $this->selectedLop = null;
        $this->scoreTypes = collect();
        $this->scoresMatrix = [];
        $this->resetPage();

        $this->loadLops();
    }

    /**
     * Khi thay đổi lớp
     */
    public function updatedSelectedLop(): void
    {
        $this->selectedLop = $this->sanitizeInteger($this->selectedLop);

        if ($this->selectedLop !== null) {
            try {
                $this->validateOnly('selectedLop');
            } catch (ValidationException $e) {
                $this->selectedLop = null;
                session()->flash('warning', 'Lớp không hợp lệ');
            }
        }

        $this->resetPage();
        $this->loadScoresData();
    }

    /**
     * Khi thay đổi học kỳ
     */
    public function updatedSelectedSemester(): void
    {
        $this->selectedSemester = is_numeric($this->selectedSemester)
            ? (int) $this->selectedSemester
            : 1;

        if (!in_array($this->selectedSemester, [1, 2])) {
            $this->selectedSemester = 1;
        }

        $this->resetPage();
        $this->loadScoresData();
    }

    // ==================== SCORE ENTRY ACTIONS ====================

    /**
     * Mở modal nhập điểm
     */
    public function openScoreForm(int $studentClassId, int $scoreTypeId)
    {
        $this->currentStudentClassId = $studentClassId;
        $this->currentScoreTypeId = $scoreTypeId;

        $existing = $this->getScoreData($studentClassId, $scoreTypeId);

        if ($existing) {
            $this->scoreValue = $existing['value'];
            $this->attempt = $existing['attempt'];
            $this->scoreNote = $existing['note'];
        } else {
            $this->resetScoreForm();
        }

        $this->showScoreForm = true;
    }

    /**
     * Lưu điểm
     */
    public function saveScore(): void
    {
        $this->requireManager();

        $this->validate($this->scoreRules, $this->messages);

        try {
            DB::beginTransaction();

            StudentScore::updateOrCreate(
                [
                    'student_class_id' => $this->currentStudentClassId,
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

            $this->emit('scoreUpdated');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError($e, 'Error saving score', [
                'student_class_id' => $this->currentStudentClassId,
                'score_type_id' => $this->currentScoreTypeId,
            ]);
            session()->flash('error', 'Có lỗi khi lưu điểm');
        }
    }

    /**
     * Đóng modal nhập điểm
     */
    public function closeScoreForm(): void
    {
        $this->showScoreForm = false;
        $this->resetScoreForm();
        $this->resetValidation();
    }

    /**
     * Reset form nhập điểm
     */
    protected function resetScoreForm(): void
    {
        $this->reset([
            // 'currentStudentClassId',
            // 'currentScoreTypeId',
            'scoreValue',
            'attempt',
            'scoreNote',
        ]);

        $this->attempt = 1;
    }

    // ==================== SCORE TYPE CONFIG ACTIONS ====================

    /**
     * Mở form tạo mới loại điểm
     */
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

    /**
     * Mở form edit loại điểm
     */
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
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading score type for edit', ['id' => $id]);
            session()->flash('error', 'Có lỗi khi tải thông tin loại điểm');
        }
    }

    /**
     * Lưu loại điểm
     */
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
                DB::rollBack();
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

            $this->emit('scoreTypeUpdated');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError($e, 'Error saving score type', [
                'editing_id' => $this->editingScoreTypeId,
                'type' => $this->scoreTypeType,
            ]);
            session()->flash('error', 'Có lỗi khi lưu loại điểm. Vui lòng thử lại.');
        }
    }

    /**
     * Xóa loại điểm
     */
    public function deleteScoreType(int $id): void
    {
        $this->requireAdmin(); // Chỉ Admin mới được xóa

        try {
            DB::beginTransaction();

            $scoreType = ScoreType::findOrFail($id);

            // Check if has scores
            $hasScores = StudentScore::where('score_type_id', $id)->exists();

            if ($hasScores) {
                DB::rollBack();
                session()->flash('error', 'Không thể xóa loại điểm đã có dữ liệu');
                return;
            }

            $scoreType->delete();

            DB::commit();

            session()->flash('message', 'Đã xóa loại điểm thành công');
            $this->loadScoresData();
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            session()->flash('error', 'Không tìm thấy loại điểm này');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError($e, 'Error deleting score type', ['id' => $id]);
            session()->flash('error', 'Có lỗi khi xóa loại điểm');
        }
    }

    /**
     * Toggle trạng thái loại điểm
     */
    public function toggleScoreTypeStatus(int $id): void
    {
        $this->requireManager();

        try {
            $scoreType = ScoreType::findOrFail($id);

            $scoreType->update(['is_active' => !$scoreType->is_active]);

            $message = $scoreType->is_active
                ? 'Đã kích hoạt loại điểm'
                : 'Đã tắt loại điểm';

            session()->flash('message', $message);

            $this->loadScoresData();
        } catch (ModelNotFoundException $e) {
            session()->flash('error', 'Không tìm thấy loại điểm này');
        } catch (\Exception $e) {
            $this->logError($e, 'Error toggling score type status', ['id' => $id]);
            session()->flash('error', 'Có lỗi khi thay đổi trạng thái loại điểm');
        }
    }

    /**
     * Đóng modal cấu hình loại điểm
     */
    public function closeScoreTypeForm(): void
    {
        $this->showScoreTypeForm = false;
        $this->resetScoreTypeForm();
        $this->resetValidation();
    }

    /**
     * Reset form loại điểm
     */
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

        // Set defaults
        $this->typeOrder = 0;
        $this->typeCoefficient = 1.0;
        $this->typeMaxScore = 10.0;
        $this->typeIsActive = true;
    }

    // ==================== EVENT HANDLERS ====================

    /**
     * Handle filters changed event từ FilterBar
     */
    public function handleFilterChanged($filters)
    {
        if (!is_array($filters)) {
            return;
        }

        // Năm học
        if (array_key_exists('namHoc', $filters)) {
            $newNamHoc = $this->sanitizeInteger($filters['namHoc']);

            if ($newNamHoc !== $this->selectedNamHoc) {
                $this->selectedNamHoc = $newNamHoc;
                $this->selectedLop = null;
                $this->resetPage();
                $this->loadLops();
            }
        }

        // Lớp
        if (array_key_exists('lop', $filters)) {
            $this->selectedLop = $this->sanitizeInteger($filters['lop']);
            $this->resetPage();
            $this->loadScoresData();
        }

        // Học kỳ
        if (array_key_exists('semester', $filters)) {
            $newSemester = is_numeric($filters['semester'])
                ? (int) $filters['semester']
                : 1;

            if (in_array($newSemester, [1, 2])) {
                $this->selectedSemester = $newSemester;
                $this->resetPage();
                $this->loadScoresData();
            }
        }
    }

    /**
     * Reset filters
     */
    public function resetFilters(): void
    {
        $this->selectedLop = null;
        $this->selectedSemester = 1;
        $this->scoreTypes = collect();
        $this->scoresMatrix = [];
        $this->resetPage();

        session()->flash('message', 'Đã đặt lại bộ lọc');
    }

    // ==================== HELPER METHODS ====================

    /**
     * Sanitize integer value
     */
    private function sanitizeInteger($value): ?int
    {
        if ($value === '' || $value === null) {
            return null;
        }

        return is_numeric($value) ? (int) $value : null;
    }

    /**
     * Get năm học mặc định (năm active mới nhất)
     */
    protected function getDefaultNamHocId(): ?int
    {
        return NamHoc::ofParish($this->parishId)
            ->active()
            ->orderByDesc('start_date_one')
            ->value('id');
    }

    /**
     * Get điểm của học sinh theo loại điểm
     */
    public function getScoreValue(int $studentClassId, int $scoreTypeId): ?float
    {
        return $this->scoresMatrix[$studentClassId][$scoreTypeId]['value'] ?? null;
    }

    /**
     * Get score data (bao gồm cả attempt, note)
     */
    public function getScoreData(int $studentClassId, int $scoreTypeId): ?array
    {
        return $this->scoresMatrix[$studentClassId][$scoreTypeId] ?? null;
    }

    /**
     * Tính điểm trung bình học kỳ của học sinh
     * TODO: Implement weighted average calculation
     */
    public function calculateStudentAverage(int $studentClassId): ?float
    {
        // Placeholder for future implementation
        return null;
    }

    // ==================== RENDER ====================

    /**
     * Render component
     */
    public function render()
    {
        $studentsPaginated = $this->getStudentsPaginated();

        return view('livewire.score.score-manager', [
            'students' => $studentsPaginated,
        ])
            ->extends('frontend.layout.main')
            ->section('content');
    }
}
