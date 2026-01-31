<?php

namespace App\Http\Livewire\Student;

use App\Http\Livewire\Base\BaseComponent;
use App\Models\Student;
use App\Models\Diocese;
use App\Models\Deanery;
use App\Models\Parish;
use App\Models\Holymanagement;
use App\Models\Ethnicmanagement;
use App\Models\Careermanagement;
use App\Models\Levelmanagement;
use App\Models\Positionmanagement;
use App\Models\Languagemanagement;
use App\Models\ParishManagement;
use App\Models\Teacher;
use Illuminate\Support\Facades\DB;

/**
 * Student Edit/Create Component - Optimized for Catechism Students
 * 
 * ✅ CHỈ CÁC TRƯỜNG CÓ TRONG BẢNG STUDENT (học sinh giáo lý)
 * ❌ ĐÃ LOẠI BỎ: Rước lễ, Xức dầu, Qua đời, Trú quán
 * 
 * Features:
 * - Create/Edit student with 4 tabs: basic, baptism, more_power, other
 * - Dynamic dropdowns for locations and relationships
 * - Real-time validation
 */
class StudentEdit extends BaseComponent
{
    // ==================== PROPERTIES ====================

    /** @var int|null Student ID for edit mode */
    public $studentId = null;

    /** @var bool Is edit mode or create mode */
    public $isEdit = false;

    /** @var bool Loading state */
    public $isLoading = true;

    /** @var string Active tab - CHỈ 4 TAB */
    public $activeTab = 'basic'; // basic, baptism, more_power, other

    /** @var bool Disable pagination */
    protected $usePagination = false;

    // ==================== FORM DATA ====================

    // ========== BASIC INFO ==========
    public $last_name = '';
    public $name = '';
    public $sex = 1;
    public $birthday = '';
    public $phone = '';
    public $email = '';
    public $cccd = '';

    // ✅ Address - CHỈ NGUYÊN QUÁN (student table only)
    public $origin = '';
    public $ward = '';
    public $province = '';

    // Family
    public $father = '';
    public $mother = '';

    // ========== PARISH & CLASS ==========
    public $diocese_id = null;
    public $deanery_id = null;
    public $parish_id = null;
    public $paid = null; // Parish Children (Giáo họ)
    public $holy = null;

    // ========== EDUCATION & CAREER ==========
    public $ethnic_id = null;
    public $career_id = null;
    public $level_id = null;
    public $position_id = null;
    public $language_id = null;
    public $professional_level = '';

    // ========== BAPTISM (Rửa tội) - CÓ TRONG STUDENT ==========
    public $baptism_date = null;
    public $baptism_number = '';
    public $baptism_giver_id = null;
    public $baptism_sponsor_id = null;
    public $baptism_diocese_id = null;
    public $baptism_deanery_id = null;
    public $baptism_parish_id = null;

    // ========== MORE POWER (Thêm sức) - CÓ TRONG STUDENT ==========
    public $more_power_date = null;
    public $more_power_number = '';
    public $more_power_giver_id = null;
    public $more_power_sponsor_id = null;
    public $more_power_diocese_id = null;
    public $more_power_deanery_id = null;
    public $more_power_parish_id = null;

    // ========== OTHER INFO ==========
    public $promise_day = null;
    public $note = '';
    public $status = 1;

    // ==================== DROPDOWN DATA ====================

    public $dioceses = [];
    public $deaneries = [];
    public $parishes = [];
    public $parishChildren = [];
    public $holies = [];
    public $ethnics = [];
    public $careers = [];
    public $levels = [];
    public $positions = [];
    public $languages = [];
    public $catechists = [];

    // For sacrament locations
    public $baptismDeaneries = [];
    public $baptismParishes = [];
    public $morePowerDeaneries = [];
    public $morePowerParishes = [];

    // ==================== VALIDATION ====================

