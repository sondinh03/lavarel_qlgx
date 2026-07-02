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
        $this->suggestedParishioners = collect();
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

        $this->finalizeFilterChange();
    }

    public function updatedModalSearch(): void
    {
        $this->modalSearch = trim($this->modalSearch);
        $this->resetEnrollModalSelection();
        $this->resetPage('modal_page');
    }

    public function updatedBirthYear(): void
    {
        $this->validateOnly('birthYear');
        $this->resetEnrollModalSelection();
        $this->resetPage('modal_page');
    }

    public function updatedParishionerSearch(): void
    {
        $this->parishionerSearch = trim($this->parishionerSearch);
        $this->resetParishionerModalSelection();
        $this->resetPage('parishioner_page');
    }

    public function updatedParishionerBirthYear(): void
    {
        $this->resetParishionerModalSelection();
        $this->resetPage('parishioner_page');
    }

    public function updatedAgeFrom(): void
    {
        $this->resetParishionerModalSelection();
        $this->resetPage('parishioner_page');
    }

    public function updatedAgeTo(): void
    {
        $this->resetParishionerModalSelection();
        $this->resetPage('parishioner_page');
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
        $this->search = '';
        $this->finalizeFilterChange();
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
        $this->finalizeFilterChange();
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

        $this->finalizeFilterChange();
    }

    public function updatedSelectAll($value): void
    {
        $pageIds = $this->getCurrentPageStudentIds();

        if ($value) {
            $this->selectedStudents = array_values(array_unique(array_merge($this->selectedStudents, $pageIds)));
        } else {
            $this->selectedStudents = array_values(array_diff($this->selectedStudents, $pageIds));
        }

        $this->syncSelectAllForCurrentPage();
    }

    public function updatedSelectedStudents(): void
    {
        $this->selectedStudents = $this->normalizeIdList($this->selectedStudents);
        $this->syncSelectAllForCurrentPage();
    }

    public function updatedSelectAllInModal($value): void
    {
        $pageIds = $this->getCurrentPageAvailableStudentIds();

        if ($value) {
            $this->studentsToAdd = array_values(array_unique(array_merge($this->studentsToAdd, $pageIds)));
        } else {
            $this->studentsToAdd = array_values(array_diff($this->studentsToAdd, $pageIds));
        }

        $this->syncSelectAllInModalForCurrentPage();
    }

    public function updatedStudentsToAdd(): void
    {
        $this->studentsToAdd = $this->normalizeIdList($this->studentsToAdd);
        $this->syncSelectAllInModalForCurrentPage();
    }

    public function updatedSelectAllParishioners($value): void
    {
        $pageIds = $this->getCurrentPageParishionerIds();

        if ($value) {
            $this->selectedParishioners = array_values(array_unique(array_merge($this->selectedParishioners, $pageIds)));
        } else {
            $this->selectedParishioners = array_values(array_diff($this->selectedParishioners, $pageIds));
        }

        $this->syncSelectAllParishionersForCurrentPage();
    }

    public function updatedSelectedParishioners(): void
    {
        $this->selectedParishioners = $this->normalizeIdList($this->selectedParishioners);
        $this->syncSelectAllParishionersForCurrentPage();
    }

    // ==================== PARISHIONER LINKING ====================

    public function openLinkParishioner(int $studentId): void
    {
        $this->authorize('update', StudentNew::findOrFail($studentId));

        try {
            $student = StudentNew::with('saint')->findOrFail($studentId);
            if ($student->parishioner_id) {
                $this->emit('toast', 'info', 'Học sinh này đã được liên kết với giáo dân. Vui lòng hủy liên kết trước khi liên kết mới.');
                return;
            }
            $this->linkingStudentId      = $studentId;
            $this->suggestedParishioners = $this->findSuggestedParishioners($student);
            $this->showLinkModal         = true;
            $this->emit('openLinkModal');
        } catch (ModelNotFoundException $e) {
            $this->emit('toast', 'error', 'Không tìm thấy học sinh này');
        }
    }

    protected function findSuggestedParishioners(StudentNew $student): \Illuminate\Support\Collection
    {
        $normalizedName = $this->normalizePersonName($student->last_name, $student->first_name);

        if ($normalizedName === '') {
            return collect();
        }

        $baseQuery = function () use ($student, $normalizedName) {
            return Parishioner::query()
                ->whereDoesntHave('student')
                ->active()
                ->alive()
                ->when($student->parish_id, fn ($q) => $q->where('parish_id', $student->parish_id))
                ->whereRaw(
                    'LOWER(TRIM(CONCAT(TRIM(last_name), \' \', TRIM(first_name)))) = ?',
                    [$normalizedName]
                )
                ->with('saint');
        };

        $columns = ['id', 'last_name', 'first_name', 'saint_id', 'gender', 'birthday', 'avatar_path', 'cccd', 'phone'];

        $candidates = collect();

        if ($student->birthday) {
            $candidates = $baseQuery()
                ->whereDate('birthday', $student->birthday)
                ->limit(10)
                ->get($columns);
        }

        if ($candidates->isEmpty()) {
            $candidates = $baseQuery()
                ->limit(15)
                ->get($columns);
        }

        return $candidates
            ->sortByDesc(fn (Parishioner $p) => $this->scoreParishionerLinkMatch($student, $p))
            ->take(5)
            ->values();
    }

    /**
     * Chuẩn hóa họ tên để so khớp (bỏ khoảng trắng thừa, lowercase).
     */
    protected function normalizePersonName(?string $lastName, ?string $firstName): string
    {
        $full = trim(preg_replace('/\s+/u', ' ', trim(($lastName ?? '') . ' ' . ($firstName ?? ''))));

        return mb_strtolower($full, 'UTF-8');
    }

    /**
     * Điểm ưu tiên gợi ý: ngày sinh > tên thánh > giới tính.
     */
    protected function scoreParishionerLinkMatch(StudentNew $student, Parishioner $parishioner): int
    {
        $score = 0;

        if ($student->birthday && $parishioner->birthday) {
            $score += $student->birthday->isSameDay($parishioner->birthday) ? 10 : -8;
        }

        if ($student->saint_id && $parishioner->saint_id) {
            $score += (int) $student->saint_id === (int) $parishioner->saint_id ? 5 : 0;
        }

        if ($student->gender && $parishioner->gender && $student->gender === $parishioner->gender) {
            $score += 2;
        }

        return $score;
    }

    public function confirmLink(int $parishionerId): void
    {
        $this->authorize('update', StudentNew::findOrFail($this->linkingStudentId));

        try {
            DB::beginTransaction();

            $student     = StudentNew::findOrFail($this->linkingStudentId);
            $parishioner = Parishioner::query()->findOrFail($parishionerId);

            if ($parishioner->student()->exists()) {
                DB::rollBack();
                $this->emit('toast', 'warning', 'Giáo dân này đã được liên kết với học sinh khác');
                return;
            }

            if ($student->parish_id && $parishioner->parish_id && (int) $student->parish_id !== (int) $parishioner->parish_id) {
                DB::rollBack();
                $this->emit('toast', 'error', 'Giáo dân không thuộc cùng giáo xứ với học sinh');
                return;
            }

            $student->update(['parishioner_id' => $parishioner->id]);

            DB::commit();

            $this->emit('toast', 'message', "Đã liên kết {$student->full_name_with_saint} với giáo dân {$parishioner->full_name_with_saint}");
            $this->closeLinkModal();
            $this->emit('refreshStudents');
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
            $this->emit('refreshStudents');
        } catch (\Exception $e) {
            $this->logError($e, 'Error unlinking parishioner');
            $this->emit('toast', 'error', 'Có lỗi khi hủy liên kết');
        }
    }

    public function closeLinkModal(): void
    {
        $this->resetLinkModalState();
    }

    protected function resetLinkModalState(): void
    {
        $this->showLinkModal         = false;
        $this->linkingStudentId      = null;
        $this->suggestedParishioners = collect();
        $this->emit('closeLinkModal');
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

    /**
     * Map dữ liệu giáo dân (parishioners_new) sang hồ sơ học sinh.
     */
    protected function buildStudentAttributesFromParishioner(Parishioner $parishioner): array
    {
        $birthday = $parishioner->getRawOriginal('birthday');

        return [
            'first_name'      => $parishioner->first_name,
            'last_name'       => $parishioner->last_name,
            'saint_id'        => $parishioner->saint_id,
            'gender'          => in_array($parishioner->gender, ['male', 'female'], true)
                ? $parishioner->gender
                : 'male',
            'birthday'        => $birthday ?: null,
            'phone'           => $parishioner->phone,
            'email'           => $parishioner->email,
            'father_name'     => $parishioner->father_name,
            'mother_name'     => $parishioner->mother_name,
            'parishioner_id'  => $parishioner->id,
            'parish_id'       => $parishioner->parish_id ?? $this->parishId,
            'parish_group_id' => $parishioner->parish_area_id,
            'note'            => $parishioner->note,
            'is_active'       => true,
        ];
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
                    if ($p->student) {
                        $p->student->classes()->syncWithoutDetaching([
                            $catechismClass->id => [
                                'enrolled_at' => now(),
                                'updated_at'  => now(),
                            ],
                        ]);
                        $successCount++;
                        continue;
                    }

                    $student = StudentNew::create(
                        $this->buildStudentAttributesFromParishioner($p)
                    );

                    $student->classes()->attach($catechismClass->id, [
                        'enrolled_at' => now(),
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);

                    $successCount++;
                } catch (\Exception $e) {
                    $errors[] = "{$p->full_name}: {$e->getMessage()}";
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
        $this->emit('openEnrollModal');
    }

    public function closeEnrollModal(): void
    {
        $this->resetEnrollModalState();
    }

    protected function resetEnrollModalState(): void
    {
        $this->showEnrollNewModal = false;
        $this->resetEnrollForm();
        $this->resetValidation();
        $this->emit('closeEnrollModal');
    }

    public function switchEnrollTab(string $tab): void
    {
        $this->enrollTab = $tab;
        $this->resetEnrollForm();
        $this->resetValidation();
    }

    protected function resetEnrollForm(): void
    {
        $this->resetEnrollModalSelection();
        $this->modalSearch           = '';
        $this->birthYear             = null;
        $this->parishionerSearch     = '';
        $this->parishionerBirthYear  = null;
        $this->ageFrom               = null;
        $this->ageTo                 = null;
        $this->resetParishionerModalSelection();
    }

    protected function resetEnrollModalSelection(): void
    {
        $this->studentsToAdd    = [];
        $this->selectAllInModal = false;
    }

    protected function resetParishionerModalSelection(): void
    {
        $this->selectedParishioners    = [];
        $this->selectAllParishioners = false;
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

    public function deleteProfile(int $studentId): void
    {
        try {
            $student = StudentNew::findOrFail($studentId);

            $this->authorize('delete', $student);

            DB::beginTransaction();

            // Xóa khỏi tất cả lớp học (pivot table students_class)
            $student->classes()->detach();

            // Xóa các bản ghi trong studentsClass (nếu có dữ liệu phụ)
            $student->studentsClass()->delete();

            // Xóa profile học sinh
            $student->delete();

            DB::commit();

            $this->emit('toast', 'message', 'Đã xóa học sinh thành công');
            $this->emit('refreshStudents');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            DB::rollBack();
            $this->emit('toast', 'error', 'Bạn không có quyền xóa học sinh này');
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            $this->emit('toast', 'error', 'Không tìm thấy học sinh');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError($e, 'Error deleting StudentNew profile', [
                'student_id' => $studentId,
            ]);
            $this->emit('toast', 'error', 'Có lỗi khi xóa học sinh. Vui lòng thử lại.');
        }
    }

    // ==================== QUERY HELPERS ====================

    /**
     * Lọc danh sách học sinh theo năm học / khối / lớp / từ khóa (dùng chung list + stats).
     */
    protected function applyListFilters($query, bool $withSearch = true): void
    {
        if ($this->selectedNamHoc || $this->selectedKhoi || $this->selectedLop) {
            $query->whereHas('classes', function ($q) {
                if ($this->selectedNamHoc) {
                    $q->where('classes.school_year_id', $this->selectedNamHoc);
                }
                if ($this->selectedKhoi) {
                    $q->where('classes.grade_level_id', $this->selectedKhoi);
                }
                if ($this->selectedLop) {
                    $q->where('classes.id', $this->selectedLop);
                }
            });
        }

        if ($withSearch && !empty(trim($this->search))) {
            $query->search(trim($this->search));
        }
    }

    protected function getCurrentStudentsQuery()
    {
        $query = StudentNew::with(['saint', 'parishGroup']);
        $this->applyListFilters($query);
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
                $q->search($search);

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

    protected function getGenderStats(bool $withSearch = false): array
    {
        try {
            $query = StudentNew::query();
            $this->applyListFilters($query, $withSearch);

            $stats = $query
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
            $newLop = is_numeric($filters['lop']) ? (int) $filters['lop'] : null;
            if ($newLop !== $this->selectedLop) {
                $this->selectedLop = $newLop;
                $this->search      = '';
            }
        }

        $this->finalizeFilterChange();
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
        $this->finalizeFilterChange();
        $this->emitTo('filters.filter-bar', 'resetFilters');
        $this->emit('toast', 'success', 'Đã đặt lại bộ lọc');
    }

    public function handleRefresh(): void
    {
        $this->finalizeFilterChange();
    }

    // ==================== HELPERS ====================

    protected function finalizeFilterChange(): void
    {
        $this->lopCache = null;
        $this->resetPage();
        $this->resetSelection();
    }

    protected function getListContext(): string
    {
        return implode('|', [
            'nam:' . ($this->selectedNamHoc ?? 'none'),
            'khoi:' . ($this->selectedKhoi ?? 'none'),
            'lop:' . ($this->selectedLop ?? 'none'),
            'search:' . md5(trim($this->search ?? '')),
            'page:' . ($this->page ?? 1),
            'sort:' . $this->sortField . '-' . $this->sortDirection,
            'per:' . $this->perPage,
        ]);
    }

    protected function normalizeIdList(array $ids): array
    {
        return array_values(
            array_unique(
                array_map('intval', array_filter($ids, 'is_numeric'))
            )
        );
    }

    protected function getCurrentPageStudentIds(): array
    {
        return $this->getCurrentStudentsQuery()
            ->paginate($this->perPage, ['id'], 'page', $this->page ?? 1)
            ->getCollection()
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->toArray();
    }

    protected function getCurrentPageAvailableStudentIds(): array
    {
        return $this->getAvailableStudentsQuery()
            ->paginate(15, ['id'], 'modal_page')
            ->getCollection()
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->toArray();
    }

    protected function getCurrentPageParishionerIds(): array
    {
        return $this->getAvailableParishionersQuery()
            ->paginate(15, ['id'], 'parishioner_page')
            ->getCollection()
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->toArray();
    }

    protected function syncSelectAllForCurrentPage(): void
    {
        $pageIds = $this->getCurrentPageStudentIds();
        $this->selectAll = !empty($pageIds)
            && count(array_intersect($this->selectedStudents, $pageIds)) === count($pageIds);
    }

    protected function syncSelectAllInModalForCurrentPage(): void
    {
        $pageIds = $this->getCurrentPageAvailableStudentIds();
        $this->selectAllInModal = !empty($pageIds)
            && count(array_intersect($this->studentsToAdd, $pageIds)) === count($pageIds);
    }

    protected function syncSelectAllParishionersForCurrentPage(): void
    {
        $pageIds = $this->getCurrentPageParishionerIds();
        $this->selectAllParishioners = !empty($pageIds)
            && count(array_intersect($this->selectedParishioners, $pageIds)) === count($pageIds);
    }

    protected function resetSelection(): void
    {
        $this->selectedStudents = [];
        $this->selectAll        = false;
    }

    protected function getDefaultNamHocId(): ?int
    {
        // Ưu tiên 1: năm học đang trong khoảng thời gian hiện tại
        $current = \App\Models\NamHoc::query()
            ->active()
            ->current()
            ->value('id');

        if ($current) {
            return $current;
        }

        // Ưu tiên 2: fallback về năm học mới nhất nếu không có năm học hiện tại
        return \App\Models\NamHoc::query()
            ->active()
            ->orderByDesc('name')
            ->value('id');
    }

    public function clearBirthYearFilters(): void
    {
        $this->birthYear = null;
        $this->resetEnrollModalSelection();
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
        }, 'DanhSachLop_' . $selectedNameClass . '_' . now()->format('dmY_His') . '.xlsx');
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

        $linkingStudent = $this->linkingStudentId
            ? StudentNew::find($this->linkingStudentId)
            : null;

        $layout = auth()->user()?->usesCatechistLayout()
            ? 'frontend.layout.catechist'
            : 'frontend.layout.main';

        return view('livewire.student.student-list-new', [
            'lop'                   => $lop,
            'students'              => $students,
            'listContext'           => $this->getListContext(),
            'total'                 => $stats['total'],
            'countnam'              => $stats['countnam'],
            'countnu'               => $stats['countnu'],
            'parishId'              => $this->parishId,
            'availableStudents'     => $availableStudents,
            'availableParishioners' => $availableParishioners,
            'linkingStudent'        => $linkingStudent,
        ])
            ->extends($layout)
            ->section('content');
    }
}
