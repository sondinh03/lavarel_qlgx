<?php

namespace App\Http\Livewire\Score\Concerns;

use App\Exports\ScoreDistributionWorkbookExport;
use App\Models\CatechismClass;
use App\Models\GradeLevel;
use App\Models\NamHoc;
use App\Models\ParishNew;
use App\Models\ScoreType;
use App\Services\ScoreDistributionReport;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Xuất file thống kê điểm (Tổng quan + Phân phối + Chi tiết) cho HK1, HK2 và cả năm.
 *
 * Dùng chung cho trang bảng điểm và trang thống kê điểm để hai nơi luôn ra cùng một file.
 * File xuất luôn lấy toàn giáo xứ trong năm học đang chọn; bộ lọc lớp/khối chỉ dùng cho biểu đồ.
 */
trait ExportsScoreDistribution
{
    public function exportScoreStatistics()
    {
        $this->authorize('create', ScoreType::class);

        $classIds = $this->scoreStatisticsClassIds();

        if ($classIds->isEmpty()) {
            $this->emit('toast', 'warning', 'Không tìm thấy lớp nào trong giáo xứ ở năm học đã chọn');

            return null;
        }

        $payload = app(ScoreDistributionReport::class)
            ->payloadForAllPeriods($classIds->all());

        if ($payload['students']->isEmpty()) {
            $this->emit('toast', 'warning', 'Không tìm thấy học sinh nào trong giáo xứ ở năm học đã chọn');

            return null;
        }

        $scopeName = $this->scoreStatisticsScopeName();
        $export = new ScoreDistributionWorkbookExport(
            $payload,
            (string) (NamHoc::query()->whereKey($this->selectedNamHoc)->value('name') ?? 'Năm học'),
            $scopeName,
        );
        $filename = 'ThongKeDiem_HK1_HK2_CaNam'
            . '_' . Str::slug($scopeName, '_')
            . '_' . now()->format('dmY_His') . '.xlsx';

        return response()->streamDownload(function () use ($export) {
            echo Excel::raw($export, \Maatwebsite\Excel\Excel::XLSX);
        }, $filename);
    }

    /** Danh sách lớp dùng riêng cho file xuất: luôn là toàn giáo xứ của năm học. */
    protected function scoreStatisticsClassIds(): Collection
    {
        if (! $this->selectedNamHoc || ! $this->parishId) {
            return collect();
        }

        return CatechismClass::query()
            ->where('school_year_id', $this->selectedNamHoc)
            ->where('parish_id', $this->parishId)
            ->active()
            ->pluck('id');
    }

    protected function scoreStatisticsScopeName(): string
    {
        return 'Toàn giáo xứ ' . (
            ParishNew::query()->whereKey($this->parishId)->value('name') ?? ''
        );
    }

    /** @return Collection<int, int> */
    protected function distributionClassIds(): Collection
    {
        if (! $this->selectedNamHoc) {
            return collect();
        }

        $query = CatechismClass::query()
            ->where('school_year_id', $this->selectedNamHoc)
            ->where('parish_id', $this->parishId)
            ->active();

        return match ($this->distributionScope()) {
            'class'  => collect([(int) $this->selectedLop]),
            'grade'  => $query->where('grade_level_id', $this->selectedKhoi)->pluck('id'),
            'parish' => $query->pluck('id'),
            default  => collect(),
        };
    }

    protected function distributionScope(): string
    {
        if ($this->selectedLop) {
            return 'class';
        }

        if ($this->selectedKhoi) {
            return 'grade';
        }

        return 'parish';
    }

    protected function distributionScopeName(): string
    {
        return match ($this->distributionScope()) {
            'class' => 'Lớp ' . (
                CatechismClass::query()->whereKey($this->selectedLop)->value('name') ?? 'đã chọn'
            ),
            'grade' => 'Khối ' . (
                GradeLevel::query()->whereKey($this->selectedKhoi)->value('name') ?? 'đã chọn'
            ),
            default => 'Toàn giáo xứ ' . (
                ParishNew::query()->whereKey($this->parishId)->value('name') ?? ''
            ),
        };
    }
}