    protected function rules()
    {
        return [
            // Basic - Required
            'last_name' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'sex' => 'required|in:1,2',

            // Basic - Optional
            'birthday' => 'nullable|string|max:10',
            'phone' => 'nullable|string|max:15',
            'email' => 'nullable|email|max:255',
            'cccd' => 'nullable|integer',

            // ✅ Address - CHỈ NGUYÊN QUÁN
            'origin' => 'nullable|string|max:255',
            'ward' => 'nullable|integer',
            'province' => 'nullable|string|max:255',

            // Family
            'father' => 'nullable|string|max:255',
            'mother' => 'nullable|string|max:255',

            // Parish - Required
            'diocese_id' => 'required|exists:dioceses,id',
            'deanery_id' => 'required|exists:deanerys,id',
            'parish_id' => 'required|exists:parish_managements,id',

            // Parish - Optional
            'paid' => 'nullable|exists:parishs,id',
            'holy' => 'nullable|exists:holymanagements,id',

            // Education & Career
            'ethnic_id' => 'nullable|exists:ethnicmanagements,id',
            'career_id' => 'nullable|exists:careermanagements,id',
            'level_id' => 'nullable|exists:levelmanagements,id',
            'position_id' => 'nullable|exists:positionmanagements,id',
            'language_id' => 'nullable|exists:languagemanagements,id',
            'professional_level' => 'nullable|string|max:255',

            // ✅ Baptism - CÓ TRONG STUDENT
            'baptism_date' => 'nullable|date',
            'baptism_number' => 'nullable|integer',
            'baptism_giver_id' => 'nullable|exists:teacher,id',
            'baptism_sponsor_id' => 'nullable|exists:teacher,id',
            'baptism_diocese_id' => 'nullable|exists:dioceses,id',
            'baptism_deanery_id' => 'nullable|exists:deanerys,id',
            'baptism_parish_id' => 'nullable|exists:parish_managements,id',

            // ✅ More Power - CÓ TRONG STUDENT
            'more_power_date' => 'nullable|date',
            'more_power_number' => 'nullable|integer',
            'more_power_giver_id' => 'nullable|exists:teacher,id',
            'more_power_sponsor_id' => 'nullable|exists:teacher,id',
            'more_power_diocese_id' => 'nullable|exists:dioceses,id',
            'more_power_deanery_id' => 'nullable|exists:deanerys,id',
            'more_power_parish_id' => 'nullable|exists:parish_managements,id',

            // Other
            'promise_day' => 'nullable|date',
            'note' => 'nullable|string|max:255',
            'status' => 'required|boolean',
        ];
    }

    protected $messages = [
        'last_name.required' => 'Vui lòng nhập họ',
        'name.required' => 'Vui lòng nhập tên',
        'sex.required' => 'Vui lòng chọn giới tính',
        'email.email' => 'Email không hợp lệ',
        'diocese_id.required' => 'Vui lòng chọn giáo phận',
        'deanery_id.required' => 'Vui lòng chọn giáo hạt',
        'parish_id.required' => 'Vui lòng chọn giáo xứ',
    ];

    // ==================== QUERY STRING ====================

    protected function queryString()
    {
        return [
            'activeTab' => ['except' => 'basic', 'as' => 'tab'],
        ];
    }

    // ==================== LIFECYCLE ====================

    public function mount($id = null): void
    {
        $this->studentId = $id ? (int) $id : null;
        $this->isEdit = $this->studentId !== null;

        parent::mount();

        // Check permission
        $this->requireManager();
    }

    protected function loadInitialData(): void
    {
        try {
            // Load all dropdown data
            $this->loadDropdownData();

            if ($this->isEdit) {
                $this->loadStudent();
            } else {
                $this->initializeNewStudent();
            }
        } catch (\Exception $e) {
            $this->logError($e, 'Failed to load initial data');
            session()->flash('error', 'Có lỗi khi tải dữ liệu');
        } finally {
            $this->isLoading = false;
        }
    }

    // ==================== DATA LOADING ====================

    /**
     * ✅ Load only necessary dropdown data
     */
    protected function loadDropdownData(): void
    {
        $this->dioceses = Diocese::orderBy('name')->get(['id', 'name']);
        // $this->deaneries = Deanery::orderBy('name')->get(['id', 'name']);
        $this->holies = Holymanagement::orderBy('name')->get(['id', 'name']);
        $this->ethnics = Ethnicmanagement::orderBy('name')->get(['id', 'name']);
        $this->careers = Careermanagement::orderBy('name')->get(['id', 'name']);
        $this->levels = Levelmanagement::orderBy('name')->get(['id', 'name']);
        $this->positions = Positionmanagement::orderBy('name')->get(['id', 'name']);
        $this->languages = Languagemanagement::orderBy('name')->get(['id', 'name']);
        $this->catechists = Teacher::orderBy('name')->get(['id', 'name']);
    }

