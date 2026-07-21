<?php

namespace App\Http\Livewire\Teacher;

use App\Http\Livewire\Base\BaseComponent;
use App\Models\Teacher;
use App\Models\User;
use App\Support\CatechistDefaultPassword;
use App\Support\UserAccountEmailResolver;
use Illuminate\Support\Facades\DB;

class TeacherDetail extends BaseComponent
{
    public $teacherId;
    public array $teacherData = [];
    public $isLoading = true;

    protected $usePagination = false;

    protected $listeners = [
        'refresh' => 'loadTeacherData',
        'deleteTeacher' => 'deleteTeacher',
    ];

    public function mount($id = null): void
    {
        $this->requireManager();
        $this->teacherId = (int) $id;

        if ($this->teacherId <= 0) {
            $this->emit('toast', 'error', 'ID giáo lý viên không hợp lệ.');
            $this->redirectRoute('catechists.index');
            return;
        }

        parent::mount();
        $this->requireParishId();
    }

    protected function loadInitialData(): void
    {
        $this->loadTeacherData();
    }

    public function loadTeacherData(): void
    {
        try {
            $this->isLoading = true;

            $teacher = Teacher::with(['parishGroup', 'saint', 'user', 'classes.schoolYear'])
                ->where('parish_id', $this->parishId)
                ->findOrFail($this->teacherId);

            $this->teacherData = [
                'id'                   => $teacher->id,
                'full_name'            => $teacher->full_name,
                'full_name_with_saint' => $teacher->full_name_with_saint,
                'saint_name'           => $teacher->saint->name ?? '',
                'gender_label'         => $teacher->gender_text,
                'birthday'             => $teacher->birthday?->format('d/m/Y') ?? '',
                'phone_number'         => $teacher->phone_number ?? '',
                'email'                => $teacher->email ?? '',
                'address'              => $teacher->address ?? '',
                'parish_group'         => $teacher->parishGroup->name ?? '',
                'is_active'            => $teacher->is_active,
                'status_label'         => $teacher->is_active ? 'Hoạt động' : 'Đã nghỉ',
                'status_badge_class'   => $teacher->is_active
                    ? 'bg-green-100 text-green-700'
                    : 'bg-slate-200 text-slate-600',
                'has_account'          => (bool) $teacher->user_id,
                'login_identifier'     => UserAccountEmailResolver::displayLoginIdentifier(
                    $teacher->user->email ?? null,
                    $teacher->phone_number
                ),
                'login_is_phone'       => $teacher->user
                    ? UserAccountEmailResolver::isSyntheticEmail((string) $teacher->user->email)
                    : false,
                'default_password'     => CatechistDefaultPassword::fromBirthday($teacher->birthday),
                'has_birthday'         => (bool) $teacher->birthday,
                'note'                 => $teacher->note ?? '',
                'created_at'           => $teacher->created_at?->format('d/m/Y H:i') ?? '',
                'updated_at'           => $teacher->updated_at?->format('d/m/Y H:i') ?? '',
                'classes'              => $teacher->classes->map(fn ($c) => [
                    'name'        => $c->name ?? '',
                    'school_year' => $c->schoolYear->name ?? '',
                ])->values()->all(),
            ];
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $this->emit('toast', 'error', 'Không tìm thấy giáo lý viên');
            $this->redirectRoute('catechists.index');
        } catch (\Exception $e) {
            $this->logError($e, 'Failed to load teacher', ['teacher_id' => $this->teacherId]);
            $this->emit('toast', 'error', 'Có lỗi khi tải thông tin giáo lý viên');
        } finally {
            $this->isLoading = false;
        }
    }

    public function edit(): void
    {
        $this->requireManager();
        $this->redirect(route('catechists.edit', $this->teacherId));
    }

    public function deleteTeacher(): void
    {
        $this->requireManager();

        try {
            DB::beginTransaction();

            $teacher = Teacher::where('parish_id', $this->parishId)->findOrFail($this->teacherId);

            if ($teacher->user_id) {
                User::find($teacher->user_id)?->delete();
            }

            $teacher->delete();
            DB::commit();

            $this->emit('toast', 'message', 'Đã xóa giáo lý viên thành công');
            $this->redirect(route('catechists.index'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            $this->emit('toast', 'error', 'Không tìm thấy giáo lý viên này');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError($e, 'Failed to delete teacher', ['teacher_id' => $this->teacherId]);
            $this->emit('toast', 'error', 'Có lỗi khi xóa giáo lý viên');
        }
    }

    public function render()
    {
        return view('livewire.teacher.teacher-detail', [
            'teacher'   => $this->teacherData,
            'isLoading' => $this->isLoading,
        ])
            ->extends('frontend.layout.main')
            ->section('content');
    }
}
