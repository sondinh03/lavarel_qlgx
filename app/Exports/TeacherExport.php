<?php

namespace App\Exports;

use App\Models\ParishNew;
use App\Models\Teacher;
use App\Support\UserAccountEmailResolver;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Xuất danh sách GLV — cột khớp file import (không gồm dòng kỹ thuật / hướng dẫn).
 */
class TeacherExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents
{
    private int $rowIndex = 0;

    public function __construct(
        private int $parishId,
        private ?string $filterParishGroup = null,
        private ?string $filterGender = null,
        private ?string $filterActive = null,
        private ?string $search = null,
    ) {}

    public function collection(): Collection
    {
        $query = Teacher::query()
            ->with(['saint', 'parishGroup', 'user'])
            ->where('parish_id', $this->parishId);

        if ($this->search !== null && trim($this->search) !== '') {
            $term = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('first_name', 'like', $term)
                    ->orWhere('last_name', 'like', $term)
                    ->orWhere('phone_number', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('teacher_code', 'like', $term);
            });
        }

        if ($this->filterParishGroup !== null && $this->filterParishGroup !== '') {
            $query->where('parish_group_id', $this->filterParishGroup);
        }

        if ($this->filterGender !== null && $this->filterGender !== '') {
            $query->where('gender', $this->filterGender);
        }

        if ($this->filterActive !== null && $this->filterActive !== '') {
            $query->where('is_active', (bool) $this->filterActive);
        }

        return $query
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();
    }

    public function headings(): array
    {
        return [
            'STT',
            'Tên thánh',
            'Họ đệm',
            'Tên',
            'Ngày sinh',
            'Giới tính',
            'Email',
            'Số điện thoại',
            'Giáo họ',
            'Tên đăng nhập tài khoản',
            'Mã GLV',
        ];
    }

    public function map($teacher): array
    {
        ++$this->rowIndex;

        $loginIdentifier = $teacher->user
            ? UserAccountEmailResolver::displayLoginIdentifier(
                $teacher->user->email ?? null,
                $teacher->phone_number
            )
            : '';

        return [
            $this->rowIndex,
            $teacher->saint?->name ?? '',
            $teacher->last_name ?? '',
            $teacher->first_name ?? '',
            $teacher->birthday?->format('d/m/Y') ?? '',
            match ($teacher->gender) {
                'male'   => 'Nam',
                'female' => 'Nữ',
                default  => '',
            },
            $teacher->email ?? '',
            $teacher->phone_number ?? '',
            $teacher->parishGroup?->name ?? '',
            $loginIdentifier,
            $teacher->teacher_code ?? '',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            'A1:K1000' => [
                'font' => [
                    'name' => 'Times New Roman',
                    'size' => 12,
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $parishName = ParishNew::query()->whereKey($this->parishId)->value('name') ?? 'Giáo xứ';

                // Đẩy heading + data xuống 3 dòng:
                // 1 parish · 2 tiêu đề · 3 ngày xuất · 4 heading · 5+ data
                $sheet->insertNewRowBefore(1, 3);

                $sheet->setCellValue('A1', $parishName);
                $sheet->mergeCells('A1:K1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 18,
                        'name' => 'Times New Roman',
                    ],
                    'alignment' => [
                        'horizontal' => 'left',
                        'vertical'   => 'center',
                    ],
                ]);

                $sheet->setCellValue('A2', 'DANH SÁCH GIÁO LÝ VIÊN');
                $sheet->mergeCells('A2:K2');
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 16,
                        'name' => 'Times New Roman',
                    ],
                    'alignment' => [
                        'horizontal' => 'center',
                        'vertical'   => 'center',
                    ],
                ]);

                $sheet->setCellValue('A3', 'Ngày xuất: ' . now()->format('d/m/Y H:i:s'));
                $sheet->mergeCells('A3:K3');
                $sheet->getStyle('A3')->applyFromArray([
                    'font' => [
                        'size' => 12,
                        'name' => 'Times New Roman',
                    ],
                    'alignment' => [
                        'horizontal' => 'right',
                        'vertical'   => 'center',
                    ],
                ]);

                $headerRow = 4;
                $dataLastRow = $this->rowIndex > 0
                    ? $this->rowIndex + $headerRow
                    : $headerRow;

                $sheet->getStyle("A{$headerRow}:K{$headerRow}")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 12,
                        'name' => 'Times New Roman',
                    ],
                    'fill' => [
                        'fillType'   => 'solid',
                        'startColor' => ['rgb' => 'EAF7EF'],
                    ],
                    'alignment' => [
                        'horizontal' => 'center',
                        'vertical'   => 'center',
                    ],
                ]);

                if ($this->rowIndex > 0) {
                    $sheet->getStyle("A{$headerRow}:K{$dataLastRow}")->applyFromArray([
                        'borders' => [
                            'outline' => [
                                'borderStyle' => 'thick',
                                'color'       => ['rgb' => '000000'],
                            ],
                            'inside' => [
                                'borderStyle' => 'thin',
                                'color'       => ['rgb' => '000000'],
                            ],
                        ],
                    ]);

                    $sheet->getStyle('A' . ($headerRow + 1) . ":A{$dataLastRow}")
                        ->getAlignment()
                        ->setHorizontal('center');

                    // Cột Tên (D) in đậm
                    $sheet->getStyle('D' . ($headerRow + 1) . ":D{$dataLastRow}")
                        ->getFont()
                        ->setBold(true);
                }

                foreach (range('A', 'K') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                $sheet->freezePane('A5');
            },
        ];
    }
}
