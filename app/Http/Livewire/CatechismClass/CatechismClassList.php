<?php

namespace App\Http\Livewire\CatechismClass;

use App\Http\Livewire\Base\BaseComponent;
use App\Models\CatechismClass;
use App\Models\GradeLevel;
use App\Models\NamHoc;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Component danh sách Lớp học (CatechismClass)
 *
 * Thay thế LopList cũ, dùng model CatechismClass / bảng `classes`.
 *
 * Mapping thay đổi so với LopList:
 *  - Lop            → CatechismClass
 *  - lop.pid        → classes.parish_id   (BelongsToParish global scope)
 *  - lop.schoolyear → classes.school_year_id
 *  - lop.block      → classes.grade_level_id
 *  - Block          → GradeLevel
 *  - lop.symbol     → (bỏ — không còn trong schema mới)
 *  - lop.status     → classes.is_active (boolean)
 *  - did/deid/paid  → (bỏ)
 *
 * Features:
 * - Phân trang với options
 * - Tìm kiếm theo tên lớp
 * - Lọc theo năm học, khối (grade level)
 * - Query string support (URL sharing)
 * - Auto-select năm học mới nhất
 * - CRUD với modal form
 */
class CatechismClassList extends BaseComponent
{
    // ==================== FILTERS ====================

    /** @var int|null Selected năm học ID */
    public $selectedNamHoc = null;

    /** @var int|string Selected grade level ('' = all) */
    public $selectedGradeLevel = '';

    // ==================== FORM STATE ====================

    /** @var bool Hiển thị modal form */
    public $showForm = false;

    /** @var int|null ID lớp đang edit (null = create) */
    public $editingId = null;

    // ==================== FORM FIELDS ====================

    /** @var string Tên lớp */
    public $name;

    /** @var int|null ID khối (grade_level_id) */
    public $gradeLevelId;

    /** @var int|null Sĩ số tối đa */
    public $capacity;

    /** @var bool Trạng thái */
    public $isActive = true;

    // ==================== DATA ====================

    /** @var \Illuminate\Support\Collection Danh sách khối có thể chọn */
    public $availableGradeLevels;

    // ==================== VALIDATION ====================

    protected $rules = [
        'selectedNamHoc'   => 'nullable|integer|exists:nam_hoc,id',
        'selectedGradeLevel' => 'nullable|integer',
        'search'           => 'nullable|string|max:255',
        'perPage'          => 'required|integer|in:10,15,25,50',
    ];

    /**
     * Rules riêng cho form — chỉ dùng khi save
     */
    protected $formRules = [
        'name'         => 'required|string|max:255',
        'gradeLevelId' => 'required|integer|exists:grade_levels,id',
        'capacity'     => 'nullable|integer|min:1|max:999',
        'isActive'     => 'required|boolean',
    ];

    /**
     * Custom validation messages
     */
    protected $messages = [
        'selectedNamHoc.exists'      => 'Năm học không tồn tại',
        'selectedGradeLevel.integer' => 'Khối không hợp lệ',
        'search.max'                 => 'Tìm kiếm không được quá 255 ký tự',
        'perPage.in'                 => 'Số mục trên trang không hợp lệ',
        'name.required'              => 'Vui lòng nhập tên lớp',
        'name.max'                   => 'Tên lớp không được quá 255 ký tự',
        'gradeLevelId.required'      => 'Vui lòng chọn khối học',
        'gradeLevelId.exists'        => 'Khối học không tồn tại',
        'capacity.integer'           => 'Sĩ số phải là số nguyên',
        'capacity.min'               => 'Sĩ số tối thiểu là 1',
        'capacity.max'               => 'Sĩ số không được quá 999',
    ];

    // ==================== QUERY STRING ====================

    protected function queryString()
    {
        return array_merge([
            'selectedNamHoc'    => ['as' => 'school-year', 'except' => null],
            'selectedGradeLevel' => ['as' => 'grade', 'except' => ''],
        ], parent::queryString());
    }

    // ==================== LISTENERS ====================

    protected $listeners = [
        'refresh'              => 'handleRefresh',
        'filterChanged'        => 'handleFilterChanged',
        'classCreated'         => '$refresh',
        'classUpdated'         => '$refresh',
    ];

    // ==================== LIFECYCLE ====================

    public function mount()
    {
        $this->authorize('viewAny', CatechismClass::class);
        parent::mount();
    }

    protected function loadInitialData(): void
    {
        if (!$this->selectedNamHoc) {
            $this->selectedNamHoc = $this->getDefaultNamHocId();
        }

        $this->availableGradeLevels = collect();

        if ($this->selectedNamHoc) {
            $this->loadAvailableGradeLevels();
        }
    }

