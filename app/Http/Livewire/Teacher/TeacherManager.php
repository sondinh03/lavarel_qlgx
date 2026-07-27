<?php

namespace App\Http\Livewire\Teacher;

use App\Exports\TeacherExport;
use App\Http\Livewire\Base\BaseComponent;
use App\Models\ParishGroup;
use App\Models\Teacher;
use App\Models\User;
use App\Services\UploadService;
use App\Support\CatechistPermissions;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

class TeacherManager extends BaseComponent
{
    use WithFileUploads;

    public $filterParishGroup = '';
    public $filterPermission = '';
    public $filterActive = '';

    public $parishGroups;

    /** @var array<int> */
    public array $selectedTeachers = [];

    public bool $selectAll = false;

    public string $sortField = 'first_name';

    protected array $allowedSortFields = ['first_name', 'birthday'];

    /** @var array<int|string, mixed> */
    public array $quickAvatars = [];

    /** Chỉ parish_admin / catechism_admin / super_admin */
    public bool $canQuickUploadAvatars = false;

    protected function queryString()
    {
        return array_merge(parent::queryString(), [
            'filterParishGroup' => ['except' => ''],
            'filterPermission'  => ['except' => ''],
            'filterActive'      => ['except' => ''],
            'sortField'         => ['except' => 'first_name', 'as' => 'sort'],
        ]);
    }

    protected $listeners = [
        'refresh'        => '$refresh',
        'teacherDeleted' => '$refresh',
    ];

    public function mount()
    {
        $this->requireManager();
        parent::mount();
        $this->requireParishId();
    }

