<?php

namespace App\Http\Livewire\Parishioners;

use App\Http\Livewire\Base\BaseComponent;
use App\Models\Parishioners;
use App\Models\NamHoc;
use App\Models\Lop;
use App\Models\Student;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;

/**
 * Component quản lý Giáo dân
 * 
 * Features:
 * - Phân trang với options
 * - Tìm kiếm theo tên/CCCD/SĐT
 * - Lọc theo giới tính, độ tuổi, trạng thái
 * - CRUD với modal form
 * - Liên kết với học sinh
 * - Upload ảnh đại diện
 * - Import/Export (future)
 */

class ParishionersManager extends BaseComponent
{
    // ==================== FILTERS ====================

    /** @var string|null Lọc theo giới tính (0=nữ, 1=nam) */
    public $selectedSex = '';

    /** @var string|null Lọc theo nhóm tuổi */
    public $selectedAgeGroup = '';

    /** @var string|null Lọc theo trạng thái hôn nhân */
    public $selectedMarried = '';

    /** @var string|null Lọc theo trạng thái */
    public $selectedStatus = '';

    // ==================== FORM STATE ====================

    /** @var bool Hiển thị modal form */
    public $showForm = false;

    /** @var int|null ID giáo dân đang edit (null = create) */
    public $editingId = null;

    /** @var bool Hiển thị modal liên kết học sinh */
    public $showStudentLink = false;

    /** @var int|null ID giáo dân để liên kết */
    public $linkingParishionerId = null;

    // ==================== FORM FIELDS - THÔNG TIN CƠ BẢN ====================

    /** @var string Họ */
    public $last_name;

    /** @var string Tên */
    public $name;

    /** @var int|null Thánh danh */
    public $holy;

    /** @var int Giới tính (0=nữ, 1=nam) */
    public $sex = 1;

    /** @var string|null Ngày sinh */
    public $birthday;

    /** @var string|null CCCD */
    public $cccd;

    /** @var string|null Số điện thoại */
    public $phone;

    /** @var string|null Email */
    public $email;

    // ==================== FORM FIELDS - ĐỊA CHỈ ====================

    /** @var string|null Nguyên quán */
    public $origin;

    /** @var int|null Phường/Xã nguyên quán */
    public $ward;

    /** @var string|null Tỉnh/TP nguyên quán */
    public $province;

    /** @var string|null Nơi ở hiện tại */
    public $residence;

    /** @var int|null Phường/Xã nơi ở */
    public $resi_ward;

    /** @var string|null Tỉnh/TP nơi ở */
    public $resi_province;

    // ==================== FORM FIELDS - GIA ĐÌNH ====================

    /** @var string|null Cha */
    public $father;

    /** @var string|null Mẹ */
    public $mother;

    /** @var int Tình trạng hôn nhân */
    public $married = 0;

    // ==================== FORM FIELDS - NGHỀ NGHIỆP/HỌC VẤN ====================

    /** @var int|null Nghề nghiệp */
    public $career;

    /** @var string|null Trình độ chuyên môn */
    public $professional_level;

    /** @var int|null Trình độ học vấn */
    public $level;

    /** @var int|null Chức vụ */
    public $position;

    // ==================== FORM FIELDS - THÁNH SỰ ====================

    /** @var string|null Ngày rửa tội */
    public $baptism_date;

    /** @var int|null Số rửa tội */
    public $baptism_number;

    /** @var int|null Người làm phép */
    public $baptism_giver;

    /** @var int|null Cha/Mẹ đỡ đầu */
    public $baptism_sponsor;

    /** @var string|null Ngày thêm sức */
    public $more_power_date;

    /** @var int|null Số thêm sức */
    public $more_power_number;

    /** @var string|null Ngày rước lễ lần đầu */
    public $communion_date;

    // ==================== FORM FIELDS - KHÁC ====================

    /** @var int Trạng thái */
    public $status = 1;

    /** @var string|null Ghi chú */
    public $note;

    /** @var mixed Ảnh đại diện (upload) */
    public $image;

    /** @var string|null URL ảnh hiện tại */
    public $currentImage;