    /**
     * ✅ Load student data - CHỈ CÁC TRƯỜNG CÓ TRONG STUDENT TABLE
     */
    protected function loadStudent(): void
    {
        $student = Student::with([
            'diocese',
            'deanery',
            'parish',
            'paidRelation',
            'holyRelation',
            'ethnicRelation',
            'careerRelation',
            'levelRelation',
            'positionRelation',
            'languageRelation',
            // ✅ Baptism relationships
            'baptismGiver',
            'baptismSponsor',
            'baptismDiocese',
            'baptismDeanery',
            'baptismParish',
            // ✅ More Power relationships
            'morePowerGiver',
            'morePowerSponsor',
            'morePowerDiocese',
            'morePowerDeanery',
            'morePowerParish',
        ])->findOrFail($this->studentId);

        // Check permission
        if ($this->isDecen && $student->pid != $this->parishId) {
            abort(403, 'Bạn không có quyền chỉnh sửa học sinh này');
        }

        $this->mapStudentToForm($student);
    }

    /**
     * ✅ Map ONLY fields that exist in student table
     */
    protected function mapStudentToForm(Student $student): void
    {
        // Basic Info
        $this->last_name = $student->last_name ?? '';
        $this->name = $student->name ?? '';
        $this->sex = $student->sex ?? 1;
        $this->birthday = $student->birthday ?? '';
        $this->phone = $student->phone_number ?? '';
        $this->email = $student->email ?? '';
        $this->cccd = $student->cccd ?? '';

        // ✅ Address - CHỈ NGUYÊN QUÁN
        $this->origin = $student->origin ?? '';
        $this->ward = $student->ward ?? '';
        $this->province = $student->province ?? '';

        // Family
        $this->father = $student->father ?? '';
        $this->mother = $student->mother ?? '';

        // Parish & Class
        $this->diocese_id = $student->did;
        $this->deanery_id = $student->deid;
        $this->parish_id = $student->pid;
        $this->paid = $student->paid;
        $this->holy = $student->holy;

        // Load dependent dropdowns

        if ($this->diocese_id) {
            $this->loadDeaneries();
        }

        if ($this->deanery_id) {
            $this->loadParishes();
        }

        if ($this->parish_id) {
            $this->loadParishChildren();
        }

        // Education & Career
        $this->ethnic_id = $student->ethnic;
        $this->career_id = $student->career;
        $this->level_id = $student->level;
        $this->position_id = $student->position;
        $this->language_id = $student->language;
        $this->professional_level = $student->professional_level ?? '';

        // ✅ Baptism - CÓ TRONG STUDENT
        $this->baptism_date = $student->baptism_date ? $student->baptism_date->format('Y-m-d') : null;
        $this->baptism_number = $student->baptism_number ?? '';
        $this->baptism_giver_id = $student->baptism_giver;
        $this->baptism_sponsor_id = $student->baptism_sponsor;
        $this->baptism_diocese_id = $student->baptism_dioceses;
        $this->baptism_deanery_id = $student->baptism_deanerys;
        $this->baptism_parish_id = $student->baptism_parish;

        // Load baptism dropdowns
        // if ($this->baptism_diocese_id) {
        //     $this->updatedBaptismDioceseId();
        // }
        // if ($this->baptism_deanery_id) {
        //     $this->updatedBaptismDeaneryId();
        // }

        if ($this->baptism_diocese_id) {
            $this->loadBaptismDeaneries();
        }

        if ($this->baptism_deanery_id) {
            $this->loadBaptismParishes();
        }

        // ✅ More Power - CÓ TRONG STUDENT
        $this->more_power_date = $student->more_power_date ? $student->more_power_date->format('Y-m-d') : null;
        $this->more_power_number = $student->more_power_number ?? '';
        $this->more_power_giver_id = $student->more_power_giver;
        $this->more_power_sponsor_id = $student->more_power_sponsor;
        $this->more_power_diocese_id = $student->more_power_dioceses;
        $this->more_power_deanery_id = $student->more_power_deanerys;
        $this->more_power_parish_id = $student->more_power_parish;

        // Load more power dropdowns
        // if ($this->more_power_diocese_id) {
        //     $this->updatedMorePowerDioceseId();
        // }
        // if ($this->more_power_deanery_id) {
        //     $this->updatedMorePowerDeaneryId();
        // }

        if ($this->more_power_diocese_id) {
            $this->loadMorePowerDeaneries();
        }

        if ($this->more_power_deanery_id) {
            $this->loadMorePowerParishes();
        }

        // Other
        $this->promise_day = $student->promise_day ? $student->promise_day->format('Y-m-d') : null;
        $this->note = $student->note ?? '';
        $this->status = $student->status ?? 1;

        // dd($this->deanery_id);
    }

