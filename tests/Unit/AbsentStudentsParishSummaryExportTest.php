<?php

namespace Tests\Unit;

use App\Exports\AbsentStudentsParishSummaryExport;
use App\Exports\AbsentStudentsWorkbookExport;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\Support\CatechistAuthFixture;
use Tests\TestCase;

class AbsentStudentsParishSummaryExportTest extends TestCase
{
    use DatabaseTransactions;

    private CatechistAuthFixture $fx;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fx = CatechistAuthFixture::make();
    }

    public function test_summary_sheet_aggregates_by_class_and_workbook_starts_with_summary(): void
    {
        $class = $this->fx->classAssigned;
        $student = $this->fx->studentAssigned;
        $date = now()->toDateString();

        $session = AttendanceSession::factory()->open()->create([
            'class_id' => $class->id,
            'date'     => $date,
            'semester' => 1,
            'type'     => AttendanceSession::TYPE_CLASS,
        ]);

        AttendanceRecord::query()->create([
            'session_id' => $session->id,
            'student_id' => $student->id,
            'status'     => AttendanceRecord::STATUS_ABSENT_EXCUSED,
            'note'       => 'Bệnh',
        ]);

        $raw = Excel::raw(
            new AbsentStudentsWorkbookExport(
                (int) $this->fx->parishA->id,
                (int) $this->fx->yearA->id,
                $date,
                $date,
                AttendanceSession::TYPE_CLASS,
            ),
            \Maatwebsite\Excel\Excel::XLSX
        );

        $tmp = tempnam(sys_get_temp_dir(), 'absent_sum_') . '.xlsx';
        file_put_contents($tmp, $raw);

        try {
            $spreadsheet = IOFactory::load($tmp);
            $names = $spreadsheet->getSheetNames();
            $this->assertSame('Tổng hợp', $names[0]);

            $summary = $spreadsheet->getSheetByName('Tổng hợp');
            $this->assertStringContainsString('Tổng hợp học sinh vắng', (string) $summary->getCell('A1')->getValue());
            $this->assertSame('Lớp', (string) $summary->getCell('B4')->getValue());
            $this->assertSame('Số HS vắng', (string) $summary->getCell('D4')->getValue());

            $foundClass = false;
            $foundCp = false;
            for ($row = 5; $row <= 40; $row++) {
                if ((string) $summary->getCell('B' . $row)->getValue() === (string) $class->name) {
                    $foundClass = true;
                    $this->assertEquals(1, (int) $summary->getCell('D' . $row)->getValue());
                    $this->assertEquals(1, (int) $summary->getCell('E' . $row)->getValue());
                    $foundCp = true;
                    break;
                }
            }
            $this->assertTrue($foundClass, 'Expected class row in summary');
            $this->assertTrue($foundCp);
        } finally {
            @unlink($tmp);
        }
    }

    public function test_summary_export_alone_has_expected_columns(): void
    {
        $raw = Excel::raw(
            new AbsentStudentsParishSummaryExport(
                (int) $this->fx->parishA->id,
                (int) $this->fx->yearA->id,
                now()->toDateString(),
                now()->toDateString(),
            ),
            \Maatwebsite\Excel\Excel::XLSX
        );

        $tmp = tempnam(sys_get_temp_dir(), 'absent_sum2_') . '.xlsx';
        file_put_contents($tmp, $raw);

        try {
            $sheet = IOFactory::load($tmp)->getActiveSheet();
            $this->assertSame('Tổng hợp', $sheet->getTitle());
            $this->assertSame('STT', (string) $sheet->getCell('A4')->getValue());
            $this->assertSame('Tổng lượt vắng', (string) $sheet->getCell('G4')->getValue());
            $this->assertSame('Số buổi', (string) $sheet->getCell('H4')->getValue());
        } finally {
            @unlink($tmp);
        }
    }
}
