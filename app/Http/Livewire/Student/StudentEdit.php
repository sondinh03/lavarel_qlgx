<?php

namespace App\Http\Livewire\Student;

use App\Http\Livewire\Base\BaseComponent;
use App\Models\Student;
use App\Models\Diocese;
use App\Models\Deanery;
use App\Models\Parish;
use App\Models\ParishChildren;
use App\Models\Holy;
use App\Models\Ethnic;
use App\Models\Career;
use App\Models\Careermanagement;
use App\Models\Level;
use App\Models\Position;
use App\Models\Language;
use App\Models\Catechist;
use App\Models\Ethnicmanagement;
use App\Models\Holymanagement;
use App\Models\Languagemanagement;
use App\Models\Levelmanagement;
use App\Models\Positionmanagement;
use App\Models\Teacher;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Student Edit/Create Component
 * Form chỉnh sửa/tạo mới học sinh với đầy đủ thông tin
 * 
 * Features:
 * - Create/Edit student with all information
 * - Multi-tab form (basic, baptism, more_power, communion, anoint, other)
 * - Dynamic dropdowns for locations and relationships
 * - Real-time validation
 * - Auto-save support
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

    /** @var string Active tab */
    public $activeTab = 'basic';

    /** @var bool Disable pagination */
    protected $usePagination = false;

    // ==================== FORM DATA ====================

    // Basic Info
    public $last_name = '';
    public $name = '';
    public $sex = 1;
    public $birthday = null;
    public $phone = '';
    public $email = '';
    public $cccd = 0;

    // Address - Nguyên quán
    public $origin = '';
    public $ward = '';
    public $province = '';

    // Address - Trú quán
    public $residence = '';
    public $resi_ward = '';
    public $resi_province = '';

    // Family
    public $father = '';
    public $mother = '';

    // Parish & Class
    public $diocese_id = null;
    public $deanery_id = null;
    public $parish_id = null;
    public $paid = null; // Parish Children (Giáo họ)
    public $holy = null;

    // Education & Career
    public $ethnic_id = null;
    public $career_id = null;
    public $level_id = null;
    public $position_id = null;
    public $language_id = null;
    public $professional_level = '';

    // Baptism (Rửa tội)
    public $baptism_date = null;
    public $baptism_number = null;
    public $baptism_giver_id = null;
    public $baptism_sponsor_id = null;
    public $baptism_diocese_id = null;
    public $baptism_deanery_id = null;
    public $baptism_parish_id = null;

    // More Power (Thêm sức)
    public $more_power_date = null;
    public $more_power_number = null;
    public $more_power_giver_id = null;
    public $more_power_sponsor_id = null;
    public $more_power_diocese_id = null;
    public $more_power_deanery_id = null;
    public $more_power_parish_id = null;

    // Communion (Rước lễ)
    public $communion_date = null;
    public $communion_number = '';
    public $communion_giver_id = null;
    public $communion_diocese_id = null;
    public $communion_deanery_id = null;
    public $communion_parish_id = null;

    // Anoint (Xức dầu)
    public $anoint_date = null;
    public $anoint_status = 0;
    public $anoint_giver_id = null;
    public $anoint_note = '';

    // Other Info
    public $study = 0;
    public $new_convert = false;
    public $married = false;
    public $statistical = false;
    public $promise_day = null;
    public $note = '';
    public $status = 1;

    // Die Status
    public $die_status = 0;
    public $die_time = null;
    public $die_lottery = '';
    public $die_death = '';
    public $die_burial = '';

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
    public $communionDeaneries = [];
    public $communionParishes = [];

    // ==================== VALIDATION ====================

    protected function rules()
    {
        $rules = [
            // Basic - Required
            // 'last_name' => 'required|string|max:255',
            // 'name' => 'required|string|max:255',
            'sex' => 'required|in:0,1',

            // Basic - Optional
            'birthday' => 'nullable|date|before:today',
            'phone' => 'nullable|string|max:15',
            'email' => 'nullable|email|max:255',
            // 'cccd' => 'nullable|string|max:20',

            // Address
            // 'origin' => 'nullable|string|max:255',
            // 'ward' => 'nullable|string|max:255',
            // 'province' => 'nullable|string|max:255',
            // 'residence' => 'nullable|string|max:255',
            // 'resi_ward' => 'nullable|string|max:255',
            // 'resi_province' => 'nullable|string|max:255',

            // Family
            // 'father' => 'nullable|string|max:255',
            // 'mother' => 'nullable|string|max:255',

            // Parish - Required
            // 'diocese_id' => 'required|exists:diocese,id',
            // 'deanery_id' => 'required|exists:deanerys,id',
            // 'parish_id' => 'required|exists:parish,id',

            // Parish - Optional
            // 'paid' => 'nullable|exists:parishs,id',
            // 'holy' => 'nullable|exists:holymanagements,id',

            // Education & Career
            // 'ethnic_id' => 'nullable|exists:ethnic,id',
            // 'career_id' => 'nullable|exists:career,id',
            // 'level_id' => 'nullable|exists:level,id',
            // 'position_id' => 'nullable|exists:position,id',
            // 'language_id' => 'nullable|exists:language,id',
            // 'professional_level' => 'nullable|string|max:255',

            // Baptism
            'baptism_date' => 'nullable|date',
            // 'baptism_number' => 'nullable|string|max:50',
            'baptism_giver_id' => 'nullable|exists:catechist,id',
            'baptism_sponsor_id' => 'nullable|exists:catechist,id',
            'baptism_diocese_id' => 'nullable|exists:diocese,id',
            'baptism_deanery_id' => 'nullable|exists:deanerys,id',
            'baptism_parish_id' => 'nullable|exists:parish,id',

            // More Power
            'more_power_date' => 'nullable|date',
            // 'more_power_number' => 'nullable|string|max:50',
            'more_power_giver_id' => 'nullable|exists:catechist,id',
            'more_power_sponsor_id' => 'nullable|exists:catechist,id',
            'more_power_diocese_id' => 'nullable|exists:diocese,id',
            'more_power_deanery_id' => 'nullable|exists:deanerys,id',
            'more_power_parish_id' => 'nullable|exists:parish,id',

            // Communion
            'communion_date' => 'nullable|date',
            'communion_number' => 'nullable|string|max:50',
            'communion_giver_id' => 'nullable|exists:catechist,id',
            'communion_diocese_id' => 'nullable|exists:diocese,id',
            'communion_deanery_id' => 'nullable|exists:deanerys,id',
            'communion_parish_id' => 'nullable|exists:parish,id',

            // Anoint
            'anoint_date' => 'nullable|date',
            'anoint_status' => 'nullable|integer|in:0,1,2',
            'anoint_giver_id' => 'nullable|exists:catechist,id',
            'anoint_note' => 'nullable|string|max:500',

            // Other
            'study' => 'nullable|integer|in:0,1,2,3,4,5,6',
            'new_convert' => 'boolean',
            'married' => 'boolean',
            'statistical' => 'boolean',
            'promise_day' => 'nullable|date',
            'note' => 'nullable|string|max:1000',
            'status' => 'required|boolean',

            // Die Status
            'die_status' => 'required|boolean',
            'die_time' => 'nullable|date',
            'die_lottery' => 'nullable|string|max:50',
            'die_death' => 'nullable|string|max:255',
            'die_burial' => 'nullable|string|max:255',
        ];

        return $rules;
    }

    protected $messages = [
        'last_name.required' => 'Vui lòng nhập họ',
        'name.required' => 'Vui lòng nhập tên',
        'sex.required' => 'Vui lòng chọn giới tính',
        'birthday.before' => 'Ngày sinh phải trước ngày hôm nay',
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

    /**
     * Mount component
     */
    public function mount($id = null): void
    {
        $this->studentId = $id ? (int) $id : null;
        $this->isEdit = $this->studentId !== null;

        parent::mount();

        // Check permission
        $this->requireManager();
    }

    /**
     * Load initial data
     */
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
     * Load all dropdown data
     */
    protected function loadDropdownData(): void
    {
        $this->dioceses = Diocese::orderBy('name')->get(['id', 'name']);
        $this->holies = Holymanagement::orderBy('name')->get(['id', 'name']);
        $this->ethnics = Ethnicmanagement::orderBy('name')->get(['id', 'name']);
        $this->careers = Careermanagement::orderBy('name')->get(['id', 'name']);
        $this->levels = Levelmanagement::orderBy('name')->get(['id', 'name']);
        $this->positions = Positionmanagement::orderBy('name')->get(['id', 'name']);
        $this->languages = Languagemanagement::orderBy('name')->get(['id', 'name']);
        $this->catechists = Teacher::orderBy('name')->get(['id', 'name']);
    }

    /**
     * Load student data for editing
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
            'baptismGiver',
            'baptismSponsor',
            'baptismDiocese',
            'baptismDeanery',
            'baptismParish',
            'morePowerGiver',
            'morePowerSponsor',
            'morePowerDiocese',
            'morePowerDeanery',
            'morePowerParish',
            'communionGiver',
            'communionDiocese',
            'communionDeanery',
            'communionParish',
            'anointGiver',
        ])->findOrFail($this->studentId);

        // Check permission
        if ($this->isDecen && $student->pid != $this->parishId) {
            abort(403, 'Bạn không có quyền chỉnh sửa học sinh này');
        }

        // Map data to properties
        $this->mapStudentToForm($student);
    }

    /**
     * Map student model to form properties
     */
    protected function mapStudentToForm(Student $student): void
    {
        // Basic Info
        $this->last_name = $student->last_name ?? '';
        $this->name = $student->name ?? '';
        $this->sex = $student->sex ?? 1;
        // $this->birthday = $student->birthday ? $student->birthday->format('Y-m-d') : null;
        $this->birthday = $student->birthday ?? '';
        $this->phone = $student->phone ?? '';
        $this->email = $student->email ?? '';
        $this->cccd = $student->cccd ?? 0;

        // Address
        $this->origin = $student->origin ?? '';
        $this->ward = $student->ward ?? '';
        $this->province = $student->province ?? '';
        $this->residence = $student->residence ?? '';
        $this->resi_ward = $student->resi_ward ?? '';
        $this->resi_province = $student->resi_province ?? '';

        // Family
        $this->father = $student->father ?? '';
        $this->mother = $student->mother ?? '';

        // Parish & Class
        $this->diocese_id = $student->diocese_id;
        $this->deanery_id = $student->deanery_id;
        $this->parish_id = $student->pid;
        $this->paid = $student->paid;
        $this->holy = $student->holy;

        // Load dependent dropdowns
        if ($this->diocese_id) {
            $this->updatedDioceseId();
        }
        if ($this->deanery_id) {
            $this->updatedDeaneryId();
        }
        if ($this->parish_id) {
            $this->updatedParishId();
        }

        // Education & Career
        $this->ethnic_id = $student->ethnic_id;
        $this->career_id = $student->career_id;
        $this->level_id = $student->level_id;
        $this->position_id = $student->position_id;
        $this->language_id = $student->language_id;
        $this->professional_level = $student->professional_level ?? '';

        // Baptism
        $this->baptism_date = $student->baptism_date ? $student->baptism_date->format('Y-m-d') : null;
        $this->baptism_number = $student->baptism_number ?? null;
        $this->baptism_giver_id = $student->baptism_giver_id;
        $this->baptism_sponsor_id = $student->baptism_sponsor_id;
        $this->baptism_diocese_id = $student->baptism_diocese_id;
        $this->baptism_deanery_id = $student->baptism_deanery_id;
        $this->baptism_parish_id = $student->baptism_parish_id;

        // More Power
        $this->more_power_date = $student->more_power_date ? $student->more_power_date->format('Y-m-d') : null;
        $this->more_power_number = $student->more_power_number ?? null;
        $this->more_power_giver_id = $student->more_power_giver_id;
        $this->more_power_sponsor_id = $student->more_power_sponsor_id;
        $this->more_power_diocese_id = $student->more_power_diocese_id;
        $this->more_power_deanery_id = $student->more_power_deanery_id;
        $this->more_power_parish_id = $student->more_power_parish_id;

        // Communion
        $this->communion_date = $student->communion_date ? $student->communion_date->format('Y-m-d') : null;
        $this->communion_number = $student->communion_number ?? '';
        $this->communion_giver_id = $student->communion_giver_id;
        $this->communion_diocese_id = $student->communion_diocese_id;
        $this->communion_deanery_id = $student->communion_deanery_id;
        $this->communion_parish_id = $student->communion_parish_id;

        // Anoint
        $this->anoint_date = $student->anoint_date ? $student->anoint_date->format('Y-m-d') : null;
        $this->anoint_status = $student->anoint_status ?? 0;
        $this->anoint_giver_id = $student->anoint_giver_id;
        $this->anoint_note = $student->anoint_note ?? '';

        // Other
        $this->study = $student->study ?? 0;
        $this->new_convert = (bool) $student->new_convert;
        $this->married = (bool) $student->married;
        $this->statistical = (bool) $student->statistical;
        $this->promise_day = $student->promise_day ? $student->promise_day->format('Y-m-d') : null;
        $this->note = $student->note ?? '';
        $this->status = $student->status ?? 1;

        // Die Status
        $this->die_status = $student->die_status ?? 0;
        $this->die_time = $student->die_time ? $student->die_time->format('Y-m-d') : null;
        $this->die_lottery = $student->die_lottery ?? '';
        $this->die_death = $student->die_death ?? '';
        $this->die_burial = $student->die_burial ?? '';
    }

    /**
     * Initialize form for new student
     */
    protected function initializeNewStudent(): void
    {
        // Set default parish from session if available
        if ($this->parishId) {
            $parish = Parish::with('diocese', 'deanery')->find($this->parishId);

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

    /**
     * When diocese changes, reload deaneries
     */
    public function updatedDioceseId(): void
    {
        $this->deaneries = $this->diocese_id
            ? Deanery::where('diocese', $this->diocese_id)->orderBy('name')->get(['id', 'name'])
            : [];

        // Reset dependent fields
        $this->deanery_id = null;
        $this->parish_id = null;
        $this->parishes = [];
        $this->parishChildren = [];
    }

    /**
     * When deanery changes, reload parishes
     */
    public function updatedDeaneryId(): void
    {
        $this->parishes = $this->deanery_id
            ? Parish::where('deanerys', $this->deanery_id)->orderBy('name')->get(['id', 'name'])
            : [];

        // Reset dependent fields
        $this->parish_id = null;
        $this->parishChildren = [];
    }

    /**
     * When parish changes, reload parish children
     */
    public function updatedParishId(): void
    {
        $this->parishChildren = $this->parish_id
            ? Parish::where('pid', $this->parish_id)->orderBy('name')->get(['id', 'name'])
            : [];
    }

    /**
     * When baptism diocese changes
     */
    public function updatedBaptismDioceseId(): void
    {
        $this->baptismDeaneries = $this->baptism_diocese_id
            ? Deanery::where('diocese', $this->baptism_diocese_id)->orderBy('name')->get(['id', 'name'])
            : [];

        $this->baptism_deanery_id = null;
        $this->baptism_parish_id = null;
        $this->baptismParishes = [];
    }

    /**
     * When baptism deanery changes
     */
    public function updatedBaptismDeaneryId(): void
    {
        $this->baptismParishes = $this->baptism_deanery_id
            ? Parish::where('deanerys', $this->baptism_deanery_id)->orderBy('name')->get(['id', 'name'])
            : [];

        $this->baptism_parish_id = null;
    }

    /**
     * When more power diocese changes
     */
    public function updatedMorePowerDioceseId(): void
    {
        $this->morePowerDeaneries = $this->more_power_diocese_id
            ? Deanery::where('diocese', $this->more_power_diocese_id)->orderBy('name')->get(['id', 'name'])
            : [];

        $this->more_power_deanery_id = null;
        $this->more_power_parish_id = null;
        $this->morePowerParishes = [];
    }

    /**
     * When more power deanery changes
     */
    public function updatedMorePowerDeaneryId(): void
    {
        $this->morePowerParishes = $this->more_power_deanery_id
            ? Parish::where('deanerys', $this->more_power_deanery_id)->orderBy('name')->get(['id', 'name'])
            : [];

        $this->more_power_parish_id = null;
    }

    /**
     * When communion diocese changes
     */
    public function updatedCommunionDioceseId(): void
    {
        $this->communionDeaneries = $this->communion_diocese_id
            ? Deanery::where('diocese', $this->communion_diocese_id)->orderBy('name')->get(['id', 'name'])
            : [];

        $this->communion_deanery_id = null;
        $this->communion_parish_id = null;
        $this->communionParishes = [];
    }

    /**
     * When communion deanery changes
     */
    public function updatedCommunionDeaneryId(): void
    {
        $this->communionParishes = $this->communion_deanery_id
            ? Parish::where('deanerys', $this->communion_deanery_id)->orderBy('name')->get(['id', 'name'])
            : [];

        $this->communion_parish_id = null;
    }

    // ==================== TAB NAVIGATION ====================

    /**
     * Switch active tab
     */
    public function switchTab(string $tab): void
    {
        $allowedTabs = ['basic', 'baptism', 'more_power', 'communion', 'anoint', 'other'];

        if (in_array($tab, $allowedTabs)) {
            $this->activeTab = $tab;
        }
    }

    // ==================== ACTIONS ====================

    /**
     * Save student data
     */
    public function save(): void
    {
        $this->requireManager();

        // Validate current tab or all data
        $this->validate();

        try {
            DB::beginTransaction();

            // Create or update student
            $student = $this->isEdit
                ? Student::findOrFail($this->studentId)
                : new Student();

            // Fill basic data
            $student->fill([
                'last_name' => $this->last_name,
                'name' => $this->name,
                'sex' => $this->sex,
                'birthday' => $this->birthday,
                'phone' => $this->phone,
                'email' => $this->email,
                'cccd' => $this->cccd,

                'origin' => $this->origin,
                'ward' => $this->ward,
                'province' => $this->province,
                'residence' => $this->residence,
                'resi_ward' => $this->resi_ward,
                'resi_province' => $this->resi_province,

                'father' => $this->father,
                'mother' => $this->mother,

                'diocese_id' => $this->diocese_id,
                'deanery_id' => $this->deanery_id,
                'pid' => $this->parish_id,
                'paid' => $this->paid,
                'holy' => $this->holy,

                'ethnic_id' => $this->ethnic_id,
                'career_id' => $this->career_id,
                'level_id' => $this->level_id,
                'position_id' => $this->position_id,
                'language_id' => $this->language_id,
                'professional_level' => $this->professional_level,

                'baptism_date' => $this->baptism_date,
                'baptism_number' => $this->baptism_number,
                'baptism_giver_id' => $this->baptism_giver_id,
                'baptism_sponsor_id' => $this->baptism_sponsor_id,
                'baptism_diocese_id' => $this->baptism_diocese_id,
                'baptism_deanery_id' => $this->baptism_deanery_id,
                'baptism_parish_id' => $this->baptism_parish_id,

                'more_power_date' => $this->more_power_date,
                'more_power_number' => $this->more_power_number,
                'more_power_giver_id' => $this->more_power_giver_id,
                'more_power_sponsor_id' => $this->more_power_sponsor_id,
                'more_power_diocese_id' => $this->more_power_diocese_id,
                'more_power_deanery_id' => $this->more_power_deanery_id,
                'more_power_parish_id' => $this->more_power_parish_id,

                'communion_date' => $this->communion_date,
                'communion_number' => $this->communion_number,
                'communion_giver_id' => $this->communion_giver_id,
                'communion_diocese_id' => $this->communion_diocese_id,
                'communion_deanery_id' => $this->communion_deanery_id,
                'communion_parish_id' => $this->communion_parish_id,

                'anoint_date' => $this->anoint_date,
                'anoint_status' => $this->anoint_status,
                'anoint_giver_id' => $this->anoint_giver_id,
                'anoint_note' => $this->anoint_note,

                'study' => $this->study,
                'new_convert' => $this->new_convert,
                'married' => $this->married,
                'statistical' => $this->statistical,
                'promise_day' => $this->promise_day,
                'note' => $this->note,
                'status' => $this->status,

                'die_status' => $this->die_status,
                'die_time' => $this->die_time,
                'die_lottery' => $this->die_lottery,
                'die_death' => $this->die_death,
                'die_burial' => $this->die_burial,
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

    /**
     * Cancel and go back
     */
    public function cancel(): void
    {
        if ($this->isEdit) {
            $this->redirect(route('students.show', $this->studentId));
        } else {
            $this->redirect(route('students.idnex'));
        }
    }

    // ==================== RENDER ====================

    /**
     * Render component
     */
    public function render()
    {
        return view('livewire.student.student-edit', [
            'isLoading' => $this->isLoading,
        ])
            ->extends('frontend.layout.main')
            ->section('content');
    }
}
