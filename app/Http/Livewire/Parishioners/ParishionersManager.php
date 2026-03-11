<?php

namespace App\Http\Livewire\Parishioners;

use App\Http\Livewire\Base\BaseComponent;
use App\Models\Parish;
use App\Models\Parishioner;         // ✅ Model mới (số ít)
use App\Models\Student;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Component quản lý Giáo dân
 * Dùng bảng parishioners_new
 */
class ParishionersManager extends BaseComponent
{
    // ==================== FILTERS ====================

    /** @var string Lọc theo giới tính (male/female) */
    public $selectedGender = '';

    /** @var string Lọc theo nhóm tuổi */
    public $selectedAgeGroup = '';

    /** @var string Lọc theo tình trạng hôn nhân */
    public $selectedMarried = '';

    /** @var string Lọc theo trạng thái */
    public $selectedStatus = '';

    // ==================== FORM STATE ====================

    /** @var bool Hiển thị modal form */
    public $showForm = false;

    /** @var int|null ID đang edit (null = create) */
    public $editingId = null;

    /** @var bool Hiển thị modal liên kết học sinh */
    public $showStudentLink = false;

    /** @var int|null ID giáo dân để xem học sinh */
    public $linkingParishionerId = null;

    // ==================== FORM FIELDS - THÔNG TIN CƠ BẢN ====================

    /** @var string Họ */
    public $last_name;

    /** @var string Tên */
    public $first_name;

    /** @var int|null Thánh bổn mạng */
    public $saint_id;

    /** @var string Giới tính (male/female) */
    public $gender = 'male';

    /** @var string|null Ngày sinh */
    public $birthday;

    /** @var string|null CCCD */
    public $cccd;

    /** @var string|null Số điện thoại */
    public $phone;

    /** @var string|null Email */
    public $email;

    /** @var string|null Ghi chú */
    public $note;

    /** @var mixed Ảnh đại diện (upload) */
    public $avatar;

    /** @var string|null Đường dẫn ảnh hiện tại */
    public $currentAvatarPath;

    // ==================== FORM FIELDS - ĐỊA CHỈ ====================

    /** @var string|null Quê quán */
    public $origin;

    /** @var int|null Phường/Xã thường trú */
    public $permanent_ward_id;

    /** @var string|null Tỉnh/TP thường trú */
    public $permanent_province;

    /** @var string|null Địa chỉ thường trú chi tiết */
    public $permanent_residence;

    /** @var int|null Phường/Xã tạm trú */
    public $temporary_ward_id;

    /** @var string|null Tỉnh/TP tạm trú */
    public $temporary_province;

    /** @var string|null Địa chỉ tạm trú chi tiết */
    public $temporary_residence;

    // ==================== FORM FIELDS - GIA ĐÌNH ====================

    /** @var string|null Tên cha */
    public $father_name;

    /** @var string|null Tên mẹ */
    public $mother_name;

    /** @var int Tình trạng hôn nhân */
    public $married = 0;

    // ==================== FORM FIELDS - PHÂN LOẠI ====================

    /** @var int|null Nghề nghiệp */
    public $career;

    /** @var int|null Trình độ học vấn */
    public $education_level;

    /** @var int|null Trình độ giáo lý */
    public $catechism_level;

    /** @var int|null Chức vụ */
    public $position;

    /** @var int|null Ngôn ngữ */
    public $language;

    /** @var int|null Dân tộc */
    public $ethnic;

    // ==================== FORM FIELDS - TRẠNG THÁI ====================

    /** @var bool Trạng thái */
    public $status = true;

    /** @var bool Đang sinh hoạt tại giáo xứ */
    public $is_active = true;

    // ==================== DATA ====================

    /** @var array Nhóm tuổi */
    public $ageGroups = [
        '0-12'  => 'Thiếu nhi (0-12)',
        '13-18' => 'Thiếu niên (13-18)',
        '19-35' => 'Thanh niên (19-35)',
        '36-60' => 'Trung niên (36-60)',
        '60+'   => 'Cao niên (60+)',
    ];

    /** @var \Illuminate\Support\Collection Danh sách học sinh liên kết */
    public $linkedStudents;

    // ==================== VALIDATION ====================

    protected $rules = [
        'selectedGender'   => 'nullable|in:male,female',
        'selectedAgeGroup' => 'nullable|string',
        'selectedMarried'  => 'nullable|in:0,1',
        'selectedStatus'   => 'nullable|in:0,1',
        'search'           => 'nullable|string|max:255',
        'perPage'          => 'required|integer|in:10,15,25,50',
    ];