    protected function sanitizeQueryString(): void
    {
        parent::sanitizeQueryString();

        $this->selectedNamHoc = ($this->selectedNamHoc !== '' && $this->selectedNamHoc !== null && is_numeric($this->selectedNamHoc))
            ? (int) $this->selectedNamHoc
            : null;

        $this->selectedGradeLevel = ($this->selectedGradeLevel !== '' && $this->selectedGradeLevel !== null && is_numeric($this->selectedGradeLevel))
            ? (int) $this->selectedGradeLevel
            : '';
    }

    protected function resetToDefaults(): void
    {
        parent::resetToDefaults();
        $this->selectedGradeLevel = '';
    }

    // ==================== PROPERTY UPDATERS ====================

    public function updatedSearch(): void
    {
        parent::updatedSearch();
    }

    public function updatedSelectedNamHoc(): void
    {
        $this->selectedNamHoc = is_numeric($this->selectedNamHoc)
            ? (int) $this->selectedNamHoc
            : null;

        try {
            $this->validateOnly('selectedNamHoc');
        } catch (ValidationException $e) {
            $this->selectedNamHoc = null;
            session()->flash('warning', 'Năm học không hợp lệ.');
            $this->logError($e, 'Invalid selectedNamHoc');
        }

        $this->selectedGradeLevel = '';
        $this->search = '';
        $this->resetPage();

        $this->loadAvailableGradeLevels();
    }

    public function updatedSelectedGradeLevel(): void
    {
        $this->selectedGradeLevel = ($this->selectedGradeLevel !== '' && is_numeric($this->selectedGradeLevel))
            ? (int) $this->selectedGradeLevel
            : '';

        if ($this->selectedGradeLevel !== '') {
            try {
                $this->validateOnly('selectedGradeLevel');
            } catch (ValidationException $e) {
                $this->selectedGradeLevel = '';
                session()->flash('warning', 'Khối không hợp lệ.');
            }
        }

        $this->resetPage();
    }

    // ==================== CRUD ACTIONS ====================

    public function create(): void
    {
        $this->requireManager();

        if (!$this->selectedNamHoc) {
            session()->flash('warning', 'Vui lòng chọn năm học trước');
            return;
        }

        $this->loadAvailableGradeLevels();

        if ($this->availableGradeLevels->isEmpty()) {
            session()->flash('warning', 'Chưa có khối học nào. Vui lòng tạo khối học trước');
            return;
        }

        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $this->requireManager();

        try {
            // BelongsToParish global scope tự filter parish_id
            $class = CatechismClass::where('school_year_id', $this->selectedNamHoc)
                ->findOrFail($id);

            $this->loadAvailableGradeLevels();

            $this->editingId       = $class->id;
            $this->name            = $class->name;
            $this->gradeLevelId    = $class->grade_level_id;
            $this->capacity        = $class->capacity;
            $this->isActive        = (bool) $class->is_active;

            $this->showForm = true;
        } catch (ModelNotFoundException $e) {
            session()->flash('error', 'Không tìm thấy lớp học này');
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading class for edit', ['id' => $id]);
            session()->flash('error', 'Có lỗi khi tải thông tin lớp học');
        }
    }

    public function save(): void
    {
        $this->requireManager();

        if (!$this->selectedNamHoc) {
            session()->flash('error', 'Vui lòng chọn năm học');
            return;
        }

        $this->validate($this->formRules, $this->messages);

        if (!$this->validateUniqueName()) {
            session()->flash('error', 'Tên lớp đã tồn tại trong khối và năm học này');
            return;
        }

        try {
            DB::beginTransaction();

            CatechismClass::updateOrCreate(
                ['id' => $this->editingId],
                [
                    'parish_id'       => $this->parishId,
                    'school_year_id'  => $this->selectedNamHoc,
                    'grade_level_id'  => $this->gradeLevelId,
                    'name'            => $this->name,
                    'capacity'        => $this->capacity ?: null,
                    'is_active'       => $this->isActive,
                ]
            );

            DB::commit();

            session()->flash(
                'message',
                $this->editingId
                    ? 'Cập nhật lớp học thành công'
                    : 'Tạo lớp học mới thành công'
            );

            $this->emit($this->editingId ? 'classUpdated' : 'classCreated');

            $this->resetForm();
            $this->closeModal();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError($e, 'Error saving class', [
                'editing_id'      => $this->editingId,
                'name'            => $this->name,
                'grade_level_id'  => $this->gradeLevelId,
            ]);
            session()->flash('error', 'Có lỗi khi lưu dữ liệu. Vui lòng thử lại.');
        }
    }

    // public function toggleStatus(int $id): void
    // {
    //     $this->requireManager();

    //     try {
    //         $class = CatechismClass::where('school_year_id', $this->selectedNamHoc)
    //             ->findOrFail($id);

