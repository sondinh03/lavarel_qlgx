<?php

namespace App\Http\Livewire\Parishioners;

use App\Actions\Parishioner\ImportParishionerAction;
use App\Http\Livewire\Base\BaseComponent;
use App\Imports\ParishionerPreviewImport;
use App\Models\Holymanagement;
use App\Models\ParishGroup;
use App\Models\Parishioner;
use App\Support\ExcelDateParser;
use App\Support\ParishionerEnumResolver;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

class ParishionerImportPreview extends BaseComponent
{
    use WithFileUploads;

    public $file = null;

    public array $rows          = [];
    public array $fileErrors    = [];
    public array $warnings      = [];
    public bool  $readyToImport = false;

    protected $rules = [
        'file' => 'required|mimes:xlsx,csv|max:5120',
    ];

    protected $messages = [
        'file.required' => 'Vui lòng chọn file Excel',
        'file.mimes'    => 'File phải có định dạng .xlsx hoặc .csv',
        'file.max'      => 'File không được vượt quá 5MB',
    ];

    private const GIAODAN_SACRAMENT_COLUMNS = [
        'rua_toi_ngay', 'rua_toi_so', 'rua_toi_nguoi_ban', 'rua_toi_dau_dau', 'rua_toi_giao_xu',
        'ruoc_le_ngay', 'ruoc_le_so', 'ruoc_le_nguoi_ban', 'ruoc_le_giao_xu',
        'them_suc_ngay', 'them_suc_so', 'them_suc_nguoi_ban', 'them_suc_dau_dau', 'them_suc_giao_xu',
    ];

    private const BITICH_SHEET_COLUMNS = [
        'xuc_dau_ngay', 'xuc_dau_tinh_trang', 'xuc_dau_nguoi_ban',
        'hon_phoi_ngay', 'hon_phoi_so', 'hon_phoi_noi', 'hon_phoi_tinh',
        'hon_phoi_lm_chung', 'hon_phoi_nhan_chung_1', 'hon_phoi_nhan_chung_2', 'hon_phoi_tinh_trang',
    ];

    public function mount(): void
    {
        parent::mount();
        $this->requireManager();
        $this->requireParishId();
        $this->authorize('create', Parishioner::class);
    }

    public function loadInitialData(): void {}

    public function updatedFile(): void
    {
        $this->resetPreview();
        $this->preview();
    }

