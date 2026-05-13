<?php

namespace App\Http\Livewire\Student;

use App\Exports\StudentExport;
use App\Http\Livewire\Base\BaseComponent;
use App\Models\CatechismClass;
use App\Models\Parishioner;
use App\Models\StudentNew;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StudentListNew extends BaseComponent
{
    // ==================== FILTERS ====================
    public $selectedNamHoc = null;
    public $selectedKhoi = null;
    public $selectedLop = null;

    // ==================== SELECTION ====================
    public $selectedStudents = [];
    public $selectAll = false;

    // ==================== MODAL GHI DANH (3 tabs) ====================
    public $studentsToAdd = [];
    public $selectAllInModal = false;
    public $modalSearch = '';

    // ==================== BIRTH YEAR FILTER ====================
    public $birthYear = null;

    // ==================== IMPORT FROM PARISHIONERS ====================
    public $selectedParishioners = [];
    public $selectAllParishioners = false;
    public $parishionerSearch = '';
    public $parishionerBirthYear = null;
    public ?int $ageFrom = null;
    public ?int $ageTo = null;

    // ==================== ENROLL NEW STUDENT ====================
    public $showEnrollNewModal = false;
    public $enrollTab = 'existing';

    protected array $allowedSortFields = ['last_name', 'first_name', 'birthday', 'gender'];

    public string $sortField = 'first_name';
    public string $sortDirection = 'asc';

    // ==================== CACHE ====================
    protected $lopCache = null;

    // ==================== PARISHIONER LINKING ====================
    public $suggestedParishioners;
    public $linkingStudentId = null;
    public $showLinkModal = false;

    // ==================== VALIDATION ====================
    protected $rules = [
        'selectedNamHoc'     => 'nullable|integer|exists:nam_hoc,id',
        'selectedKhoi'       => 'nullable|integer|exists:classes,grade_level_id',
        'selectedLop'        => 'nullable|integer|exists:classes,id',
        'search'             => 'nullable|string|max:255',
        'perPage'            => 'required|integer|in:10,15,25,50,100',
        'selectedStudents'   => 'nullable|array',
        'selectedStudents.*' => 'integer',
        'studentsToAdd'      => 'nullable|array',
        'studentsToAdd.*'    => 'integer|exists:students,id',
        'modalSearch'        => 'nullable|string|max:255',
        'birthYear'          => 'nullable|integer|min:1900|max:' . PHP_INT_MAX,
    ];

    protected $messages = [
        'selectedNamHoc.exists'    => 'Năm học không tồn tại',
        'selectedKhoi.exists'      => 'Khối không tồn tại',
        'selectedLop.exists'       => 'Lớp không tồn tại',
        'search.max'               => 'Tìm kiếm không được quá 255 ký tự',
        'perPage.in'               => 'Số mục trên trang không hợp lệ',
        'studentsToAdd.*.exists'   => 'Học sinh không tồn tại',
        'modalSearch.max'          => 'Tìm kiếm không được quá 255 ký tự',
        'birthYear.integer'        => 'Năm sinh phải là số',
        'birthYear.min'            => 'Năm sinh không hợp lệ',
    ];

    // ==================== QUERY STRING ====================
    protected function queryString()
    {
        return array_merge([
            'selectedNamHoc' => ['as' => 'school-year', 'except' => null],
            'selectedKhoi'   => ['as' => 'grade', 'except' => ''],
            'selectedLop'    => ['as' => 'class', 'except' => ''],
            'sortField'      => ['except' => 'last_name', 'as' => 'sort'],
            'sortDirection'  => ['except' => 'asc', 'as' => 'dir'],
        ], parent::queryString());
    }

    // ==================== LISTENERS ====================
    protected $listeners = [
        'refresh'         => 'handleRefresh',
        'filterChanged'   => 'handleFilterChanged',
        'refreshStudents' => 'handleRefresh',
    ];

    // ==================== LIFECYCLE ====================
    public function mount(): void
    {
        $this->authorize('viewAny', StudentNew::class);
        parent::mount();
        $this->requireParishId();
    }

    protected function loadInitialData(): void
    {
        if (!$this->selectedNamHoc) {
            $this->selectedNamHoc = $this->getDefaultNamHocId();
        }

        if (!$this->selectedLop) {
            // Catechist → dùng defaultClassId từ BaseComponent
            // Không catechist → fallback lớp đầu tiên của năm học
            $this->selectedLop = $this->defaultClassId
                ?? CatechismClass::where('school_year_id', $this->selectedNamHoc)
                ->orderBy('id')
                ->value('id');
        }
    }

    // ==================== HELPERS ====================
    protected function sanitizeQueryString(): void
    {
        parent::sanitizeQueryString();

        $this->selectedNamHoc = $this->selectedNamHoc && is_numeric($this->selectedNamHoc)
            ? (int) $this->selectedNamHoc
            : null;

        $this->selectedKhoi = $this->selectedKhoi && is_numeric($this->selectedKhoi)
            ? (int) $this->selectedKhoi
            : null;

        $this->selectedLop = $this->selectedLop && is_numeric($this->selectedLop)
            ? (int) $this->selectedLop
            : null;
    }

    protected function resetToDefaults(): void
    {
        parent::resetToDefaults();
        $this->selectedKhoi = null;
        $this->selectedLop  = null;
    }

    // ==================== PROPERTY UPDATERS ====================

    public function updatedSearch(): void
    {
        $this->search = trim($this->search);

        try {
            $this->validateOnly('search');
        } catch (ValidationException $e) {
            $this->search = '';
            $this->emit('toast', 'warning', 'Từ khóa tìm kiếm không hợp lệ.');
        }

        $this->resetPage();
        $this->resetSelection();
    }

    public function updatedModalSearch(): void
    {
        $this->modalSearch      = trim($this->modalSearch);
        $this->selectAllInModal = false;
        $this->studentsToAdd    = [];
        $this->resetPage('modal_page');
    }

    public function updatedBirthYear(): void
    {
        $this->validateOnly('birthYear');
        $this->resetPage('modal_page');
    }

    public function updatedSelectedNamHoc(): void
    {
        $this->selectedNamHoc = $this->selectedNamHoc && is_numeric($this->selectedNamHoc)
            ? (int) $this->selectedNamHoc
            : null;

        try {
            $this->validateOnly('selectedNamHoc');
        } catch (ValidationException $e) {
            $this->selectedNamHoc = null;
            $this->emit('toast', 'warning', 'Năm học không hợp lệ.');
        }

        $this->selectedKhoi = null;
        $this->selectedLop  = null;
        $this->search       = '';
        $this->resetPage();
        $this->resetSelection();
    }

    public function updatedSelectedKhoi(): void
    {
        $this->selectedKhoi = $this->selectedKhoi && is_numeric($this->selectedKhoi)
            ? (int) $this->selectedKhoi
            : null;

        if ($this->selectedKhoi) {
            try {
                $this->validateOnly('selectedKhoi');
            } catch (ValidationException $e) {
                $this->selectedKhoi = null;
                $this->emit('toast', 'warning', 'Khối không hợp lệ.');
            }
        }

        $this->selectedLop = null;
        $this->resetPage();
        $this->resetSelection();
    }

    public function updatedSelectedLop(): void
    {
        $this->lopCache    = null;
        $this->selectedLop = $this->selectedLop && is_numeric($this->selectedLop)
            ? (int) $this->selectedLop
            : null;

        if ($this->selectedLop) {
            try {
                $this->validateOnly('selectedLop');
            } catch (ValidationException $e) {
                $this->selectedLop = null;
                $this->emit('toast', 'warning', 'Lớp không hợp lệ.');
            }
        }

        $this->resetPage();
        $this->resetSelection();
    }

    public function updatedSelectAll($value): void
    {
        if ($value) {
            $this->selectedStudents = $this->getCurrentStudentsQuery()
                ->pluck('id')
                ->map(fn($id) => (int) $id)
                ->toArray();
        } else {
            $this->selectedStudents = [];
        }
    }

    public function updatedSelectedStudents(): void
    {
        $this->selectedStudents = array_values(
            array_unique(
                array_map('intval', array_filter($this->selectedStudents, 'is_numeric'))
            )
        );

        $totalCount    = $this->getCurrentStudentsQuery()->count();
        $selectedCount = count($this->selectedStudents);

        $this->selectAll = $totalCount > 0 && $selectedCount >= $totalCount;
    }

    public function updatedSelectAllInModal($value): void
    {
        if ($value) {
            $this->studentsToAdd = $this->getAvailableStudentsQuery()
                ->pluck('id')
                ->map(fn($id) => (int) $id)
                ->toArray();
        } else {
            $this->studentsToAdd = [];
        }
    }

    public function updatedStudentsToAdd(): void
    {
        $this->studentsToAdd = array_values(
            array_unique(
                array_map('intval', array_filter($this->studentsToAdd, 'is_numeric'))
            )
        );

        $availableIds  = $this->getAvailableStudentsQuery()->pluck('id')->toArray();
        $selectedCount = count(array_intersect($this->studentsToAdd, $availableIds));
        $totalCount    = count($availableIds);

        $this->selectAllInModal = $totalCount > 0 && $selectedCount === $totalCount;
    }

    public function updatedSelectAllParishioners($value): void
    {
        if ($value) {
            $this->selectedParishioners = $this->getAvailableParishionersQuery()
                ->pluck('id')
                ->toArray();
        } else {
            $this->selectedParishioners = [];
        }
    }

    // ==================== PARISHIONER LINKING ====================

    public function openLinkParishioner(int $studentId): void
    {
        $this->authorize('update', StudentNew::findOrFail($studentId));

        try {
            $student = StudentNew::findOrFail($studentId);
            $this->linkingStudentId      = $studentId;
            $this->suggestedParishioners = $this->findSuggestedParishioners($student);
            $this->showLinkModal         = true;
        } catch (ModelNotFoundException $e) {
            $this->emit('toast', 'error', 'Không tìm thấy học sinh này');
        }
    }

    protected function findSuggestedParishioners(StudentNew $student): \Illuminate\Support\Collection
    {
        $linkedIds = StudentNew::whereNotNull('parishioner_id')
            ->pluck('parishioner_id');

        $candidates = Parishioner::ofParish($this->parishId)
            ->whereNotIn('id', $linkedIds)
            ->where(function ($q) use ($student) {
                $q->whereRaw(
                    "LOWER(CONCAT(last_name, ' ', first_name)) LIKE ?",
                    ['%' . strtolower(trim($student->last_name . ' ' . $student->name)) . '%']
                );
            })
            ->with('saint')
            ->limit(20)
            ->get(['id', 'last_name', 'first_name', 'saint_id', 'gender', 'birthday', 'avatar_path', 'cccd', 'phone']);

        return $candidates->filter(function ($p) use ($student) {
            return strtolower($p->full_name_with_saint) === strtolower($student->full_name_with_saint);
        })->take(5)->values();
    }

    public function confirmLink(int $parishionerId): void
    {
        $this->authorize('update', StudentNew::findOrFail($this->linkingStudentId));

        try {
            DB::beginTransaction();

            $student     = StudentNew::findOrFail($this->linkingStudentId);
            $parishioner = Parishioner::ofParish($this->parishId)->findOrFail($parishionerId);

            $student->update(['parishioner_id' => $parishioner->id]);

            DB::commit();

            $this->emit('toast', 'message', "Đã liên kết {$student->name} với giáo dân {$parishioner->full_name}");
            $this->closeLinkModal();
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            $this->emit('toast', 'error', 'Không tìm thấy dữ liệu');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError($e, 'Error linking parishioner to student');
            $this->emit('toast', 'error', 'Có lỗi khi liên kết');
        }
    }

    public function skipLink(): void
    {
        $this->emit('toast', 'info', 'Đã bỏ qua liên kết giáo dân');
        $this->closeLinkModal();
    }

    public function unlinkParishioner(int $studentId): void
    {
        $this->authorize('update', StudentNew::findOrFail($studentId));

        try {
            StudentNew::findOrFail($studentId)->update(['parishioner_id' => null]);
            $this->emit('toast', 'message', 'Đã hủy liên kết giáo dân');
        } catch (\Exception $e) {
            $this->logError($e, 'Error unlinking parishioner');
            $this->emit('toast', 'error', 'Có lỗi khi hủy liên kết');
        }
    }

    public function closeLinkModal(): void
    {
        $this->showLinkModal         = false;
        $this->linkingStudentId      = null;
        $this->suggestedParishioners = collect();
    }

    // ==================== IMPORT FROM PARISHIONERS ====================
    private function getAvailableParishionersQuery()
    {
        if (!$this->selectedNamHoc) {
            return Parishioner::whereRaw('1 = 0');
        }

        return Parishioner::query()
            ->active()
            ->whereDoesntHave('student')
            ->when($this->parishionerBirthYear, function ($q) {
                $q->whereYear('birthday', $this->parishionerBirthYear);
            })
            ->when(trim($this->parishionerSearch), function ($q, $search) {
                $q->where(function ($q2) use ($search) {
                    $q2->where('last_name', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('cccd', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhereRaw("CONCAT(last_name, ' ', first_name) LIKE ?", ["%{$search}%"]);
                });
            })
            ->when($this->ageFrom || $this->ageTo, function ($q) {
                $from = $this->ageTo   ? now()->subYears($this->ageTo)->startOfDay()   : null;
                $to   = $this->ageFrom ? now()->subYears($this->ageFrom)->endOfDay()   : null;

                if ($from && $to) {
                    $q->whereBetween('birthday', [$from, $to]);
                } elseif ($from) {
                    $q->where('birthday', '<=', $from);
                } elseif ($to) {
                    $q->where('birthday', '>=', $to);
                }
            })
            ->orderBy('first_name')
            ->orderBy('last_name');
    }

    public function getImportUrlProperty(): string
    {
        return route('students.import') . ($this->selectedLop ? '?classId=' . $this->selectedLop : '');
    }

    private function getAvailableParishionersPaginated()
    {
        return $this->getAvailableParishionersQuery()
            ->paginate(15, ['*'], 'parishioner_page');
    }

    public function importParishionersToStudents(): void
    {
        $this->authorize('create', StudentNew::class);

        if (empty($this->selectedParishioners)) {
            $this->emit('toast', 'warning', 'Vui lòng chọn ít nhất 1 giáo dân');
            return;
        }

        try {
            DB::beginTransaction();

            $catechismClass = CatechismClass::findOrFail($this->selectedLop);
            $successCount   = 0;
            $errorCount     = 0;
            $errors         = [];

            foreach (Parishioner::whereIn('id', $this->selectedParishioners)->get() as $p) {
                try {
                    $student = StudentNew::create([
                        'first_name'      => $p->first_name,
                        'last_name'       => $p->last_name,
                        'saint_id'        => $p->holy ?? null,
                        'gender'          => $p->sex == 1 ? 'male' : 'female',
                        'birthday'        => $p->birthday?->format('Y-m-d'),
                        'phone'           => $p->phone,
                        'email'           => $p->email,
                        'father_name'     => $p->father ?? null,
                        'mother_name'     => $p->mother ?? null,
                        'parishioner_id'  => $p->id,
                        'parish_id'       => $this->parishId,
                        'parish_group_id' => $p->paid ?? null,
                        'note'            => $p->note ?? null,
                        'is_active'       => true,
                    ]);

                    $student->classes()->attach($catechismClass->id, [
                        'enrolled_at' => now(),
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);

                    $successCount++;
                } catch (\Exception $e) {
                    $errors[] = "{$p->last_name} {$p->name}: {$e->getMessage()}";
                    $errorCount++;
                    $this->logError($e, 'Error creating StudentNew from parishioner', [
                        'parishioner_id' => $p->id,
                    ]);
                }
            }

            DB::commit();

            $message = "✅ Đã import {$successCount} học sinh thành công";
            if ($errorCount > 0) {
                $message .= " | ❌ {$errorCount} lỗi";
            }
            $this->emit('toast', 'message', $message);

            if (!empty($errors)) {
                $detail = '<strong>Chi tiết lỗi:</strong><br>' . implode('<br>', array_slice($errors, 0, 5));
                if (count($errors) > 5) {
                    $detail .= '<br><em>... và ' . (count($errors) - 5) . ' lỗi khác</em>';
                }
                $this->emit('toast', 'warning', $detail);
            }

            $this->closeEnrollModal();
            $this->emit('refreshStudents');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError($e, 'Error importing parishioners to StudentNew', [
                'selected_count' => count($this->selectedParishioners),
                'lop_id'         => $this->selectedLop,
            ]);
            $this->emit('toast', 'error', 'Có lỗi khi import học sinh: ' . $e->getMessage());
        }
    }

    // ==================== ENROLL NEW STUDENT ====================

    public function openEnrollModal(string $tab = 'existing'): void
    {
        $this->authorize('create', StudentNew::class);

        if (!$this->selectedLop) {
            $this->emit('toast', 'warning', 'Vui lòng chọn lớp trước khi ghi danh');
            return;
        }

        $this->enrollTab = $tab;
        $this->resetEnrollForm();
        $this->showEnrollNewModal = true;
    }

    public function closeEnrollModal(): void
    {
        $this->showEnrollNewModal = false;
        $this->resetEnrollForm();
        $this->resetValidation();
    }

    public function switchEnrollTab(string $tab): void
    {
        $this->enrollTab = $tab;
        $this->resetEnrollForm();
        $this->resetValidation();
    }

    private function resetEnrollForm(): void
    {
        $this->studentsToAdd     = [];
        $this->selectAllInModal  = false;
        $this->modalSearch       = '';
        $this->birthYear         = null;
    }

    // ==================== ADD STUDENTS MODAL ====================
    public function addStudentsToClass(): void
    {
        $this->authorize('create', StudentNew::class);

        if (empty($this->studentsToAdd)) {
            $this->emit('toast', 'warning', 'Vui lòng chọn ít nhất một học sinh');
            return;
        }

        $this->validate([
            'studentsToAdd'   => 'required|array|min:1',
            'studentsToAdd.*' => 'integer|exists:students,id',
        ]);

        try {
            DB::beginTransaction();

            $catechismClass     = CatechismClass::findOrFail($this->selectedLop);
            $existingStudentIds = $catechismClass->students()->pluck('students.id')->toArray();
            $newStudentIds      = array_diff($this->studentsToAdd, $existingStudentIds);

            if (empty($newStudentIds)) {
                $this->emit('toast', 'warning', 'Tất cả học sinh đã có trong lớp này');
                return;
            }

            $catechismClass->students()->attach($newStudentIds, [
                'enrolled_at' => now(),
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            DB::commit();

            $this->emit('toast', 'message', 'Đã thêm ' . count($newStudentIds) . ' học sinh vào lớp thành công');

            $this->closeEnrollModal();
            $this->emit('refreshStudents');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError($e, 'Error adding StudentNew to class', [
                'lop_id'      => $this->selectedLop,
                'student_ids' => $this->studentsToAdd,
            ]);
            $this->emit('toast', 'error', 'Có lỗi khi thêm học sinh vào lớp. Vui lòng thử lại.');
        }
    }

    public function delete(int $studentId): void
    {
        try {
            $student = StudentNew::findOrFail($studentId);

            $this->authorize('delete', $student);

            DB::beginTransaction();

            if ($this->selectedLop) {
                // Đã chọn lớp cụ thể → xóa khỏi lớp đó
                CatechismClass::findOrFail($this->selectedLop)
                    ->students()
                    ->detach($studentId);
            } elseif ($this->selectedNamHoc) {
                // Chỉ có năm học → xóa khỏi tất cả lớp trong năm đó
                $classIds = CatechismClass::where('school_year_id', $this->selectedNamHoc)
                    ->pluck('id');

                \App\Models\StudentsClass::where('student_id', $studentId)
                    ->whereIn('class_id', $classIds)
                    ->delete();
            } else {
                $this->emit('toast', 'error', 'Vui lòng chọn năm học trước khi xóa');
                return;
            }

            DB::commit();

            $this->emit('toast', 'message', 'Đã xóa học sinh khỏi lớp thành công');
            $this->emit('refreshStudents');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->emit('toast', 'error', 'Bạn không có quyền xóa học sinh này');
        } catch (ModelNotFoundException $e) {
            $this->emit('toast', 'error', 'Không tìm thấy học sinh hoặc lớp học');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError($e, 'Error deleting StudentNew from class', [
                'lop_id'     => $this->selectedLop,
                'student_id' => $studentId,
            ]);
            $this->emit('toast', 'error', 'Có lỗi khi xóa học sinh khỏi lớp. Vui lòng thử lại.');
        }
    }

    // ==================== QUERY HELPERS ====================

    protected function getCurrentStudentsQuery()
    {
        $query = StudentNew::with(['saint', 'parishGroup']);

        if ($this->selectedNamHoc) {
            $query->whereHas('classes', function ($q) {
                $q->where('classes.school_year_id', $this->selectedNamHoc);
            });
        }

        if ($this->selectedKhoi) {
            $query->whereHas('classes', function ($q) {
                $q->where('classes.grade_level_id', $this->selectedKhoi);
            });
        }

        if ($this->selectedLop) {
            $query->whereHas('classes', function ($q) {
                $q->where('classes.id', $this->selectedLop);
            });
        }

        if (!empty(trim($this->search))) {
            $search = trim($this->search);
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('student_code', 'like', "%{$search}%")
                    ->orWhereRaw("CONCAT(last_name, ' ', first_name) LIKE ?", ["%{$search}%"])
                    ->orWhereHas('saint', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $this->applySorting($query);

        return $query;
    }

    protected function getAvailableStudentsQuery()
    {
        if (!$this->selectedLop) {
            return StudentNew::whereRaw('1 = 0');
        }

        $query = StudentNew::with(['saint'])
            ->where('is_active', true)
            ->whereDoesntHave('classes', function ($q) {
                $q->where('classes.school_year_id', $this->selectedNamHoc);
            });

        if ($this->birthYear) {
            $query->whereYear('birthday', $this->birthYear);
        }

        if (!empty(trim($this->modalSearch))) {
            $search = trim($this->modalSearch);
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('student_code', 'like', "%{$search}%")
                    ->orWhereRaw("CONCAT(last_name, ' ', first_name) LIKE ?", ["%{$search}%"])
                    ->orWhereHas('saint', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    });

                if (is_numeric($search) && strlen($search) === 4) {
                    $q->orWhereYear('birthday', (int) $search);
                }
            });
        }

        return $query->orderBy('birthday', 'desc')
            ->orderBy('last_name')
            ->orderBy('first_name');
    }

    protected function getAvailableStudentsPaginated(): LengthAwarePaginator
    {
        try {
            return $this->getAvailableStudentsQuery()
                ->paginate(15, ['*'], 'modal_page');
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading available StudentNew');
            return new LengthAwarePaginator([], 0, 15, 1);
        }
    }

    protected function getStudentsPaginated(): LengthAwarePaginator
    {
        try {
            return $this->getCurrentStudentsQuery()
                ->paginate($this->perPage);
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading StudentNew list', [
                'namhoc' => $this->selectedNamHoc,
                'khoi'   => $this->selectedKhoi,
                'lop'    => $this->selectedLop,
                'search' => $this->search,
            ]);
            $this->emit('toast', 'error', 'Có lỗi khi tải danh sách học viên.');
            return new LengthAwarePaginator([], 0, $this->perPage, $this->page ?? 1);
        }
    }

    // Trong StudentListNew.php — thêm method này
    public function printSelected(): void
    {
        if (empty($this->selectedStudents) && !$this->selectedLop) {
            $this->emit('toast', 'warning', 'Vui lòng chọn học sinh hoặc lớp');
            return;
        }

        if (!empty($this->selectedStudents)) {
            // In những học sinh đã checkbox
            $ids = implode(',', $this->selectedStudents);
            $this->redirect(route('students.print-cards', ['ids' => $ids]));
        } else {
            // Không chọn ai → in cả lớp
            $this->redirect(route('students.print-cards', ['classId' => $this->selectedLop]));
        }
    }

    // ==================== STATISTICS ====================

    protected function getGenderStats(): array
    {
        try {
            $stats = $this->getCurrentStudentsQuery()
                ->selectRaw('gender, COUNT(*) as total')
                ->groupBy('gender')
                ->pluck('total', 'gender');

            return [
                'total'    => ($stats['male'] ?? 0) + ($stats['female'] ?? 0),
                'countnam' => $stats['male'] ?? 0,
                'countnu'  => $stats['female'] ?? 0,
            ];
        } catch (\Exception $e) {
            $this->logError($e, 'Error calculating gender stats');
            return ['total' => 0, 'countnam' => 0, 'countnu' => 0];
        }
    }

    // ==================== LOP INFO ====================

    protected function getCurrentLopInfo(): ?object
    {
        if (!$this->selectedLop) {
            return null;
        }

        if ($this->lopCache && $this->lopCache->id === $this->selectedLop) {
            return $this->lopCache;
        }

        try {
            $this->lopCache = CatechismClass::with(['schoolYear', 'gradeLevel', 'teachers'])
                ->findOrFail($this->selectedLop);

            return $this->lopCache;
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading class info');
            return null;
        }
    }

    // ==================== EVENT HANDLERS ====================

    public function handleFilterChanged($filters): void
    {
        if (!is_array($filters)) {
            return;
        }

        if (array_key_exists('namHoc', $filters)) {
            $newNamHoc = is_numeric($filters['namHoc']) ? (int) $filters['namHoc'] : null;
            if ($newNamHoc !== $this->selectedNamHoc) {
                $this->selectedNamHoc = $newNamHoc;
                $this->selectedKhoi   = null;
                $this->selectedLop    = null;
                $this->search         = '';
            }
        }

        if (array_key_exists('khoi', $filters)) {
            $newKhoi = is_numeric($filters['khoi']) ? (int) $filters['khoi'] : null;
            if ($newKhoi !== $this->selectedKhoi) {
                $this->selectedKhoi = $newKhoi;
                $this->selectedLop  = null;
                $this->search       = '';
            }
        }

        if (array_key_exists('lop', $filters)) {
            $this->selectedLop = is_numeric($filters['lop']) ? (int) $filters['lop'] : null;
            $this->search      = '';
        }

        $this->resetPage();
        $this->resetSelection();
    }

    public function resetFilters(): void
    {
        if (!$this->selectedKhoi && !$this->selectedLop && !$this->search) {
            $this->emit('toast', 'warning', 'Không có bộ lọc nào đang được áp dụng');
            return;
        }

        $this->selectedKhoi = null;
        $this->selectedLop  = null;
        $this->search       = '';
        $this->resetPage();
        $this->resetSelection();
        $this->emitTo('filters.filter-bar', 'resetFilters');
        $this->emit('toast', 'success', 'Đã đặt lại bộ lọc');
    }

    public function handleRefresh(): void
    {
        $this->lopCache = null;
        $this->resetPage();
        $this->resetSelection();
    }

    // ==================== HELPERS ====================

    protected function resetSelection(): void
    {
        $this->selectedStudents = [];
        $this->selectAll        = false;
    }

    protected function getDefaultNamHocId(): ?int
    {
        // Ưu tiên 1: năm học đang trong khoảng thời gian hiện tại
        $current = \App\Models\NamHoc::ofParish($this->parishId)
            ->active()
            ->current()
            ->value('id');

        if ($current) {
            return $current;
        }

        // Ưu tiên 2: fallback về năm học mới nhất nếu không có năm học hiện tại
        return \App\Models\NamHoc::ofParish($this->parishId)
            ->active()
            ->orderByDesc('name')
            ->value('id');
    }

    public function clearBirthYearFilters(): void
    {
        $this->birthYear = null;
        $this->resetPage('modal_page');
    }

    public function getQuickYearOptions(): array
    {
        $currentYear = now()->year;
        $years       = [];
        for ($i = 0; $i < 15; $i++) {
            $year         = $currentYear - 5 - $i;
            $years[$year] = $year;
        }
        return $years;
    }

    public function export()
    {
        if (!$this->selectedLop) {
            $this->emit('toast', 'warning', 'Vui lòng chọn lớp trước khi xuất file');
            return;
        }

        $selectedNameClass = CatechismClass::findOrFail($this->selectedLop)->name;

        return response()->streamDownload(function () {
            echo \Maatwebsite\Excel\Facades\Excel::raw(
                new StudentExport($this->selectedLop),
                \Maatwebsite\Excel\Excel::XLSX
            );
        }, 'Lop-' . $selectedNameClass . '-' . now()->format('dmY_His') . '.xlsx');
    }

    // ==================== RENDER ====================

    public function render()
    {
        $students  = $this->getStudentsPaginated();
        $stats     = $this->getGenderStats();
        $lop       = $this->getCurrentLopInfo();

        $availableStudents = ($this->showEnrollNewModal && $this->enrollTab === 'existing')
            ? $this->getAvailableStudentsPaginated()
            : null;

        $availableParishioners = ($this->showEnrollNewModal && $this->enrollTab === 'parishioner')
            ? $this->getAvailableParishionersPaginated()
            : null;

        $layout = auth()->user()?->isCatechist()
            ? 'frontend.layout.catechist'
            : 'frontend.layout.main';

        return view('livewire.student.student-list-new', [
            'lop'                   => $lop,
            'students'              => $students,
            'total'                 => $stats['total'],
            'countnam'              => $stats['countnam'],
            'countnu'               => $stats['countnu'],
            'parishId'              => $this->parishId,
            'availableStudents'     => $availableStudents,
            'availableParishioners' => $availableParishioners,
        ])
            ->extends($layout)
            ->section('content');
    }
}