    protected $formRules = [
        'last_name'           => 'required|string|max:100',
        'first_name'          => 'required|string|max:100',
        'gender'              => 'required|in:male,female',
        'birthday'            => 'nullable|date|before:today',
        'saint_id'            => 'nullable|integer',
        'cccd'                => 'nullable|string|max:20',
        'phone'               => 'nullable|string|max:20',
        'email'               => 'nullable|email|max:255',
        'origin'              => 'nullable|string|max:255',
        'permanent_residence' => 'nullable|string|max:255',
        'temporary_residence' => 'nullable|string|max:255',
        'father_name'         => 'nullable|string|max:255',
        'mother_name'         => 'nullable|string|max:255',
        'married'             => 'required|in:0,1',
        'status'              => 'required|boolean',
        'note'                => 'nullable|string|max:1000',
        'avatar'              => 'nullable|image|max:2048',
    ];

    protected $messages = [
        'last_name.required'  => 'Vui lòng nhập họ',
        'last_name.max'       => 'Họ không được quá 100 ký tự',
        'first_name.required' => 'Vui lòng nhập tên',
        'first_name.max'      => 'Tên không được quá 100 ký tự',
        'gender.required'     => 'Vui lòng chọn giới tính',
        'gender.in'           => 'Giới tính không hợp lệ',
        'birthday.date'       => 'Ngày sinh không hợp lệ',
        'birthday.before'     => 'Ngày sinh phải trước ngày hiện tại',
        'email.email'         => 'Email không hợp lệ',
        'avatar.image'        => 'File phải là ảnh',
        'avatar.max'          => 'Ảnh không được quá 2MB',
    ];

    // ==================== QUERY STRING ====================

    protected function queryString()
    {
        return array_merge([
            'selectedGender'   => ['as' => 'gender', 'except' => ''],
            'selectedAgeGroup' => ['as' => 'age', 'except' => ''],
            'selectedMarried'  => ['as' => 'married', 'except' => ''],
            'selectedStatus'   => ['as' => 'status', 'except' => ''],
        ], parent::queryString());
    }

    // ==================== LISTENERS ====================

    protected $listeners = [
        'refresh'              => 'handleRefresh',
        'parishionerCreated'   => '$refresh',
        'parishionerUpdated'   => '$refresh',
    ];

    // ==================== LIFECYCLE ====================

    public function mount()
    {
        $this->authorize('viewAny', Parishioner::class);
        parent::mount();
        $this->requireParishId();
        $this->linkedStudents = collect();
    }

    protected function loadInitialData(): void
    {
        // Không cần load data trước
    }

    protected function sanitizeQueryString(): void
    {
        parent::sanitizeQueryString();

        if (!in_array($this->selectedGender, ['male', 'female', ''], true)) {
            $this->selectedGender = '';
        }

        if (!in_array($this->selectedMarried, ['0', '1', ''], true)) {
            $this->selectedMarried = '';
        }

        if (!in_array($this->selectedStatus, ['0', '1', ''], true)) {
            $this->selectedStatus = '';
        }

        if (!array_key_exists($this->selectedAgeGroup, $this->ageGroups) && $this->selectedAgeGroup !== '') {
            $this->selectedAgeGroup = '';
        }
    }

    protected function resetToDefaults(): void
    {
        parent::resetToDefaults();
        $this->selectedGender   = '';
        $this->selectedAgeGroup = '';
        $this->selectedMarried  = '';
        $this->selectedStatus   = '';
    }

    // ==================== PROPERTY UPDATERS ====================

    public function updatedSelectedGender(): void
    {
        $this->resetPage();
    }
    public function updatedSelectedAgeGroup(): void
    {
        $this->resetPage();
    }
    public function updatedSelectedMarried(): void
    {
        $this->resetPage();
    }
    public function updatedSelectedStatus(): void
    {
        $this->resetPage();
    }

    // ==================== CRUD ACTIONS ====================