    //         $class->update(['is_active' => !$class->is_active]);

    //         session()->flash(
    //             'message',
    //             $class->is_active
    //                 ? 'Đã kích hoạt lớp học'
    //                 : 'Đã tắt lớp học'
    //         );
    //     } catch (ModelNotFoundException $e) {
    //         session()->flash('error', 'Không tìm thấy lớp học này');
    //     } catch (\Exception $e) {
    //         $this->logError($e, 'Error toggling class status', ['id' => $id]);
    //         session()->flash('error', 'Có lỗi khi thay đổi trạng thái lớp học');
    //     }
    // }


    // ==================== DATA LOADING ====================

    protected function loadAvailableGradeLevels(): void
    {
        try {
            $this->availableGradeLevels = GradeLevel::orderBy('sort_order')
                ->get(['id', 'name']);
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading available grade levels');
            $this->availableGradeLevels = collect();
        }
    }

    private function getClassesPaginated()
    {
        if (!$this->selectedNamHoc) {
            return new \Illuminate\Pagination\LengthAwarePaginator(
                [],
                0,
                $this->perPage,
                $this->page ?? 1
            );
        }

        try {

            // BelongsToParish global scope đã filter parish_id tự động
            $query = CatechismClass::with(['gradeLevel', 'schoolYear', 'teachers'])
                ->where('school_year_id', $this->selectedNamHoc)
                ->withCount('students')
                ->withCount('teachers');

            if ($this->selectedGradeLevel !== '') {
                $query->where('grade_level_id', $this->selectedGradeLevel);
            }

            if (!empty(trim($this->search))) {
                $searchTerm = '%' . trim($this->search) . '%';
                $query->where('classes.name', 'like', $searchTerm);
            }

            // Order theo thứ tự khối rồi tên lớp
            // $query->join('grade_levels', 'classes.grade_level_id', '=', 'grade_levels.id')
            //     ->orderBy('grade_levels.sort_order', 'asc')
            //     ->orderBy('classes.name', 'asc')
            //     ->select('classes.*');

            return $query->paginate($this->perPage);
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading classes', [
                'school_year_id'  => $this->selectedNamHoc,
                'grade_level_id'  => $this->selectedGradeLevel,
                'search'          => $this->search,
            ]);

            session()->flash('error', 'Có lỗi khi tải danh sách lớp học.');

            return new \Illuminate\Pagination\LengthAwarePaginator(
                [],
                0,
                $this->perPage,
                $this->page ?? 1
            );
        }
    }



    // ==================== EVENT HANDLERS ====================

    public function handleFilterChanged($filters): void
    {
        if (!is_array($filters)) {
            return;
        }

        $namHocChanged = false;

        if (array_key_exists('namHoc', $filters)) {
            $newNamHoc = is_numeric($filters['namHoc']) ? (int) $filters['namHoc'] : null;

            if ($newNamHoc !== $this->selectedNamHoc) {
                $this->selectedNamHoc = $newNamHoc;
                $namHocChanged = true;
            }
        }

        if ($namHocChanged) {
            $this->selectedGradeLevel = '';
            $this->loadAvailableGradeLevels();
        } elseif (array_key_exists('khoi', $filters)) {
            $this->selectedGradeLevel = is_numeric($filters['khoi'])
                ? (int) $filters['khoi']
                : '';
        }

        $this->search = '';
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->selectedGradeLevel = '';
        $this->search = '';
        $this->resetPage();
        session()->flash('message', 'Đã đặt lại bộ lọc');
    }

    // ==================== FORM HELPERS ====================

    public function closeModal(): void
    {
        $this->showForm = false;
        $this->resetForm();
        $this->resetValidation();
    }

    public function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'gradeLevelId', 'capacity']);
        $this->isActive = true;
        $this->resetValidation();
    }

    // ==================== HELPER METHODS ====================

    /**
     * Validate tên lớp không trùng trong cùng khối + năm học
     */
    protected function validateUniqueName(): bool
    {
        return !CatechismClass::where('school_year_id', $this->selectedNamHoc)
            ->where('grade_level_id', $this->gradeLevelId)
            ->where('name', $this->name)
            ->when($this->editingId, fn($q) => $q->where('id', '!=', $this->editingId))
            ->exists();
    }

    protected function getDefaultNamHocId(): ?int
    {
        return NamHoc::ofParish($this->parishId)
            ->active()
            ->orderByDesc('name')
            ->value('id');
    }

    // ==================== RENDER ====================

    public function render()
    {
        $classes = $this->getClassesPaginated();

        return view('livewire.catechism-class.catechism-class-list', [
            'classes'  => $classes,
            'parishId' => $this->parishId,
        ])
            ->extends('frontend.layout.main')
            ->section('content');
    }
}