    // ==================== DATA ====================

    /** @var array Nhóm tuổi */
    public $ageGroups = [
        '0-12' => 'Thiếu nhi (0-12)',
        '13-18' => 'Thiếu niên (13-18)',
        '19-35' => 'Thanh niên (19-35)',
        '36-60' => 'Trung niên (36-60)',
        '60+' => 'Cao niên (60+)',
    ];

    /** @var \Illuminate\Support\Collection Danh sách học sinh của giáo dân */
    public $linkedStudents;

    // ==================== VALIDATION ====================

    protected $rules = [
        'selectedSex' => 'nullable|in:0,1',
        'selectedAgeGroup' => 'nullable|string',
        'selectedMarried' => 'nullable|in:0,1',
        'selectedStatus' => 'nullable|in:0,1',
        'search' => 'nullable|string|max:255',
        'perPage' => 'required|integer|in:10,15,25,50',
    ];

    /**
     * Rules riêng cho form - chỉ dùng khi save
     */
    protected $formRules = [
        'last_name' => 'required|string|max:255',
        'name' => 'required|string|max:255',
        'sex' => 'required|in:0,1',
        'birthday' => 'nullable|date|before:today',
        'cccd' => 'nullable|string|max:20',
        'phone' => 'nullable|string|max:20',
        'email' => 'nullable|email|max:255',
        'origin' => 'nullable|string|max:255',
        'residence' => 'nullable|string|max:255',
        'father' => 'nullable|string|max:255',
        'mother' => 'nullable|string|max:255',
        'married' => 'required|in:0,1',
        'professional_level' => 'nullable|string|max:255',
        'baptism_date' => 'nullable|date',
        'more_power_date' => 'nullable|date',
        'communion_date' => 'nullable|date',
        'status' => 'required|boolean',
        'note' => 'nullable|string|max:1000',
        'image' => 'nullable|image|max:2048', // 2MB max
    ];

    /**
     * Custom validation messages
     */
    protected $messages = [
        'last_name.required' => 'Vui lòng nhập họ',
        'last_name.max' => 'Họ không được quá 255 ký tự',
        'name.required' => 'Vui lòng nhập tên',
        'name.max' => 'Tên không được quá 255 ký tự',
        'sex.required' => 'Vui lòng chọn giới tính',
        'birthday.date' => 'Ngày sinh không hợp lệ',
        'birthday.before' => 'Ngày sinh phải trước ngày hiện tại',
        'email.email' => 'Email không hợp lệ',
        'image.image' => 'File phải là ảnh',
        'image.max' => 'Ảnh không được quá 2MB',
    ];

    // ==================== QUERY STRING ====================

    protected function queryString()
    {
        return array_merge([
            'selectedSex' => ['as' => 'sex', 'except' => ''],
            'selectedAgeGroup' => ['as' => 'age', 'except' => ''],
            'selectedMarried' => ['as' => 'married', 'except' => ''],
            'selectedStatus' => ['as' => 'status', 'except' => ''],
        ], parent::queryString());
    }

    // ==================== LISTENERS ====================

    protected $listeners = [
        'refresh' => 'handleRefresh',
        'parishionerCreated' => '$refresh',
        'parishionerUpdated' => '$refresh',
    ];

    // ==================== LIFECYCLE ====================

    /**
     * Component initialization
     */
    public function mount()
    {
        $this->authorize('viewAny', Parishioners::class);
        parent::mount();

        // Bắt buộc phải có parish_id
        $this->requireParishId();

        $this->linkedStudents = collect();
    }

    /**
     * Load dữ liệu ban đầu (required by BaseComponent)
     */
    protected function loadInitialData(): void
    {
        // No initial data needed
    }

