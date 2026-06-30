<?php

namespace App\Http\Livewire\Student;

use App\Http\Livewire\Base\BaseComponent;
use App\Models\StudentNew;

class StudentDetail extends BaseComponent
{
    public $studentId;
    public $studentData = [];
    public $isLoading = true;
    public $activeTab = 'basic';
    public $confirmingDelete = false;

    private ?StudentNew $cachedStudent = null;

    protected $usePagination = false;

    protected function queryString()
    {
        return [
            'activeTab' => ['except' => 'basic', 'as' => 'tab'],
        ];
    }

    protected $listeners = [
        'refresh'        => 'loadStudentData',
        'studentUpdated' => 'loadStudentData',
        'deleteStudent'  => 'deleteStudent',
    ];

    // ==================== LIFECYCLE ====================

    public function mount($id = null): void
    {
        $this->studentId = (int) $id;

        if ($this->studentId <= 0) {
            $this->emit('toast', 'error', 'ID học sinh không hợp lệ.');
            $this->redirectRoute('classes.index');
            return;
        }

        parent::mount();
    }

    protected function loadInitialData(): void
    {
        $this->loadStudentData();
    }



    // ==================== DATA LOADING ====================

    private function getStudent()
    {
        return $this->cachedStudent ??= StudentNew::findOrFail($this->studentId);
    }

    public function loadStudentData(): void
    {
        try {
            $this->isLoading = true;

            $student = $this->getStudent();

            $student->load([
                'parish',
                'parishGroup',
                'saint',
                'classes.schoolYear',
            ]);

            $this->authorize('view', $student);

            $this->studentData = $this->mapStudentData($student);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            abort(403, 'Bạn không có quyền xem học sinh này');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $this->emit('toast', 'error', 'Không tìm thấy học sinh');
            $this->redirectRoute('classes.index');
        } catch (\Exception $e) {
            $this->logError($e, 'Failed to load student data', ['student_id' => $this->studentId]);
            $this->emit('toast', 'error', 'Có lỗi xảy ra khi tải thông tin học sinh');
        } finally {
            $this->isLoading = false;
        }
    }



    protected function mapStudentData(StudentNew $student): array
    {
        return [
            'id'           => $student->id,
            'student_code' => $student->student_code ?? 'Chưa có mã',
            'avatar_path'  => $student->avatar_path ?? '',

            'full_name_with_saint' => $student->full_name_with_saint ?? $student->full_name ?? '',
            'full_name'    => $student->full_name,
            'first_name'   => $student->first_name ?? '',
            'last_name'    => $student->last_name ?? '',
            'gender_label' => match ($student->gender) {
                'male'  => 'Nam',
                'female' => 'Nữ',
                default  => 'Chưa xác định',
            },
            'birthday' => $student->birthday?->format('d/m/Y') ?? '',
            'phone'    => $student->phone ?? '',
            'email'    => $student->email ?? '',

            'father_name' => $student->father_name ?? '',
            'mother_name' => $student->mother_name ?? '',

            'parish'       => $student->parish->name ?? '',
            'parish_group' => $student->parishGroup->name ?? '',
            'saint_name'   => $student->saint->name ?? '',

            'parishioner_id'  => $student->parishioner_id,
            'parishioner_url' => $student->parishioner_id
                ? route('parishioners.show', $student->parishioner_id)
                : null,

            'current_class' => $this->getCurrentClassName($student),
            'class_history' => $this->getClassHistory($student),

            'is_active'          => $student->is_active,
            'status_label'       => $student->is_active ? 'Đang học' : 'Ngừng học',
            'status_badge_class' => $student->is_active
                ? 'bg-green-100 text-green-700'
                : 'bg-slate-200 text-slate-600',

            'note'       => $student->note ?? '',
            'created_at' => $student->created_at?->format('d/m/Y H:i') ?? '',
            'updated_at' => $student->updated_at?->format('d/m/Y H:i') ?? '',
        ];
    }

    protected function getCurrentClassName(StudentNew $student): string
    {
        if ($student->relationLoaded('classes') && $student->classes->isNotEmpty()) {
            return $student->classes->first()->name ?? '';
        }
        return '';
    }

    protected function getClassHistory(StudentNew $student): array
    {
        if (!$student->relationLoaded('classes') || $student->classes->isEmpty()) {
            return [];
        }

        return $student->classes->map(function ($class) {
            return [
                'class_name'   => $class->name ?? '',
                'school_year'  => $class->schoolYear->name ?? '',
                'joined_at'    => $class->pivot->created_at?->format('d/m/Y') ?? '',
            ];
        })->toArray();
    }

    // ==================== TAB ====================

    public function switchTab(string $tab): void
    {
        if (in_array($tab, ['basic', 'history'])) {
            $this->activeTab = $tab;
        }
    }

    // ==================== ACTIONS ====================

    public function edit(): void
    {
        $this->authorize('update', $this->getStudent());
        $this->redirect(route('students.edit', ['id' => $this->studentId]));
    }

    public function deleteStudent(): void
    {
        $student = $this->getStudent();
        $this->authorize('delete', $student);

        try {
            if ($student->avatar_path) {
                delete_stored_media($student->avatar_path);
            }

            $student->delete();
            $this->emit('toast', 'message', 'Đã xóa học sinh thành công');
            $this->redirect(route('classes.index'));
        } catch (\Exception $e) {
            $this->logError($e, 'Failed to delete student', ['student_id' => $this->studentId]);
            $this->emit('toast', 'error', 'Có lỗi xảy ra khi xóa học sinh');
        }
    }

    public function printProfile(): void
    {
        $this->dispatch('print-profile');
    }

    public function exportPDF(): void
    {
        $this->redirect(route('student.export-pdf', ['id' => $this->studentId]));
    }

    public function exportLyLichCanhan(): void
    {
        $this->redirect(route('student.export-lylich', ['id' => $this->studentId]));
    }

    public function exportBiTich(): void
    {
        $this->redirect(route('student.export-bitich', ['id' => $this->studentId]));
    }

    // ==================== RENDER ====================

    public function render()
    {
        $layout = auth()->user()?->usesCatechistLayout() ? 'frontend.layout.catechist' : 'frontend.layout.main';

        return view('livewire.student.student-detail', [
            'student'   => $this->studentData,
            'isLoading' => $this->isLoading,
        ])
            ->extends($layout)
            ->section('content');
    }
}
