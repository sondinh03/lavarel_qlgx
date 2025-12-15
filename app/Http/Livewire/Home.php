<?php

namespace App\Http\Livewire;

use App\Models\Lop;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\NamHoc;
use App\Models\Block;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class Home extends Component
{
    public $parish_id;
    public $isAdmin = false;
    
    // Statistics
    public $totalStudents = 0;
    public $totalTeachers = 0;
    public $totalClasses = 0;
    public $activeSchoolYear = null;
    
    // Charts data
    public $studentsByGrade = [];
    public $studentsByGender = [];
    public $recentActivities = [];
    
    // Quick actions
    public $quickStats = [];
    
    protected $listeners = ['refreshDashboard' => 'refreshDashboard'];

    public function mount()
    {
        // DEV MODE - Hard-coded for now
        // TODO: [AUTH] Replace with middleware values
        $this->parish_id = request()->get('giaoxu', 1);
        $this->isAdmin = true;
        
        if (!$this->parish_id) {
            session()->flash('error', 'Không xác định được giáo xứ');
            return;
        }
        
        $this->loadDashboardData();
    }

    private function loadDashboardData()
    {
        $cacheKey = "dashboard_data_v2_{$this->parish_id}";
        $cacheDuration = 300; // 5 minutes

        try {
            $data = Cache::remember($cacheKey, $cacheDuration, function () {
                $schoolYear = $this->getActiveSchoolYear();
                
                if (!$schoolYear) {
                    return [
                        'stats' => ['students' => 0, 'teachers' => 0, 'classes' => 0],
                        'schoolYear' => null,
                        'studentsByGrade' => [],
                        'studentsByGender' => ['male' => 0, 'female' => 0],
                        'activities' => [],
                        'quickStats' => [],
                    ];
                }
                
                return [
                    'stats' => $this->getStatistics($schoolYear),
                    'schoolYear' => $schoolYear,
                    'studentsByGrade' => $this->getStudentsByGrade($schoolYear),
                    'studentsByGender' => $this->getStudentsByGender($schoolYear),
                    'activities' => $this->getRecentActivities(),
                    'quickStats' => $this->getQuickStats($schoolYear),
                ];
            });

            $this->totalStudents = $data['stats']['students'];
            $this->totalTeachers = $data['stats']['teachers'];
            $this->totalClasses = $data['stats']['classes'];
            $this->activeSchoolYear = $data['schoolYear'];
            $this->studentsByGrade = $data['studentsByGrade'];
            $this->studentsByGender = $data['studentsByGender'];
            $this->recentActivities = $data['activities'];
            $this->quickStats = $data['quickStats'];
            
        } catch (\Exception $e) {
            Log::error('Home: Error loading dashboard data', [
                'parish_id' => $this->parish_id,
                'error' => $e->getMessage(),
            ]);
            
            session()->flash('error', 'Có lỗi khi tải dữ liệu dashboard');
        }
    }

    private function getActiveSchoolYear()
    {
        // Tìm năm học active của parish này
        return NamHoc::where('parish_id', $this->parish_id)
            ->where('status', 1)
            ->orderBy('id', 'desc')
            ->first();
    }

    private function getStatistics($schoolYear): array
    {
        if (!$schoolYear) {
            return ['students' => 0, 'teachers' => 0, 'classes' => 0];
        }

        // Count classes
        $classCount = Lop::where('schoolyear', $schoolYear->id)
            ->where('pid', $this->parish_id)
            ->where('status', 1)
            ->count();

        // Count students (using correct pivot table)
        // Lop model uses 'students_class' as pivot table
        $studentCount = DB::table('students_class')
            ->join('lop', 'students_class.class_id', '=', 'lop.id')
            ->where('lop.schoolyear', $schoolYear->id)
            ->where('lop.pid', $this->parish_id)
            ->where('students_class.status', 1)
            ->distinct('students_class.student_id')
            ->count('students_class.student_id');

        // Count teachers
        $teacherCount = DB::table('class_teachers')
            ->join('lop', 'class_teachers.class_id', '=', 'lop.id')
            ->where('lop.schoolyear', $schoolYear->id)
            ->where('lop.pid', $this->parish_id)
            ->where('class_teachers.status', 1)
            ->distinct('class_teachers.teacher_id')
            ->count('class_teachers.teacher_id');

        return [
            'students' => $studentCount,
            'teachers' => $teacherCount,
            'classes' => $classCount,
        ];
    }

    private function getStudentsByGrade($schoolYear): array
    {
        if (!$schoolYear) {
            return [];
        }

        $data = DB::table('lop')
            ->join('students_class', 'lop.id', '=', 'students_class.class_id')
            ->join('blocks', 'lop.block', '=', 'blocks.id')
            ->where('lop.schoolyear', $schoolYear->id)
            ->where('lop.pid', $this->parish_id)
            ->where('students_class.status', 1)
            ->select(
                'blocks.name as grade',
                'blocks.id as grade_id',
                DB::raw('COUNT(DISTINCT students_class.student_id) as count')
            )
            ->groupBy('blocks.id', 'blocks.name')
            ->orderBy('blocks.id')
            ->get();

        return $data->map(function ($item) {
            return [
                'grade' => $item->grade,
                'count' => (int) $item->count,
            ];
        })->toArray();
    }

    private function getStudentsByGender($schoolYear): array
    {
        if (!$schoolYear) {
            return ['male' => 0, 'female' => 0];
        }

        $data = DB::table('students_class')
            ->join('lop', 'students_class.class_id', '=', 'lop.id')
            ->join('student', 'students_class.student_id', '=', 'student.id')
            ->where('lop.schoolyear', $schoolYear->id)
            ->where('lop.pid', $this->parish_id)
            ->where('students_class.status', 1)
            ->select(DB::raw('
                SUM(CASE WHEN student.sex = 1 THEN 1 ELSE 0 END) as male,
                SUM(CASE WHEN student.sex = 0 THEN 1 ELSE 0 END) as female
            '))
            ->first();

        return [
            'male' => (int) ($data->male ?? 0),
            'female' => (int) ($data->female ?? 0),
        ];
    }

    private function getRecentActivities(): array
    {
        // Get recent classes created in last 30 days
        $recentClasses = Lop::where('pid', $this->parish_id)
            ->where('status', 1)
            ->with(['schoolYear', 'blockRelation'])
            ->where('created_at', '>=', now()->subDays(30))
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        if ($recentClasses->isEmpty()) {
            return [];
        }

        return $recentClasses->map(function ($lop) {
            $blockName = $lop->blockRelation?->name ?? 'N/A';
            $yearName = $lop->schoolYear?->display_name ?? 'N/A';
            
            return [
                'type' => 'class',
                'icon' => 'school',
                'title' => "Lớp {$lop->name} được tạo",
                'description' => "{$blockName} - {$yearName}",
                'time' => $lop->created_at->diffForHumans(),
                'color' => 'blue',
            ];
        })->toArray();
    }

    private function getQuickStats($schoolYear): array
    {
        if (!$schoolYear) {
            return [];
        }

        return [
            [
                'label' => 'Sĩ số trung bình',
                'value' => $this->getAverageClassSize($schoolYear),
                'icon' => 'users',
                'color' => 'green',
            ],
            [
                'label' => 'Lớp đông nhất',
                'value' => $this->getLargestClassSize($schoolYear),
                'icon' => 'trending-up',
                'color' => 'yellow',
            ],
            [
                'label' => 'Giáo viên/Lớp',
                'value' => $this->getTeacherPerClass(),
                'icon' => 'user-check',
                'color' => 'purple',
            ],
        ];
    }

    private function getAverageClassSize($schoolYear): string
    {
        $avg = DB::table('students_class')
            ->join('lop', 'students_class.class_id', '=', 'lop.id')
            ->where('lop.schoolyear', $schoolYear->id)
            ->where('lop.pid', $this->parish_id)
            ->where('students_class.status', 1)
            ->select('lop.id', DB::raw('COUNT(students_class.student_id) as student_count'))
            ->groupBy('lop.id')
            ->get()
            ->avg('student_count');

        return $avg > 0 ? number_format($avg, 1) : '0';
    }

    private function getLargestClassSize($schoolYear): string
    {
        $max = DB::table('students_class')
            ->join('lop', 'students_class.class_id', '=', 'lop.id')
            ->where('lop.schoolyear', $schoolYear->id)
            ->where('lop.pid', $this->parish_id)
            ->where('students_class.status', 1)
            ->select('lop.id', DB::raw('COUNT(students_class.student_id) as student_count'))
            ->groupBy('lop.id')
            ->orderBy('student_count', 'desc')
            ->first();

        return $max ? (string) $max->student_count : '0';
    }

    private function getTeacherPerClass(): string
    {
        if ($this->totalClasses == 0) {
            return '0';
        }

        $ratio = $this->totalTeachers / $this->totalClasses;
        return number_format($ratio, 1);
    }

    public function refreshDashboard()
    {
        Cache::forget("dashboard_data_v2_{$this->parish_id}");
        $this->loadDashboardData();
        
        $this->dispatchBrowserEvent('notify', [
            'type' => 'success',
            'message' => 'Đã làm mới dữ liệu dashboard'
        ]);
    }

    public function render()
    {
        return view('livewire.home')
            ->extends('frontend.layout.main')
            ->section('content');
    }
}