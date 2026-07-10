<?php

namespace App\Http\Livewire\Teacher;

use App\Actions\Teacher\ImportTeacherAction;
use App\Http\Livewire\Base\BaseComponent;
use App\Imports\TeacherPreviewImport;
use App\Models\Holymanagement;
use App\Models\ParishGroup;
use App\Models\Teacher;
use App\Support\ExcelDateParser;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

class TeacherImportPreview extends BaseComponent
{
    use WithFileUploads;

    // ==================== FILE ====================

    public $file = null;

    // ==================== PREVIEW STATE ====================

    public array $rows          = [];
    public array $fileErrors    = [];
    public array $warnings      = [];
    public bool  $readyToImport = false;

    // ==================== VALIDATION ====================

    protected $rules = [
        'file' => 'required|mimes:xlsx,csv|max:5120',
    ];

    protected $messages = [
        'file.required' => 'Vui lòng chọn file Excel',
        'file.mimes'    => 'File phải có định dạng .xlsx hoặc .csv',
        'file.max'      => 'File không được vượt quá 5MB',
    ];

    // ==================== LIFECYCLE ====================

    public function mount(): void
    {
        parent::mount();
        $this->requireManager();
        $this->requireParishId();
    }

    public function loadInitialData(): void {}

    // ==================== PROPERTY UPDATERS ====================

    public function updatedFile(): void
    {
        $this->resetPreview();
        $this->preview();
    }

    // ==================== ACTIONS ====================