    public function create(): void
    {
        $this->requireManager();
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $this->requireManager();

        try {
            $p = Parishioner::ofParish($this->parishId)->findOrFail($id);

            $this->editingId           = $p->id;
            $this->last_name           = $p->last_name;
            $this->first_name          = $p->first_name;
            $this->gender              = $p->gender ?? 'male';
            $this->saint_id            = $p->saint_id;
            $this->birthday            = $p->birthday?->format('Y-m-d');
            $this->cccd                = $p->cccd;
            $this->phone               = $p->phone;
            $this->email               = $p->email;
            $this->note                = $p->note;
            $this->currentAvatarPath   = $p->avatar_path;

            $this->origin              = $p->origin;
            $this->permanent_ward_id   = $p->permanent_ward_id;
            $this->permanent_province  = $p->permanent_province;
            $this->permanent_residence = $p->permanent_residence;
            $this->temporary_ward_id   = $p->temporary_ward_id;
            $this->temporary_province  = $p->temporary_province;
            $this->temporary_residence = $p->temporary_residence;

            $this->father_name         = $p->father_name;
            $this->mother_name         = $p->mother_name;
            $this->married             = $p->married ?? 0;

            $this->career              = $p->career;
            $this->education_level     = $p->education_level;
            $this->catechism_level     = $p->catechism_level;
            $this->position            = $p->position;
            $this->language            = $p->language;
            $this->ethnic              = $p->ethnic;

            $this->status              = $p->status;
            $this->is_active           = $p->is_active;

            $this->showForm = true;
        } catch (ModelNotFoundException $e) {
            session()->flash('error', 'Không tìm thấy giáo dân này');
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading parishioner for edit', ['id' => $id]);
            session()->flash('error', 'Có lỗi khi tải thông tin giáo dân');
        }
    }

    public function save(): void
    {
        $this->requireManager();
        $this->validate($this->formRules, $this->messages);

        try {
            DB::beginTransaction();

            $data = [
                'last_name'           => $this->last_name,
                'first_name'          => $this->first_name,
                'gender'              => $this->gender,
                'saint_id'            => $this->saint_id,
                'birthday'            => $this->birthday ?: null,
                'cccd'                => $this->cccd,
                'phone'               => $this->phone,
                'email'               => $this->email,
                'note'                => $this->note,
                'parish_id'           => $this->parishId,

                'origin'              => $this->origin,
                'permanent_ward_id'   => $this->permanent_ward_id,
                'permanent_province'  => $this->permanent_province,
                'permanent_residence' => $this->permanent_residence,
                'temporary_ward_id'   => $this->temporary_ward_id,
                'temporary_province'  => $this->temporary_province,
                'temporary_residence' => $this->temporary_residence,

                'father_name'         => $this->father_name,
                'mother_name'         => $this->mother_name,
                'married'             => $this->married,

                'career'              => $this->career,
                'education_level'     => $this->education_level,
                'catechism_level'     => $this->catechism_level,
                'position'            => $this->position,
                'language'            => $this->language,
                'ethnic'              => $this->ethnic,

                'status'              => $this->status,
                'is_active'           => $this->is_active,
            ];

            // Xử lý upload ảnh
            if ($this->avatar) {
                // Xóa ảnh cũ
                if ($this->currentAvatarPath) {
                    Storage::disk('public')->delete($this->currentAvatarPath);
                }
                $data['avatar_path'] = $this->avatar->store('parishioners', 'public');
            }

            // Parishioner::updateOrCreate(
            //     ['id' => $this->editingId],
            //     $data
            // );

            try {
                Parishioner::updateOrCreate(
                    ['id' => $this->editingId], 
                    $data
                );
            } catch (\Exception $e) {
                $this->logError($e, 'Error in updateOrCreate', [
                    'editing_id' => $this->editingId,
                    'data'       => $data,
                ]);
                throw $e; // Rethrow để catch bên ngoài xử lý rollback và flash error
            }

            DB::commit();

            session()->flash(
                'message',
                $this->editingId
                    ? 'Cập nhật giáo dân thành công'
                    : 'Tạo giáo dân mới thành công'
            );

            $this->emit($this->editingId ? 'parishionerUpdated' : 'parishionerCreated');
            $this->resetForm();
            $this->closeModal();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError($e, 'Error saving parishioner', [
                'editing_id' => $this->editingId,
                'name'       => $this->last_name . ' ' . $this->first_name,
            ]);
            session()->flash('error', 'Có lỗi khi lưu dữ liệu. Vui lòng thử lại.');
        }
    }

    public function toggleStatus(int $id): void
    {
        $this->requireManager();

        try {
            $p = Parishioner::ofParish($this->parishId)->findOrFail($id);
            $p->update(['status' => !$p->status]);

            session()->flash('message', $p->status ? 'Đã kích hoạt giáo dân' : 'Đã tắt giáo dân');
        } catch (ModelNotFoundException $e) {
            session()->flash('error', 'Không tìm thấy giáo dân này');
        } catch (\Exception $e) {
            $this->logError($e, 'Error toggling parishioner status', ['id' => $id]);
            session()->flash('error', 'Có lỗi khi thay đổi trạng thái');
        }
    }

