<?php

namespace App\Http\Livewire\Lop;

use App\Models\ClassTeacher;
use App\Models\Lop;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class LopDetail extends Component
{
    public ?int $lopId = null;
    protected ?Lop $lopModel = null;
    public $lopData = [];
    public $teachers = [];
    public $block;
    public $namHoc;
    public $statistics = [];
    protected $listeners = ['filtersChanged' => 'handleFiltersChanged'];

    public function mount($id)
    {
        $this->lopId = $id;
        $this->loadLopDetails();
        if (!$this->lopModel) {
            $this->redirectRoute('lop.index');
            return;
        }

        $this->loadStatistics();
    }

    private function loadLopDetails()
    {
        try {
            $this->lopModel = Lop::with([
                'blockRelation',
                'schoolYear',
                'slug',
                // Load teachers via classTeachers
                'classTeachers' => function ($q) {
                    $q->where('status', 1)
                        ->with('teacher')
                        ->orderBy('role', 'asc');
                }
            ])
                ->withCount('students')
                ->findOrFail($this->lopId);

            $teachersData = [];

            if ($this->lopModel->relationLoaded('classTeachers') && $this->lopModel->classTeachers->isNotEmpty()) {
                $schoolYearId = $this->lopModel->schoolYear?->id ?? null;
                foreach ($this->lopModel->classTeachers as $ct) {
                    // filter by school year if available, and ensure teacher exists and is active
                    if ($ct->teacher && $ct->teacher->status == 1 && (is_null($schoolYearId) || $ct->namhoc_id == $schoolYearId)) {
                        $teacher = $ct->teacher;
                        $isChuNhiem = ($ct->role == ClassTeacher::ROLE_CHU_NHIEM);

                        $teachersData[] = [
                            'id' => $teacher->id,
                            'name' => $teacher->name,
                            'birthday' => $teacher->birthday,
                            'phone' => $teacher->phone,
                            'is_chu_nhiem' => $isChuNhiem
                        ];
                    }
                }
            }

            $this->teachers = collect($teachersData);
            $this->block = $this->lopModel->blockRelation;
            $this->namHoc = $this->lopModel->schoolYear;

            // expose minimal public data to avoid serializing full model
            $this->lopData = [
                'id' => $this->lopModel->id,
                'name' => $this->lopModel->name ?? null,
                'symbol' => $this->lopModel->symbol ?? null,
                'students_count' => (int) ($this->lopModel->students_count ?? 0),
                'slug' => $this->lopModel->slug?->keyword ?? null,
                'start_date_one' => $this->lopModel->start_date_one ?? null,
                'end_date_one' => $this->lopModel->end_date_one ?? null,
                'start_date_two' => $this->lopModel->start_date_two ?? null,
                'end_date_two' => $this->lopModel->end_date_two ?? null,
                'note' => $this->lopModel->note ?? null,
            ];
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            session()->flash('error', 'Không tìm thấy lớp học này.');
            Log::warning('LopDetail: class not found', ['lop_id' => $this->lopId]);
            $this->lopModel = null;
        } catch (\Exception $e) {
            Log::error('LopDetail: Error loading class details', [
                'lop_id' => $this->lopId,
                'error'  => $e->getMessage(),
                'trace'  => $e->getTraceAsString(),
            ]);

            session()->flash('error', 'Có lỗi trong lúc tải thông tin lớp.');
        }
    }

    private function loadStatistics()
    {
        if (!$this->lopModel) {
            $this->statistics = ['total' => 0, 'male' => 0, 'female' => 0];
            return;
        }
        $schoolYearId = $this->lopModel->schoolYear?->id ?? 'none';
        $key = "class_stats:{$this->lopModel->id}:{$schoolYearId}";
        $ttlSeconds = 300; // cache 5 minutes

        $this->statistics = Cache::remember($key, $ttlSeconds, function () {
            $res = $this->lopModel->students()
                ->wherePivot('status', 1)
                ->withoutGlobalScopes()
                ->selectRaw("COUNT(*) as total, SUM(CASE WHEN sex = 1 THEN 1 ELSE 0 END) as male, SUM(CASE WHEN sex = 0 THEN 1 ELSE 0 END) as female")
                ->getQuery()
                ->first();

            return [
                'total'  => (int) ($res->total ?? 0),
                'male'   => (int) ($res->male ?? 0),
                'female' => (int) ($res->female ?? 0),
            ];
        });
    }

    public function render()
    {
        // Generate slug URL (use minimal public data when possible)
        $slugKeyword = $this->lopModel->slug?->keyword ?? $this->lopData['slug'] ?? null;
        $slugUrl = $slugKeyword
            ? url($slugKeyword . config('settings.url_prefix', ''))
            : route('lop.show', $this->lopModel->id);

        // determine parish id (DB uses `pid`); prefer `pid` then fallbacks
        $parishId = $this->lopModel->pid  ?? null;
        $parishId = is_null($parishId) ? null : (int) $parishId;

        return view('livewire.lop.lop-detail', [
            'slugUrl' => $slugUrl,
            'parishId' => $parishId,
        ])
            ->extends('frontend.layout.main')
            ->section('content');
    }

    public function handleFiltersChanged($filters)
    {
        if (!is_array($filters)) {
            return;
        }

        $lopId = $filters['lop'] ?? null;
        if ($lopId && (int)$lopId !== (int)$this->lopId) {
            $this->lopId = (int) $lopId;
            $this->loadLopDetails();
            $this->loadStatistics();
        }
    }
}
