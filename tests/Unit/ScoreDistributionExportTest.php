<?php

namespace Tests\Unit;

use App\Exports\ScoreDistributionWorkbookExport;
use App\Http\Livewire\Score\ScoreManager;
use App\Http\Livewire\Score\ScoreStatistics;
use App\Models\ScoreType;
use App\Models\StudentNew;
use App\Models\StudentScore;
use App\Models\StudentsClass;
use App\Services\ScoreDistributionReport;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\Support\CatechistAuthFixture;
use Tests\TestCase;

class ScoreDistributionExportTest extends TestCase
{
    use DatabaseTransactions;

    private CatechistAuthFixture $fx;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fx = CatechistAuthFixture::make();
    }

    public function test_bucket_boundaries_match_distribution_chart(): void
    {
        $this->assertSame(0, ScoreDistributionReport::bucketFor(0));
        $this->assertSame(0, ScoreDistributionReport::bucketFor(0.9));
        $this->assertSame(1, ScoreDistributionReport::bucketFor(1));
        $this->assertSame(8, ScoreDistributionReport::bucketFor(8.9));
        $this->assertSame(9, ScoreDistributionReport::bucketFor(9));
        $this->assertSame(9, ScoreDistributionReport::bucketFor(10));
    }

    public function test_workbook_has_overview_distribution_and_single_detail_sheet(): void
    {
        StudentScore::query()->create([
            'student_class_id' => $this->fx->pivotAssigned->id,
            'score_type_id'    => $this->fx->scoreTypeAssigned->id,
            'score_value'      => 8.2,
            'attempt'          => 1,
        ]);

        StudentScore::query()->create([
            'student_class_id' => $this->fx->pivotOtherSameParish->id,
            'score_type_id'    => $this->fx->scoreTypeOther->id,
            'score_value'      => 4.9,
            'attempt'          => 1,
        ]);

        $semesterTwoAssigned = ScoreType::query()->create([
            'class_id'   => $this->fx->classAssigned->id,
            'semester'   => ScoreType::SEMESTER_2,
            'type'       => ScoreType::TYPE_15_PHUT,
            'name'       => '15p-hk2',
            'order'      => 1,
            'coefficient' => 1,
            'max_score'  => 10,
            'is_active'  => true,
        ]);
        $semesterTwoOther = ScoreType::query()->create([
            'class_id'   => $this->fx->classOtherSameParish->id,
            'semester'   => ScoreType::SEMESTER_2,
            'type'       => ScoreType::TYPE_15_PHUT,
            'name'       => '15p-other-hk2',
            'order'      => 1,
            'coefficient' => 1,
            'max_score'  => 10,
            'is_active'  => true,
        ]);
        StudentScore::query()->create([
            'student_class_id' => $this->fx->pivotAssigned->id,
            'score_type_id'    => $semesterTwoAssigned->id,
            'score_value'      => 9.2,
            'attempt'          => 1,
        ]);
        StudentScore::query()->create([
            'student_class_id' => $this->fx->pivotOtherSameParish->id,
            'score_type_id'    => $semesterTwoOther->id,
            'score_value'      => 5.1,
            'attempt'          => 1,
        ]);

        $studentWithoutScores = StudentNew::query()->create([
            'parish_id' => $this->fx->parishA->id,
            'first_name' => 'ChuaDiem',
            'last_name' => 'HocSinh',
            'phone' => '0999' . random_int(100000, 999999),
            'is_active' => true,
        ]);
        StudentsClass::query()->create([
            'student_id' => $studentWithoutScores->id,
            'class_id' => $this->fx->classAssigned->id,
            'status' => StudentsClass::STATUS_ENROLLED,
        ]);

        $classIds = [
            $this->fx->classAssigned->id,
            $this->fx->classOtherSameParish->id,
        ];

        $payload = app(ScoreDistributionReport::class)
            ->payloadForAllPeriods($classIds);

        $raw = Excel::raw(
            new ScoreDistributionWorkbookExport(
                $payload,
                $this->fx->yearA->name,
                'Toàn giáo xứ ' . $this->fx->parishA->name,
            ),
            \Maatwebsite\Excel\Excel::XLSX
        );

        $tmp = tempnam(sys_get_temp_dir(), 'score_distribution_') . '.xlsx';
        file_put_contents($tmp, $raw);

        try {
            $workbook = IOFactory::load($tmp);

            $this->assertSame(
                ['Tổng quan', 'Phân phối', 'Chi tiết'],
                $workbook->getSheetNames()
            );

            $overview = $workbook->getSheetByName('Tổng quan');
            $this->assertSame('Tổng quan kết quả học tập', (string) $overview->getCell('A1')->getValue());
            $this->assertSame('Học kỳ 1', (string) $overview->getCell('B3')->getValue());
            $this->assertSame('Học kỳ 2', (string) $overview->getCell('C3')->getValue());
            $this->assertSame('Cả năm', (string) $overview->getCell('D3')->getValue());
            $this->assertSame('Số học sinh đã có điểm', (string) $overview->getCell('A7')->getValue());
            $this->assertEquals(2, (int) $overview->getCell('B7')->getValue());
            $this->assertEquals(3, (int) $overview->getCell('B9')->getValue());
            $this->assertSame('Xếp loại học lực', (string) $overview->getCell('A12')->getValue());
            $this->assertSame('F2F7FB', $overview->getStyle('B4')->getFill()->getStartColor()->getRGB());
            $this->assertSame('FFF7ED', $overview->getStyle('C4')->getFill()->getStartColor()->getRGB());
            $this->assertSame('EAF7EF', $overview->getStyle('D4')->getFill()->getStartColor()->getRGB());
            $this->assertSame('F2F7FB', $overview->getStyle('B13')->getFill()->getStartColor()->getRGB());
            $this->assertSame('FFF7ED', $overview->getStyle('D13')->getFill()->getStartColor()->getRGB());
            $this->assertSame('EAF7EF', $overview->getStyle('F13')->getFill()->getStartColor()->getRGB());

            // Phân phối: 10 khoảng + dòng tổng cộng, 8.2 vào khoảng 8-9 và 4.9 vào khoảng 4-5.
            $ranges = $workbook->getSheetByName('Phân phối');
            $this->assertSame('0-1', (string) $ranges->getCell('A4')->getValue());
            $this->assertSame('8-9', (string) $ranges->getCell('A12')->getValue());
            $this->assertEquals(1, (int) $ranges->getCell('C12')->getValue());
            $this->assertEquals(1, (int) $ranges->getCell('E13')->getValue());
            $this->assertSame('4-5', (string) $ranges->getCell('A8')->getValue());
            $this->assertEquals(1, (int) $ranges->getCell('C8')->getValue());
            $this->assertSame('Tổng cộng', (string) $ranges->getCell('A14')->getValue());
            $this->assertEquals(2, (int) $ranges->getCell('C14')->getValue());
            $this->assertSame('F2F7FB', $ranges->getStyle('C4')->getFill()->getStartColor()->getRGB());
            $this->assertSame('FFF7ED', $ranges->getStyle('E4')->getFill()->getStartColor()->getRGB());
            $this->assertSame('EAF7EF', $ranges->getStyle('G4')->getFill()->getStartColor()->getRGB());

            // Chi tiết: không còn cột khoảng điểm, mỗi dòng có đủ HK1, HK2 và cả năm.
            $detail = $workbook->getSheetByName('Chi tiết');
            $this->assertSame('Học kỳ 1', (string) $detail->getCell('G3')->getValue());
            $this->assertSame('Học kỳ 2', (string) $detail->getCell('I3')->getValue());
            $this->assertSame('Tổng kết cả năm', (string) $detail->getCell('K3')->getValue());
            $this->assertSame('Điểm TB HK1', (string) $detail->getCell('G4')->getValue());
            $this->assertSame('Điểm TB HK2', (string) $detail->getCell('I4')->getValue());
            $this->assertSame('Điểm TB cả năm', (string) $detail->getCell('K4')->getValue());
            $this->assertSame('Xếp loại cả năm', (string) $detail->getCell('L4')->getValue());
            $this->assertNotContains('Khoảng điểm', $detail->rangeToArray('A4:L4')[0]);
            $this->assertSame('An', (string) $detail->getCell('D5')->getValue());
            $this->assertEquals(8.2, (float) $detail->getCell('G5')->getValue());
            $this->assertSame('Giỏi', (string) $detail->getCell('H5')->getValue());
            $this->assertEquals(9.2, (float) $detail->getCell('I5')->getValue());
            $this->assertNotSame('', (string) $detail->getCell('K5')->getValue());
            $this->assertSame('Binh', (string) $detail->getCell('D6')->getValue());
            $this->assertEquals(4.9, (float) $detail->getCell('G6')->getValue());
            $this->assertEquals(5.1, (float) $detail->getCell('I6')->getValue());
            $this->assertNotSame('', (string) $detail->getCell('K6')->getValue());
            $this->assertSame('ChuaDiem', (string) $detail->getCell('D7')->getValue());
            $this->assertNull($detail->getCell('G7')->getValue());
            $this->assertNull($detail->getCell('I7')->getValue());
            $this->assertNull($detail->getCell('K7')->getValue());
            $this->assertSame(7, $detail->getHighestDataRow());
            $this->assertSame('E5', $detail->getFreezePane());
            $this->assertSame('A4:L7', $detail->getAutoFilter()->getRange());
            $this->assertTrue($detail->getStyle('D5')->getFont()->getBold());

            // Mỗi nhóm học kỳ có màu nền riêng, dùng lại bảng màu của file bảng điểm.
            $this->assertSame(
                'F2F7FB',
                $detail->getStyle('G5')->getFill()->getStartColor()->getRGB()
            );
            $this->assertSame(
                'FFF7ED',
                $detail->getStyle('I5')->getFill()->getStartColor()->getRGB()
            );
            $this->assertSame(
                'EAF7EF',
                $detail->getStyle('K5')->getFill()->getStartColor()->getRGB()
            );
        } finally {
            @unlink($tmp);
        }
    }

    public function test_score_statistics_downloads_all_periods_regardless_of_selected_semester(): void
    {
        StudentScore::query()->create([
            'student_class_id' => $this->fx->pivotAssigned->id,
            'score_type_id'    => $this->fx->scoreTypeAssigned->id,
            'score_value'      => 8.2,
            'attempt'          => 1,
        ]);

        Livewire::actingAs($this->fx->parishAdmin)
            ->test(ScoreStatistics::class)
            ->set('selectedNamHoc', $this->fx->yearA->id)
            ->set('selectedKhoi', null)
            ->set('selectedLop', null)
            ->set('selectedSemester', 0)
            ->call('exportScoreStatistics')
            ->assertHasNoErrors()
            ->assertFileDownloaded();
    }

    public function test_score_manager_exports_same_statistics_workbook(): void
    {
        StudentScore::query()->create([
            'student_class_id' => $this->fx->pivotAssigned->id,
            'score_type_id'    => $this->fx->scoreTypeAssigned->id,
            'score_value'      => 8.2,
            'attempt'          => 1,
        ]);

        $component = Livewire::actingAs($this->fx->parishAdmin)
            ->test(ScoreManager::class)
            ->set('selectedNamHoc', $this->fx->yearA->id)
            ->set('selectedLop', $this->fx->classAssigned->id);

        $method = new \ReflectionMethod($component->instance(), 'scoreStatisticsClassIds');
        $method->setAccessible(true);
        $classIds = $method->invoke($component->instance())->map(fn ($id) => (int) $id)->sort()->values();

        $this->assertSame(
            collect([
                $this->fx->classAssigned->id,
                $this->fx->classOtherSameParish->id,
            ])->sort()->values()->all(),
            $classIds->all()
        );

        $component
            ->call('exportScoreStatistics')
            ->assertHasNoErrors()
            ->assertFileDownloaded();
    }
}
