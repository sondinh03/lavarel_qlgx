<?php

namespace Tests\Unit;

use App\Exports\AttendanceExport;
use App\Exports\AttendanceWorkbookExport;
use App\Http\Livewire\AttendanceManager;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\CatechismClass;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\Support\CatechistAuthFixture;
use Tests\TestCase;

class AttendanceExportTest extends TestCase
{
    use DatabaseTransactions;

    private CatechistAuthFixture $fx;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fx = CatechistAuthFixture::make();
    }

    public function test_export_whole_year_includes_both_semesters_and_excludes_other_class_or_type(): void
    {
        $class = $this->fx->classAssigned;
        $student = $this->fx->studentAssigned;

        $hk1 = AttendanceSession::factory()->open()->create([
            'class_id' => $class->id,
            'date'     => now()->subMonths(4)->toDateString(),
            'semester' => 1,
            'type'     => AttendanceSession::TYPE_CLASS,
        ]);

        $hk2 = AttendanceSession::factory()->open()->create([
            'class_id' => $class->id,
            'date'     => now()->subMonths(1)->toDateString(),
            'semester' => 2,
            'type'     => AttendanceSession::TYPE_CLASS,
        ]);

        // Khác loại — không xuất khi type=đi học
        AttendanceSession::factory()->open()->create([
            'class_id' => $class->id,
            'date'     => now()->subDays(3)->toDateString(),
            'semester' => 1,
            'type'     => AttendanceSession::TYPE_CEREMONY,
        ]);

        // Khác lớp — không xuất
        AttendanceSession::factory()->open()->create([
            'class_id' => $this->fx->classOtherSameParish->id,
            'date'     => now()->subDays(2)->toDateString(),
            'semester' => 1,
            'type'     => AttendanceSession::TYPE_CLASS,
        ]);

        AttendanceRecord::query()->create([
            'session_id' => $hk1->id,
            'student_id' => $student->id,
            'status'     => AttendanceRecord::STATUS_PRESENT,
            'note'       => null,
        ]);

        AttendanceRecord::query()->create([
            'session_id' => $hk2->id,
            'student_id' => $student->id,
            'status'     => AttendanceRecord::STATUS_ABSENT_EXCUSED,
            'note'       => 'Bệnh',
        ]);

        $raw = Excel::raw(
            new AttendanceExport($class->id, null, AttendanceSession::TYPE_CLASS),
            \Maatwebsite\Excel\Excel::XLSX
        );

        $tmp = tempnam(sys_get_temp_dir(), 'att_export_') . '.xlsx';
        file_put_contents($tmp, $raw);

        try {
            $sheet = IOFactory::load($tmp)->getActiveSheet();

            $this->assertStringContainsString('Cả năm', (string) $sheet->getCell('A1')->getValue());
            $this->assertStringContainsString('Đi học', (string) $sheet->getCell('A1')->getValue());
            $this->assertStringContainsString('2 buổi', (string) $sheet->getCell('A2')->getValue());

            // Hàng 3 phân biệt các học kỳ
            $this->assertSame('Thông tin học sinh', (string) $sheet->getCell('A3')->getValue());
            $this->assertSame('Học kỳ 1', (string) $sheet->getCell('H3')->getValue());
            $this->assertSame('Học kỳ 2', (string) $sheet->getCell('I3')->getValue());
            $this->assertSame('Tổng kết', (string) $sheet->getCell('J3')->getValue());

            // Header ở hàng 4
            $this->assertSame('STT', (string) $sheet->getCell('A4')->getValue());
            $this->assertSame('Có mặt', (string) $sheet->getCell('J4')->getValue());
            $this->assertSame('Vắng CP', (string) $sheet->getCell('K4')->getValue());

            // Dòng học sinh: HK1 Có mặt, HK2 Vắng CP
            $this->assertSame('Có mặt', (string) $sheet->getCell('H5')->getValue());
            $this->assertSame('Vắng CP', (string) $sheet->getCell('I5')->getValue());
            $this->assertEquals(1, (int) $sheet->getCell('J5')->getValue());
            $this->assertEquals(1, (int) $sheet->getCell('K5')->getValue());

            // 3 dòng thống kê cuối
            $this->assertStringContainsString('Thống kê — Có mặt', (string) $sheet->getCell('A6')->getValue());
            $this->assertEquals(1, (int) $sheet->getCell('H6')->getValue());
            $this->assertEquals(0, (int) $sheet->getCell('I6')->getValue());

            $this->assertStringContainsString('Thống kê — Vắng CP', (string) $sheet->getCell('A7')->getValue());
            $this->assertEquals(0, (int) $sheet->getCell('H7')->getValue());
            $this->assertEquals(1, (int) $sheet->getCell('I7')->getValue());

            $this->assertStringContainsString('Thống kê — Vắng KP', (string) $sheet->getCell('A8')->getValue());
            $this->assertEquals(0, (int) $sheet->getCell('H8')->getValue());
            $this->assertEquals(0, (int) $sheet->getCell('I8')->getValue());
        } finally {
            @unlink($tmp);
        }
    }

    public function test_workbook_has_separate_class_and_ceremony_sheets(): void
    {
        $class = $this->fx->classAssigned;

        AttendanceSession::factory()->open()->create([
            'class_id' => $class->id,
            'date'     => now()->subMonth()->toDateString(),
            'semester' => 1,
            'type'     => AttendanceSession::TYPE_CLASS,
        ]);

        AttendanceSession::factory()->open()->create([
            'class_id' => $class->id,
            'date'     => now()->toDateString(),
            'semester' => 2,
            'type'     => AttendanceSession::TYPE_CEREMONY,
        ]);

        $raw = Excel::raw(
            new AttendanceWorkbookExport($class->id),
            \Maatwebsite\Excel\Excel::XLSX
        );

        $tmp = tempnam(sys_get_temp_dir(), 'att_workbook_') . '.xlsx';
        file_put_contents($tmp, $raw);

        try {
            $spreadsheet = IOFactory::load($tmp);

            $this->assertSame(['Đi học', 'Đi lễ'], $spreadsheet->getSheetNames());
            $this->assertStringContainsString(
                'Đi học',
                (string) $spreadsheet->getSheetByName('Đi học')->getCell('A1')->getValue()
            );
            $this->assertStringContainsString(
                'Đi lễ',
                (string) $spreadsheet->getSheetByName('Đi lễ')->getCell('A1')->getValue()
            );
            $this->assertSame(
                'Học kỳ 1',
                (string) $spreadsheet->getSheetByName('Đi học')->getCell('H3')->getValue()
            );
            $this->assertSame(
                'Học kỳ 2',
                (string) $spreadsheet->getSheetByName('Đi lễ')->getCell('H3')->getValue()
            );
        } finally {
            @unlink($tmp);
        }
    }

    public function test_export_attendance_ignores_ui_semester_and_downloads_whole_year(): void
    {
        $class = $this->fx->classAssigned;

        AttendanceSession::factory()->open()->create([
            'class_id' => $class->id,
            'date'     => now()->subMonths(1)->toDateString(),
            'semester' => 2,
            'type'     => AttendanceSession::TYPE_CLASS,
        ]);

        // UI đang chọn HK1 — trước đây sẽ báo "Chưa có buổi"; giờ vẫn xuất cả năm
        Livewire::actingAs($this->fx->parishAdmin)
            ->test(AttendanceManager::class)
            ->set('selectedClassId', $class->id)
            ->set('selectedKy', 1)
            ->set('attendanceType', AttendanceSession::TYPE_CLASS)
            ->call('exportAttendance')
            ->assertHasNoErrors()
            ->assertFileDownloaded();
    }

    public function test_export_attendance_warns_when_no_sessions(): void
    {
        $emptyClass = CatechismClass::query()->create([
            'name'            => 'Empty Export Class',
            'parish_id'       => $this->fx->parishA->id,
            'school_year_id'  => $this->fx->yearA->id,
            'grade_level_id'  => $this->fx->classAssigned->grade_level_id,
            'is_active'       => true,
        ]);

        Livewire::actingAs($this->fx->parishAdmin)
            ->test(AttendanceManager::class)
            ->set('selectedClassId', $emptyClass->id)
            ->set('attendanceType', AttendanceSession::TYPE_CLASS)
            ->call('exportAttendance')
            ->assertHasNoErrors()
            ->assertEmitted('toast', 'warning', 'Chưa có buổi để xuất');
    }
}
