<?php

namespace App\Http\Livewire\Student;

use App\Actions\Student\ImportStudentAction;
use App\Http\Livewire\Base\BaseComponent;
use App\Imports\StudentPreviewImport;
use App\Models\CatechismClass;
use App\Models\Holymanagement;
use App\Models\NamHoc;
use App\Models\ParishGroup;
use App\Models\StudentNew;
use App\Support\ExcelDateParser;
use Illuminate\Support\Facades\Log;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Component import học sinh từ Excel.
 *
 * Flow:
 *   1. Chọn năm học → khối → lớp ngay trên trang (dùng FilterBar event)
 *   2. Upload file → tự preview
 *   3. Confirm import → redirect về danh sách lớp đó
 */
class StudentImportPreview extends BaseComponent
{
    use WithFileUploads;

    // ==================== FILTERS ====================

    public $selectedNamHoc = null;
    public $selectedKhoi   = null;
    public $selectedLop    = null;
    public ?string $className = null;

    // ==================== FILE ====================

    public $file = null;

    // ==================== PREVIEW STATE ====================

    public array $rows          = [];
    public array $errors        = [];
    public array $warnings      = [];
    public bool  $readyToImport = false;

    // ==================== VALIDATION ====================

    protected $rules = [
        'file'        => 'nullable|mimes:xlsx,csv|max:5120',
        'selectedLop' => 'nullable|integer|exists:classes,id',
    ];

    protected $formRules = [
        'file'        => 'required|mimes:xlsx,csv|max:5120',
        'selectedLop' => 'required|integer|exists:classes,id',
    ];

    protected $messages = [
        'file.required'        => 'Vui lòng chọn file Excel',
        'file.mimes'           => 'File phải có định dạng .xlsx hoặc .csv',
        'file.max'             => 'File không được vượt quá 5MB',
        'selectedLop.required' => 'Vui lòng chọn lớp trước khi upload',
        'selectedLop.exists'   => 'Lớp không tồn tại',
    ];

    // ==================== QUERY STRING ====================

    protected function queryString(): array
    {
        return [
            'selectedNamHoc' => ['except' => null, 'as' => 'namHoc'],
            'selectedKhoi'   => ['except' => null, 'as' => 'khoi'],
            'selectedLop'    => ['except' => null, 'as' => 'class'],
        ];
    }

    // ==================== LISTENERS ====================

    protected $listeners = [
        'filterChanged' => 'handleFilterChanged',
    ];

    // ==================== LIFECYCLE ====================

    public function mount(): void
    {
        parent::mount();
        $this->requireManager();
        $this->requireParishId();
    }

    protected function loadInitialData(): void
    {
        if (!$this->selectedNamHoc) {
            $this->selectedNamHoc = NamHoc::ofParish($this->parishId)
                ->active()
                ->orderByDesc('name')
                ->value('id');
        }
    }

    // ==================== EVENT HANDLERS ====================

    public function handleFilterChanged(array $filters): void
    {
        if (array_key_exists('namHoc', $filters)) {
            $newNamHoc = is_numeric($filters['namHoc']) ? (int) $filters['namHoc'] : null;
            if ($newNamHoc !== $this->selectedNamHoc) {
                $this->selectedNamHoc = $newNamHoc;
                $this->selectedKhoi   = null;
                $this->selectedLop    = null;
                $this->resetPreview();
                $this->file = null;
            }
        }

        if (array_key_exists('khoi', $filters)) {
            $this->selectedKhoi = is_numeric($filters['khoi']) ? (int) $filters['khoi'] : null;
        }

        if (array_key_exists('lop', $filters)) {
            $newLop = is_numeric($filters['lop']) ? (int) $filters['lop'] : null;
            if ($newLop !== $this->selectedLop) {
                $this->selectedLop = $newLop;
                $this->resetPreview();
                $this->file = null;
            }

            $this->className = $this->selectedLop
                ? CatechismClass::where('id', $this->selectedLop)->value('name')
                : null;
        }
    }

    // ==================== PROPERTY UPDATERS ====================

    public function updatedFile(): void
    {
        $this->resetPreview();

        if (!$this->selectedLop) {
            $this->addError('selectedLop', 'Vui lòng chọn lớp trước khi upload file');
            $this->file = null;
            return;
        }

        $this->validateOnly('file', $this->formRules, $this->messages);

        $this->preview();
    }

    // ==================== ACTIONS ====================

