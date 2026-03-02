<?php

namespace App\Http\Livewire\Student;

use App\Http\Livewire\Base\BaseComponent;
use App\Models\Lop;
use App\Models\Student;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StudentList extends BaseComponent
{
    // ==================== FILTERS ====================
    public $selectedNamHoc = null;
    public $selectedKhoi = null;
    public $selectedLop = null;

    // ==================== SELECTION ====================
    public $selectedStudents = [];
    public $selectAll = false;

    // ==================== ADD STUDENTS MODAL ====================
    /** @var bool Hiển thị modal thêm học sinh */
    public $showAddStudentsModal = false;

    /** @var array Danh sách ID học sinh được chọn để thêm */
    public $studentsToAdd = [];

    /** @var bool Select all trong modal */
    public $selectAllInModal = false;

    /** @var string Search trong modal */
    public $modalSearch = '';

    // ==================== PROTECTED DATA ====================
    protected $lopModel = null;

    protected $lopCache = null;

    protected $studentsBaseQuery = null;

    // ==================== PROPERTIES ====================

    /** @var string|null Năm sinh để lọc (VD: 2015) */
    public $birthYear = null;

    // ==================== IMPORT FROM PARISHIONERS ====================

    /** @var bool Hiển thị modal import từ giáo dân */
    public $showImportFromParishioners = false;

    /** @var array Danh sách ID giáo dân được chọn */
    public $selectedParishioners = [];

    /** @var bool Select all */
    public $selectAllParishioners = false;

    /** @var string Search trong modal import */
    public $parishionerSearch = '';

    /** @var int|null Năm sinh để lọc giáo dân */
    public $parishionerBirthYear = null;

    public ?int $ageFrom = null;

    public ?int $ageTo = null;


    // ==================== VALIDATION ====================
    protected $rules = [
        'selectedNamHoc' => 'nullable|integer|exists:nam_hoc,id',
        'selectedKhoi' => 'nullable|integer|exists:block,id',
        'selectedLop' => 'nullable|integer|exists:lop,id',
        'search' => 'nullable|string|max:255',
        'perPage' => 'required|integer|in:10,15,25,50,100',
        'selectedStudents' => 'nullable|array',
        'selectedStudents.*' => 'integer',
        'studentsToAdd' => 'nullable|array',
        'studentsToAdd.*' => 'integer|exists:student,id',
        'modalSearch' => 'nullable|string|max:255',
        'birthYear' => 'nullable|integer|min:1900|max:' . PHP_INT_MAX,
    ];

    protected $messages = [
        'selectedNamHoc.exists' => 'Năm học không tồn tại',
        'selectedKhoi.exists' => 'Khối không tồn tại',
        'selectedLop.exists' => 'Lớp không tồn tại',
        'search.max' => 'Tìm kiếm không được quá 255 ký tự',
        'perPage.in' => 'Số mục trên trang không hợp lệ',
        'studentsToAdd.*.exists' => 'Học sinh không tồn tại',
        'modalSearch.max' => 'Tìm kiếm không được quá 255 ký tự',
        'birthYear.integer' => 'Năm sinh phải là số',
        'birthYear.min' => 'Năm sinh không hợp lệ',
    ];

    // ==================== QUERY STRING ====================
    protected function queryString()
    {
        return array_merge([
            'selectedNamHoc' => ['as' => 'school-year', 'except' => null],
            'selectedKhoi' => ['as' => 'grade', 'except' => ''],
            'selectedLop' => ['as' => 'class', 'except' => ''],
        ], parent::queryString());
    }

    // ==================== LISTENERS ====================
    protected $listeners = [
        'refresh' => 'handleRefresh',
        'filterChanged' => 'handleFilterChanged',
        'refreshStudents' => 'handleRefresh',
    ];

    // ==================== LIFECYCLE ====================
    public function mount(): void
    {
        parent::mount();
        $this->requireParishId();
    }

    protected function loadInitialData(): void
    {
        if (!$this->selectedNamHoc) {
            $this->selectedNamHoc = $this->getDefaultNamHocId();
        }
    }

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
        $this->selectedLop = null;
    }

    // ==================== PROPERTY UPDATERS ====================
    public function updatedSearch(): void
    {
        $this->search = trim($this->search);

        try {
            $this->validateOnly('search');
        } catch (ValidationException $e) {
            $this->search = '';
            session()->flash('warning', 'Từ khóa tìm kiếm không hợp lệ.');
        }

        $this->resetPage();
        $this->resetSelection();
    }

    public function updatedModalSearch(): void
    {
        $this->modalSearch = trim($this->modalSearch);
        $this->selectAllInModal = false;
        $this->studentsToAdd = [];
        $this->resetPage('modal_page');
    }

    public function updatedBirthYear()
    {
        $this->validateOnly('birthYear');
        // $this->resetPage(); // Main page
        $this->resetPage('modal_page'); // Modal page
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
            session()->flash('warning', 'Năm học không hợp lệ.');
        }

        $this->selectedKhoi = null;
        $this->selectedLop = null;
        $this->search = '';
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
                session()->flash('warning', 'Khối không hợp lệ.');
            }
        }

        $this->selectedLop = null;
        $this->resetPage();
        $this->resetSelection();
    }

    public function updatedSelectedLop(): void
    {
        $this->lopCache = null;

        $this->selectedLop = $this->selectedLop && is_numeric($this->selectedLop)
            ? (int) $this->selectedLop
            : null;

        if ($this->selectedLop) {
            try {
                $this->validateOnly('selectedLop');
            } catch (ValidationException $e) {
                $this->selectedLop = null;
                session()->flash('warning', 'Lớp không hợp lệ.');
            }
        }

        $this->resetPage();
        $this->resetSelection();
    }

    public function updatedSelectAll($value): void
    {
        if ($value) {
            $ids = $this->getCurrentStudentsQuery()
                ->pluck('id')
                ->map(fn($id) => (int) $id)
                ->toArray();

            $this->selectedStudents = $ids;
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

        $currentIds = $this->getCurrentStudentsQuery()->pluck('id')->toArray();
        $selectedCount = count(array_intersect($this->selectedStudents, $currentIds));
        $totalCount = count($currentIds);

        $this->selectAll = $totalCount > 0 && $selectedCount === $totalCount;
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

        $availableIds = $this->getAvailableStudentsQuery()->pluck('id')->toArray();
        $selectedCount = count(array_intersect($this->studentsToAdd, $availableIds));
        $totalCount = count($availableIds);

        $this->selectAllInModal = $totalCount > 0 && $selectedCount === $totalCount;
    }

    /**
     * Mở modal import từ giáo dân
     */
    public function openImportFromParishioners(): void
    {
        $this->requireManager();

        if (!$this->selectedLop) {
            session()->flash('warning', 'Vui lòng chọn lớp trước');
            return;
        }

        $this->selectedParishioners = [];
        $this->selectAllParishioners = false;
        $this->parishionerSearch = '';
        $this->parishionerBirthYear = null;

        $this->showImportFromParishioners = true;
    }

    /**
     * Đóng modal
     */
    public function closeImportFromParishioners(): void
    {
        $this->showImportFromParishioners = false;
        $this->selectedParishioners = [];
        $this->selectAllParishioners = false;
        $this->parishionerSearch = '';
        $this->parishionerBirthYear = null;
    }

    /**
     * Get danh sách giáo dân có thể import
     */
    private function getAvailableParishionersQuery()
    {
        if (!$this->selectedNamHoc) {
            return \App\Models\Parishioners::whereRaw('1 = 0');
        }

        return \App\Models\Parishioners::query()
            ->where('pid', $this->parishId)
            ->active()
            ->whereDoesntHave('student')
            ->when($this->parishionerBirthYear, function ($q) {
                $q->whereYear('birthday', $this->parishionerBirthYear);
            })
            ->when(trim($this->parishionerSearch), function ($q, $search) {
                $q->where(function ($q2) use ($search) {
                    $q2->where('last_name', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('cccd', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhereRaw("CONCAT(last_name, ' ', name) like ?", ["%{$search}%"]);
                });
            })
            ->when($this->ageFrom || $this->ageTo, function ($q) {
                $from = $this->ageTo
                    ? now()->subYears($this->ageTo)->startOfDay()
                    : null;

                $to = $this->ageFrom
                    ? now()->subYears($this->ageFrom)->endOfDay()
                    : null;

                if ($from && $to) {
                    $q->whereBetween('birthday', [$from, $to]);
                } elseif ($from) {
                    $q->where('birthday', '<=', $from);
                } elseif ($to) {
                    $q->where('birthday', '>=', $to);
                }
            })
            ->orderBy('last_name')
            ->orderBy('name');
    }

    /**
     * Get paginated parishioners
     */
    private function getAvailableParishionersPaginated()
    {
        return $this->getAvailableParishionersQuery()
            ->paginate(15, ['*'], 'parishioner_page');
    }

    /**
     * Import giáo dân thành học sinh
     */
    public function importParishionersToStudents(): void
    {
        $this->requireManager();

        if (empty($this->selectedParishioners)) {
            session()->flash('warning', 'Vui lòng chọn ít nhất 1 giáo dân');
            return;
        }

        try {
            DB::beginTransaction();

            $lop = \App\Models\Lop::findOrFail($this->selectedLop);
            $successCount = 0;
            $errorCount = 0;
            $errors = [];

            // Lấy giáo dân đã chọn
            $parishioners = \App\Models\Parishioners::whereIn('id', $this->selectedParishioners)
                ->where('pid', $this->parishId)
                ->get();

            foreach ($parishioners as $p) {
                try {
                    // ✅ Tạo học sinh với ĐẦY ĐỦ các trường
                    $student = \App\Models\Student::create([
                        // Thông tin cơ bản
                        'last_name' => $p->last_name,
                        'name' => $p->name,
                        'holy' => $p->holy,
                        'sex' => $p->sex,
                        'birthday' => $p->birthday?->format('Y-m-d'),
                        'phone' => $p->phone,
                        'phone_number' => $p->phone, // Có cả phone_number
                        'email' => $p->email,
                        'cccd' => $p->cccd,

                        // Địa chỉ
                        'origin' => $p->origin,
                        'ward' => $p->ward,
                        'province' => $p->province,

                        // Gia đình
                        'father' => $p->father,
                        'mother' => $p->mother,

                        // Thánh sự - Rửa tội
                        'baptism_date' => $p->baptism_date,
                        'baptism_number' => $p->baptism_number,
                        'baptism_giver' => $p->baptism_giver,
                        'baptism_sponsor' => $p->baptism_sponsor,
                        'baptism_dioceses' => $p->baptism_dioceses,
                        'baptism_deanerys' => $p->baptism_deanerys,
                        'baptism_parish' => $p->baptism_parish,

                        // Thánh sự - Thêm sức
                        'more_power_date' => $p->more_power_date,
                        'more_power_number' => $p->more_power_number,
                        'more_power_giver' => $p->more_power_giver,
                        'more_power_sponsor' => $p->more_power_sponsor,
                        'more_power_address' => $p->more_power_address ?? null,
                        'more_power_dioceses' => $p->more_power_dioceses,
                        'more_power_deanerys' => $p->more_power_deanerys,
                        'more_power_parish' => $p->more_power_parish,

                        // Thánh sự - Rước lễ (nếu có)
                        'communion_date' => $p->communion_date ?? null,
                        // 'communion_number' => $p->communion_number ?? null,
                        // 'communion_giver' => $p->communion_giver ?? null,

                        // Ghi chú
                        'note' => $p->note ?? null,

                        // ✅ Liên kết giáo dân
                        'parishioner_id' => $p->id,

                        // ✅ Context - BẮT BUỘC
                        'pid' => $this->parishId,
                        'did' => $p->did ?? 0,
                        'deid' => $p->deid ?? 0,
                        'paid' => $p->paid ?? 0,

                        // ✅ Lớp (có thể để null vì sẽ thêm vào bảng trung gian)
                        // 'lop' => $lop->id,

                        // ✅ Mã học viên (để null, sẽ generate sau hoặc auto)
                        'mahv' => null,
                        'magd' => null,
                        'magdcg' => null,

                        // ✅ Trạng thái
                        'status' => \App\Models\Student::STATUS_STUDYING,
                    ]);

                    // ✅ Thêm vào lớp qua bảng trung gian students_class
                    $student->lops()->attach($lop->id, [
                        'status' => \App\Models\StudentsClass::STATUS_ENROLLED ?? 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $successCount++;
                } catch (\Exception $e) {
                    $errors[] = "{$p->last_name} {$p->name}: {$e->getMessage()}";
                    $errorCount++;

                    // ✅ Log chi tiết để debug
                    $this->logError($e, 'Error creating student from parishioner', [
                        'parishioner_id' => $p->id,
                        'parishioner_name' => "{$p->last_name} {$p->name}",
                    ]);
                }
            }

            DB::commit();

            $message = "✅ Đã import {$successCount} học sinh thành công";
            if ($errorCount > 0) {
                $message .= " | ❌ {$errorCount} lỗi";
            }

            session()->flash('message', $message);

            if (!empty($errors)) {
                $errorMessage = '<strong>Chi tiết lỗi:</strong><br>' . implode('<br>', array_slice($errors, 0, 5));
                if (count($errors) > 5) {
                    $errorMessage .= '<br><em>... và ' . (count($errors) - 5) . ' lỗi khác</em>';
                }
                session()->flash('warning', $errorMessage);
            }

            $this->closeImportFromParishioners();
            $this->emit('refreshStudents');
        } catch (\Exception $e) {
            DB::rollBack();

            $this->logError($e, 'Error importing parishioners to students', [
                'selected_count' => count($this->selectedParishioners),
                'lop_id' => $this->selectedLop,
            ]);

            session()->flash('error', 'Có lỗi khi import học sinh: ' . $e->getMessage());
        }
    }

    /**
     * Update khi select all thay đổi
     */
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

    // ==================== ADD STUDENTS ACTIONS ====================

    /**
     * Mở modal thêm học sinh
     */
    public function openAddStudentsModal(): void
    {
        $this->requireManager();

        if (!$this->selectedLop) {
            session()->flash('warning', 'Vui lòng chọn lớp trước khi thêm học sinh');
            return;
        }

        // Reset modal state
        $this->studentsToAdd = [];
        $this->selectAllInModal = false;
        $this->modalSearch = '';

        $this->showAddStudentsModal = true;
    }

    /**
     * Đóng modal
     */
    public function closeAddStudentsModal(): void
    {
        $this->showAddStudentsModal = false;
        $this->studentsToAdd = [];
        $this->selectAllInModal = false;
        $this->modalSearch = '';
        $this->resetValidation();
    }

    /**
     * Thêm học sinh vào lớp
     */
    public function addStudentsToClass(): void
    {
        $this->requireManager();

        if (empty($this->studentsToAdd)) {
            session()->flash('warning', 'Vui lòng chọn ít nhất một học sinh');
            return;
        }

        $this->validate([
            'studentsToAdd' => 'required|array|min:1',
            'studentsToAdd.*' => 'integer|exists:student,id',
        ]);

        try {
            DB::beginTransaction();

            $lop = Lop::findOrFail($this->selectedLop);

            // Lọc ra các học sinh chưa có trong lớp
            $existingStudentIds = $lop->students()->pluck('student.id')->toArray();
            $newStudentIds = array_diff($this->studentsToAdd, $existingStudentIds);

            if (empty($newStudentIds)) {
                session()->flash('warning', 'Tất cả học sinh đã có trong lớp này');
                return;
            }

            // Thêm học sinh vào lớp (giả sử có bảng trung gian lop_student)
            $lop->students()->attach($newStudentIds, [
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            $count = count($newStudentIds);
            session()->flash('message', "Đã thêm {$count} học sinh vào lớp thành công");

            $this->closeAddStudentsModal();
            $this->emit('refreshStudents');
        } catch (\Exception $e) {
            DB::rollBack();

            $this->logError($e, 'Error adding students to class', [
                'lop_id' => $this->selectedLop,
                'student_ids' => $this->studentsToAdd,
            ]);

            session()->flash('error', 'Có lỗi khi thêm học sinh vào lớp. Vui lòng thử lại.');
        }
    }

    // ==================== QUERY HELPERS ====================

    /**
     * Get query cho danh sách học sinh hiện tại (trong lớp)
     */
    private function getCurrentStudentsQuery()
    {
        if ($this->studentsBaseQuery) {
            return clone $this->studentsBaseQuery;
        }

        $query = Student::with(['holyRelation', 'paidRelation', 'slug'])
            ->ofParish($this->parishId);

        if ($this->selectedNamHoc) {
            $query->whereHas('lops', function ($q) {
                $q->where('schoolyear', $this->selectedNamHoc);
            });
        }

        if ($this->selectedKhoi) {
            $query->whereHas('lops', function ($q) {
                $q->where('block', $this->selectedKhoi);
            });
        }

        if ($this->selectedLop) {
            $query->whereHas('lops', function ($q) {
                $q->where('lop.id', $this->selectedLop);
            });
        }

        // if (!empty(trim($this->search))) {
        //     $search = trim($this->search);
        //     $query->where(function ($q) use ($search) {
        //         $q->where('name', 'like', "%{$search}%")
        //             ->orWhere('last_name', 'like', "%{$search}%")
        //             ->orWhere('holy', 'like', "%{$search}%")
        //             ->orWhere('mahv', 'like', "%{$search}%");
        //     });
        // }

        if ($this->search) {
            $query->search($this->search);
        }

        // return $query->orderBy('name', 'asc');
        return $this->studentsBaseQuery = clone $query;
    }

    /**
     * Get query cho học sinh có thể thêm vào lớp (chưa có trong lớp)
     */
    private function getAvailableStudentsQuery()
    {
        if (!$this->selectedLop) {
            return Student::whereRaw('1 = 0'); // Empty query
        }

        $lop = Lop::find($this->selectedLop);
        if (!$lop) {
            return Student::whereRaw('1 = 0');
        }

        // Lấy danh sách học sinh chưa có trong lớp này
        $query = Student::ofParish($this->parishId)
            ->whereDoesntHave('lops', function ($q) {
                $q->where('lop.schoolyear', $this->selectedNamHoc);
            });

        // Filter theo khối (nếu có)
        if ($this->selectedKhoi) {
            // Có thể thêm logic lọc theo độ tuổi phù hợp với khối
        }

        if ($this->birthYear) {
            $query->whereYear('birthday', $this->birthYear);
        }

        // Search trong modal
        if (!empty(trim($this->modalSearch))) {
            $search = trim($this->modalSearch);

            $query
                ->leftJoin('holymanagements', 'student.holy', '=', 'holymanagements.id')
                ->where(function ($q) use ($search) {
                    $q->whereRaw(
                        "CONCAT(
                            COALESCE(holymanagements.name, ''),
                            ' ',
                            student.last_name,
                            ' ',
                            student.name
                        ) LIKE ?",
                        ["%{$search}%"]
                    )
                        ->orWhere('student.name', 'like', "%{$search}%")
                        ->orWhere('student.last_name', 'like', "%{$search}%")
                        ->orWhere('holymanagements.name', 'like', "%{$search}%")
                        ->orWhere('student.mahv', 'like', "%{$search}%");
                    if (is_numeric($search) && strlen($search) === 4) {
                        $q->orWhereYear('student.birthday', (int)$search);
                    }
                })
                ->select('student.*');
        }

        return $query->orderBy('student.birthday', 'desc')
            ->orderBy('name', 'asc');
    }

    /**
     * Get danh sách học sinh có thể thêm (paginated)
     */
    private function getAvailableStudentsPaginated(): LengthAwarePaginator
    {
        try {
            $students = $this->getAvailableStudentsQuery()
                ->paginate(15, ['*'], 'modal_page');

            $students->getCollection()->transform(function ($student) {
                $student->holy = $student->holyRelation->name ?? '';
                return $student;
            });

            return $students;
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading available students');

            return new LengthAwarePaginator([], 0, 15, 1);
        }
    }

    /**
     * Get paginated students
     */
    private function getStudentsPaginated(): LengthAwarePaginator
    {
        try {
            $students = $this->getCurrentStudentsQuery()->paginate($this->perPage);

            $students->getCollection()->transform(function ($student, $index) use ($students) {
                return $this->transformStudent($student, $students->firstItem() + $index);
            });

            return $students;
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading students', [
                'namhoc' => $this->selectedNamHoc,
                'khoi' => $this->selectedKhoi,
                'lop' => $this->selectedLop,
                'search' => $this->search,
            ]);

            session()->flash('error', 'Có lỗi khi tải danh sách học viên.');

            return new LengthAwarePaginator([], 0, $this->perPage, $this->page ?? 1);
        }
    }

    /**
     * Transform student data for display
     */
    private function transformStudent($student, int $stt)
    {
        $student->stt = $stt;

        $slug = optional($student->slug)->slug;

        if ($slug) {
            $baseSlug = $slug . config('app.url_prefix', '');
            $student->slug = url($baseSlug);
            $student->thugioithieu = url($baseSlug . '/thugioithieu=' . $student->id);
        } else {
            $student->slug = null;
            $student->thugioithieu = null;
        }

        $student->edit = config('app.url') . '/admin/student/' . $student->id . '/edit';

        // $student->holy = $student->holyRelation->name ?? '';
        // $student->paid = $student->paidRelation->name ?? '';

        // $student->ward = $this->getXaPhuongName($student->ward);
        // $student->province = $this->getTinhThanhName($student->province);

        return $student;
    }

    // ==================== STATISTICS ====================
    private function getGenderStats(): array
    {
        try {
            $query = clone $this->getCurrentStudentsQuery();

            // $countnam = (clone $query)->where('sex', 1)->count();
            // $countnu = (clone $query)->where('sex', 0)->count();

            $stats = $query
                ->selectRaw('sex, COUNT(*) total')
                ->groupBy('sex')
                ->pluck('total', 'sex');

            $countnam = $stats[1] ?? 0;
            $countnu  = $stats[0] ?? 0;

            return [
                'total' => $countnam + $countnu,
                'countnam' => $countnam,
                'countnu' => $countnu,
            ];
        } catch (\Exception $e) {
            $this->logError($e, 'Error calculating gender stats');

            return [
                'total' => 0,
                'countnam' => 0,
                'countnu' => 0,
            ];
        }
    }

    // ==================== LOP INFO HELPERS ====================
    private function getCurrentLopInfo(): ?object
    {
        if (!$this->selectedLop) {
            return null;
        }

        if ($this->lopCache && $this->lopCache->id === $this->selectedLop) {
            return $this->lopCache;
        }

        try {
            $lop = Lop::with(['blockRelation', 'schoolYear', 'classTeachers.teacher'])
                ->findOrFail($this->selectedLop);

            $lop->slug = url(slug($lop) . config('app.url_prefix', ''));
            $lop->block = $lop->blockRelation->name ?? '';
            $lop->schoolyear = $lop->schoolYear->name ?? '';

            $this->lopCache = $lop;
            return $lop;
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading lop info');
            return null;
        }
    }

    // ==================== ADDRESS HELPERS ====================
    private function getAllTinhThanh(): array
    {
        return Cache::remember('all_tinh_thanh', 3600, function () {
            $filePath = resource_path('cities/tinh_thanhpho.php');
            if (!file_exists($filePath)) {
                return [];
            }
            include $filePath;
            return $tinh_thanhpho ?? [];
        });
    }

    private function getTinhThanhName($provinceId): string
    {
        if (!$provinceId) return '';

        $all = $this->getAllTinhThanh();
        return isset($all[$provinceId]) ? ', ' . $all[$provinceId] : '';
    }

    private function getXaPhuongName($wardId): string
    {
        if (!$wardId) {
            return '';
        }

        return Cache::remember("xa_phuong_{$wardId}", 3600, function () use ($wardId) {
            $filePath = resource_path('cities/xa_phuong_thitran.php');

            if (!file_exists($filePath)) {
                return '';
            }

            include $filePath;

            foreach ($xa_phuong_thitran as $xaphuong) {
                if ($xaphuong['xaid'] == $wardId) {
                    return $xaphuong['name'] ?? '';
                }
            }

            return '';
        });
    }

    // ==================== EVENT HANDLERS ====================
    public function handleFilterChanged($filters)
    {
        if (!is_array($filters)) {
            return;
        }

        if (array_key_exists('namHoc', $filters)) {
            $newNamHoc = is_numeric($filters['namHoc'])
                ? (int) $filters['namHoc']
                : null;

            if ($newNamHoc !== $this->selectedNamHoc) {
                $this->selectedNamHoc = $newNamHoc;
                $this->selectedKhoi = null;
                $this->selectedLop = null;
            }
        }

        if (array_key_exists('khoi', $filters)) {
            $newKhoi = is_numeric($filters['khoi'])
                ? (int) $filters['khoi']
                : null;

            if ($newKhoi !== $this->selectedKhoi) {
                $this->selectedKhoi = $newKhoi;
                $this->selectedLop = null;
            }
        }

        if (array_key_exists('lop', $filters)) {
            $this->selectedLop = is_numeric($filters['lop'])
                ? (int) $filters['lop']
                : null;
        }

        $this->search = '';
        $this->resetPage();
        $this->resetSelection();
    }

    public function resetFilters(): void
    {
        $this->selectedKhoi = null;
        $this->selectedLop = null;
        $this->search = '';
        $this->resetPage();
        $this->resetSelection();

        session()->flash('message', 'Đã đặt lại bộ lọc');
    }

    // ==================== ACTIONS ====================
    private function resetSelection(): void
    {
        $this->selectedStudents = [];
        $this->selectAll = false;
    }

    public function handleRefresh(): void
    {
        $this->resetPage();
        $this->resetSelection();
        session()->flash('message', 'Đã làm mới danh sách học viên');
    }

    // ==================== HELPER METHODS ====================
    protected function getDefaultNamHocId(): ?int
    {
        return \App\Models\NamHoc::ofParish($this->parishId)
            ->active()
            ->orderByDesc('name')
            ->value('id');
    }

    /**
     * Reset tất cả birth year filters
     */
    public function clearBirthYearFilters()
    {
        $this->birthYear = null;
        $this->resetPage('modal_page');
    }

    /**
     * Get quick year options (15 năm gần đây)
     */
    public function getQuickYearOptions(): array
    {
        $currentYear = now()->year;
        $years = [];

        for ($i = 0; $i < 15; $i++) {
            $year = $currentYear - 5 - $i; // Từ 5 năm trước
            $years[$year] = $year;
        }

        return $years;
    }

    // ==================== RENDER ====================
    public function render()
    {
        $students = $this->getStudentsPaginated();
        $stats = $this->getGenderStats();
        $lop = $this->getCurrentLopInfo();

        // Modal 1: Thêm học sinh có sẵn
        $availableStudents = $this->showAddStudentsModal
            ? $this->getAvailableStudentsPaginated()
            : null;

        // ✅ Modal 2: Import từ giáo dân - THÊM DÒNG NÀY
        $availableParishioners = $this->showImportFromParishioners
            ? $this->getAvailableParishionersPaginated()
            : null;

        return view('livewire.student.student-list', [
            'lop' => $lop,
            'students' => $students,
            'total' => $stats['total'],
            'countnam' => $stats['countnam'],
            'countnu' => $stats['countnu'],
            'parishId' => $this->parishId,
            'availableStudents' => $availableStudents,
            'availableParishioners' => $availableParishioners, // ✅ THÊM
        ])
            ->extends('frontend.layout.main')
            ->section('content');
    }
}