    /**
     * ✅ Initialize form for new student
     */
    protected function initializeNewStudent(): void
    {
        // Set default parish from session if available
        if ($this->parishId) {
            $parish = ParishManagement::with('diocese', 'deanery')->find($this->parishId);

            if ($parish) {
                $this->diocese_id = $parish->diocese;
                $this->deanery_id = $parish->deanerys;
                $this->parish_id = $parish->id;

                // Load dependent dropdowns
                $this->updatedDioceseId();
                $this->updatedDeaneryId();
                $this->updatedParishId();
            }
        }
    }

    // ==================== PROPERTY UPDATERS ====================

    public function updatedDioceseId(): void
    {
        $this->deaneries = $this->diocese_id
            ? Deanery::where('did', $this->diocese_id)->orderBy('name')->get(['id', 'name'])
            : [];

        $this->deanery_id = null;
        $this->parish_id = null;
        $this->parishes = [];
        $this->parishChildren = [];
    }

    public function updatedDeaneryId(): void
    {
        $this->parishes = $this->deanery_id
            ? ParishManagement::where('deanerys', $this->deanery_id)->orderBy('name')->get(['id', 'name'])
            : [];

        $this->parish_id = null;
        $this->parishChildren = [];
    }

    public function updatedParishId(): void
    {
        $this->parishChildren = $this->parish_id
            ? Parish::where('pid', $this->parish_id)->orderBy('name')->get(['id', 'name'])
            : [];
    }

    protected function loadDeaneries(): void
    {
        $this->deaneries = $this->diocese_id
            ? Deanery::where('did', $this->diocese_id)
            ->orderBy('name')
            ->get(['id', 'name'])
            : [];
    }

    protected function loadParishes(): void
    {
        $this->parishes = $this->deanery_id
            ? ParishManagement::where('deanerys', $this->deanery_id)
            ->orderBy('name')
            ->get(['id', 'name'])
            : [];
    }

    protected function loadParishChildren(): void
    {
        $this->parishChildren = $this->parish_id
            ? Parish::where('pid', $this->parish_id)
            ->orderBy('name')
            ->get(['id', 'name'])
            : [];
    }

    public function updatedBaptismDioceseId(): void
    {
        $this->baptismDeaneries = $this->baptism_diocese_id
            ? Deanery::where('did', $this->baptism_diocese_id)->orderBy('name')->get(['id', 'name'])
            : [];

        $this->baptism_deanery_id = null;
        $this->baptism_parish_id = null;
        $this->baptismParishes = [];
    }

    public function updatedBaptismDeaneryId(): void
    {
        $this->baptismParishes = $this->baptism_deanery_id
            ? ParishManagement::where('deanerys', $this->baptism_deanery_id)->orderBy('name')->get(['id', 'name'])
            : [];

        $this->baptism_parish_id = null;
    }

    protected function loadBaptismDeaneries(): void
    {
        $this->baptismDeaneries = $this->baptism_diocese_id
            ? Deanery::where('did', $this->baptism_diocese_id)
            ->orderBy('name')
            ->get(['id', 'name'])
            : [];
    }

    protected function loadBaptismParishes(): void
    {
        $this->baptismParishes = $this->baptism_deanery_id
            ? ParishManagement::where('deanerys', $this->baptism_deanery_id)
            ->orderBy('name')
            ->get(['id', 'name'])
            : [];
    }


    public function updatedMorePowerDioceseId(): void
    {
        $this->morePowerDeaneries = $this->more_power_diocese_id
            ? Deanery::where('did', $this->more_power_diocese_id)->orderBy('name')->get(['id', 'name'])
            : [];

        $this->more_power_deanery_id = null;
        $this->more_power_parish_id = null;
        $this->morePowerParishes = [];
    }

    public function updatedMorePowerDeaneryId(): void
    {
        $this->morePowerParishes = $this->more_power_deanery_id
            ? ParishManagement::where('deanerys', $this->more_power_deanery_id)->orderBy('name')->get(['id', 'name'])
            : [];

        $this->more_power_parish_id = null;
    }

