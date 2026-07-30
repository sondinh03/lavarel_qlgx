<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/** Workbook HK1 + HK2 + cả năm: Tổng quan + Phân phối + Chi tiết. */
class ScoreDistributionWorkbookExport implements WithMultipleSheets
{
    /**
     * @param  array{
     *     periods: array<string, array<string, mixed>>,
     *     students: Collection<int, array<string, mixed>>
     * }  $payload
     */
    public function __construct(
        private array $payload,
        private string $schoolYearName,
        private string $scopeName,
    ) {}

    public function sheets(): array
    {
        return [
            new ScoreDistributionOverviewSheetExport(
                $this->payload['periods'],
                $this->schoolYearName,
                $this->scopeName,
            ),
            new ScoreDistributionRangesSheetExport(
                $this->payload['periods'],
                $this->schoolYearName,
                $this->scopeName,
            ),
            new ScoreDistributionDetailSheetExport(
                $this->payload['students'],
                $this->schoolYearName,
                $this->scopeName,
            ),
        ];
    }
}