    /**
     * Override sanitizeQueryString để xử lý thêm filters
     */
    protected function sanitizeQueryString(): void
    {
        parent::sanitizeQueryString();

        // Sanitize filters
        if (!in_array($this->selectedSex, ['0', '1', ''], true)) {
            $this->selectedSex = '';
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

    /**
     * Override resetToDefaults để reset thêm filters
     */
    protected function resetToDefaults(): void
    {
        parent::resetToDefaults();
        $this->selectedSex = '';
        $this->selectedAgeGroup = '';
        $this->selectedMarried = '';
        $this->selectedStatus = '';
    }

    // ==================== PROPERTY UPDATERS ====================

    /**
     * Khi search thay đổi
     */
    public function updatedSearch(): void
    {
        parent::updatedSearch();
    }

    /**
     * Khi filter thay đổi
     */
    public function updatedSelectedSex(): void
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

    /**
     * Mở form tạo mới
     */
    public function create(): void
    {
        $this->requireManager();
        $this->resetForm();
        $this->showForm = true;
    }

    /**
     * Mở form edit
     */
    public function edit(int $id): void
    {
        $this->requireManager();

        try {
            $parishioner = Parishioners::where('pid', $this->parishId)
                ->findOrFail($id);

            $this->editingId = $parishioner->id;
            $this->last_name = $parishioner->last_name ?? '';
            $this->name = $parishioner->name ?? '';
            $this->holy = $parishioner->holy;
            $this->sex = $parishioner->sex ?? 1;
            $this->birthday = $parishioner->birthday?->format('Y-m-d');
            $this->cccd = $parishioner->cccd;
            $this->phone = $parishioner->phone;
            $this->email = $parishioner->email;
            $this->origin = $parishioner->origin;
            $this->ward = $parishioner->ward;
            $this->province = $parishioner->province;
            $this->residence = $parishioner->residence;
            $this->resi_ward = $parishioner->resi_ward;
            $this->resi_province = $parishioner->resi_province;
            $this->father = $parishioner->father;
            $this->mother = $parishioner->mother;
            $this->married = $parishioner->married ?? 0;
            $this->career = $parishioner->career;
            $this->professional_level = $parishioner->professional_level;
            $this->level = $parishioner->level;
            $this->position = $parishioner->position;
            $this->baptism_date = $parishioner->baptism_date?->format('Y-m-d');
            $this->baptism_number = $parishioner->baptism_number;
            $this->baptism_giver = $parishioner->baptism_giver;
            $this->baptism_sponsor = $parishioner->baptism_sponsor;
            $this->more_power_date = $parishioner->more_power_date?->format('Y-m-d');
            $this->more_power_number = $parishioner->more_power_number;
            $this->communion_date = $parishioner->communion_date?->format('Y-m-d');
            $this->status = $parishioner->status;
            $this->note = $parishioner->note;
            $this->currentImage = $parishioner->image;

            $this->showForm = true;
        } catch (ModelNotFoundException $e) {
            session()->flash('error', 'Không tìm thấy giáo dân này');
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading parishioner for edit', ['id' => $id]);
            session()->flash('error', 'Có lỗi khi tải thông tin giáo dân');
        }
    }

    /**
     * Lưu (create hoặc update)
     */
    public function save(): void
    {
        $this->requireManager();

        $this->validate($this->formRules, $this->messages);

        try {
            DB::beginTransaction();

            $data = [
                'last_name' => $this->last_name,
                'name' => $this->name,
                'holy' => $this->holy,
                'pid' => $this->parishId,
                'sex' => $this->sex,
                'birthday' => $this->birthday ?: null,
                'cccd' => $this->cccd,
                'phone' => $this->phone,
                'email' => $this->email,
                'origin' => $this->origin,
                'ward' => $this->ward,
                'province' => $this->province,
                'residence' => $this->residence,
                'resi_ward' => $this->resi_ward,
                'resi_province' => $this->resi_province,
                'father' => $this->father,
                'mother' => $this->mother,
                'married' => $this->married,
                'career' => $this->career,
                'professional_level' => $this->professional_level,
                'level' => $this->level,
                'position' => $this->position,
                'baptism_date' => $this->baptism_date ?: null,
                'baptism_number' => $this->baptism_number,
                'baptism_giver' => $this->baptism_giver,
                'baptism_sponsor' => $this->baptism_sponsor,
                'more_power_date' => $this->more_power_date ?: null,
                'more_power_number' => $this->more_power_number,
                'communion_date' => $this->communion_date ?: null,
                'status' => $this->status,
                'note' => $this->note,
                // Default values for required fields
                'deid' => $this->editingId ? Parishioners::find($this->editingId)->deid : 0,
                'did' => $this->editingId ? Parishioners::find($this->editingId)->did : 0,
                'paid' => $this->editingId ? Parishioners::find($this->editingId)->paid : 0,
                'assid' => $this->editingId ? Parishioners::find($this->editingId)->assid : 0,
            ];

            // Handle image upload
            if ($this->image) {
                // Delete old image if exists
                if ($this->editingId && $this->currentImage) {
                    Storage::delete($this->currentImage);
                }

                $data['image'] = $this->image->store('parishioners', 'public');
            }

            Parishioners::updateOrCreate(
                ['id' => $this->editingId],
                $data
            );

            DB::commit();

            $message = $this->editingId
                ? 'Cập nhật giáo dân thành công'
                : 'Tạo giáo dân mới thành công';

            session()->flash('message', $message);

            $this->resetForm();
            $this->closeModal();

            $this->emit($this->editingId ? 'parishionerUpdated' : 'parishionerCreated');
        } catch (\Exception $e) {
            DB::rollBack();

            $this->logError($e, 'Error saving parishioner', [
                'editing_id' => $this->editingId,
                'name' => $this->last_name . ' ' . $this->name,
            ]);

            session()->flash('error', 'Có lỗi khi lưu dữ liệu. Vui lòng thử lại.');
        }
    }

    /**
     * Toggle status giáo dân
     */
    public function toggleStatus(int $id): void
    {
        $this->requireManager();

        try {
            $parishioner = Parishioners::where('pid', $this->parishId)
                ->findOrFail($id);

            $parishioner->update(['status' => !$parishioner->status]);

            $message = $parishioner->status
                ? 'Đã kích hoạt giáo dân'
                : 'Đã tắt giáo dân';

            session()->flash('message', $message);
        } catch (ModelNotFoundException $e) {
            session()->flash('error', 'Không tìm thấy giáo dân này');
        } catch (\Exception $e) {
            $this->logError($e, 'Error toggling parishioner status', ['id' => $id]);
            session()->flash('error', 'Có lỗi khi thay đổi trạng thái giáo dân');
        }
    }

    /**
     * Xóa giáo dân
     */
    public function delete(int $id): void
    {
        $this->requireAdmin(); // Chỉ admin mới được xóa

        try {
            DB::beginTransaction();

            $parishioner = Parishioners::where('pid', $this->parishId)
                ->findOrFail($id);

            // Check if linked to students
            $hasStudents = Student::where('parishioner_id', $parishioner->id)->exists();

            if ($hasStudents) {
                session()->flash('error', 'Không thể xóa giáo dân đang có học sinh liên kết');
                return;
            }

            // Delete image if exists
            if ($parishioner->image) {
                Storage::delete($parishioner->image);
            }

            $parishioner->delete();

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

    /**
     * Mở modal liên kết học sinh
     */
    public function openStudentLink(int $parishionerId): void
    {
        $this->requireManager();

        try {
            $parishioner = Parishioners::where('pid', $this->parishId)
                ->findOrFail($parishionerId);

            $this->linkingParishionerId = $parishionerId;

            // Load học sinh hiện tại
            $this->loadLinkedStudents();

            $this->showStudentLink = true;
        } catch (ModelNotFoundException $e) {
            session()->flash('error', 'Không tìm thấy giáo dân này');
        }
    }

    /**
     * Load danh sách học sinh liên kết
     */
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

    /**
     * Đóng modal liên kết học sinh
     */
    public function closeStudentLink(): void
    {
        $this->showStudentLink = false;
        $this->linkingParishionerId = null;
        $this->linkedStudents = collect();
    }

    // ==================== DATA LOADING ====================

    /**
     * Get paginated parishioners với filters
     */
    private function getParishionersPaginated()
    {
        try {
            $query = Parishioners::where('pid', $this->parishId);

            // Filter by sex
            if ($this->selectedSex !== '') {
                $query->where('sex', $this->selectedSex);
            }

            // Filter by married status
            if ($this->selectedMarried !== '') {
                $query->where('married', $this->selectedMarried);
            }

            // Filter by status
            if ($this->selectedStatus !== '') {
                $query->where('status', $this->selectedStatus);
            }

            // Filter by age group
            if ($this->selectedAgeGroup !== '') {
                $range = explode('-', $this->selectedAgeGroup);

                if (count($range) === 2) {
                    if ($range[1] === '+') {
                        // 60+
                        $minDate = now()->subYears((int)$range[0])->format('Y-m-d');
                        $query->where('birthday', '<=', $minDate);
                    } else {
                        // Normal range like 13-18
                        $minDate = now()->subYears((int)$range[1])->format('Y-m-d');
                        $maxDate = now()->subYears((int)$range[0])->format('Y-m-d');
                        $query->whereBetween('birthday', [$minDate, $maxDate]);
                    }
                }
            }

            // Search
            if (!empty(trim($this->search))) {
                $searchTerm = '%' . trim($this->search) . '%';
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('last_name', 'like', $searchTerm)
                        ->orWhere('name', 'like', $searchTerm)
                        ->orWhere('cccd', 'like', $searchTerm)
                        ->orWhere('phone', 'like', $searchTerm)
                        ->orWhere(DB::raw("CONCAT(last_name, ' ', name)"), 'like', $searchTerm);
                });
            }

            // Order by name
            $query->orderBy('last_name', 'asc')
                ->orderBy('name', 'asc');

            return $query->paginate($this->perPage);
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading parishioners', [
                'search' => $this->search,
                'filters' => [
                    'sex' => $this->selectedSex,
                    'age' => $this->selectedAgeGroup,
                    'married' => $this->selectedMarried,
                    'status' => $this->selectedStatus,
                ],
            ]);

            session()->flash('error', 'Có lỗi khi tải danh sách giáo dân.');

            return new \Illuminate\Pagination\LengthAwarePaginator(
                [],
                0,
                $this->perPage,
                $this->page ?? 1
            );
        }
    }

