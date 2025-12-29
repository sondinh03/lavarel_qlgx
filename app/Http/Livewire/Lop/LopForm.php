<?php

namespace App\Http\Livewire\Lop;

use App\Models\Block;
use App\Models\Lop;
use App\Models\NamHoc;
use App\Models\Teacher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class LopForm extends Component
{
    public $classId;
    public $isEdit = false;
    public $parish_id;

    public $form = [
        'symbol' => '',
        'name' => '',
        'schoolyear' => '',
        'block' => '',
        'note' => null,
        'status' => 1,
    ];

    public $schoolyears = [];
    public $blocks = [];
    public $teachers = [];

    protected $rules = [
        'form.symbol' => 'required|string|max:50',
        'form.name' => 'required|string|max:255',
        'form.schoolyear' => 'required|exists:nam_hoc,id',
        'form.block' => 'required|exists:block,id',
        'form.note' => 'nullable|string|max:1000',
    ];

    protected $messages = [
        'form.symbol.required' => 'Mã lớp là bắt buộc',
        'form.name.required' => 'Tên lớp là bắt buộc',
        'form.schoolyear.required' => 'Vui lòng chọn năm học',
        'form.block.required' => 'Vui lòng chọn khối',
    ];

    public function mount($id = null)
    {
        $this->parish_id = session('parish_id');
        $this->classId = $id;
        $this->isEdit = !is_null($id);

        $this->schoolyears = NamHoc::where('parish_id', $this->parish_id)
            ->orderBy('name', 'desc')
            ->pluck('name', 'id')
            ->toArray();

        $this->teachers = Teacher::pluck('name', 'id')->toArray();

        if ($this->isEdit) {
            $class = Lop::with('teachers')->findOrFail($id);

            $this->form = [
                'symbol' => $class->symbol,
                'name' => $class->name,
                'schoolyear' => $class->schoolyear,
                'block' => $class->block,
                'note' => $class->note ?? '',
                'status' => $class->status,
            ];

            // Load blocks theo schoolyear
            $this->blocks = Block::where('namhoc', $class->schoolyear)
                ->where('pid', $this->parish_id)
                ->pluck('name', 'id')
                ->toArray();
        }
    }

    public function updatedFormSchoolyear($schoolyear)
    {
        $this->blocks = Block::where('namhoc', $schoolyear)
            ->where('pid', $this->parish_id)
            ->pluck('name', 'id')
            ->toArray();

        $this->form['block'] = '';
    }

    public function save()
    {
        $this->validate();

        DB::beginTransaction();

        try {
            if ($this->isEdit) {
                $class = Lop::findOrFail($this->classId);
                $class->update($this->form);
            } else {
                $class = Lop::create($this->form);
            }

            // 👉 nếu sau này có sync teacher, pivot, log... thì đặt ở đây

            DB::commit();

            session()->flash(
                'success',
                $this->isEdit
                    ? 'Cập nhật lớp học thành công!'
                    : 'Tạo lớp học thành công!'
            );

            return redirect()->route('ds-lop');
        } catch (\Throwable $e) {

            DB::rollBack(); // ⬅️ QUAN TRỌNG

            session()->flash('error', 'Lưu dữ liệu thất bại!');

            Log::error('LopForm save error', [
                'error' => $e->getMessage(),
                'data' => $this->form,
            ]);
        }
    }

    public function render()
    {
        return view('livewire.lop.lop-form')
            ->extends('frontend.layout.main')
            ->section('content');
    }
}
