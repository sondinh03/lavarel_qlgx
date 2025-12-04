<?php

namespace App\Http\Livewire\Lop;

use App\Models\Lop;
use App\Models\NamHoc;
use App\Models\Teacher;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;

class LopDetail extends Component
{
    public $lopId;
    public $lop;
    public $teachers = [];
    public $block;
    public $namHoc;
    public $statistics = [];

    public function mount($id)
    {
        $this->lopId = $id;
        $this->loadLopDetails();
        $this->loadStatistics();
    }

    // private function loadLopDetails()
    // {
    //     try {
    //         $this->lop = Lop::with(['blockRelation', 'schoolYear', 'slug'])
    //             ->withCount('students')
    //             ->findOrFail($this->lopId);

    //         // Load teachers
    //         $teacherIds = $this->lop->teacher;

    //         if (empty($teacherIds) || in_array($teacherIds, ['', '[]', 'null', null], true)) {
    //             $teacherIds = [];
    //         } elseif (is_string($teacherIds)) {
    //             $decoded = json_decode($teacherIds, true);
    //             $teacherIds = (json_last_error() === JSON_ERROR_NONE && is_array($decoded))
    //                 ? $decoded
    //                 : preg_split('/[\[\]\s,"\']+/', $teacherIds, -1, PREG_SPLIT_NO_EMPTY);
    //         } elseif (is_numeric($teacherIds)) {
    //             $teacherIds = [(int)$teacherIds];
    //         } elseif (!is_array($teacherIds)) {
    //             $teacherIds = [];
    //         }

    //         $teacherIds = array_values(array_unique(array_map('intval', array_filter($teacherIds, 'is_numeric'))));

    //         if (!empty($teacherIds)) {
    //             $this->teachers = Teacher::whereIn('id', $teacherIds)
    //                 ->where('status', 1)
    //                 ->orderByRaw('FIELD(id, ' . implode(',', $teacherIds) . ')')
    //                 ->get();
    //         }

    //         $this->block = $this->lop->blockRelation;
    //         $this->namHoc = $this->lop->schoolYear;
    //     } catch (\Exception $e) {
    //         Log::error('LopDetail: Error loading class details', [
    //             'lop_id' => $this->lopId,
    //             'error' => $e->getMessage()
    //         ]);

    //         session()->flash('error', 'Không tìm thấy lớp học này.');
    //         return redirect()->route('lop.index');
    //     }
    // }

    private function loadLopDetails()
    {
        try {
            $this->lop = Lop::with(['blockRelation', 'schoolYear', 'slug'])
                ->withCount('students')
                ->findOrFail($this->lopId);

            // === PHẦN QUAN TRỌNG NHẤT: LẤY DANH SÁCH GIÁO VIÊN AN TOÀN ===
            $teacherIds = $this->lop->teacher ?? []; // nhờ $casts => luôn là array hoặc null

            // Đảm bảo chỉ lấy số nguyên, loại bỏ rác
            $teacherIds = array_unique(
                array_filter(
                    array_map('intval', $teacherIds),
                    'is_numeric'
                )
            );

            if (!empty($teacherIds)) {
                $this->teachers = Teacher::whereIn('id', $teacherIds)
                    ->where('status', 1)
                    // Cách viết orderByRaw AN TOÀN 100% với placeholder ?
                    ->orderByRaw(
                        'FIELD(id, ' . implode(',', array_fill(0, count($teacherIds), '?')) . ')',
                        $teacherIds
                    )
                    ->get();
            } else {
                $this->teachers = collect(); // collection rỗng
            }

            $this->block   = $this->lop->blockRelation;
            $this->namHoc  = $this->lop->schoolYear;
        } catch (\Exception $e) {
            Log::error('LopDetail: Error loading class details', [
                'lop_id' => $this->lopId,
                'error'  => $e->getMessage(),
                'trace'  => $e->getTraceAsString(),
            ]);

            session()->flash('error', 'Không tìm thấy lớp học này.');
            // return redirect()->route('lop.index');
        }
    }

    private function loadStatistics()
    {
        $stats = $this->lop->students()
            ->selectRaw('
            COUNT(*) as total,
            SUM(sex = 1) as male,
            SUM(sex = 0) as female
        ')
            ->wherePivot('status', 1)
            ->withoutGlobalScopes() // optional
            ->getQuery()            // LẤY QUERY GỐC KHÔNG LẤY PIVOT COLUMNS
            ->first();

        $this->statistics = [
            'total'  => $stats->total,
            'male'   => $stats->male,
            'female' => $stats->female,
        ];
    }


    public function render()
    {
        if (!$this->lop) {
            return redirect()->route('lop.index');
        }

        // Generate slug URL
        $slugUrl = $this->lop->slug?->keyword
            ? url($this->lop->slug->keyword . config('settings.url_prefix', ''))
            : route('lop.show', $this->lop->id);

        return view('livewire.lop.lop-detail', [
            'slugUrl' => $slugUrl,
        ])
            ->extends('frontend.layout.main')
            ->section('content');
    }
}
