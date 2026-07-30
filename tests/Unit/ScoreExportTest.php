<?php

namespace Tests\Unit;

use App\Exports\ScoreExport;
use App\Exports\ScoreParishWorkbookExport;
use App\Http\Livewire\Score\ScoreManager;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\GradingSetting;
use App\Models\ScoreType;
use App\Models\StudentScore;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\Support\CatechistAuthFixture;
use Tests\TestCase;

class ScoreExportTest extends TestCase
{
    use DatabaseTransactions;

    private CatechistAuthFixture $fx;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fx = CatechistAuthFixture::make();
    }

    public function test_export_contains_both_semesters_and_freezes_headers_and_student_columns(): void
    {
        $semesterTwoType = ScoreType::query()->create([
            'class_id' => $this->fx->classAssigned->id,
            'semester' => ScoreType::SEMESTER_2,
            'type' => ScoreType::TYPE_15_PHUT,
            'name' => '15p HK2',
            'order' => 1,
            'coefficient' => 1,
            'max_score' => 10,
            'is_active' => true,
        ]);

        StudentScore::query()->create([
            'student_class_id' => $this->fx->pivotAssigned->id,
            'score_type_id' => $this->fx->scoreTypeAssigned->id,
            'score_value' => 8,
            'attempt' => 1,
        ]);

        StudentScore::query()->create([
            'student_class_id' => $this->fx->pivotAssigned->id,
            'score_type_id' => $semesterTwoType->id,
            'score_value' => 6,
            'attempt' => 1,
        ]);

        $raw = Excel::raw(
            new ScoreExport($this->fx->classAssigned->id),
            \Maatwebsite\Excel\Excel::XLSX
        );

        $tmp = tempnam(sys_get_temp_dir(), 'score_export_') . '.xlsx';
        file_put_contents($tmp, $raw);

        try {
            $sheet = IOFactory::load($tmp)->getActiveSheet();

            $this->assertStringContainsString('Bảng điểm cả năm', (string) $sheet->getCell('A1')->getValue());
            $this->assertStringContainsString(
                'TB học kỳ = trung bình học tập 100% + chuyên cần học 0% + chuyên cần lễ 0%',
                (string) $sheet->getCell('A2')->getValue()
            );
            $this->assertSame('Thông tin học sinh', (string) $sheet->getCell('A3')->getValue());
            $this->assertSame('Học kỳ 1', (string) $sheet->getCell('F3')->getValue());
            $this->assertSame('Học kỳ 2', (string) $sheet->getCell('K3')->getValue());
            $this->assertSame('Tổng kết cả năm', (string) $sheet->getCell('P3')->getValue());

            // Mỗi học kỳ: cột loại điểm → 3 hạng mục điểm → TB học kỳ
            $this->assertSame('15p', (string) $sheet->getCell('F4')->getValue());
            $this->assertSame('Trung bình học tập (100%)', (string) $sheet->getCell('G4')->getValue());
            $this->assertSame('Chuyên cần học (0%)', (string) $sheet->getCell('H4')->getValue());
            $this->assertSame('Chuyên cần lễ (0%)', (string) $sheet->getCell('I4')->getValue());
            $this->assertSame('Trung bình học kỳ 1', (string) $sheet->getCell('J4')->getValue());

            $this->assertSame('15p HK2', (string) $sheet->getCell('K4')->getValue());
            $this->assertSame('Trung bình học tập (100%)', (string) $sheet->getCell('L4')->getValue());
            $this->assertSame('Trung bình học kỳ 2', (string) $sheet->getCell('O4')->getValue());
            $this->assertSame('Trung bình cả năm', (string) $sheet->getCell('P4')->getValue());
            $this->assertSame('Xếp loại', (string) $sheet->getCell('Q4')->getValue());

            $this->assertEquals(8, (float) $sheet->getCell('F5')->getValue());
            $this->assertEquals(8, (float) $sheet->getCell('G5')->getValue());
            $this->assertSame('', (string) $sheet->getCell('H5')->getValue());
            $this->assertEquals(8, (float) $sheet->getCell('J5')->getValue());
            $this->assertEquals(6, (float) $sheet->getCell('K5')->getValue());
            $this->assertEquals(6, (float) $sheet->getCell('O5')->getValue());
            $this->assertEquals(7, (float) $sheet->getCell('P5')->getValue());
            $this->assertSame('Khá', (string) $sheet->getCell('Q5')->getValue());

            $this->assertSame('E5', $sheet->getFreezePane());
            $this->assertTrue($sheet->getStyle('D5')->getFont()->getBold());
        } finally {
            @unlink($tmp);
        }
    }

    public function test_export_fills_attendance_components_and_weighted_average(): void
    {
        StudentScore::query()->create([
            'student_class_id' => $this->fx->pivotAssigned->id,
            'score_type_id'    => $this->fx->scoreTypeAssigned->id,
            'score_value'      => 8,
            'attempt'          => 1,
        ]);

        $session = AttendanceSession::query()->create([
            'class_id' => $this->fx->classAssigned->id,
            'date'     => now()->subDay()->toDateString(),
            'semester' => ScoreType::SEMESTER_1,
            'type'     => AttendanceSession::TYPE_CLASS,
            'status'   => AttendanceSession::STATUS_CLOSED,
        ]);

        AttendanceRecord::query()->create([
            'session_id' => $session->id,
            'student_id' => $this->fx->studentAssigned->id,
            'status'     => AttendanceRecord::STATUS_PRESENT,
        ]);

        GradingSetting::query()->create(array_merge(GradingSetting::DEFAULTS, [
            'parish_id'               => $this->fx->parishA->id,
            'school_year_id'          => $this->fx->yearA->id,
            'weight_academic'         => 50,
            'weight_class_attendance' => 50,
            'weight_mass_attendance'  => 0,
        ]));

        $raw = Excel::raw(
            new ScoreExport($this->fx->classAssigned->id),
            \Maatwebsite\Excel\Excel::XLSX
        );

        $tmp = tempnam(sys_get_temp_dir(), 'score_export_') . '.xlsx';
        file_put_contents($tmp, $raw);

        try {
            $sheet = IOFactory::load($tmp)->getActiveSheet();

            $this->assertSame('Trung bình học tập (50%)', (string) $sheet->getCell('G4')->getValue());
            $this->assertSame('Chuyên cần học (50%)', (string) $sheet->getCell('H4')->getValue());
            $this->assertSame('Chuyên cần lễ (0%)', (string) $sheet->getCell('I4')->getValue());

            $this->assertEquals(8, (float) $sheet->getCell('G5')->getValue());
            $this->assertEquals(10, (float) $sheet->getCell('H5')->getValue());
            // 8×0.5 + 10×0.5 = 9
            $this->assertEquals(9, (float) $sheet->getCell('J5')->getValue());
        } finally {
            @unlink($tmp);
        }
    }

    public function test_changing_semester_filter_reloads_scores_matrix(): void
    {
        $semesterTwoType = ScoreType::query()->create([
            'class_id' => $this->fx->classAssigned->id,
            'semester' => ScoreType::SEMESTER_2,
            'type' => ScoreType::TYPE_15_PHUT,
            'name' => '15p HK2',
            'order' => 1,
            'coefficient' => 1,
            'max_score' => 10,
            'is_active' => true,
        ]);

        StudentScore::query()->create([
            'student_class_id' => $this->fx->pivotAssigned->id,
            'score_type_id' => $this->fx->scoreTypeAssigned->id,
            'score_value' => 8,
            'attempt' => 1,
        ]);

        StudentScore::query()->create([
            'student_class_id' => $this->fx->pivotAssigned->id,
            'score_type_id' => $semesterTwoType->id,
            'score_value' => 6,
            'attempt' => 1,
        ]);

        $component = Livewire::actingAs($this->fx->parishAdmin)
            ->test(ScoreManager::class)
            ->emit('filterChanged', [
                'namHoc' => $this->fx->yearA->id,
                'lop' => $this->fx->classAssigned->id,
                'ky' => 1,
            ]);

        $pivotId = $this->fx->pivotAssigned->id;

        $this->assertEquals(
            8.0,
            $component->get('scoresMatrix')[$pivotId][$this->fx->scoreTypeAssigned->id]['value'] ?? null
        );

        // Đổi kỳ qua filter — trước đây scoresLoaded không reset nên ma trận trống
        $component->emit('filterChanged', ['ky' => 2]);

        $this->assertEquals(
            6.0,
            $component->get('scoresMatrix')[$pivotId][$semesterTwoType->id]['value'] ?? null
        );

        // Quay lại kỳ 1 vẫn hiển thị đúng
        $component->emit('filterChanged', ['ky' => 1]);

        $this->assertEquals(
            8.0,
            $component->get('scoresMatrix')[$pivotId][$this->fx->scoreTypeAssigned->id]['value'] ?? null
        );
    }

    public function test_parish_workbook_creates_one_sheet_for_each_class_in_selected_year(): void
    {
        $classes = \App\Models\CatechismClass::query()
            ->where('parish_id', $this->fx->parishA->id)
            ->where('school_year_id', $this->fx->yearA->id)
            ->orderBy('name')
            ->get(['id', 'name']);

        $raw = Excel::raw(
            new ScoreParishWorkbookExport($classes),
            \Maatwebsite\Excel\Excel::XLSX
        );
        $tmp = tempnam(sys_get_temp_dir(), 'score_parish_') . '.xlsx';
        file_put_contents($tmp, $raw);

        try {
            $workbook = IOFactory::load($tmp);

            $this->assertCount(2, $workbook->getAllSheets());
            $this->assertSame(
                $classes->pluck('name')->map(fn (string $name) => mb_substr($name, 0, 31))->all(),
                $workbook->getSheetNames()
            );
            $this->assertStringContainsString(
                $this->fx->classAssigned->name,
                (string) $workbook->getSheetByName(mb_substr($this->fx->classAssigned->name, 0, 31))
                    ->getCell('A1')->getValue()
            );
        } finally {
            @unlink($tmp);
        }
    }

    public function test_score_manager_downloads_all_parish_classes_regardless_of_selected_class_or_semester(): void
    {
        ScoreType::query()->create([
            'class_id' => $this->fx->classAssigned->id,
            'semester' => ScoreType::SEMESTER_2,
            'type' => ScoreType::TYPE_15_PHUT,
            'name' => 'HK2 only',
            'order' => 1,
            'coefficient' => 1,
            'max_score' => 10,
            'is_active' => true,
        ]);

        Livewire::actingAs($this->fx->parishAdmin)
            ->test(ScoreManager::class)
            ->set('selectedLop', $this->fx->classAssigned->id)
            ->set('selectedSemester', ScoreType::SEMESTER_1)
            ->call('exportScores')
            ->assertHasNoErrors()
            ->assertFileDownloaded();
    }
}
