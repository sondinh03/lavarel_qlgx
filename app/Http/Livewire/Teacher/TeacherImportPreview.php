<?php

namespace App\Http\Livewire\Teacher;

use App\Actions\Teacher\ImportTeacherAction;
use App\Http\Livewire\Base\BaseComponent;
use App\Imports\TeacherPreviewImport;
use App\Models\Holymanagement;
use App\Models\ParishGroup;
use App\Models\Teacher;
use App\Support\ExcelDateParser;
use App\Support\TeacherImportDuplicateMessage;
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

    /** Số dòng trùng hồ sơ đã có trong giáo xứ */
    public int $duplicateProfileCount = 0;

    /** Số dòng lặp lại trong cùng file Excel */
    public int $duplicateInFileCount = 0;

    /** Số dòng có mã GLV không hợp lệ */
    public int $duplicateInvalidCount = 0;

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

            // Kiểm tra cột bắt buộc — hiển thị theo tiêu đề tiếng Việt người dùng thấy trong file
            $requiredHeaders = [
                'ho_dem'        => 'Họ đệm',
                'ten'           => 'Tên',
                'so_dien_thoai' => 'Số điện thoại',
            ];
            $firstRow = $data[0] ?? [];

            foreach ($requiredHeaders as $header => $label) {
                if (!array_key_exists($header, $firstRow)) {
                    $this->fileErrors[] = "Thiếu cột bắt buộc: <strong>{$label}</strong> — hãy tải lại file mẫu và không thay đổi các dòng tiêu đề.";
                }
            }

            if (!empty($this->fileErrors)) {
                return;
            }

            // Cache lookups để tránh N+1
            $saintIdByName = Holymanagement::pluck('id', 'name')
                ->mapWithKeys(fn($id, $name) => [strtolower(trim($name)) => $id])
                ->toArray();

            $groupNames = ParishGroup::active()
                ->pluck('name')
                ->map(fn($n) => strtolower(trim($n)))
                ->toArray();

            // Load hồ sơ đã có để đối chiếu trùng — ParishScope tự filter parish_id
            $existingByKey  = [];
            $existingByCode = [];
            $existingPhones = [];

            Teacher::with('saint')->get()->each(function (Teacher $teacher) use (&$existingByKey, &$existingByCode, &$existingPhones) {
                if ($teacher->teacher_code) {
                    $existingByCode[$teacher->teacher_code] = $teacher;
                }

                $existingByKey[TeacherImportDuplicateMessage::duplicateKey(
                    $teacher->saint_id,
                    $teacher->last_name,
                    $teacher->first_name,
                    $teacher->birthday?->format('Y-m-d'),
                )] = $teacher;

                $digits = preg_replace('/\D/', '', (string) $teacher->phone_number);
                if ($digits !== '') {
                    $existingPhones[$digits] = $teacher;
                }
            });

            $seenKeys              = [];
            $duplicateProfileCount = 0;
            $duplicateInFileCount  = 0;
            $duplicateInvalidCount = 0;

            foreach ($data as $index => $row) {
                $rowNumber = $index + 6; // +6 vì data bắt đầu từ dòng 6

                $hoDem = trim($row['ho_dem'] ?? '');
                $ten   = trim($row['ten'] ?? '');

                // Bỏ qua dòng trống
                if ($hoDem === '' && $ten === '') {
                    continue;
                }

                $rowWarnings = [];

                $tenThanh = trim($row['ten_thanh'] ?? '');
                $giaoHo   = trim($row['giao_ho'] ?? '');
                $ngaySinh = $row['ngay_sinh'] ?? '';
                $phoneRaw = trim((string) ($row['so_dien_thoai'] ?? ''));
                $phone    = $this->normalizeExcelPhone($phoneRaw);
                $email    = trim($row['email'] ?? '');
                $teacherCode = trim((string) ($row['ma_giao_ly_vien'] ?? ''));

                // Tên là bắt buộc — không có thì dòng sẽ bị bỏ qua khi import
                if ($ten === '') {
                    $rowWarnings[] = 'Thiếu cột "Tên" — dòng này sẽ bị bỏ qua khi import';
                }

                // Kiểm tra tên thánh
                $saintId = $tenThanh !== ''
                    ? ($saintIdByName[strtolower($tenThanh)] ?? null)
                    : null;

                if ($tenThanh !== '' && $saintId === null) {
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

                // Kiểm tra SĐT hợp lệ — dòng sai định dạng sẽ bị bỏ qua khi import
                if ($phone !== '' && !preg_match('/^0\d{9}$/', $phone)) {
                    $rowWarnings[] = "Số điện thoại \"{$phoneRaw}\" không hợp lệ (cần 10 số, bắt đầu bằng 0) — dòng này sẽ bị bỏ qua khi import";
                }

                // Kiểm tra SĐT trùng — chỉ cảnh báo, vẫn import/cập nhật
                $phoneDuplicate = $phone !== '' && isset($existingPhones[$phone]);
                if ($phoneDuplicate) {
                    $matchedByPhone = $existingPhones[$phone];
                    // Không cảnh báo nếu SĐT thuộc chính hồ sơ đang được cập nhật theo mã
                    if (! ($teacherCode !== '' && ($matchedByPhone->teacher_code ?? '') === $teacherCode)) {
                        $rowWarnings[] = TeacherImportDuplicateMessage::forPhoneMatch($phone, $matchedByPhone);
                    }
                }

                // Kiểm tra email
                if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $rowWarnings[] = "Email \"{$email}\" không đúng định dạng";
                }

                // Kiểm tra mã GLV & trùng hồ sơ (tên thánh + họ tên + ngày sinh)
                $key         = TeacherImportDuplicateMessage::duplicateKey($saintId, $hoDem, $ten, $parsedDate);
                $isDuplicate = false;
                $willUpdate  = false;

                if ($teacherCode !== '') {
                    if (isset($existingByCode[$teacherCode])) {
                        $willUpdate    = true;
                        $rowWarnings[] = TeacherImportDuplicateMessage::forCodeWillUpdate($teacherCode);
                    } else {
                        $isDuplicate = true;
                        $duplicateInvalidCount++;
                        $rowWarnings[] = TeacherImportDuplicateMessage::forInvalidCode($teacherCode);
                    }
                } elseif (isset($existingByKey[$key])) {
                    $isDuplicate = true;
                    $duplicateProfileCount++;
                    $rowWarnings[] = TeacherImportDuplicateMessage::forProfileMatch($existingByKey[$key]);
                } elseif (isset($seenKeys[$key])) {
                    $isDuplicate = true;
                    $duplicateInFileCount++;
                    $rowWarnings[] = TeacherImportDuplicateMessage::forDuplicateInFile($seenKeys[$key]);
                } else {
                    $seenKeys[$key] = $rowNumber;
                }

                if (!empty($rowWarnings)) {
                    $this->warnings[$rowNumber] = $rowWarnings;
                }

                $this->rows[] = [
                    'row_number'      => $rowNumber,
                    'ma_giao_ly_vien' => $teacherCode,
                    'ten_thanh'       => $tenThanh,
                    'ho_dem'          => $hoDem,
                    'ten'             => $ten,
                    'ngay_sinh'       => $ngaySinh,
                    'gioi_tinh'       => trim($row['gioi_tinh'] ?? ''),
                    'email'           => $email,
                    'so_dien_thoai'   => $phone,
                    'giao_ho'         => $giaoHo,
                    'tao_tai_khoan'   => trim($row['tao_tai_khoan'] ?? ''),
                    'has_warning'     => !empty($rowWarnings),
                    'is_duplicate'    => $isDuplicate,
                    'will_update'     => $willUpdate,
                    'phone_duplicate' => $phoneDuplicate,
                ];
            }

            $this->duplicateProfileCount = $duplicateProfileCount;
            $this->duplicateInFileCount  = $duplicateInFileCount;
            $this->duplicateInvalidCount = $duplicateInvalidCount;

            $this->readyToImport = empty($this->fileErrors) && !empty($this->rows);

            if ($this->readyToImport) {
                $duplicateCount = collect($this->rows)->where('is_duplicate', true)->count();
                $updateCount    = collect($this->rows)->where('will_update', true)->count();
                $willImport     = count($this->rows) - $duplicateCount - $updateCount;

                $parts   = [];
                $parts[] = sprintf('Đã kiểm tra %d dòng dữ liệu.', count($this->rows));

                if ($willImport > 0) {
                    $parts[] = "Thêm mới {$willImport} giáo lý viên.";
                }
                if ($updateCount > 0) {
                    $parts[] = "Cập nhật {$updateCount} giáo lý viên.";
                }
                if ($duplicateCount > 0) {
                    $skipParts = [];
                    if ($duplicateProfileCount > 0) {
                        $skipParts[] = "{$duplicateProfileCount} người đã có hồ sơ trong giáo xứ";
                    }
                    if ($duplicateInFileCount > 0) {
                        $skipParts[] = "{$duplicateInFileCount} dòng lặp trong file";
                    }
                    if ($duplicateInvalidCount > 0) {
                        $skipParts[] = "{$duplicateInvalidCount} dòng lỗi mã";
                    }
                    $parts[] = 'Bỏ qua ' . implode(', ', $skipParts) . '. Xem chi tiết bên dưới.';
                }

                $msg = implode(' ', $parts);
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

            if (($result['updated'] ?? 0) > 0) {
                $message .= " | Cập nhật {$result['updated']} giáo lý viên";
            }

            if ($result['skipped_duplicate'] > 0) {
                $message .= " | Bỏ qua {$result['skipped_duplicate']} dòng trùng";
            }

            if ($result['skipped'] > 0) {
                $message .= " | Bỏ qua {$result['skipped']} dòng trống/không hợp lệ";
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
        $this->rows                  = [];
        $this->fileErrors            = [];
        $this->warnings              = [];
        $this->readyToImport         = false;
        $this->duplicateProfileCount = 0;
        $this->duplicateInFileCount  = 0;
        $this->duplicateInvalidCount = 0;
    }

    /**
     * Excel lưu SĐT dạng số nên mất chữ số 0 đầu (0827686945 → 827686945).
     * Phục hồi trước khi validate để dòng hợp lệ không bị bỏ qua.
     */
    protected function normalizeExcelPhone(string $value): string
    {
        $digits = preg_replace('/\D/', '', $value) ?? '';

        if ($digits === '') {
            return '';
        }

        if (str_starts_with($digits, '84') && strlen($digits) === 11) {
            $digits = '0' . substr($digits, 2);
        }

        if (strlen($digits) === 9) {
            $digits = '0' . $digits;
        }

        return $digits;
    }

    // ==================== RENDER ====================

    public function render()
    {
        return view('livewire.teacher.teacher-import-preview')
            ->extends('frontend.layout.main')
            ->section('content');
    }
}