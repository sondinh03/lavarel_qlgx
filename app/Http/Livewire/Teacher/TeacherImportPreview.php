<?php

namespace App\Http\Livewire\Teacher;

use App\Actions\Teacher\ImportTeacherAction;
use App\Http\Livewire\Base\BaseComponent;
use App\Imports\TeacherPreviewImport;
use App\Models\Teacher;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

class TeacherImportPreview extends BaseComponent
{
    use WithFileUploads;

    public $file;
    public $rows = [];
    public $errors = [];
    public $readyToImport = false;

    // ==================== VALIDATION ====================

    protected $rules = [
        'file' => 'required|mimes:xlsx,csv|max:2048',
    ];

    protected $messages = [
        'file.required' => 'Vui lòng chọn file Excel',
        'file.mimes' => 'File phải có định dạng .xlsx hoặc .csv',
        'file.max' => 'File không được vượt quá 2MB',
    ];

    public function mount()
    {
        parent::mount();
        $this->requireManager();
        $this->requireParishId();
    }

    public function loadInitialData(): void {}

    public function updateFile()
    {
        $this->validate();
        $this->preview();
    }

    public function preview()
    {
        $this->validate();

        $this->rows = [];
        $this->errors = [];
        $this->readyToImport = false;

        try {
            $data = Excel::toArray(new TeacherPreviewImport, $this->file)[0];

            if (empty($data)) {
                $this->errors[] = 'File Excel trống hoặc không đúng định dạng';
                return;
            }

            // Validate headers
            $requiredHeaders = ['ho_ten', 'so_dien_thoai'];
            $firstRow = $data[0] ?? [];

            foreach ($requiredHeaders as $header) {
                if (!array_key_exists($header, $firstRow)) {
                    $this->errors[] = "Thiếu cột bắt buộc: {$header}";
                    return;
                }
            }

            foreach ($data as $index => $row) {
                // Bỏ qua dòng trống
                if (empty(trim($row['ho_ten'] ?? ''))) {
                    continue;
                }

                $phone = preg_replace('/[^0-9]/', '', $row['so_dien_thoai'] ?? '');

                $duplicate = $phone
                    && Teacher::where('pid', $this->parishId)
                    ->where('phone_number', $phone)
                    ->exists();

                if ($duplicate) {
                    $this->errors[] = sprintf(
                        'Dòng %d: Trùng SĐT %s (đã tồn tại trong hệ thống)',
                        $index + 2,
                        $phone
                    );
                }

                $this->rows[] = [
                    'row_number' => $index + 2,
                    'ten_thanh' => $row['ten_thanh'] ?? '',
                    'ho_ten' => $row['ho_ten'] ?? '',
                    'ngay_sinh' => $row['ngay_sinh'] ?? '',
                    'so_dien_thoai' => $phone,
                    'giao_ho' => $row['giao_ho'] ?? '',
                    'tao_tai_khoan' => $row['tao_tai_khoan'] ?? '',
                    'duplicate' => $duplicate,
                ];
            }

            $this->readyToImport = empty($this->errors);

            if ($this->readyToImport) {
                session()->flash('info', sprintf(
                    'Đã kiểm tra %d dòng dữ liệu. Sẵn sàng import.',
                    count($this->rows)
                ));
            }
        } catch (\Exception $e) {
            $this->logError($e, 'Error previewing teacher import');
            $this->errors[] = 'Lỗi khi đọc file: ' . $e->getMessage();
        }
    }

    public function confirmImport()
    {
        if (!$this->readyToImport) {
            session()->flash('error', 'Dữ liệu chưa hợp lệ, không thể import');
            return;
        }

        try {
            app(ImportTeacherAction::class)
                ->handle($this->file, $this->parishId);

            session()->flash('message', sprintf(
                'Import thành công %d giáo lý viên',
                count($this->rows)
            ));
            return redirect()->route('catechists.index');
        } catch (\Exception $e) {
            $this->logError($e, 'Error importing teachers');
            session()->flash('error', 'Có lỗi khi import: ' . $e->getMessage());
        }
    }

    /**
     * Reset và upload lại
     */
    // public function resetToDefaults()
    // {
    //     $this->file = null;
    //     $this->rows = [];
    //     $this->errors = [];
    //     $this->readyToImport = false;
    //     $this->resetValidation();
    // }

    public function render()
    {
        return view('livewire.teacher.teacher-import-preview')
            ->extends('frontend.layout.main')
            ->section('content');
    }
}