    public function preview(): void
    {
        $this->validate();
        $this->resetPreview();

        try {
            $allSheets = Excel::toArray(new ParishionerPreviewImport, $this->file);
            $data      = $allSheets[0] ?? [];

            if (empty($data)) {
                $this->fileErrors[] = 'File Excel trống hoặc không đúng định dạng (sheet GiaoDan)';
                return;
            }

            $requiredHeaders = ['ho_ten_dem', 'ten', 'gioi_tinh'];
            $firstRow        = $data[0] ?? [];

            foreach ($requiredHeaders as $header) {
                if (!array_key_exists($header, $firstRow)) {
                    $this->fileErrors[] = "Thiếu cột bắt buộc: <strong>{$header}</strong>";
                }
            }

            if (!empty($this->fileErrors)) {
                return;
            }

            $sacramentMap = $this->buildSacramentMap($allSheets[1] ?? []);

            $saintNames = Holymanagement::pluck('name')
                ->map(fn($n) => strtolower(trim($n)))
                ->toArray();

            $groupNames = ParishGroup::active()
                ->pluck('name')
                ->map(fn($n) => strtolower(trim($n)))
                ->toArray();

            $existingByName = Parishioner::get(['first_name', 'last_name', 'birthday'])
                ->map(fn($p) => strtolower(trim($p->last_name . ' ' . $p->first_name))
                    . '_' . ($p->birthday?->format('Y-m-d') ?? ''))
                ->toArray();

            $existingCccds = Parishioner::whereNotNull('cccd')
                ->pluck('cccd')
                ->map(fn($c) => preg_replace('/[^0-9]/', '', $c ?? ''))
                ->filter()
                ->toArray();

            $existingPhones = Parishioner::whereNotNull('phone')
                ->pluck('phone')
                ->map(fn($p) => preg_replace('/[^0-9]/', '', $p ?? ''))
                ->filter()
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
                $gioiTinh    = trim($row['gioi_tinh'] ?? '');
                $email       = trim($row['email'] ?? '');
                $cccd        = preg_replace('/[^0-9]/', '', $row['cccd'] ?? '');
                $phone       = $this->normalizePhone($row['so_dien_thoai'] ?? '');

                if (empty($gioiTinh)) {
                    $rowWarnings[] = 'Thiếu giới tính — mặc định sẽ ghi nhận là <strong>nam</strong>.';
                } elseif (!in_array(mb_strtolower($gioiTinh, 'UTF-8'), $validGenders, true)) {
                    $rowWarnings[] = "Giới tính <strong>\"{$gioiTinh}\"</strong> không hợp lệ — chỉ chấp nhận: nam / nữ.";
                }

                if (!empty($tenThanh) && !in_array(strtolower($tenThanh), $saintNames, true)) {
                    $rowWarnings[] = "Tên thánh <strong>\"{$tenThanh}\"</strong> không tìm thấy trong hệ thống.";
                }

                if (!empty($giaoHo) && !in_array(strtolower($giaoHo), $groupNames, true)) {
                    $rowWarnings[] = "Giáo họ <strong>\"{$giaoHo}\"</strong> không tìm thấy trong hệ thống.";
                }

                $parsedDate = null;
                if (!empty($ngaySinh)) {
                    $parsedDate = ExcelDateParser::parse($ngaySinh);
                    if ($parsedDate === null) {
                        $rowWarnings[] = "Ngày sinh <strong>\"{$ngaySinh}\"</strong> không hợp lệ — định dạng: <strong>dd/MM/yyyy</strong>.";
                    }
                }

                if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $rowWarnings[] = "Email <strong>\"{$email}\"</strong> không đúng định dạng.";
                }

                if ($phone && !preg_match('/^0[0-9]{9}$/', $phone)) {
                    $rowWarnings[] = "Số điện thoại <strong>\"{$row['so_dien_thoai']}\"</strong> không hợp lệ — yêu cầu 10 số, bắt đầu bằng 0.";
                }

                $this->validateEnumField($rowWarnings, 'dan_toc', $row['dan_toc'] ?? null, 'ethnic', 'Dân tộc');
                $this->validateEnumField($rowWarnings, 'nghe_nghiep', $row['nghe_nghiep'] ?? null, 'career', 'Nghề nghiệp');
                $this->validateEnumField($rowWarnings, 'trinh_do_hoc_van', $row['trinh_do_hoc_van'] ?? null, 'education_level', 'Trình độ học vấn');
                $this->validateEnumField($rowWarnings, 'trinh_do_chuyen_mon', $row['trinh_do_chuyen_mon'] ?? null, 'specialist_level', 'Trình độ chuyên môn');
                $this->validateEnumField($rowWarnings, 'trinh_do_giao_ly', $row['trinh_do_giao_ly'] ?? null, 'catechism_level', 'Trình độ giáo lý');
                $this->validateEnumField($rowWarnings, 'chuc_vu', $row['chuc_vu'] ?? null, 'position', 'Chức vụ');
                $this->validateEnumField($rowWarnings, 'cap_bac', $row['cap_bac'] ?? null, 'level', 'Cấp bậc');

                $fullName    = strtolower(trim(($row['ho_ten_dem'] ?? '') . ' ' . ($row['ten'] ?? '')));
                $nameKey     = $fullName . '_' . ($parsedDate ?? '');
                $isDuplicate = false;

                if ($cccd && in_array($cccd, $existingCccds, true)) {
                    $isDuplicate   = true;
                    $rowWarnings[] = "CCCD <strong>{$cccd}</strong> đã tồn tại — dòng này sẽ bị <strong>bỏ qua</strong>.";
                } elseif ($phone && in_array($phone, $existingPhones, true)) {
                    $isDuplicate   = true;
                    $rowWarnings[] = "Số điện thoại <strong>{$phone}</strong> đã tồn tại — dòng này sẽ bị <strong>bỏ qua</strong>.";
                } elseif (in_array($nameKey, $existingByName, true)) {
                    $isDuplicate   = true;
                    $rowWarnings[] = 'Giáo dân <strong>' . trim(($row['ho_ten_dem'] ?? '') . ' ' . ($row['ten'] ?? '')) . '</strong> đã tồn tại — dòng này sẽ bị <strong>bỏ qua</strong>.';
                }

                $sacramentRow = $this->mergeSacramentColumns($row, $sacramentMap[$nameKey] ?? []);

                if (!empty($rowWarnings)) {
                    $this->warnings[$rowNumber] = $rowWarnings;
                }

                $this->rows[] = array_merge([
                    'row_number'           => $rowNumber,
                    'ten_thanh'            => $tenThanh,
                    'ho_ten_dem'           => trim($row['ho_ten_dem'] ?? ''),
                    'ten'                  => trim($row['ten'] ?? ''),
                    'ngay_sinh'            => $ngaySinh,
                    'gioi_tinh'            => $gioiTinh,
                    'giao_ho'              => $giaoHo,
                    'so_dien_thoai'        => $phone,
                    'email'                => $email,
                    'cccd'                 => $cccd ?: null,
                    'ho_ten_bo'            => trim($row['ho_ten_bo'] ?? ''),
                    'ho_ten_me'            => trim($row['ho_ten_me'] ?? ''),
                    'tinh_trang_hon_nhan'  => trim($row['tinh_trang_hon_nhan'] ?? ''),
                    'tan_tong'             => trim($row['tan_tong'] ?? ''),
                    'ghi_chu'              => trim($row['ghi_chu'] ?? ''),
                    'que_quan'             => trim($row['que_quan'] ?? ''),
                    'dia_chi_thuong_tru'   => trim($row['dia_chi_thuong_tru'] ?? ''),
                    'tinh_thuong_tru'      => trim($row['tinh_thuong_tru'] ?? ''),
                    'con_thu'              => trim($row['con_thu'] ?? ''),
                    'dan_toc'              => trim($row['dan_toc'] ?? ''),
                    'nghe_nghiep'          => trim($row['nghe_nghiep'] ?? ''),
                    'trinh_do_hoc_van'     => trim($row['trinh_do_hoc_van'] ?? ''),
                    'trinh_do_chuyen_mon'  => trim($row['trinh_do_chuyen_mon'] ?? ''),
                    'trinh_do_giao_ly'     => trim($row['trinh_do_giao_ly'] ?? ''),
                    'chuc_vu'              => trim($row['chuc_vu'] ?? ''),
                    'cap_bac'              => trim($row['cap_bac'] ?? ''),
                    'xa_thuong_tru'        => trim($row['xa_thuong_tru'] ?? ''),
                    'dia_chi_tam_tru'      => trim($row['dia_chi_tam_tru'] ?? ''),
                    'tinh_tam_tru'         => trim($row['tinh_tam_tru'] ?? ''),
                    'ngay_gia_nhap'        => trim($row['ngay_gia_nhap'] ?? ''),
                    'ngay_mat'             => trim($row['ngay_mat'] ?? ''),
                    'so_so_mat'            => trim($row['so_so_mat'] ?? ''),
                    'noi_an_tang'          => trim($row['noi_an_tang'] ?? ''),
                    'has_warning'          => !empty($rowWarnings),
                    'is_duplicate'         => $isDuplicate,
                    'has_sacrament_data'   => $this->hasSacramentData($sacramentRow),
                ], $sacramentRow);
            }

