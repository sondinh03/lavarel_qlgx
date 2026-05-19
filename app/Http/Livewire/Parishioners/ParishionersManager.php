<?php

namespace App\Http\Livewire\Parishioners;

use App\Http\Livewire\Base\BaseComponent;
use App\Models\Parishioner;
use App\Models\Student;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ParishionersManager extends BaseComponent
{
    // ==================== FILTERS ====================

    public string $selectedGender   = '';
    public string $selectedAgeGroup = '';
    public string $selectedMarried  = '';
    public string $selectedStatus   = '';
    public string $selectedGroup    = '';
    public string $selectedDeceased = '';  // '' | '0' = còn sống | '1' = đã mất

    // ==================== UI STATE ====================

    public bool $showForm        = false;
    public bool $showStudentLink = false;
    public bool $showSacraments  = false;
    public $showAdvancedFilters = false;

    public ?int $editingId              = null;
    public ?int $linkingParishionerId   = null;
    public ?int $sacramentParishionerId = null;

    public string $activeTab = 'basic';

    // ==================== FORM: CƠ BẢN ====================

    public string  $last_name  = '';
    public string  $first_name = '';
    public string  $gender     = 'male';
    public ?string $birthday   = null;
    public ?int    $birth_order = null;
    public ?int    $saint_id   = null;
    public ?string $cccd       = null;
    public ?string $phone      = null;
    public ?string $email      = null;
    public ?string $note       = null;
    public         $avatar     = null;
    public ?string $currentAvatarPath = null;

    // ==================== FORM: ĐỊA CHỈ ====================

    public ?string $origin               = null;
    public ?string $permanent_province   = null;
    public ?int    $permanent_ward_id    = null;
    public ?string $permanent_residence  = null;
    public ?string $temporary_province   = null;
    public ?int    $temporary_ward_id    = null;
    public ?string $temporary_residence  = null;

    // ==================== FORM: GIA ĐÌNH ====================

    public ?string $father_name = null;
    public ?string $mother_name = null;
    public ?int    $father_id   = null;
    public ?int    $mother_id   = null;
    public ?int    $family_id   = null;
    public int     $married     = 0;

    // ==================== FORM: PHÂN LOẠI ====================

    public ?int    $ethnic            = null;
    public ?int    $career            = null;
    public ?int    $education_level   = null;
    public ?int    $specialist_level  = null;
    public ?int    $catechism_level   = null;
    public ?string $catechism_major   = null;
    public ?int    $position          = null;
    public ?int    $language          = null;
    public ?int    $holy_order_status = null;

    // ==================== FORM: TRẠNG THÁI ====================

    public bool    $status               = true;
    public bool    $is_active            = true;
    public bool    $is_new_convert       = false;
    public bool    $is_included_in_stats = true;

    // ==================== FORM: TỬ VONG ====================

    public bool    $is_deceased       = false;
    public ?string $death_date        = null;
    public ?string $death_book_number = null;
    public ?string $death_place       = null;
    public ?string $burial_place      = null;

    // ==================== DATA ====================

    public array $ageGroups = [
        '0-12'  => 'Thiếu nhi (0-12)',
        '13-18' => 'Thiếu niên (13-18)',
        '19-35' => 'Thanh niên (19-35)',
        '36-60' => 'Trung niên (36-60)',
        '60+'   => 'Cao niên (60+)',
    ];

    public $linkedStudents;

    // ==================== VALIDATION ====================

    protected array $formRules = [
        'last_name'           => 'required|string|max:100',
        'first_name'          => 'required|string|max:100',
        'gender'              => 'required|in:male,female',
        'birthday'            => 'nullable|date|before:today',
        'birth_order'         => 'nullable|integer|min:1',
        'saint_id'            => 'nullable|integer|exists:holymanagements,id',
        'cccd'                => 'nullable|string|max:20',
        'phone'               => 'nullable|string|max:20',
        'email'               => 'nullable|email|max:255',
        'origin'              => 'nullable|string|max:255',
        'permanent_residence' => 'nullable|string|max:255',
        'temporary_residence' => 'nullable|string|max:255',
        'father_name'         => 'nullable|string|max:255',
        'mother_name'         => 'nullable|string|max:255',
        'married'             => 'required|integer|in:0,1,2,3',
        'specialist_level'    => 'nullable|integer',
        'catechism_major'     => 'nullable|string|max:100',
        'status'              => 'required|boolean',
        'note'                => 'nullable|string|max:1000',
        'avatar'              => 'nullable|image|max:2048',
        'family_id'           => 'nullable|integer|exists:families,id',
        'father_id'           => 'nullable|integer|exists:parishioners_new,id',
        'mother_id'           => 'nullable|integer|exists:parishioners_new,id',
        'death_date'          => 'nullable|date|required_if:is_deceased,true',
        'death_book_number'   => 'nullable|string|max:20',
        'death_place'         => 'nullable|string|max:255',
        'burial_place'        => 'nullable|string|max:255',
    ];

    protected $messages = [
        'last_name.required'       => 'Vui lòng nhập họ',
        'first_name.required'      => 'Vui lòng nhập tên',
        'gender.required'          => 'Vui lòng chọn giới tính',
        'birthday.before'          => 'Ngày sinh phải trước hôm nay',
        'email.email'              => 'Email không hợp lệ',
        'avatar.image'             => 'File phải là ảnh',
        'avatar.max'               => 'Ảnh không được quá 2MB',
        'married.in'               => 'Tình trạng hôn nhân không hợp lệ',
        'death_date.required_if'   => 'Vui lòng nhập ngày mất',
    ];

    // ==================== QUERY STRING ====================

    protected function queryString(): array
    {
        return array_merge(parent::queryString(), [
            'selectedGender'   => ['except' => '', 'as' => 'gender'],
            'selectedAgeGroup' => ['except' => '', 'as' => 'age'],
            'selectedMarried'  => ['except' => '', 'as' => 'married'],
            'selectedStatus'   => ['except' => '', 'as' => 'status'],
            'selectedGroup'    => ['except' => '', 'as' => 'group'],
            'selectedDeceased' => ['except' => '', 'as' => 'deceased'],
        ]);
    }

    // ==================== LISTENERS ====================

    protected $listeners = [
        'refresh' => 'handleRefresh',
    ];

    // ==================== LIFECYCLE ====================

    public function mount(): void
    {
        $this->authorize('viewAny', Parishioner::class);
        parent::mount();
        $this->requireParishId();
        $this->linkedStudents = collect();
    }

    protected function loadInitialData(): void {}

    protected function sanitizeQueryString(): void
    {
        parent::sanitizeQueryString();

        if (!in_array($this->selectedGender, ['male', 'female', ''], true)) {
            $this->selectedGender = '';
        }
        if (!in_array($this->selectedMarried, ['0', '1', '2', '3', ''], true)) {
            $this->selectedMarried = '';
        }
        if (!in_array($this->selectedStatus, ['0', '1', ''], true)) {
            $this->selectedStatus = '';
        }
        if (!in_array($this->selectedDeceased, ['0', '1', ''], true)) {
            $this->selectedDeceased = '0'; // mặc định chỉ hiện người còn sống
        }
        if (!array_key_exists($this->selectedAgeGroup, $this->ageGroups) && $this->selectedAgeGroup !== '') {
            $this->selectedAgeGroup = '';
        }
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
    public function updatedSelectedGroup(): void
    {
        $this->resetPage();
    }
    public function updatedSelectedDeceased(): void
    {
        $this->resetPage();
    }

    public function updatedIsDeceased(): void
    {
        if (!$this->is_deceased) {
            $this->death_date        = null;
            $this->death_book_number = null;
            $this->death_place       = null;
            $this->burial_place      = null;
        }
    }

    // ==================== CRUD ====================

    public function create(): void
    {
        $this->authorize('create', Parishioner::class);
        $this->resetForm();
        $this->activeTab = 'basic';
        $this->showForm  = true;
    }

    public function edit(int $id): void
    {
        try {
            $p = Parishioner::ofParish($this->parishId)->findOrFail($id);
            $this->authorize('update', $p);

            $this->editingId         = $p->id;
            $this->activeTab         = 'basic';

            // Cơ bản
            $this->last_name         = $p->last_name;
            $this->first_name        = $p->first_name;
            $this->gender            = $p->gender ?? 'male';
            $this->saint_id          = $p->saint_id;
            $this->birthday          = $p->birthday?->format('Y-m-d');
            $this->birth_order       = $p->birth_order;
            $this->cccd              = $p->cccd;
            $this->phone             = $p->phone;
            $this->email             = $p->email;
            $this->note              = $p->note;
            $this->currentAvatarPath = $p->avatar_path;

            // Địa chỉ
            $this->origin              = $p->origin;
            $this->permanent_province  = $p->permanent_province;
            $this->permanent_ward_id   = $p->permanent_ward_id;
            $this->permanent_residence = $p->permanent_residence;
            $this->temporary_province  = $p->temporary_province;
            $this->temporary_ward_id   = $p->temporary_ward_id;
            $this->temporary_residence = $p->temporary_residence;

            // Gia đình
            $this->father_name = $p->father_name;
            $this->mother_name = $p->mother_name;
            $this->father_id   = $p->father_id;
            $this->mother_id   = $p->mother_id;
            $this->family_id   = $p->family_id;
            $this->married     = $p->married ?? 0;

            // Phân loại
            $this->ethnic           = $p->ethnic;
            $this->career           = $p->career;
            $this->education_level  = $p->education_level;
            $this->specialist_level = $p->specialist_level;
            $this->catechism_level  = $p->catechism_level;
            $this->catechism_major  = $p->catechism_major;
            $this->position         = $p->position;
            $this->language         = $p->language;
            $this->holy_order_status = $p->holy_order_status;

            // Trạng thái
            $this->status               = (bool) $p->status;
            $this->is_active            = (bool) $p->is_active;
            $this->is_new_convert       = (bool) $p->is_new_convert;
            $this->is_included_in_stats = (bool) $p->is_included_in_stats;

            // Tử vong
            $this->is_deceased       = $p->death_date !== null;
            $this->death_date        = $p->death_date?->format('Y-m-d');
            $this->death_book_number = $p->death_book_number;
            $this->death_place       = $p->death_place;
            $this->burial_place      = $p->burial_place;

            $this->showForm = true;
        } catch (ModelNotFoundException) {
            $this->emit('toast', 'error', 'Không tìm thấy giáo dân');
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading parishioner for edit', ['id' => $id]);
            $this->emit('toast', 'error', 'Có lỗi khi tải thông tin');
        }
    }

    public function nextTab(): string
    {
        return match ($this->activeTab) {
            'basic'    => 'address',
            'address'  => 'family',
            'family'   => 'classify',
            'classify' => 'other',
            default    => 'basic',
        };
    }

    public function goToTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    // ==================== TAB VALIDATION ====================

    private array $tabFields = [
        'basic' => [
            'last_name',
            'first_name',
            'gender',
            'birthday',
            'birth_order',
            'cccd',
            'phone',
            'email',
            'avatar',
        ],
        'address' => [
            'origin',
            'permanent_residence',
            'permanent_province',
            'temporary_residence',
            'temporary_province',
        ],
        'family' => [
            'married',
            'father_id',
            'mother_id',
            'family_id',
            'father_name',
            'mother_name',
        ],
        'classify' => [
            'specialist_level',
            'catechism_major',
            'ethnic',
            'career',
            'education_level',
            'catechism_level',
            'position',
            'language',
            'holy_order_status',
        ],
        'other' => [
            'note',
            'status',
            'is_active',
            'is_new_convert',
            'is_included_in_stats',
            'death_date',
            'death_book_number',
            'death_place',
            'burial_place',
        ],
    ];

    private function validateCurrentTab(): bool
    {
        $fields = $this->tabFields[$this->activeTab] ?? [];
        if (empty($fields)) return true;

        // Chỉ lấy rules của các field thuộc tab hiện tại
        $rules = array_intersect_key($this->formRules, array_flip($fields));
        if (empty($rules)) return true;

        try {
            $this->validate($rules, $this->messages);
            return true;
        } catch (\Illuminate\Validation\ValidationException $e) {
            return false;
        }
    }

    public function save(): void
    {
        // Validate tab hiện tại trước khi cho phép next
        if (!$this->validateCurrentTab()) {
            return; // Dừng lại, lỗi đã được set bởi validate()
        }

        // Nếu chưa đến tab cuối → chuyển tab
        if ($this->activeTab !== 'other') {
            $this->activeTab = $this->nextTab();
            return;
        }

        // Tab cuối → validate toàn bộ rồi save
        $this->validate($this->formRules, $this->messages);

        if ($this->editingId) {
            $p = Parishioner::ofParish($this->parishId)->find($this->editingId);
            if (!$p) {
                $this->emit('toast', 'error', 'Không tìm thấy giáo dân');
                return;
            }
            $this->authorize('update', $p);
        } else {
            $this->authorize('create', Parishioner::class);
        }

        try {
            DB::beginTransaction();

            $data = [
                'last_name'             => $this->last_name,
                'first_name'            => $this->first_name,
                'gender'                => $this->gender,
                'saint_id'              => $this->saint_id,
                'birthday'              => $this->birthday ?: null,
                'birth_order'           => $this->birth_order,
                'cccd'                  => $this->cccd,
                'phone'                 => $this->phone,
                'email'                 => $this->email,
                'note'                  => $this->note,
                'parish_id'             => $this->parishId,
                'origin'                => $this->origin,
                'permanent_province'    => $this->permanent_province,
                'permanent_ward_id'     => $this->permanent_ward_id,
                'permanent_residence'   => $this->permanent_residence,
                'temporary_province'    => $this->temporary_province,
                'temporary_ward_id'     => $this->temporary_ward_id,
                'temporary_residence'   => $this->temporary_residence,
                'father_name'           => $this->father_name,
                'mother_name'           => $this->mother_name,
                'father_id'             => $this->father_id,
                'mother_id'             => $this->mother_id,
                'family_id'             => $this->family_id,
                'married'               => $this->married,
                'ethnic'                => $this->ethnic,
                'career'                => $this->career,
                'education_level'       => $this->education_level,
                'specialist_level'      => $this->specialist_level,
                'catechism_level'       => $this->catechism_level,
                'catechism_major'       => $this->catechism_major,
                'position'              => $this->position,
                'language'              => $this->language,
                'holy_order_status'     => $this->holy_order_status,
                'status'                => $this->status,
                'is_active'             => $this->is_active,
                'is_new_convert'        => $this->is_new_convert,
                'is_included_in_stats'  => $this->is_included_in_stats,
                'death_date'            => $this->is_deceased ? ($this->death_date ?: null) : null,
                'death_book_number'     => $this->is_deceased ? $this->death_book_number : null,
                'death_place'           => $this->is_deceased ? $this->death_place : null,
                'burial_place'          => $this->is_deceased ? $this->burial_place : null,
            ];

            if ($this->avatar) {
                if ($this->currentAvatarPath) {
                    Storage::disk('public')->delete($this->currentAvatarPath);
                }
                $data['avatar_path'] = $this->avatar->store('parishioners', 'public');
            }

            Parishioner::updateOrCreate(['id' => $this->editingId], $data);

            DB::commit();

            $this->emit(
                'toast',
                'message',
                $this->editingId ? 'Cập nhật giáo dân thành công' : 'Thêm giáo dân thành công'
            );

            $this->closeModal();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError($e, 'Error saving parishioner', ['editing_id' => $this->editingId]);
            $this->emit('toast', 'error', 'Có lỗi khi lưu dữ liệu. Vui lòng thử lại.');
        }
    }

    public function toggleStatus(int $id): void
    {
        try {
            $p = Parishioner::ofParish($this->parishId)->findOrFail($id);
            $this->authorize('update', $p);
            $p->update(['status' => !$p->status]);
            $this->emit('toast', 'message', $p->status ? 'Đã kích hoạt' : 'Đã tắt');
        } catch (ModelNotFoundException) {
            $this->emit('toast', 'error', 'Không tìm thấy giáo dân');
        } catch (\Exception $e) {
            $this->logError($e, 'Error toggling status', ['id' => $id]);
            $this->emit('toast', 'error', 'Có lỗi khi thay đổi trạng thái');
        }
    }

    public function delete(int $id): void
    {
        try {
            DB::beginTransaction();

            $p = Parishioner::ofParish($this->parishId)->findOrFail($id);
            $this->authorize('delete', $p);

            if (Student::where('parishioner_id', $p->id)->exists()) {
                $this->emit('toast', 'error', 'Không thể xóa — giáo dân đang có học sinh liên kết');
                return;
            }

            if ($p->avatar_path) {
                Storage::disk('public')->delete($p->avatar_path);
            }

            $p->delete();
            DB::commit();

            $this->emit('toast', 'message', 'Đã xóa giáo dân thành công');
        } catch (ModelNotFoundException) {
            DB::rollBack();
            $this->emit('toast', 'error', 'Không tìm thấy giáo dân');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError($e, 'Error deleting parishioner', ['id' => $id]);
            $this->emit('toast', 'error', 'Có lỗi khi xóa');
        }
    }

    // ==================== STUDENT LINKING ====================

    public function openStudentLink(int $parishionerId): void
    {
        try {
            Parishioner::ofParish($this->parishId)->findOrFail($parishionerId);
            $this->linkingParishionerId = $parishionerId;
            $this->linkedStudents = Student::where('parishioner_id', $parishionerId)
                ->with(['lop', 'lop.schoolYear'])
                ->get();
            $this->showStudentLink = true;
        } catch (ModelNotFoundException) {
            $this->emit('toast', 'error', 'Không tìm thấy giáo dân');
        }
    }

    public function closeStudentLink(): void
    {
        $this->showStudentLink      = false;
        $this->linkingParishionerId = null;
        $this->linkedStudents       = collect();
    }

    // ==================== SACRAMENTS ====================

    public function openSacraments(int $parishionerId): void
    {
        try {
            Parishioner::ofParish($this->parishId)->findOrFail($parishionerId);
            $this->sacramentParishionerId = $parishionerId;
            $this->showSacraments = true;
        } catch (ModelNotFoundException) {
            $this->emit('toast', 'error', 'Không tìm thấy giáo dân');
        }
    }

    public function closeSacraments(): void
    {
        $this->showSacraments         = false;
        $this->sacramentParishionerId = null;
    }

    // ==================== FILTERS ====================

    public function resetFilters(): void
    {
        $this->selectedGender   = '';
        $this->selectedAgeGroup = '';
        $this->selectedMarried  = '';
        $this->selectedStatus   = '';
        $this->selectedGroup    = '';
        $this->selectedDeceased = '';
        $this->selectedDeceased = '0';
        $this->search           = '';
        $this->resetPage();
        $this->emit('toast', 'message', 'Đã đặt lại bộ lọc');
    }

    // ==================== DATA LOADING ====================

    private function getParishioners()
    {
        try {
            $query = Parishioner::ofParish($this->parishId)
                ->with(['saint', 'parishGroup', 'student']);

            // Mặc định chỉ hiển thị người còn sống
            if ($this->selectedDeceased === '1') {
                $query->deceased();
            } elseif ($this->selectedDeceased === '0' || $this->selectedDeceased === '') {
                $query->alive();
            }

            if ($this->selectedGender !== '') {
                $query->byGender($this->selectedGender);
            }
            if ($this->selectedMarried !== '') {
                $query->byMarriedStatus((int) $this->selectedMarried);
            }
            if ($this->selectedStatus !== '') {
                $query->where('status', (bool) $this->selectedStatus);
            }
            if ($this->selectedGroup !== '') {
                $query->ofParishGroup((int) $this->selectedGroup);
            }
            if ($this->selectedAgeGroup !== '') {
                [$min, $max] = str_contains($this->selectedAgeGroup, '+')
                    ? [(int) $this->selectedAgeGroup, null]
                    : explode('-', $this->selectedAgeGroup);
                $query->byAgeRange((int) $min, $max ? (int) $max : null);
            }
            if (!empty(trim($this->search))) {
                $query->search($this->search);
            }

            return $query
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->paginate($this->perPage);
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading parishioners');
            $this->emit('toast', 'error', 'Có lỗi khi tải danh sách');
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $this->perPage);
        }
    }

    // ==================== MODAL HELPERS ====================

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
            'saint_id',
            'birthday',
            'birth_order',
            'cccd',
            'phone',
            'email',
            'note',
            'avatar',
            'currentAvatarPath',
            'origin',
            'permanent_province',
            'permanent_ward_id',
            'permanent_residence',
            'temporary_province',
            'temporary_ward_id',
            'temporary_residence',
            'father_name',
            'mother_name',
            'father_id',
            'mother_id',
            'family_id',
            'ethnic',
            'career',
            'education_level',
            'specialist_level',
            'catechism_level',
            'catechism_major',
            'position',
            'language',
            'holy_order_status',
            'death_date',
            'death_book_number',
            'death_place',
            'burial_place',
        ]);

        $this->gender               = 'male';
        $this->married              = 0;
        $this->status               = true;
        $this->is_active            = true;
        $this->is_new_convert       = false;
        $this->is_included_in_stats = true;
        $this->is_deceased          = false;
        $this->activeTab            = 'basic';

        $this->resetValidation();
    }

    public function getParishionersProperty()
    {
        return $this->getParishioners();
    }

    // ==================== RENDER ====================

    public function render()
    {
        return view('livewire.parishioners.parishioners-manager')
            ->extends('frontend.layout.parishioner')->section('content');
    }
}