    public function preview(): void
    {
        $this->validate();
        $this->resetPreview();

        try {
            $data = Excel::toArray(new TeacherPreviewImport, $this->file)[0] ?? [];

            if (empty($data)) {
                $this->fileErrors[] = 'File Excel trống hoặc không đúng định dạng';
                return;
            }

            // Kiểm tra cột bắt buộc
            $requiredHeaders = ['ho_ten', 'so_dien_thoai'];
            $firstRow        = $data[0] ?? [];

            foreach ($requiredHeaders as $header) {
                if (!array_key_exists($header, $firstRow)) {
                    $this->fileErrors[] = "Thiếu cột bắt buộc: <strong>{$header}</strong>";
                }
            }

            if (!empty($this->fileErrors)) {
                return;
            }

            // Cache lookups để tránh N+1
            $saintNames = Holymanagement::pluck('name')
                ->map(fn($n) => strtolower(trim($n)))
                ->toArray();

            $groupNames = ParishGroup::active()
                ->pluck('name')
                ->map(fn($n) => strtolower(trim($n)))
                ->toArray();

            // Load SĐT đã tồn tại — ParishScope tự filter parish_id
            $existingPhones = Teacher::pluck('phone_number')
                ->map(fn($p) => preg_replace('/[^0-9]/', '', $p ?? ''))
                ->filter()
                ->toArray();

            foreach ($data as $index => $row) {
                $rowNumber = $index + 6; // +6 vì data bắt đầu từ dòng 6

                // Bỏ qua dòng trống
                if (empty(trim($row['ho_ten'] ?? ''))) {
                    continue;
                }

                $rowWarnings = [];

                $tenThanh = trim($row['ten_thanh'] ?? '');
                $giaoHo   = trim($row['giao_ho'] ?? '');
                $ngaySinh = $row['ngay_sinh'] ?? '';
                $phone    = preg_replace('/[^0-9]/', '', $row['so_dien_thoai'] ?? '');
                $email    = trim($row['email'] ?? '');

                // Kiểm tra tên thánh
                if (!empty($tenThanh) && !in_array(strtolower($tenThanh), $saintNames)) {
                    $rowWarnings[] = "Tên thánh \"{$tenThanh}\" không tìm thấy trong hệ thống";
                }

                // Kiểm tra giáo họ
                if (!empty($giaoHo) && !in_array(strtolower($giaoHo), $groupNames)) {
                    $rowWarnings[] = "Giáo họ \"{$giaoHo}\" không tìm thấy trong hệ thống";
                }

                // Kiểm tra ngày sinh
                $parsedDate = null;
                if (!empty($ngaySinh)) {
                    $parsedDate = ExcelDateParser::parse($ngaySinh);
                    if ($parsedDate === null) {
                        $rowWarnings[] = "Ngày sinh \"{$ngaySinh}\" không hợp lệ (định dạng: dd/mm/yyyy)";
                    }
                }

                // Kiểm tra SĐT trùng
                $isDuplicate = !empty($phone) && in_array($phone, $existingPhones);
                if ($isDuplicate) {
                    $rowWarnings[] = "Số điện thoại \"{$phone}\" đã tồn tại trong hệ thống";
                }

                // Kiểm tra email
                if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $rowWarnings[] = "Email \"{$email}\" không đúng định dạng";
                }

                if (!empty($rowWarnings)) {
                    $this->warnings[$rowNumber] = $rowWarnings;
                }

                $this->rows[] = [
                    'row_number'    => $rowNumber,
                    'ten_thanh'     => $tenThanh,
                    'ho_ten'        => trim($row['ho_ten'] ?? ''),
                    'ngay_sinh'     => $ngaySinh,
                    'gioi_tinh'     => trim($row['gioi_tinh'] ?? ''),
                    'email'         => $email,
                    'so_dien_thoai' => $phone,
                    'giao_ho'       => $giaoHo,
                    'tao_tai_khoan' => trim($row['tao_tai_khoan'] ?? ''),
                    'has_warning'   => !empty($rowWarnings),
                    'is_duplicate'  => $isDuplicate,
                ];
            }

            $this->readyToImport = empty($this->fileErrors) && !empty($this->rows);

            if ($this->readyToImport) {
                $warningCount = count($this->warnings);
                $msg = sprintf('Đã kiểm tra %d dòng dữ liệu. Sẵn sàng import.', count($this->rows));
                if ($warningCount > 0) {
                    $msg .= " ({$warningCount} dòng có cảnh báo)";
                }
                session()->flash('info', $msg);
                $this->emit('toast', 'info', $msg);
            }
        } catch (\Exception $e) {
            $this->logError($e, 'Error previewing teacher import');
            $this->fileErrors[] = 'Lỗi khi đọc file: ' . $e->getMessage();
        }
    }

    public function confirmImport()
    {
        if (!$this->readyToImport) {
            $this->emit('toast', 'error', 'Dữ liệu chưa hợp lệ, không thể import');
            return;
        }

        try {
            $result = app(ImportTeacherAction::class)
                ->handle($this->rows, $this->parishId);

            $message = "Import thành công {$result['imported']} giáo lý viên";

            if ($result['skipped'] > 0) {
                $message .= " | Bỏ qua {$result['skipped']} dòng trống";
            }

            if (!empty($result['errors'])) {
                $message .= " | " . count($result['errors']) . " dòng lỗi";
                $this->emit('toast', 'warning', strip_tags(implode(' · ', array_slice($result['errors'], 0, 5))));
            }

            $this->emit('toast', 'message', $message);
            return redirect()->route('catechists.index');
        } catch (\Exception $e) {
            $this->logError($e, 'Error confirming teacher import');
            $this->emit('toast', 'error', 'Có lỗi khi import: ' . $e->getMessage());
        }
    }

    public function resetUpload(): void
    {
        $this->file = null;
        $this->resetPreview();
        $this->resetValidation();
    }

    // ==================== HELPERS ====================

    protected function resetPreview(): void
    {
        $this->rows          = [];
        $this->fileErrors    = [];
        $this->warnings      = [];
        $this->readyToImport = false;
    }

    // ==================== RENDER ====================

    public function render()
    {
        return view('livewire.teacher.teacher-import-preview')
            ->extends('frontend.layout.main')
            ->section('content');
    }
}