            $this->readyToImport = empty($this->fileErrors) && !empty($this->rows);

            if ($this->readyToImport) {
                $duplicateCount = collect($this->rows)->where('is_duplicate', true)->count();
                $willImport     = count($this->rows) - $duplicateCount;
                $sacramentCount = collect($this->rows)->where('has_sacrament_data', true)->count();
                $msg            = sprintf('Đã đọc %d dòng dữ liệu.', count($this->rows));

                if ($willImport > 0) {
                    $msg .= " Sẽ thêm mới {$willImport} giáo dân.";
                }
                if ($duplicateCount > 0) {
                    $msg .= " Bỏ qua {$duplicateCount} giáo dân trùng.";
                }
                if ($sacramentCount > 0) {
                    $msg .= " {$sacramentCount} dòng có dữ liệu bí tích/hôn phối.";
                }

                $this->emit('toast', 'info', $msg);
            }
        } catch (\Exception $e) {
            $this->logError($e, 'Error previewing parishioner import');
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
            $result = app(ImportParishionerAction::class)
                ->handle($this->rows, $this->parishId);

            if (!empty($result['errors'])) {
                $errorMsg = implode('<br>', array_slice($result['errors'], 0, 5));
                if (count($result['errors']) > 5) {
                    $errorMsg .= '<br>... và ' . (count($result['errors']) - 5) . ' lỗi khác.';
                }
                $this->emit('toast', 'warning', $errorMsg);
            }

            $parts = ['Import hoàn tất.'];
            if ($result['imported'] > 0) {
                $parts[] = "Thêm mới {$result['imported']} giáo dân.";
            }
            if ($result['sacraments_created'] > 0) {
                $parts[] = "Tạo {$result['sacraments_created']} bí tích.";
            }
            if ($result['marriages_created'] > 0) {
                $parts[] = "Tạo {$result['marriages_created']} hôn phối.";
            }
            if ($result['skipped_duplicate'] > 0) {
                $parts[] = "Bỏ qua {$result['skipped_duplicate']} giáo dân trùng.";
            }
            if ($result['skipped'] > 0) {
                $parts[] = "Bỏ qua {$result['skipped']} dòng trống.";
            }
            if (!empty($result['errors'])) {
                $parts[] = count($result['errors']) . ' dòng có lỗi.';
            }

            session()->flash('message', implode(' ', $parts));
            return redirect()->route('parishioners.index');
        } catch (\Exception $e) {
            $this->logError($e, 'Error confirming parishioner import');
            $this->emit('toast', 'error', 'Có lỗi khi import: ' . $e->getMessage());
        }
    }

    public function resetUpload(): void
    {
        $this->file = null;
        $this->resetPreview();
        $this->resetValidation();
    }

    public function getDuplicateCountProperty(): int
    {
        return collect($this->rows)->where('is_duplicate', true)->count();
    }

    public function getNewCountProperty(): int
    {
        return count($this->rows) - $this->duplicateCount;
    }

    protected function resetPreview(): void
    {
        $this->rows          = [];
        $this->fileErrors    = [];
        $this->warnings      = [];
        $this->readyToImport = false;
    }

    private function buildSacramentMap(array $sacramentSheet): array
    {
        $map = [];

        foreach ($sacramentSheet as $row) {
            if (empty(trim($row['ho_ten_dem'] ?? '')) && empty(trim($row['ten'] ?? ''))) {
                continue;
            }

            $key = ParishionerEnumResolver::rowKey(
                trim($row['ho_ten_dem'] ?? ''),
                trim($row['ten'] ?? ''),
                $row['ngay_sinh'] ?? null
            );

            $map[$key] = $row;
        }

        return $map;
    }

    private function mergeSacramentColumns(array $giaoDanRow, array $bitichRow): array
    {
        $result = [];

        foreach (self::GIAODAN_SACRAMENT_COLUMNS as $col) {
            $result[$col] = trim($giaoDanRow[$col] ?? '');
        }

        foreach (self::BITICH_SHEET_COLUMNS as $col) {
            $result[$col] = trim($bitichRow[$col] ?? '');
        }

        return $result;
    }

    private function hasSacramentData(array $sacramentRow): bool
    {
        $columns = array_merge(self::GIAODAN_SACRAMENT_COLUMNS, self::BITICH_SHEET_COLUMNS);

        foreach ($columns as $col) {
            if (!empty(trim($sacramentRow[$col] ?? ''))) {
                return true;
            }
        }

        return false;
    }

    private function validateEnumField(
        array &$warnings,
        string $fieldKey,
        ?string $value,
        string $configKey,
        string $label
    ): void {
        $value = trim($value ?? '');
        if ($value === '') {
            return;
        }

        if (ParishionerEnumResolver::resolve($configKey, $value) === null) {
            $warnings[] = "{$label} <strong>\"{$value}\"</strong> không khớp danh mục — sẽ bỏ trống khi import.";
        }
    }

    private function normalizePhone(mixed $raw): ?string
    {
        $phone = preg_replace('/\D/', '', (string) $raw);
        if (!$phone) {
            return null;
        }
        if (str_starts_with($phone, '84') && strlen($phone) === 11) {
            $phone = '0' . substr($phone, 2);
        }
        if (strlen($phone) === 9) {
            $phone = '0' . $phone;
        }

        return $phone ?: null;
    }

    public function render()
    {
        return view('livewire.parishioners.parishioner-import-preview')
            ->extends('frontend.layout.parishioner')
            ->section('content');
    }
}