    protected function loadMorePowerDeaneries(): void
    {
        $this->morePowerDeaneries = $this->more_power_diocese_id
            ? Deanery::where('did', $this->more_power_diocese_id)
            ->orderBy('name')
            ->get(['id', 'name'])
            : [];
    }

    protected function loadMorePowerParishes(): void
    {
        $this->morePowerParishes = $this->more_power_deanery_id
            ? ParishManagement::where('deanerys', $this->more_power_deanery_id)
            ->orderBy('name')
            ->get(['id', 'name'])
            : [];
    }

    // ==================== TAB NAVIGATION ====================

    /**
     * ✅ Switch tab - CHỈ 4 TAB
     */
    public function switchTab(string $tab): void
    {
        $allowedTabs = ['basic', 'baptism', 'more_power', 'other'];

        if (in_array($tab, $allowedTabs)) {
            $this->activeTab = $tab;
        }
    }

    // ==================== ACTIONS ====================

    /**
     * ✅ Save student - CHỈ CÁC TRƯỜNG CÓ TRONG STUDENT TABLE
     */
    public function save(): void
    {
        $this->requireManager();

        $this->validate();

        try {
            DB::beginTransaction();

            $student = $this->isEdit
                ? Student::findOrFail($this->studentId)
                : new Student();

            // ✅ Fill ONLY fields that exist in student table
            $student->fill([
                // Basic
                'last_name' => $this->last_name,
                'name' => $this->name,
                'sex' => $this->sex,
                'birthday' => $this->birthday,
                'phone_number' => $this->phone,
                'email' => $this->email,
                'cccd' => $this->cccd ? (int) $this->cccd : null,

                // ✅ Address - CHỈ NGUYÊN QUÁN
                'origin' => $this->origin,
                'ward' => $this->ward ? (int) $this->ward : null,
                'province' => $this->province,

                // Family
                'father' => $this->father,
                'mother' => $this->mother,

                // Parish
                'did' => $this->diocese_id,
                'deid' => $this->deanery_id,
                'pid' => $this->parish_id,
                'paid' => $this->paid,
                'holy' => $this->holy,

                // Education & Career
                'ethnic' => $this->ethnic_id,
                'career' => $this->career_id,
                'level' => $this->level_id,
                'position' => $this->position_id,
                'language' => $this->language_id,
                'professional_level' => $this->professional_level,

                // ✅ Baptism
                'baptism_date' => $this->baptism_date,
                'baptism_number' => $this->baptism_number ? (int) $this->baptism_number : null,
                'baptism_giver' => $this->baptism_giver_id,
                'baptism_sponsor' => $this->baptism_sponsor_id,
                'baptism_dioceses' => $this->baptism_diocese_id,
                'baptism_deanerys' => $this->baptism_deanery_id,
                'baptism_parish' => $this->baptism_parish_id,

                // ✅ More Power
                'more_power_date' => $this->more_power_date,
                'more_power_number' => $this->more_power_number ? (int) $this->more_power_number : null,
                'more_power_giver' => $this->more_power_giver_id,
                'more_power_sponsor' => $this->more_power_sponsor_id,
                'more_power_dioceses' => $this->more_power_diocese_id,
                'more_power_deanerys' => $this->more_power_deanery_id,
                'more_power_parish' => $this->more_power_parish_id,

                // Other
                'promise_day' => $this->promise_day,
                'note' => $this->note,
                'status' => $this->status,
            ]);

            $student->save();

            DB::commit();

            $message = $this->isEdit
                ? 'Cập nhật học sinh thành công'
                : 'Thêm học sinh mới thành công';

            session()->flash('message', $message);

            // Redirect to detail page
            $this->redirect(route('students.show', $student->id));
        } catch (\Exception $e) {
            DB::rollBack();

            $this->logError($e, 'Failed to save student', [
                'student_id' => $this->studentId,
                'is_edit' => $this->isEdit,
            ]);

            session()->flash('error', 'Có lỗi xảy ra khi lưu dữ liệu. Vui lòng thử lại.');
        }
    }

    public function cancel(): void
    {
        if ($this->isEdit) {
            $this->redirect(route('students.show', $this->studentId));
        } else {
            $this->redirect(route('classes.index'));
        }
    }

    // ==================== RENDER ====================

    public function render()
    {
        return view('livewire.student.student-edit', [
            'isLoading' => $this->isLoading,
        ])
            ->extends('frontend.layout.main')
            ->section('content');
    }
}
