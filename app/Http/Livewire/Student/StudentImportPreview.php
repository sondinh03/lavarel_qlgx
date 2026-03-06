<?php

namespace App\Http\Livewire\Student;

use App\Actions\Student\ImportStudentAction;
use App\Http\Livewire\Base\BaseComponent;
use App\Imports\StudentPreviewImport;
use App\Models\CatechismClass;
use App\Models\Holymanagement;
use App\Models\NamHoc;
use App\Models\ParishGroup;
use App\Support\ExcelDateParser;
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
    public $selectedLop    = null; // = classId

    // ==================== FILE ====================

    public $file = null;

    // ==================== PREVIEW STATE ====================

    public array $rows          = [];
    public array $errors        = [];
    public array $warnings      = [];
    public bool  $readyToImport = false;

    // ==================== VALIDATION ====================

    protected $rules = [
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

        $this->validateOnly('file');

        $this->preview();
    }

    // ==================== ACTIONS ====================

    public function preview(): void
    {
        $this->validate([
            'file'        => 'required|mimes:xlsx,csv|max:5120',
            'selectedLop' => 'required|integer|exists:classes,id',
        ], $this->messages);

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
                ->map(fn($n) => strtolower(trim($n)))->toArray();

            $groupNames = ParishGroup::active()
                ->pluck('name')
                ->map(fn($n) => strtolower(trim($n)))
                ->toArray();

            foreach ($data as $index => $row) {
                $rowNumber = $index + 6;

                if (empty(trim($row['ho_ten_dem'] ?? '')) && empty(trim($row['ten'] ?? ''))) {
                    continue;
                }

                $rowWarnings = [];
                $tenThanh    = trim($row['ten_thanh'] ?? '');

                $giaoHo      = trim($row['giao_ho'] ?? '');
                $ngaySinh    = $row['ngay_sinh'] ?? '';

                if (!empty($tenThanh) && !in_array(strtolower($tenThanh), $saintNames)) {
                    $rowWarnings[] = "Tên thánh \"{$tenThanh}\" không tìm thấy trong hệ thống";
                }

                if (!empty($giaoHo) && !in_array(strtolower($giaoHo), $groupNames)) {
                    $rowWarnings[] = "Giáo họ \"{$giaoHo}\" không tìm thấy trong hệ thống";
                }

                if (!empty($ngaySinh)) {
                    // try {
                    //     is_numeric($ngaySinh)
                    //         ? \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($ngaySinh)
                    //         : \Carbon\Carbon::parse($ngaySinh);
                    // } catch (\Exception) {
                    //     $rowWarnings[] = "Ngày sinh \"{$ngaySinh}\" không hợp lệ";
                    // }
                    $parsedDate = ExcelDateParser::parse($ngaySinh);
                    if ($parsedDate == null) {
                        $rowWarnings[] = "Ngày sinh \"{$ngaySinh}\" không hợp lệ (định dạng: dd/MM/yyyy)";
                    }
                }

                if (!empty($rowWarnings)) {
                    $this->warnings[$rowNumber] = $rowWarnings;
                }

                $this->rows[] = [
                    'row_number'  => $rowNumber,
                    'ten_thanh'   => $tenThanh,
                    'ho_ten_dem'  => trim($row['ho_ten_dem'] ?? ''),
                    'ten'         => trim($row['ten'] ?? ''),
                    'ngay_sinh'   => $ngaySinh,
                    'gioi_tinh'   => trim($row['gioi_tinh'] ?? ''),
                    'giao_ho'     => $giaoHo,
                    'ho_ten_bo'   => trim($row['ho_ten_bo'] ?? ''),
                    'ho_ten_me'   => trim($row['ho_ten_me'] ?? ''),
                    'has_warning' => !empty($rowWarnings),
                ];
            }


            $this->readyToImport = empty($this->errors) && !empty($this->rows);

            if ($this->readyToImport) {
                $warningCount = count($this->warnings);
                $msg = sprintf('Đã kiểm tra %d dòng dữ liệu. Sẵn sàng import.', count($this->rows));
                if ($warningCount > 0) {
                    $msg .= " ({$warningCount} dòng có cảnh báo — sẽ bỏ qua giá trị không khớp)";
                }
                session()->flash('info', $msg);
            }
        } catch (\Exception $e) {
            $this->logError($e, 'Error previewing student import');
            $this->errors[] = 'Lỗi khi đọc file: ' . $e->getMessage();
        }
    }

    public function confirmImport()
    {
        if (!$this->readyToImport) {
            session()->flash('error', 'Dữ liệu chưa hợp lệ, không thể import');
            return;
        }

        if (!$this->selectedLop) {
            session()->flash('error', 'Chưa chọn lớp');
            return;
        }

        try {
            $result = app(ImportStudentAction::class)
                ->handle($this->file, $this->parishId, $this->selectedLop);

            $message = "✅ Import thành công {$result['imported']} học sinh vào lớp";

            if ($result['skipped'] > 0) {
                $message .= " | Bỏ qua {$result['skipped']} dòng trống";
            }

            if (!empty($result['errors'])) {
                $message .= " | ❌ " . count($result['errors']) . " dòng lỗi";
                session()->flash('warning', implode('<br>', array_slice($result['errors'], 0, 5)));
            }

            session()->flash('message', $message);
            return redirect()->route('dashboard');

            // return redirect()->route('students.index', ['class' => $this->selectedLop]);
        } catch (\Exception $e) {
            $this->logError($e, 'Error confirming student import', ['selectedLop' => $this->selectedLop]);
            session()->flash('error', 'Có lỗi khi import: ' . $e->getMessage());
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
