<?php

namespace App\Http\Livewire;

use App\Models\Block;
use App\Models\Holymanagement;
use App\Models\Lop as ModelsLop;
use App\Models\NamHoc;
use App\Models\Parish;
use App\Models\Student;
use App\Models\Teacher;
use Livewire\Component;
use Livewire\WithPagination;
use SebastianBergmann\CodeUnit\FunctionUnit;

class Lop extends Component
{
    use WithPagination;

    public $lopId;
    public $perPage = 10;
    public $search = '';
    public $selectedStudents = [];
    public $selectAll = false;
    public $availableClasses = []; // Danh sách lớp có sẵn

    protected $paginationTheme = 'tailwind';


    public function mount($id)
    {
        $this->lopId = $id;
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        $ids = $this->getCurrentStudentsQuery()->pluck('id')->toArray();
        $this->selectedStudents = $value ? array_map('intval', $ids) : [];
    }

    public function updatedSelectedStudents()
    {
        $currentCount = $this->getCurrentStudentsQuery()->count();
        $selectedCount = count(array_intersect($this->selectedStudents, $this->getCurrentStudentsQuery()->pluck('id')->toArray()));

        $this->selectAll = $currentCount > 0 && $selectedCount === $currentCount;
    }

    // Helper để tái sử dụng query
    private function getCurrentStudentsQuery()
    {
        return ModelsLop::findOrFail($this->lopId)
            ->students()
            ->wherePivot('status', 1)
            ->when($this->search, function ($q) {
                $q->where(function ($sq) {
                    $sq->where('name', 'like', "%{$this->search}%")
                        ->orWhere('last_name', 'like', "%{$this->search}%")
                        ->orWhere('holy', 'like', "%{$this->search}%")
                        ->orWhere('mahv', 'like', "%{$this->search}%");
                });
            })
            ->orderBy('name', 'asc');
    }

    public function render()
    {
        $lop = ModelsLop::findOrFail($this->lopId);
        $lop->slug = url(slug($lop) . config('app.url_prefix', ''));

        if ($lop->block) {
            $lop->block = Block::find($lop->block)?->name ?? '';
        }
        if ($lop->schoolyear) {
            $lop->schoolyear = NamHoc::find($lop->schoolyear)?->name ?? '';
        }

        $teacherIds = $lop->teacher ?? null;

        if (empty($teacherIds) || $teacherIds === '' || $teacherIds === '[]' || $teacherIds === 'null') {
            $teacherIds = [];
        }
        // 2. Nếu là chuỗi → chuyển thành mảng
        elseif (is_string($teacherIds)) {
            // Thử json_decode trước (ưu tiên vì chính xác nhất)
            $decoded = json_decode($teacherIds, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $teacherIds = $decoded;
            } else {
                // Nếu không phải JSON → thử tách bằng dấu phẩy, khoảng trắng, ngoặc...
                $teacherIds = preg_split('/[\[\]\s,"\']+/', $teacherIds, -1, PREG_SPLIT_NO_EMPTY);
            }
        }
        // 3. Nếu là số (trường hợp chỉ có 1 giáo viên, lưu nhầm kiểu int)
        //    → chuyển thành mảng 1 phần tử
        elseif (is_numeric($teacherIds)) {
            $teacherIds = [(int)$teacherIds];
        }
        // 4. Nếu đã là mảng → giữ nguyên
        elseif (!is_array($teacherIds)) {
            // Trường hợp bất ngờ (object, bool, v.v.) → ép về mảng rỗng
            $teacherIds = [];
        }

        // === LỌC & CHUẨN HÓA ID ===
        $teacherIds = array_filter($teacherIds, 'is_numeric');           // chỉ giữ số
        $teacherIds = array_map('intval', $teacherIds);                   // ép kiểu int
        $teacherIds = array_values(array_unique($teacherIds));            // loại trùng, reset key

        // === LẤY TÊN GIÁO VIÊN (chỉ khi có ID hợp lệ) ===
        $teachers = [];
        if (!empty($teacherIds)) {
            $teachers = Teacher::whereIn('id', $teacherIds)
                ->where('status', 1)
                ->orderByRaw('FIELD(id, ' . implode(',', $teacherIds) . ')') // giữ đúng thứ tự ban đầu
                ->pluck('name', 'id')
                ->toArray();
        }

        // === GÁN KẾT QUẢ CHO $lop ===
        $lop->tech = array_values($teachers);

        // === ĐẾM NAM / NỮ – CŨNG PHẢI QUA PIVOT ===
        $activeStudents = $lop->students()->wherePivot('status', 1);
        $countnam = (clone $activeStudents)->where('sex', 1)->count();
        $countnu  = (clone $activeStudents)->where('sex', 0)->count();
        $total    = $countnam + $countnu;

        // === DANH SÁCH + PHÂN TRANG ===
        $students = $this->getCurrentStudentsQuery()->paginate($this->perPage);

        // === TRANSFORM DATA ===
        $students->getCollection()->transform(function ($student, $index) use ($students, $lop) {
            $student->stt = $students->firstItem() + $index;
            $student->slug = url(slug($student) . config('app.url_prefix', ''));
            $student->thugioithieu = url(slug($student) . config('app.url_prefix', '') . '/thugioithieu=' . $student->id);
            $student->edit = config('app.url') . '/admin/student/' . $student->id . '/edit';

            // $student->holy = Holymanagement::find($student->holy)?->name ?? '';
            // $student->schoolyear = NamHoc::find($student->schoolyear)?->name ?? '';
            // $student->paid = Parish::find($student->paid)?->name ?? '';

            $student->holy = $student->holyRelation->name ?? '';
            $student->paid = $student->paidRelation->name ?? '';

            // Địa chỉ
            $student->ward = $this->getXaPhuong($student->ward);
            $student->province = $this->getTinhThanh($student->province);

            return $student;
        });

        return view('livewire.lop', [
            'lop' => $lop,
            'students' => $students,
            'total' => $total,
            'countnam' => $countnam,
            'countnu' => $countnu,
        ]);
    }

    private function getTinhThanh($provinceId)
    {
        if (!$provinceId) return '';

        @include resource_path('cities/tinh_thanhpho.php');

        return isset($tinh_thanhpho[$provinceId]) ? ', ' . $tinh_thanhpho[$provinceId] : '';
    }

    private function getXaPhuong($wardId)
    {
        if (!$wardId) return '';

        @include resource_path('cities/xa_phuong_thitran.php');

        foreach ($xa_phuong_thitran as $xaphuong) {
            if ($xaphuong['xaid'] == $wardId) {
                return $xaphuong['name'] ?? '';
            }
        }

        return '';
    }
}
