<?php

namespace App\Http\Livewire;

use App\Models\NamHoc;
use App\Models\StudentNew;
use App\Models\Attendance;      // hoặc model tương đương của bạn
use App\Models\Score;           // hoặc model tương đương
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class Landing extends Component
{
    public $phone    = '';
    public $results  = [];
    public $error    = null;
    public $searched = false;

    // Tab khi xem chi tiết 1 học sinh
    public $viewingStudentId = null;
    public $activeTab        = 'info'; // 'info' | 'attendance' | 'scores'

    protected $rules = [
        'phone' => 'required|string|min:9|max:15',
    ];

    protected $messages = [
        'phone.required' => 'Vui lòng nhập số điện thoại',
        'phone.min'      => 'Số điện thoại không hợp lệ',
        'phone.max'      => 'Số điện thoại không hợp lệ',
    ];

    public function mount(): void
    {
        $this->resetState();
    }

    public function search(): void
    {
        $this->resetState();
        $this->validate();

        try {
            $students = StudentNew::where('phone', trim($this->phone))
                ->where('is_active', true)
                ->with(['parish', 'parishGroup', 'saint', 'classes.schoolYear'])
                ->get();

            if ($students->isEmpty()) {
                $this->error    = 'Không tìm thấy học viên nào với số điện thoại này.';
                $this->searched = true;
                return;
            }

            $this->results  = $students->map(fn($s) => $this->mapStudentData($s))->toArray();
            $this->searched = true;

            // Nếu chỉ có 1 học sinh → tự động xem chi tiết
            if ($students->count() === 1) {
                $this->viewStudent($students->first()->id);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Landing search error', ['error' => $e->getMessage()]);
            $this->error = 'Có lỗi xảy ra. Vui lòng thử lại sau.';
        }
    }

    public function viewStudent(int $studentId): void
    {
        $allowed = collect($this->results)->firstWhere('id', $studentId);
        if (! $allowed) {
            $this->error = 'Không tìm thấy học viên nào với số điện thoại này.';
            $this->viewingStudentId = null;

            return;
        }

        $this->viewingStudentId = $studentId;
        $this->activeTab        = 'info';
    }

    public function backToList(): void
    {
        $this->viewingStudentId = null;
        $this->activeTab        = 'info';
    }

    public function switchTab(string $tab): void
    {
        if (! $this->assertViewingStudentMatchesSearch()) {
            return;
        }

        if (in_array($tab, ['info', 'attendance', 'scores'])) {
            $this->activeTab = $tab;
        }
    }

    public function resetSearch(): void
    {
        $this->reset(['phone']);
        $this->resetState();
        $this->resetValidation();
    }

    // ==================== DATA ====================

    public function getViewingStudentProperty(): ?array
    {
        if (! $this->assertViewingStudentMatchesSearch()) {
            return null;
        }

        return collect($this->results)->firstWhere('id', $this->viewingStudentId);
    }

    /**
     * Điểm danh theo từng năm học — group by schoolYear → class → sessions
     */
    public function getAttendanceSummaryProperty(): array
    {
        if (! $this->assertViewingStudentMatchesSearch()) {
            return [];
        }

        // Lấy năm học hiện tại (status = 1)
        $currentYear = NamHoc::where('status', 1)
            ->orderByDesc('name')
            ->first();

        if (!$currentYear) return [];

        $records = DB::table('attendance_records as ar')
            ->join('attendance_sessions as s', 'ar.session_id', '=', 's.id')
            ->join('classes as c', 's.class_id', '=', 'c.id')
            ->where('c.school_year_id', $currentYear->id)
            ->where('ar.student_id', $this->viewingStudentId)
            ->select(
                'c.name as class_name',
                's.date',
                's.type',
                's.semester',
                'ar.status',
                'ar.note',
            )
            ->orderBy('s.type')
            ->orderBy('s.date', 'asc')
            ->get();

        if ($records->isEmpty()) return [];

        // Group: type → semester → sessions[]
        $summary = [];
        foreach ($records as $r) {
            $typeLabel = $r->type == 1 ? 'Đi học' : 'Đi lễ';
            $semLabel  = 'Học kỳ ' . $r->semester;

            $summary[$typeLabel][$semLabel][] = [
                'date'       => $r->date,
                'status'     => $r->status,
                'note'       => $r->note,
                'class_name' => $r->class_name,
            ];
        }

        return [
            'year_name' => $currentYear->name,
            'data'      => $summary,
        ];
    }

    /**
     * Điểm học tập qua các năm
     */
    public function getScoresSummaryProperty(): array
    {
        if (! $this->assertViewingStudentMatchesSearch()) {
            return [];
        }

        $rows = DB::table('student_scores as sc')
            ->join('score_types as st', 'sc.score_type_id', '=', 'st.id')
            ->join('students_class as scc', 'sc.student_class_id', '=', 'scc.id')
            ->join('classes as c', 'scc.class_id', '=', 'c.id')
            ->join('nam_hoc as ny', 'c.school_year_id', '=', 'ny.id')
            ->where('scc.student_id', $this->viewingStudentId)
            ->where('st.is_active', 1)
            ->select(
                'ny.name as year_name',
                'c.name as class_name',
                'st.semester',
                'st.name as type_name',
                'st.coefficient',
                'st.max_score',
                'sc.score_value as value',
                'sc.attempt',
                'sc.note as score_note',
            )
            ->orderBy('ny.name', 'desc')
            ->orderBy('st.semester')
            ->orderBy('st.order')
            ->get();

        if ($rows->isEmpty()) return [];

        // Group: year → class → semester → scores[]
        $summary = [];
        foreach ($rows as $r) {
            $summary[$r->year_name][$r->class_name][$r->semester][] = [
                'type_name'   => $r->type_name,
                'coefficient' => (float) $r->coefficient,
                'max_score'   => (float) $r->max_score,
                'value'       => $r->value !== null ? (float) $r->value : null,
                'attempt'     => $r->attempt,
                'note'        => $r->score_note,
            ];
        }

        // Tính TB từng học kỳ (chỉ tính các ô đã có điểm)
        foreach ($summary as $year => $classes) {
            foreach ($classes as $class => $semesters) {
                foreach ($semesters as $sem => $scores) {
                    $scored = collect($scores)->filter(fn($s) => $s['value'] !== null);

                    if ($scored->isEmpty()) {
                        $avg = null;
                    } else {
                        $totalWeight = $scored->sum('coefficient');
                        $weightedSum = $scored->sum(fn($s) => $s['value'] * $s['coefficient']);
                        $avg = $totalWeight > 0 ? round($weightedSum / $totalWeight, 1) : null;
                    }

                    $summary[$year][$class][$sem] = [
                        'scores' => $scores,
                        'avg'    => $avg,
                    ];
                }
            }
        }

        return $summary;
    }

    // ==================== HELPERS ====================

    private function assertViewingStudentMatchesSearch(): bool
    {
        if (! $this->viewingStudentId) {
            return false;
        }

        $match = collect($this->results)->firstWhere('id', $this->viewingStudentId);
        if (! $match) {
            $this->viewingStudentId = null;
            $this->activeTab = 'info';

            return false;
        }

        $phone = trim((string) $this->phone);
        if ($phone !== '' && isset($match['phone']) && trim((string) $match['phone']) !== $phone) {
            $this->viewingStudentId = null;
            $this->activeTab = 'info';

            return false;
        }

        return true;
    }

    private function resetState(): void
    {
        $this->results          = [];
        $this->error            = null;
        $this->searched         = false;
        $this->viewingStudentId = null;
        $this->activeTab        = 'info';
    }

    public function updated($propertyName): void
    {
        $this->validateOnly($propertyName);
    }

    private function mapStudentData(StudentNew $student): array
    {
        return [
            'id'                   => $student->id,
            'student_code'         => $student->student_code ?? 'Chưa có mã',
            'avatar_path'          => $student->avatar_path ?? '',
            'full_name'            => $student->full_name ?? '',
            'full_name_with_saint' => $student->full_name_with_saint ?? $student->full_name ?? '',
            'saint_name'           => $student->saint?->name ?? '',
            'birthday'             => $student->birthday?->format('d/m/Y') ?? '',
            'gender_label'         => match ($student->gender) {
                'male'   => 'Nam',
                'female' => 'Nữ',
                default  => 'Chưa xác định',
            },
            'father_name'        => $student->father_name ?? '',
            'mother_name'        => $student->mother_name ?? '',
            'phone'              => $student->phone ?? '',
            'parish'             => $student->parish?->name ?? '',
            'parish_group'       => $student->parishGroup?->name ?? '',
            'current_class'      => $student->classes->first()?->name ?? '',
            'class_history'      => $student->classes->map(fn($c) => [
                'class_name'  => $c->name ?? '',
                'school_year' => $c->schoolYear?->name ?? '',
                'joined_at'   => $c->pivot->created_at?->format('d/m/Y') ?? '',
            ])->toArray(),
            'status_label'       => $student->is_active ? 'Đang học' : 'Ngừng học',
            'status_badge_class' => $student->is_active
                ? 'bg-green-100 text-green-700'
                : 'bg-slate-200 text-slate-600',
        ];
    }

    public function render()
    {
        return view('livewire.landing', [
            'viewingStudent'    => $this->viewingStudent,
            'attendanceSummary' => $this->viewingStudentId ? $this->attendanceSummary : [],
            'scoresSummary'     => $this->viewingStudentId ? $this->scoresSummary : [],
        ])
            ->extends('frontend.layout.landing')
            ->section('content');
    }
}