    /**
     * Tính tuổi từ ngày sinh
     */
    public function calculateAge($birthday): ?int
    {
        if (!$birthday) {
            return null;
        }

        return \Carbon\Carbon::parse($birthday)->age;
    }

    // ==================== FORM HELPERS ====================

    /**
     * Đóng modal
     */
    public function closeModal()
    {
        $this->showForm = false;
        $this->resetForm();
        $this->resetValidation();
    }

    /**
     * Reset form về trạng thái mặc định
     */
    public function resetForm(): void
    {
        $this->reset([
            'editingId',
            'last_name',
            'name',
            'holy',
            'sex',
            'birthday',
            'cccd',
            'phone',
            'email',
            'origin',
            'ward',
            'province',
            'residence',
            'resi_ward',
            'resi_province',
            'father',
            'mother',
            'married',
            'career',
            'professional_level',
            'level',
            'position',
            'baptism_date',
            'baptism_number',
            'baptism_giver',
            'baptism_sponsor',
            'more_power_date',
            'more_power_number',
            'communion_date',
            'status',
            'note',
            'image',
            'currentImage',
        ]);

        $this->sex = 1; // Default male
        $this->married = 0; // Default single
        $this->status = 1; // Default active
        $this->resetValidation();
    }

    /**
     * Reset filters
     */
    public function resetFilters(): void
    {
        $this->selectedSex = '';
        $this->selectedAgeGroup = '';
        $this->selectedMarried = '';
        $this->selectedStatus = '';
        $this->search = '';
        $this->resetPage();

        session()->flash('message', 'Đã đặt lại bộ lọc');
    }

    // ==================== RENDER ====================

    /**
     * Render component
     */
    public function render()
    {
        $parishioners = $this->getParishionersPaginated();

        return view('livewire.parishioners.parishioners-manager', [
            'parishioners' => $parishioners,
            'parishId' => $this->parishId,
        ])
            ->extends('frontend.layout.main')
            ->section('content');
    }
}