    public function delete(int $id): void
    {
        // $this->requireAdmin();

        try {
            DB::beginTransaction();

            $p = Parishioner::ofParish($this->parishId)->findOrFail($id);

            if (Student::where('parishioner_id', $p->id)->exists()) {
                session()->flash('error', 'Không thể xóa giáo dân đang có học sinh liên kết');
                return;
            }

            if ($p->avatar_path) {
                Storage::disk('public')->delete($p->avatar_path);
            }

            $p->delete();
            DB::commit();

            session()->flash('message', 'Đã xóa giáo dân thành công');
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            session()->flash('error', 'Không tìm thấy giáo dân này');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError($e, 'Error deleting parishioner', ['id' => $id]);
            session()->flash('error', 'Có lỗi khi xóa giáo dân');
        }
    }

    // ==================== STUDENT LINKING ====================

    public function openStudentLink(int $parishionerId): void
    {
        $this->requireManager();

        try {
            Parishioner::ofParish($this->parishId)->findOrFail($parishionerId);
            $this->linkingParishionerId = $parishionerId;
            $this->loadLinkedStudents();
            $this->showStudentLink = true;
        } catch (ModelNotFoundException $e) {
            session()->flash('error', 'Không tìm thấy giáo dân này');
        }
    }

    protected function loadLinkedStudents(): void
    {
        if (!$this->linkingParishionerId) {
            $this->linkedStudents = collect();
            return;
        }

        try {
            $this->linkedStudents = Student::where('parishioner_id', $this->linkingParishionerId)
                ->with(['lop', 'lop.schoolYear', 'lop.blockRelation'])
                ->get();
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading linked students');
            $this->linkedStudents = collect();
        }
    }

    public function closeStudentLink(): void
    {
        $this->showStudentLink      = false;
        $this->linkingParishionerId = null;
        $this->linkedStudents       = collect();
    }

    // ==================== DATA LOADING ====================

    private function getParishionersPaginated()
    {
        try {
            $query = Parishioner::ofParish($this->parishId);

            // Lọc giới tính
            if ($this->selectedGender !== '') {
                $query->byGender($this->selectedGender);
            }

            // Lọc hôn nhân
            if ($this->selectedMarried !== '') {
                $query->byMarriedStatus((int) $this->selectedMarried);
            }

            // Lọc trạng thái
            if ($this->selectedStatus !== '') {
                $query->where('status', (bool) $this->selectedStatus);
            }

            // Lọc nhóm tuổi
            if ($this->selectedAgeGroup !== '') {
                $range = explode('-', $this->selectedAgeGroup);

                if (count($range) === 2) {
                    if ($range[1] === '+') {
                        $query->byAgeRange((int) $range[0]);
                    } else {
                        $query->byAgeRange((int) $range[0], (int) $range[1]);
                    }
                }
            }

            // Tìm kiếm
            if (!empty(trim($this->search))) {
                $query->search($this->search);
            }

            return $query
                ->orderBy('last_name', 'asc')
                ->orderBy('first_name', 'asc')
                ->paginate($this->perPage);
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading parishioners');
            session()->flash('error', 'Có lỗi khi tải danh sách giáo dân.');

            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $this->perPage, 1);
        }
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
        $this->reset([
            'editingId',
            'last_name',
            'first_name',
            'gender',
            'saint_id',
            'birthday',
            'cccd',
            'phone',
            'email',
            'note',
            'avatar',
            'currentAvatarPath',
            'origin',
            'permanent_ward_id',
            'permanent_province',
            'permanent_residence',
            'temporary_ward_id',
            'temporary_province',
            'temporary_residence',
            'father_name',
            'mother_name',
            'married',
            'career',
            'education_level',
            'catechism_level',
            'position',
            'language',
            'ethnic',
            'status',
            'is_active',
        ]);

        $this->gender    = 'male';
        $this->married   = 0;
        $this->status    = true;
        $this->is_active = true;

        $this->resetValidation();
    }

    public function resetFilters(): void
    {
        $this->selectedGender   = '';
        $this->selectedAgeGroup = '';
        $this->selectedMarried  = '';
        $this->selectedStatus   = '';
        $this->search           = '';
        $this->resetPage();

        session()->flash('message', 'Đã đặt lại bộ lọc');
    }

    // ==================== RENDER ====================

    public function render()
    {
        return view('livewire.parishioners.parishioners-manager', [
            'parishioners' => $this->getParishionersPaginated(),
            'parishId'     => $this->parishId,
        ])
            ->extends('frontend.layout.main')
            ->section('content');
    }
}
