<?php

namespace App\Http\Livewire\CatechismClass;

use App\Http\Livewire\Base\BaseComponent;
use App\Models\CatechismClass;
use App\Models\GradeLevel;
use App\Models\NamHoc;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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

    protected array $allowedSortFields = ['name', 'grade_level_id', 'students_count', 'is_active'];

    public string $sortField = 'grade_level_id';
    public string $sortDirection = 'asc';

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
        'capacity'     => 'nullable|integer|min:0|max:999',
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
            'selectedNamHoc'     => ['as' => 'school-year', 'except' => null],
            'selectedGradeLevel' => ['as' => 'grade', 'except' => ''],
            'sortField'          => ['except' => 'grade_level_id', 'as' => 'sort'],
            'sortDirection'      => ['except' => 'asc', 'as' => 'dir'],
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

    // public function updatedSearch(): void
    // {
    //     parent::updatedSearch();
    // }

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
        $this->authorize('create', CatechismClass::class);

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
        $this->emit('openModal');
    }

    public function edit(int $id): void
    {
        $class = CatechismClass::where('school_year_id', $this->selectedNamHoc)
            ->findOrFail($id);

        $this->authorize('update', $class);

        try {
            $this->loadAvailableGradeLevels();

            $this->editingId       = $class->id;
            $this->name            = $class->name;
            $this->gradeLevelId    = $class->grade_level_id;
            $this->capacity        = $class->capacity;
            $this->isActive        = (bool) $class->is_active;

            $this->showForm = true;
            $this->emit('openModal');
        } catch (ModelNotFoundException $e) {
            session()->flash('error', 'Không tìm thấy lớp học này');
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading class for edit', ['id' => $id]);
            session()->flash('error', 'Có lỗi khi tải thông tin lớp học');
        }
    }

    public function save(): void
    {
        if ($this->editingId) {
            $class = CatechismClass::find($this->editingId);
            if (!$class) {
                session()->flash('error', 'Không tìm thấy lớp học này');
                return;
            }
            $this->authorize('update', $class);
        } else {
            $this->authorize('create', CatechismClass::class);
        }

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

    public function delete(int $id): void
    {
        try {
            $class = CatechismClass::where('school_year_id', $this->selectedNamHoc)
                ->findOrFail($id);

            $this->authorize('delete', $class);

            $studentCount = $class->students()->count();
            if ($studentCount > 0) {
                $this->emit(
                    'toast',
                    'error',
                    "Không thể xóa lớp «{$class->name}» vì còn {$studentCount} học sinh. Vui lòng chuyển học sinh sang lớp khác trước."
                );
                return;
            }

            DB::beginTransaction();
            $class->delete();
            DB::commit();

            $this->emit('toast', 'success', "Đã xóa lớp «{$class->name}» thành công");
        } catch (AuthorizationException $e) {
            $this->emit('toast', 'error', 'Bạn không có quyền xóa lớp học này');
        } catch (ModelNotFoundException $e) {
            $this->emit('toast', 'error', 'Không tìm thấy lớp học này');
        } catch (QueryException $e) {
            DB::rollBack();
            $this->logError($e, 'Error deleting class', ['id' => $id]);

            if ((string) $e->getCode() === '23000' || str_contains($e->getMessage(), 'foreign key constraint')) {
                $this->emit(
                    'toast',
                    'error',
                    'Không thể xóa lớp vì còn dữ liệu liên quan (học sinh, điểm danh hoặc điểm số).'
                );
            } else {
                $this->emit('toast', 'error', 'Có lỗi khi xóa lớp học. Vui lòng thử lại.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError($e, 'Error deleting class', ['id' => $id]);
            $this->emit('toast', 'error', 'Có lỗi khi xóa lớp học. Vui lòng thử lại.');
        }
    }

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
            $query = CatechismClass::with([
                'gradeLevel',
                'schoolYear',
                'teachers:id,saint_id,last_name,first_name',
                'teachers.saint:id,name'
            ])
                ->where('school_year_id', $this->selectedNamHoc)
                ->withCount('students', 'teachers');

            if ($this->selectedGradeLevel !== '') {
                $query->where('grade_level_id', $this->selectedGradeLevel);
            }

            if (!empty(trim($this->search))) {
                $searchTerm = '%' . trim($this->search) . '%';
                $query->where('classes.name', 'like', $searchTerm);
            }
            $this->applySorting($query);

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
        $this->emit('resetFilters');
    }

    // ==================== FORM HELPERS ====================

    public function closeModal(): void
    {
        $this->showForm = false;
        $this->emit('closeModal');
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
        return app(\App\Services\SchoolYearResolver::class)
            ->resolveId($this->parishId ? (int) $this->parishId : null);
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
