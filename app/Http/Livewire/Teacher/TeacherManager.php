<?php

namespace App\Http\Livewire\Teacher;

use App\Http\Livewire\Base\BaseComponent;
use App\Models\ParishGroup;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class TeacherManager extends BaseComponent
{
    public $filterParishGroup = '';
    public $filterGender = '';
    public $filterActive = '';

    public $parishGroups;

    protected function queryString()
    {
        return array_merge([
            'filterParishGroup' => ['except' => ''],
            'filterGender'      => ['except' => ''],
            'filterActive'      => ['except' => ''],
        ], parent::queryString());
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
        $this->parishGroups = ParishGroup::where('parish_id', $this->parishId)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function updatedFilterParishGroup(): void
    {
        $this->resetPage();
    }

    public function updatedFilterGender(): void
    {
        $this->resetPage();
    }

    public function updatedFilterActive(): void
    {
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        $this->requireManager();

        try {
            DB::beginTransaction();

            $teacher = Teacher::where('parish_id', $this->parishId)->findOrFail($id);

            if ($teacher->user_id) {
                User::find($teacher->user_id)?->delete();
            }

            $teacher->delete();
            DB::commit();

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

    public function resetFilters(): void
    {
        $hadFilters = $this->search || $this->filterParishGroup || $this->filterGender || $this->filterActive !== '';

        $this->reset(['search', 'filterParishGroup', 'filterGender', 'filterActive']);
        $this->resetPage();

        if ($hadFilters) {
            $this->emit('toast', 'success', 'Đã đặt lại bộ lọc');
        } else {
            $this->emit('toast', 'warning', 'Không có bộ lọc nào đang được áp dụng');
        }
    }

    private function getTeachersPaginated()
    {
        try {
            $query = Teacher::with(['parishGroup', 'saint', 'user'])
                ->where('parish_id', $this->parishId);

            if (!empty(trim($this->search))) {
                $term = '%' . trim($this->search) . '%';
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

            if ($this->filterGender !== '') {
                $query->where('gender', $this->filterGender);
            }

            if ($this->filterActive !== '') {
                $query->where('is_active', (bool) $this->filterActive);
            }

            return $query
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->paginate($this->perPage);
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading teachers');
            $this->emit('toast', 'error', 'Có lỗi khi tải danh sách giáo lý viên');

            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $this->perPage);
        }
    }

    public function render()
    {
        return view('livewire.teacher.teacher-manager', [
            'teachers' => $this->getTeachersPaginated(),
        ])
            ->extends('frontend.layout.main')
            ->section('content');
    }
}