    public function preview(): void
    {
        $existingCodes = StudentNew::whereNotNull('student_code')
            ->pluck('student_code')
            ->toArray();

        $this->validate($this->formRules, $this->messages);

        $this->resetPreview();

        try {
            $data = Excel::toArray(new StudentPreviewImport, $this->file)[0] ?? [];

            if (empty($data)) {
                $this->errors[] = 'File Excel trống hoặc không đúng định dạng';
                return;
            }

            $requiredHeaders = ['ho_ten_dem', 'ten', 'ngay_sinh', 'gioi_tinh'];
            $firstRow        = $data[0] ?? [];

            foreach ($requiredHeaders as $header) {
                if (!array_key_exists($header, $firstRow)) {
                    $this->errors[] = "Thiếu cột bắt buộc: <strong>{$header}</strong>";
                }
            }

            if (!empty($this->errors)) {
                return;
            }

            $saintNames = Holymanagement::pluck('name')
                ->map(fn($n) => strtolower(trim($n)))
                ->toArray();

            $groupNames = ParishGroup::active()
                ->pluck('name')
                ->map(fn($n) => strtolower(trim($n)))
                ->toArray();

            $existingStudents = StudentNew::get(['first_name', 'last_name', 'birthday'])
                ->map(fn($s) => strtolower(trim($s->last_name . ' ' . $s->first_name))
                    . '_' . ($s->birthday?->format('Y-m-d') ?? ''))
                ->toArray();

            $validGenders = ['nam', 'nữ', 'nu', 'male', 'female', 'm', 'f', '1', '0'];

            foreach ($data as $index => $row) {
                $rowNumber = $index + 6;

                // Bỏ qua dòng trống
                if (empty(trim($row['ho_ten_dem'] ?? '')) && empty(trim($row['ten'] ?? ''))) {
                    continue;
                }

                $rowWarnings = [];
                $tenThanh    = trim($row['ten_thanh'] ?? '');
                $giaoHo      = trim($row['giao_ho'] ?? '');
                $ngaySinh    = $row['ngay_sinh'] ?? '';

                $soDienThoai = null;
                $phone = trim($row['so_dien_thoai'] ?? '');
                if ($phone && strlen($phone) === 9 && is_numeric($phone)) {
                    $soDienThoai = '0' . $phone;
                }

                $email       = trim($row['email'] ?? '');
                $ghiChu      = trim($row['ghi_chu'] ?? '');
                $gioi_tinh   = trim($row['gioi_tinh'] ?? '');

                // Validate giới tính
                if (!empty($gioi_tinh) && !in_array(strtolower($gioi_tinh), $validGenders)) {
                    $rowWarnings[] = "Giới tính \"{$gioi_tinh}\" không hợp lệ (dùng: nam / nữ)";
                }

                // Validate tên thánh
                if (!empty($tenThanh) && !in_array(strtolower($tenThanh), $saintNames)) {
                    $rowWarnings[] = "Tên thánh \"{$tenThanh}\" không tìm thấy trong hệ thống";
                }

                // Validate giáo họ
                if (!empty($giaoHo) && !in_array(strtolower($giaoHo), $groupNames)) {
                    $rowWarnings[] = "Giáo họ \"{$giaoHo}\" không tìm thấy trong hệ thống";
                }

                // Validate ngày sinh
                $parsedDate = null;
                if (!empty($ngaySinh)) {
                    $parsedDate = ExcelDateParser::parse($ngaySinh);
                    if ($parsedDate === null) {
                        $rowWarnings[] = "Ngày sinh \"{$ngaySinh}\" không hợp lệ (định dạng: dd/MM/yyyy)";
                    }
                }

                // Validate email
                if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $rowWarnings[] = "Email \"{$email}\" không đúng định dạng";
                }

                // Validate số điện thoại
                if (!empty($soDienThoai) && !preg_match('/^[0-9]{9,11}$/', $soDienThoai)) {
                    $rowWarnings[] = "Số điện thoại \"{$soDienThoai}\" không đúng định dạng";
                }

                // 
                $studentCode = trim($row['ma_hoc_sinh'] ?? '');
                $fullName    = strtolower(trim(($row['ho_ten_dem'] ?? '') . ' ' . ($row['ten'] ?? '')));
                $key         = $fullName . '_' . ($parsedDate ?? '');
                $isDuplicate = false;
                $willUpdate  = false;

                if ($studentCode && in_array($studentCode, $existingCodes)) {
                    $belongsToClass = StudentNew::where('student_code', $studentCode)
                        ->whereHas('classes', fn($q) => $q->where('class_id', $this->selectedLop))
                        ->exists();

                    if ($belongsToClass) {
                        $willUpdate    = true;
                        $rowWarnings[] = "Học sinh có mã <strong>{$studentCode}</strong> sẽ được <strong>cập nhật</strong>.";
                    } else {
                        $isDuplicate   = true; // block lại, không cho update
                        $rowWarnings[] = "Học sinh có mã <strong>{$studentCode}</strong> không thuộc lớp này — dòng này sẽ bị bỏ qua.";
                    }
                } elseif (!$studentCode && in_array($key, $existingStudents)) {
                    // Không có mã, trùng tên + ngày sinh → skip như cũ
                    $isDuplicate = true;
                    $rowWarnings[] = "Học sinh đã tồn tại trong hệ thống — dòng này sẽ bị bỏ qua khi import.";
                }

                if (!empty($rowWarnings)) {
                    $this->warnings[$rowNumber] = $rowWarnings;
                }

                $this->rows[] = [
                    'row_number'    => $rowNumber,
                    'ten_thanh'     => $tenThanh,
                    'ho_ten_dem'    => trim($row['ho_ten_dem'] ?? ''),
                    'ten'           => trim($row['ten'] ?? ''),
                    'ngay_sinh'     => $ngaySinh,
                    'gioi_tinh'     => $gioi_tinh,
                    'giao_ho'       => $giaoHo,
                    'ho_ten_bo'     => trim($row['ho_ten_bo'] ?? ''),
                    'ho_ten_me'     => trim($row['ho_ten_me'] ?? ''),
                    'so_dien_thoai' => $soDienThoai,
                    'email'         => $email,
                    'ghi_chu'       => $ghiChu,
                    'ma_hoc_sinh'   => $studentCode,
                    'has_warning'   => !empty($rowWarnings),
                    'is_duplicate'  => $isDuplicate,
                    'will_update'   => $willUpdate,
                ];
            }

            $this->readyToImport = empty($this->errors) && !empty($this->rows);

            if ($this->readyToImport) {
                $duplicateCount = collect($this->rows)->where('is_duplicate', true)->count();
                $updateCount    = collect($this->rows)->where('will_update', true)->count();
                $willImport     = count($this->rows) - $duplicateCount - $updateCount;

                $msg = sprintf('Đã kiểm tra %d dòng dữ liệu.', count($this->rows));
                if ($updateCount > 0) {
                    $msg .= " Sẽ cập nhật {$updateCount} học sinh có mã.";
                }

                if ($willImport > 0) {
                    $msg .= " Sẽ thêm mới {$willImport} học sinh.";
                }

                if ($duplicateCount > 0) {
                    $msg .= " Bỏ qua {$duplicateCount} học sinh đã tồn tại.";
                }

                $this->emit('toast', 'info', $msg);
            }
        } catch (\Exception $e) {
            $this->logError($e, 'Error previewing student import');
            $this->errors[] = 'Lỗi khi đọc file: ' . $e->getMessage();
        }
    }

    public function confirmImport()
    {
        if (!$this->readyToImport) {
            Log::warning('[Import] Blocked: readyToImport = false');
            $this->addError('import', 'Dữ liệu chưa hợp lệ, không thể import');
            return;
        }

        if (!$this->selectedLop) {
            Log::warning('[Import] Blocked: selectedLop is null');
            $this->addError('import', 'Chưa chọn lớp');
            return;
        }

        if (empty($this->rows)) {
            Log::warning('[Import] Blocked: rows is empty');
            $this->addError('import', 'Không có dữ liệu để import, vui lòng upload lại file');
            return;
        }

        try {
            $result = app(ImportStudentAction::class)
                ->handleFromArray($this->rows, $this->parishId, $this->selectedLop);

            // Build thông báo thành công
            $message = "Thêm thành công {$result['imported']} học sinh vào lớp **{$this->className}**";

            if (($result['updated'] ?? 0) > 0) {
                $message .= " | Cập nhật {$result['updated']} học sinh";
            }

            if ($result['skipped_empty'] > 0) {
                $message .= " | Bỏ qua {$result['skipped_empty']} dòng trống";
            }
            if ($result['skipped_duplicate'] > 0) {
                $message .= " | Bỏ qua {$result['skipped_duplicate']} học sinh đã tồn tại";
            }

            if (!empty($result['errors'])) {
                $message .= " | ❌ " . count($result['errors']) . " dòng lỗi";
            }

            // Flash warning nếu có lỗi từng dòng
            if (!empty($result['errors'])) {
                $this->emit('toast', 'warning', implode('<br>', array_slice($result['errors'], 0, 5)));
            }

            // Reset form để cho upload lại, giữ nguyên filter lớp
            $this->resetUpload();

            // Hiển thị thông báo thành công trên trang
            $this->emit('toast', 'success', $message);
        } catch (\Exception $e) {
            Log::error('[Import] Exception', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'trace'   => $e->getTraceAsString(),
            ]);
            $this->addError('import', 'Có lỗi khi import: ' . $e->getMessage());
        }
    }

    public function resetUpload(): void
    {
        $this->file = null;
        $this->resetPreview();
        $this->resetValidation();
    }

    public function getClassNameProperty(): ?string
    {
        if (!$this->selectedLop) {
            return null;
        }

        return CatechismClass::where('id', $this->selectedLop)->value('name');
    }

    // ==================== HELPERS ====================

    protected function resetPreview(): void
    {
        $this->rows          = [];
        $this->errors        = [];
        $this->warnings      = [];
        $this->readyToImport = false;
    }

    // ==================== RENDER ====================

    public function render()
    {
        return view('livewire.student.student-import-preview')
            ->extends('frontend.layout.main')
            ->section('content');
    }
}
