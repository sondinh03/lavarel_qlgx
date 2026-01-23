<?php

namespace App\Http\Livewire;

use App\Models\NamHoc;
use App\Models\Student;
use Livewire\Component;

/**
 * Landing Page Component
 * 
 * Features:
 * - Tra cứu kết quả học tập cho phụ huynh
 * - Không cần đăng nhập
 * - Tìm kiếm bằng mã học viên + ngày sinh
 */
class Landing extends Component
{
    // ==================== FORM FIELDS ====================

    /** @var string Mã học viên */
    public $student_code = '';

    /** @var string Ngày sinh (YYYY-MM-DD) */
    public $birthday = '';

    // ==================== STATE ====================

    /** @var array|null Kết quả tìm kiếm */
    public $result = null;

    /** @var string|null Thông báo lỗi */
    public $error = null;

    // ==================== VALIDATION ====================

    protected $rules = [
        'student_code' => 'required|string|max:50',
        'birthday'     => 'required|date|before_or_equal:today',
    ];

    protected $messages = [
        'student_code.required' => 'Vui lòng nhập mã học viên',
        'student_code.max' => 'Mã học viên không được quá 50 ký tự',
        'birthday.required' => 'Vui lòng chọn ngày sinh',
        'birthday.date' => 'Ngày sinh không hợp lệ',
        'birthday.before_or_equal' => 'Ngày sinh không được lớn hơn ngày hiện tại',
    ];

    // ==================== LIFECYCLE ====================

    /**
     * Component initialization
     */
    public function mount()
    {
        // Reset state khi load page
        $this->resetState();
    }

    // ==================== ACTIONS ====================

    /**
     * Tìm kiếm học viên
     */
    public function search(): void
    {
        // Reset state trước khi tìm kiếm
        $this->resetState();

        // Validate input
        $this->validate();

        try {
            // Tìm học viên theo mã và ngày sinh
            $student = Student::where('code', trim($this->student_code))
                ->whereDate('birthday', $this->birthday)
                ->first();

            if (!$student) {
                $this->error = 'Không tìm thấy học viên với thông tin này. Vui lòng kiểm tra lại mã học viên và ngày sinh.';
                return;
            }

            // Lấy năm học hiện tại (đang active)
            $schoolYear = NamHoc::where('status', 1)
                ->orderByDesc('name')
                ->first();

            // Lưu kết quả
            $this->result = [
                'student'    => $student,
                'schoolYear' => $schoolYear,
            ];

            // Flash message thành công
            session()->flash('message', 'Tìm thấy học viên: ' . $student->name);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Landing search error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->error = 'Có lỗi xảy ra khi tìm kiếm. Vui lòng thử lại sau.';
        }
    }

    /**
     * Reset form và state
     */
    public function resetSearch(): void
    {
        $this->reset(['student_code', 'birthday']);
        $this->resetState();
        $this->resetValidation();

        session()->flash('info', 'Đã đặt lại biểu mẫu tìm kiếm');
    }

    // ==================== HELPERS ====================

    /**
     * Reset result và error state
     */
    private function resetState(): void
    {
        $this->result = null;
        $this->error = null;
    }

    /**
     * Xóa validation errors khi người dùng nhập
     */
    public function updated($propertyName): void
    {
        $this->validateOnly($propertyName);
    }

    // ==================== RENDER ====================

    /**
     * Render component với landing layout
     */
    public function render()
    {
        return view('livewire.landing')
            ->extends('frontend.layout.landing')
            ->section('content');
    }
}