    protected function loadInitialData(): void
    {
        $this->canQuickUploadAvatars = (bool) auth()->user()?->canManageCatechism();

        $this->parishGroups = ParishGroup::where('parish_id', $this->parishId)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function updatedQuickAvatars($value, $key): void
    {
        if (! $value || ! $this->canQuickUploadAvatars) {
            return;
        }

        $this->saveQuickAvatar((int) $key);
    }

    public function saveQuickAvatar(int $teacherId): void
    {
        if (! $this->canQuickUploadAvatars) {
            $this->emit('toast', 'error', 'Bạn không có quyền cập nhật ảnh giáo lý viên.');

            return;
        }

        $fileKey = 'quickAvatars.' . $teacherId;

        try {
            $this->validate([
                $fileKey => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
            ], [
                $fileKey . '.required' => 'Vui lòng chọn ảnh.',
                $fileKey . '.image'    => 'Tệp phải là hình ảnh.',
                $fileKey . '.mimes'    => 'Ảnh chỉ chấp nhận JPG, PNG hoặc WEBP.',
                $fileKey . '.max'      => 'Ảnh không được vượt quá 2MB.',
            ]);
        } catch (ValidationException $e) {
            unset($this->quickAvatars[$teacherId]);
            $message = collect($e->validator->errors()->all())->first() ?: 'Ảnh không hợp lệ.';
            $this->emit('toast', 'error', $message);

            return;
        }

        $teacher = Teacher::query()
            ->whereKey($teacherId)
            ->where('parish_id', $this->parishId)
            ->first();

        if (! $teacher) {
            unset($this->quickAvatars[$teacherId]);
            $this->emit('toast', 'error', 'Không tìm thấy giáo lý viên.');

            return;
        }

        $upload = $this->quickAvatars[$teacherId] ?? null;
        if (! $upload) {
            return;
        }

        try {
            $path = app(UploadService::class)->upload($upload, 'avatars');

            if ($teacher->avatar_path) {
                delete_stored_media($teacher->avatar_path);
            }

            $teacher->update(['avatar_path' => $path]);
            unset($this->quickAvatars[$teacherId]);

            $this->emit(
                'toast',
                'message',
                'Đã cập nhật ảnh: ' . trim($teacher->last_name . ' ' . $teacher->first_name)
            );
        } catch (\Exception $e) {
            unset($this->quickAvatars[$teacherId]);
            $this->logError($e, 'Quick teacher avatar upload failed', ['teacher_id' => $teacherId]);
            $this->emit('toast', 'error', 'Không thể lưu ảnh. Vui lòng thử lại.');
        }
    }

    public function updatedFilterParishGroup(): void
    {
        $this->resetPage();
        $this->syncSelectAllForCurrentPage();
    }

    public function updatedFilterPermission(): void
    {
        $this->resetPage();
        $this->syncSelectAllForCurrentPage();
    }

    public function updatedFilterActive(): void
    {
        $this->resetPage();
        $this->syncSelectAllForCurrentPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
        $this->syncSelectAllForCurrentPage();
    }

    public function updatedSelectAll($value): void
    {
        $pageIds = $this->getCurrentPageTeacherIds();

        if ($value) {
            $this->selectedTeachers = array_values(array_unique(array_merge($this->selectedTeachers, $pageIds)));
        } else {
            $this->selectedTeachers = array_values(array_diff($this->selectedTeachers, $pageIds));
        }

        $this->syncSelectAllForCurrentPage();
    }

    public function updatedSelectedTeachers(): void
    {
        $this->selectedTeachers = $this->normalizeIdList($this->selectedTeachers);
        $this->syncSelectAllForCurrentPage();
    }

    public function updatedPage(): void
    {
        $this->syncSelectAllForCurrentPage();
    }

    public function updatedPerPage(): void
    {
        parent::updatedPerPage();
        $this->syncSelectAllForCurrentPage();
    }

    public function sortBy(string $field): void
    {
        parent::sortBy($field);
        $this->syncSelectAllForCurrentPage();
    }

    public function delete(int $id): void
    {
        $this->requireManager();

        try {
            DB::beginTransaction();

            $teacher = Teacher::where('parish_id', $this->parishId)->findOrFail($id);
            $this->deleteTeacherRecord($teacher);

            DB::commit();

            $this->selectedTeachers = array_values(array_diff($this->selectedTeachers, [$id]));
            $this->emit('toast', 'message', 'Đã xóa giáo lý viên thành công');
            $this->emit('teacherDeleted');
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            $this->emit('toast', 'error', 'Không tìm thấy giáo lý viên này');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError($e, 'Error deleting teacher', ['id' => $id]);
            $this->emit('toast', 'error', 'Có lỗi khi xóa giáo lý viên');
        }
    }

    public function deleteSelected(): void
    {
        $this->requireManager();

        $ids = $this->normalizeIdList($this->selectedTeachers);
        if ($ids === []) {
            $this->emit('toast', 'warning', 'Vui lòng chọn giáo lý viên để xóa.');

            return;
        }

        try {
            DB::beginTransaction();

            $teachers = Teacher::query()
                ->where('parish_id', $this->parishId)
                ->whereIn('id', $ids)
                ->get();

            if ($teachers->isEmpty()) {
                DB::rollBack();
                $this->selectedTeachers = [];
                $this->selectAll = false;
                $this->emit('toast', 'error', 'Không tìm thấy giáo lý viên đã chọn');

                return;
            }

            foreach ($teachers as $teacher) {
                $this->deleteTeacherRecord($teacher);
            }

            DB::commit();

            $count = $teachers->count();
            $this->selectedTeachers = [];
            $this->selectAll = false;
            $this->emit('toast', 'message', "Đã xóa {$count} giáo lý viên thành công");
            $this->emit('teacherDeleted');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError($e, 'Error deleting selected teachers', ['ids' => $ids]);
            $this->emit('toast', 'error', 'Có lỗi khi xóa giáo lý viên');
        }
    }

    public function resetFilters(): void
    {
        $hadFilters = $this->search || $this->filterParishGroup || $this->filterPermission || $this->filterActive !== '';

        $this->reset(['search', 'filterParishGroup', 'filterPermission', 'filterActive']);
        $this->resetPage();
        $this->syncSelectAllForCurrentPage();

        if ($hadFilters) {
            $this->emit('toast', 'success', 'Đã đặt lại bộ lọc');
        } else {
            $this->emit('toast', 'warning', 'Không có bộ lọc nào đang được áp dụng');
        }
    }

    public function export()
    {
        $this->requireManager();

        $count = $this->baseTeacherQuery()->count();
        if ($count === 0) {
            $this->emit('toast', 'warning', 'Không có giáo lý viên nào để xuất.');

            return;
        }

        return response()->streamDownload(function () {
            echo Excel::raw(
                new TeacherExport(
                    (int) $this->parishId,
                    $this->filterParishGroup !== '' ? (string) $this->filterParishGroup : null,
                    $this->filterPermission !== '' ? (string) $this->filterPermission : null,
                    $this->filterActive !== '' ? (string) $this->filterActive : null,
                    $this->search !== '' ? (string) $this->search : null,
                ),
                \Maatwebsite\Excel\Excel::XLSX
            );
        }, 'DanhSachGiaoLyVien_' . now()->format('dmY_His') . '.xlsx');
    }

    public function printSelected(): void
    {
        $this->requireManager();

        $ids = $this->normalizeIdList($this->selectedTeachers);
        if ($ids === []) {
            $this->emit('toast', 'warning', 'Vui lòng chọn giáo lý viên để in thẻ.');

            return;
        }

        $this->redirect(route('catechists.print-cards', [
            'ids' => implode(',', $ids),
        ]));
    }

    public function markSelectedInactive(): void
    {
        $this->requireManager();

        $ids = $this->normalizeIdList($this->selectedTeachers);
        if ($ids === []) {
            $this->emit('toast', 'warning', 'Vui lòng chọn giáo lý viên để đánh dấu đã nghỉ.');

            return;
        }

        try {
            $updated = Teacher::query()
                ->where('parish_id', $this->parishId)
                ->whereIn('id', $ids)
                ->where('is_active', true)
                ->update(['is_active' => false]);

            $this->selectedTeachers = [];
            $this->selectAll = false;

            if ($updated === 0) {
                $this->emit('toast', 'warning', 'Các giáo lý viên đã chọn đều đã ở trạng thái đã nghỉ.');

                return;
            }

            $this->emit('toast', 'message', "Đã đánh dấu {$updated} giáo lý viên là đã nghỉ.");
        } catch (\Exception $e) {
            $this->logError($e, 'Error marking teachers inactive', ['ids' => $ids]);
            $this->emit('toast', 'error', 'Có lỗi khi đánh dấu đã nghỉ.');
        }
    }

    private function baseTeacherQuery()
    {
        $query = Teacher::query()->where('parish_id', $this->parishId);

        if (! empty(trim((string) $this->search))) {
            $term = '%' . trim((string) $this->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('first_name', 'like', $term)
                    ->orWhere('last_name', 'like', $term)
                    ->orWhere('phone_number', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('teacher_code', 'like', $term);
            });
        }

        if ($this->filterParishGroup !== '') {
            $query->where('parish_group_id', $this->filterParishGroup);
        }

        $this->applyPermissionFilter($query);

        if ($this->filterActive !== '') {
            $query->where('is_active', (bool) $this->filterActive);
        }

        return $query;
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     */
    private function applyPermissionFilter($query): void
    {
        $filter = (string) $this->filterPermission;
        if ($filter === '') {
            return;
        }

        $elevated = CatechistPermissions::all();

        if ($filter === 'none') {
            $query->where(function ($q) use ($elevated) {
                $q->whereNull('user_id')
                    ->orWhereDoesntHave('user.permissions', function ($pq) use ($elevated) {
                        $pq->whereIn('name', $elevated);
                    });
            });

            return;
        }

        if (! in_array($filter, $elevated, true)) {
            return;
        }

        $query->whereHas('user.permissions', function ($pq) use ($filter) {
            $pq->where('name', $filter);
        });
    }

    /** @return array<string, string> */
    public function permissionFilterOptions(): array
    {
        return array_merge(
            ['none' => 'GLV thường (không hỗ trợ)'],
            CatechistPermissions::labels()
        );
    }

    private function getTeachersPaginated()
    {
        try {
            $sortField = in_array($this->sortField, $this->allowedSortFields, true)
                ? $this->sortField
                : 'first_name';
            $sortDirection = $this->sortDirection === 'desc' ? 'desc' : 'asc';

            $query = $this->baseTeacherQuery()
                ->with(['parishGroup', 'saint', 'user'])
                ->orderBy($sortField, $sortDirection);

            if ($sortField === 'first_name') {
                $query->orderBy('last_name', $sortDirection);
            } elseif ($sortField === 'birthday') {
                $query->orderBy('first_name', 'asc');
            }

            return $query->paginate($this->perPage);
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading teachers');
            $this->emit('toast', 'error', 'Có lỗi khi tải danh sách giáo lý viên');

            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $this->perPage);
        }
    }

    /** @return list<int> */
    private function getCurrentPageTeacherIds(): array
    {
        return $this->getTeachersPaginated()->getCollection()->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    private function syncSelectAllForCurrentPage(): void
    {
        $pageIds = $this->getCurrentPageTeacherIds();
        $this->selectAll = $pageIds !== []
            && count(array_intersect($this->selectedTeachers, $pageIds)) === count($pageIds);
    }

    /** @param  mixed  $ids */
    private function normalizeIdList($ids): array
    {
        return collect(is_array($ids) ? $ids : [])
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();
    }

    private function deleteTeacherRecord(Teacher $teacher): void
    {
        if ($teacher->avatar_path) {
            delete_stored_media($teacher->avatar_path);
        }

        if ($teacher->user_id) {
            User::find($teacher->user_id)?->delete();
        }

        $teacher->delete();
    }

    public function render()
    {
        return view('livewire.teacher.teacher-manager', [
            'teachers' => $this->getTeachersPaginated(),
            'canQuickUploadAvatars' => $this->canQuickUploadAvatars,
            'permissionFilterOptions' => $this->permissionFilterOptions(),
        ])
            ->extends('frontend.layout.main')
            ->section('content');
    }
}
