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
                $this->errors[] = 'File Excel trống hoặc không đúng định dạng.';
                return;
            }

            $requiredHeaders = ['ho_ten_dem', 'ten', 'ngay_sinh', 'gioi_tinh'];
            $firstRow        = $data[0] ?? [];

            foreach ($requiredHeaders as $header) {
                if (!array_key_exists($header, $firstRow)) {
                    $this->errors[] = "Thiếu cột bắt buộc: <strong>{$header}</strong>.";
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

                if (empty(trim($row['ho_ten_dem'] ?? '')) && empty(trim($row['ten'] ?? ''))) {
                    continue;
                }

                $rowWarnings = [];
                $tenThanh    = trim($row['ten_thanh'] ?? '');
                $giaoHo      = trim($row['giao_ho'] ?? '');
                $ngaySinh    = $row['ngay_sinh'] ?? '';
                $email       = trim($row['email'] ?? '');
                $ghiChu      = trim($row['ghi_chu'] ?? '');
                $gioi_tinh   = trim($row['gioi_tinh'] ?? '');

                // Normalize SĐT
                $soDienThoai = null;
                $phoneRaw    = trim($row['so_dien_thoai'] ?? '');
                if ($phoneRaw) {
                    $phone = preg_replace('/\D/', '', (string) $phoneRaw);
                    if (str_starts_with($phone, '84') && strlen($phone) === 11) {
                        $phone = '0' . substr($phone, 2);
                    }
                    if (strlen($phone) === 9) {
                        $phone = '0' . $phone;
                    }
                    $soDienThoai = $phone ?: null;
                }

                // Validate giới tính
                if (!empty($gioi_tinh) && !in_array(strtolower($gioi_tinh), $validGenders)) {
                    $rowWarnings[] = "Giới tính <strong>\"{$gioi_tinh}\"</strong> không hợp lệ — chỉ chấp nhận: nam / nữ.";
                }

                // Validate tên thánh
                if (!empty($tenThanh) && !in_array(strtolower($tenThanh), $saintNames)) {
                    $rowWarnings[] = "Tên thánh <strong>\"{$tenThanh}\"</strong> không tìm thấy trong hệ thống.";
                }

                // Validate giáo họ
                if (!empty($giaoHo) && !in_array(strtolower($giaoHo), $groupNames)) {
                    $rowWarnings[] = "Giáo họ <strong>\"{$giaoHo}\"</strong> không tìm thấy trong hệ thống.";
                }

                // Validate ngày sinh
                $parsedDate = null;
                if (!empty($ngaySinh)) {
                    $parsedDate = ExcelDateParser::parse($ngaySinh);
                    if ($parsedDate === null) {
                        $rowWarnings[] = "Ngày sinh <strong>\"{$ngaySinh}\"</strong> không hợp lệ — định dạng yêu cầu: <strong>dd/MM/yyyy</strong>.";
                    }
                }

                // Validate email
                if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $rowWarnings[] = "Email <strong>\"{$email}\"</strong> không đúng định dạng.";
                }

                // Validate SĐT
                if ($soDienThoai && !preg_match('/^0[0-9]{9}$/', $soDienThoai)) {
                    $rowWarnings[] = "Số điện thoại <strong>\"{$phoneRaw}\"</strong> không hợp lệ — yêu cầu 10 số, bắt đầu bằng 0.";
                }

                // Kiểm tra mã học sinh & duplicate
                $studentCode = trim($row['ma_hoc_sinh'] ?? '');
                $fullName    = strtolower(trim(($row['ho_ten_dem'] ?? '') . ' ' . ($row['ten'] ?? '')));
                $key         = $fullName . '_' . ($parsedDate ?? '');
                $isDuplicate = false;
                $willUpdate  = false;

                if ($studentCode) {
                    if (in_array($studentCode, $existingCodes)) {
                        $belongsToClass = StudentNew::where('student_code', $studentCode)
                            ->whereHas('classes', fn($q) => $q->where('class_id', $this->selectedLop))
                            ->exists();

                        if ($belongsToClass) {
                            $willUpdate    = true;
                            $rowWarnings[] = "Học sinh mã <strong>{$studentCode}</strong> đã có trong lớp — thông tin sẽ được <strong>cập nhật</strong> khi xác nhận.";
                        } else {
                            $isDuplicate   = true;
                            $rowWarnings[] = "Học sinh mã <strong>{$studentCode}</strong> không thuộc lớp này — dòng này sẽ bị <strong>bỏ qua</strong>.";
                        }
                    } else {
                        // Có mã nhưng không tồn tại trong hệ thống
                        $isDuplicate   = true;
                        $rowWarnings[] = "Mã học sinh <strong>{$studentCode}</strong> không tồn tại trong hệ thống — dòng này sẽ bị <strong>bỏ qua</strong>.";
                    }
                } elseif (in_array($key, $existingStudents)) {
                    $isDuplicate   = true;
                    $rowWarnings[] = "Học sinh <strong>" . trim(($row['ho_ten_dem'] ?? '') . ' ' . ($row['ten'] ?? '')) . "</strong> đã tồn tại trong hệ thống — dòng này sẽ bị <strong>bỏ qua</strong>.";
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

                $parts   = [];
                $parts[] = sprintf('Đã đọc %d dòng dữ liệu.', count($this->rows));

                if ($willImport > 0) {
                    $parts[] = "Thêm mới {$willImport} học sinh.";
                }
                if ($updateCount > 0) {
                    $parts[] = "Cập nhật {$updateCount} học sinh.";
                }
                if ($duplicateCount > 0) {
                    $parts[] = "Bỏ qua {$duplicateCount} học sinh trùng hoặc không hợp lệ.";
                }

                $this->emit('toast', 'info', implode(' ', $parts));
            }
        } catch (\Exception $e) {
            $this->logError($e, 'Error previewing student import');
            $this->errors[] = 'Lỗi khi đọc file: ' . $e->getMessage();
        }
    }

    public function confirmImport(): void
    {
        if (!$this->readyToImport) {
            $this->addError('import', 'Dữ liệu chưa hợp lệ, không thể import.');
            return;
        }

        if (!$this->selectedLop) {
            $this->addError('import', 'Vui lòng chọn lớp trước khi import.');
            return;
        }

        if (empty($this->rows)) {
            $this->addError('import', 'Không có dữ liệu để import, vui lòng upload lại file.');
            return;
        }

        try {
            $result = app(ImportStudentAction::class)
                ->handleFromArray($this->rows, $this->parishId, $this->selectedLop);

            // Toast lỗi từng dòng (nếu có)
            if (!empty($result['errors'])) {
                $errorMsg = implode('<br>', array_slice($result['errors'], 0, 5));
                if (count($result['errors']) > 5) {
                    $errorMsg .= '<br>... và ' . (count($result['errors']) - 5) . ' lỗi khác.';
                }
                $this->emit('toast', 'warning', $errorMsg);
            }

            $parts   = [];
            $parts[] = "Import hoàn tất lớp {$this->className}.";

            if ($result['imported'] > 0) {
                $parts[] = "Thêm mới {$result['imported']} học sinh.";
            }
            if (($result['updated'] ?? 0) > 0) {
                $parts[] = "Cập nhật {$result['updated']} học sinh.";
            }
            if ($result['skipped_duplicate'] > 0) {
                $parts[] = "Bỏ qua {$result['skipped_duplicate']} học sinh trùng.";
            }
            if ($result['skipped_empty'] > 0) {
                $parts[] = "Bỏ qua {$result['skipped_empty']} dòng trống.";
            }
            if (!empty($result['errors'])) {
                $parts[] = count($result['errors']) . " dòng có lỗi.";
            }

            $this->resetUpload();
            $this->emit('toast', 'success', implode(' ', $parts));
        } catch (\Exception $e) {
            Log::error('[Import] Exception', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'trace'   => $e->getTraceAsString(),
            ]);
            $this->addError('import', 'Có lỗi xảy ra khi import, vui lòng thử lại.');
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

    public function getDuplicateCountProperty(): int
    {
        return collect($this->rows)->where('is_duplicate', true)->count();
    }

    public function getUpdateCountProperty(): int
    {
        return collect($this->rows)->where('will_update', true)->count();
    }

    public function getNewCountProperty(): int
    {
        return count($this->rows) - $this->duplicateCount - $this->updateCount;
    }

    public function getRealWarningCountProperty(): int
    {
        return collect($this->rows)
            ->where('has_warning', true)
            ->where('is_duplicate', false)
            ->where('will_update', false)
            ->count();
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
