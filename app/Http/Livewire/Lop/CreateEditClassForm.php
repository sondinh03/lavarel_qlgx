<?php

namespace App\Http\Livewire\Lop;

use App\Models\Block;
use App\Models\Lop;
use App\Models\NamHoc;
use App\Models\Teacher;
use Livewire\Component;

class CreateEditClassForm extends Component
{
    public $classId;
    public $isEdit = false;

    public $form = [
        'symbol' => '',
        'name' => '',
        'schoolyear_id' => '',
        'block_id' => '',
        'start_date_one' => '',
        'end_date_one' => '',
        'start_date_two' => '',
        'end_date_two' => '',
        'main_teacher_id' => '',
        'assistant_teacher_ids' => [],
        'note' => '',
    ];

    public $schoolyears = [];
    public $blocks = [];
    public $teachers = [];

    protected $rules = [
        'form.symbol' => 'required|string|max:50',
        'form.name' => 'required|string|max:255',
        'form.schoolyear_id' => 'required|exists:schoolyears,id',
        'form.block_id' => 'required|exists:blocks,id',
        'form.start_date_one' => 'nullable|date',
        'form.end_date_one' => 'nullable|date|after_or_equal:form.start_date_one',
        'form.start_date_two' => 'nullable|date',
        'form.end_date_two' => 'nullable|date|after_or_equal:form.start_date_two',
        'form.main_teacher_id' => 'nullable|exists:users,id',
        'form.assistant_teacher_ids' => 'nullable|array',
        'form.assistant_teacher_ids.*' => 'exists:users,id',
        'form.note' => 'nullable|string|max:1000',
    ];

    protected $messages = [
        'form.symbol.required' => 'Mã lớp là bắt buộc',
        'form.name.required' => 'Tên lớp là bắt buộc',
        'form.schoolyear_id.required' => 'Vui lòng chọn năm học',
        'form.block_id.required' => 'Vui lòng chọn khối',
        'form.end_date_one.after_or_equal' => 'Ngày kết thúc phải sau ngày bắt đầu',
        'form.end_date_two.after_or_equal' => 'Ngày kết thúc phải sau ngày bắt đầu',
    ];

    public function mount($id = null)
    {
        $this->classId = $id;
        $this->isEdit = !is_null($id);

        // Load dropdown data
        $this->schoolyears = NamHoc::pluck('name', 'id')->toArray();
        $this->blocks = Block::pluck('name', 'id')->toArray();
        $this->teachers = Teacher::pluck('name', 'id')->toArray();

        // Load existing class data if editing
        if ($this->isEdit) {
            $class = Lop::with('teachers')->findOrFail($id);

            $this->form = [
                'symbol' => $class->symbol,
                'name' => $class->name,
                'schoolyear_id' => $class->schoolyear_id,
                'block_id' => $class->block_id,
                'start_date_one' => $class->start_date_one,
                'end_date_one' => $class->end_date_one,
                'start_date_two' => $class->start_date_two,
                'end_date_two' => $class->end_date_two,
                'main_teacher_id' => $class->main_teacher_id ?? '',
                'assistant_teacher_ids' => $class->teachers->where('is_main', false)->pluck('id')->toArray(),
                'note' => $class->note ?? '',
            ];
        }
    }

    public function save()
    {
        $this->validate();

        try {
            if ($this->isEdit) {
                $class = Lop::findOrFail($this->classId);
                $class->update($this->form);
                $message = 'Cập nhật lớp học thành công!';
            } else {
                $class = Lop::create($this->form);
                $message = 'Tạo lớp học thành công!';
            }

            // Sync teachers
            $teacherIds = [];

            // Main teacher
            if (!empty($this->form['main_teacher_id'])) {
                $teacherIds[$this->form['main_teacher_id']] = ['is_main' => true];
            }

            // Assistant teachers
            if (!empty($this->form['assistant_teacher_ids'])) {
                foreach ($this->form['assistant_teacher_ids'] as $teacherId) {
                    if ($teacherId != $this->form['main_teacher_id']) {
                        $teacherIds[$teacherId] = ['is_main' => false];
                    }
                }
            }

            $class->teachers()->sync($teacherIds);

            session()->flash('message', $message);
            return redirect()->route('ds-lop');
        } catch (\Exception $e) {
            session()->flash('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }


    public function render()
    {
        return view('livewire.lop.create-edit-class-form')
            ->extends('frontend.layout.main')
            ->section('content');
    }
}
