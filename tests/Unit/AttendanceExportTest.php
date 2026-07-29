<?php

namespace Tests\Unit;

use App\Exports\AttendanceExport;
use App\Exports\AttendanceWorkbookExport;
use App\Http\Livewire\AttendanceManager;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\StudentNew;
use App\Models\StudentsClass;
use Carbon\Carbon;
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

            // Hàng 3 phân biệt học kỳ + tổng kết (cột buổi từ F)
            $this->assertSame('Thông tin học sinh', (string) $sheet->getCell('A3')->getValue());
            $this->assertSame('Học kỳ 1', (string) $sheet->getCell('F3')->getValue());
            $this->assertSame('Học kỳ 2', (string) $sheet->getCell('G3')->getValue());
            $this->assertSame('Tổng kết', (string) $sheet->getCell('H3')->getValue());

            // Header ở hàng 4 — cột giống file vắng + tổng kết HS
            $this->assertSame('STT', (string) $sheet->getCell('A4')->getValue());
            $this->assertSame('Tên thánh', (string) $sheet->getCell('B4')->getValue());
            $this->assertSame('Họ tên đệm', (string) $sheet->getCell('C4')->getValue());
            $this->assertSame('Tên', (string) $sheet->getCell('D4')->getValue());
            $this->assertSame('Giáo họ', (string) $sheet->getCell('E4')->getValue());
            $this->assertSame('Có mặt', (string) $sheet->getCell('H4')->getValue());
            $this->assertSame('Vắng CP', (string) $sheet->getCell('I4')->getValue());
            $this->assertSame('Vắng KP', (string) $sheet->getCell('J4')->getValue());
            $this->assertSame('Tỷ lệ có mặt (%)', (string) $sheet->getCell('K4')->getValue());

            // Dòng học sinh: HK1 có mặt (trống), HK2 CP + tổng kết
            $this->assertSame('', (string) $sheet->getCell('F5')->getValue());
            $this->assertSame('CP', (string) $sheet->getCell('G5')->getValue());
            $this->assertEquals(1, (int) $sheet->getCell('H5')->getValue());
            $this->assertEquals(1, (int) $sheet->getCell('I5')->getValue());
            $this->assertEquals(0, (int) $sheet->getCell('J5')->getValue());
            $this->assertEquals(50.0, (float) $sheet->getCell('K5')->getValue());

            // 3 dòng thống kê cuối (giống UI)
            $this->assertStringContainsString('Thống kê — Có mặt', (string) $sheet->getCell('A6')->getValue());
            $this->assertEquals(1, (int) $sheet->getCell('F6')->getValue());
            $this->assertEquals(0, (int) $sheet->getCell('G6')->getValue());

            $this->assertStringContainsString('Thống kê — Vắng có phép', (string) $sheet->getCell('A7')->getValue());
            $this->assertEquals(0, (int) $sheet->getCell('F7')->getValue());
            $this->assertEquals(1, (int) $sheet->getCell('G7')->getValue());

            $this->assertStringContainsString('Thống kê — Vắng không phép', (string) $sheet->getCell('A8')->getValue());
            $this->assertEquals(0, (int) $sheet->getCell('F8')->getValue());
            $this->assertEquals(0, (int) $sheet->getCell('G8')->getValue());

            $this->assertSame('E5', $sheet->getFreezePane());
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
                (string) $spreadsheet->getSheetByName('Đi học')->getCell('F3')->getValue()
            );
            $this->assertSame(
                'Học kỳ 2',
                (string) $spreadsheet->getSheetByName('Đi lễ')->getCell('F3')->getValue()
            );
        } finally {
            @unlink($tmp);
        }
    }

    public function test_export_attendance_requires_selected_class_and_downloads(): void
    {
        $class = $this->fx->classAssigned;

        AttendanceSession::factory()->open()->create([
            'class_id' => $class->id,
            'date'     => now()->subMonths(1)->toDateString(),
            'semester' => 2,
            'type'     => AttendanceSession::TYPE_CLASS,
        ]);

        Livewire::actingAs($this->fx->parishAdmin)
            ->test(AttendanceManager::class)
            ->set('selectedNamHoc', $this->fx->yearA->id)
            ->set('selectedClassId', $class->id)
            ->set('selectedKy', 1)
            ->call('exportAttendance')
            ->assertHasNoErrors()
            ->assertEmitted('toast', 'info', 'File gồm 2 sheet: <strong>Đi học</strong> và <strong>Đi lễ</strong>.')
            ->assertFileDownloaded();
    }

    public function test_export_attendance_warns_when_no_class_selected(): void
    {
        Livewire::actingAs($this->fx->parishAdmin)
            ->test(AttendanceManager::class)
            ->set('selectedNamHoc', $this->fx->yearA->id)
            ->set('selectedClassId', null)
            ->call('exportAttendance')
            ->assertHasNoErrors()
            ->assertEmitted('toast', 'warning', 'Vui lòng chọn lớp để xuất');
    }

    public function test_export_attendance_warns_when_no_sessions(): void
    {
        Livewire::actingAs($this->fx->parishAdmin)
            ->test(AttendanceManager::class)
            ->set('selectedNamHoc', $this->fx->yearA->id)
            ->set('selectedClassId', $this->fx->classAssigned->id)
            ->call('exportAttendance')
            ->assertHasNoErrors()
            ->assertEmitted('toast', 'warning', 'Chưa có buổi để xuất');
    }

    public function test_export_warns_before_cutoff_and_shows_inferred_kp_after_cutoff(): void
    {
        $class = $this->fx->classAssigned;
        $this->fx->parishA->update([
            'attendance_auto_finalize_enabled' => true,
            'attendance_auto_finalize_time'    => '20:00:00',
        ]);
        $missing = StudentNew::query()->create([
            'parish_id'  => $this->fx->parishA->id,
            'last_name'  => 'Nguyễn Văn',
            'first_name' => 'ZZ',
            'gender'     => 'male',
            'is_active'  => true,
        ]);
        StudentsClass::query()->create([
            'student_id' => $missing->id,
            'class_id'   => $class->id,
            'status'     => StudentsClass::STATUS_ENROLLED,
        ]);
        $session = AttendanceSession::factory()->open()->create([
            'class_id' => $class->id,
            'date'     => now()->toDateString(),
            'semester' => 1,
            'type'     => AttendanceSession::TYPE_CLASS,
        ]);
        AttendanceRecord::query()->create([
            'session_id' => $session->id,
            'student_id' => $this->fx->studentAssigned->id,
            'status'     => AttendanceRecord::STATUS_PRESENT,
        ]);

        Carbon::setTestNow(Carbon::today()->setTime(19, 0));
        Livewire::actingAs($this->fx->parishAdmin)
            ->test(AttendanceManager::class)
            ->set('selectedNamHoc', $this->fx->yearA->id)
            ->set('selectedClassId', $class->id)
            ->call('exportAttendance')
            ->assertEmitted('confirmEarlyAttendanceExport');

        Carbon::setTestNow(Carbon::today()->setTime(21, 0));
        $raw = Excel::raw(
            new AttendanceExport($class->id, null, AttendanceSession::TYPE_CLASS),
            \Maatwebsite\Excel\Excel::XLSX
        );
        $tmp = tempnam(sys_get_temp_dir(), 'att_inferred_') . '.xlsx';
        file_put_contents($tmp, $raw);

        try {
            $sheet = IOFactory::load($tmp)->getActiveSheet();
            $rows = $sheet->toArray();
            $missingRow = collect($rows)->first(fn ($row) => ($row[3] ?? null) === 'ZZ');

            $this->assertNotNull($missingRow);
            $this->assertSame('KP', $missingRow[5]);
            $this->assertStringContainsString('Giờ chốt: 20:00', (string) $sheet->getCell('A2')->getValue());
            $this->assertDatabaseMissing('attendance_records', [
                'session_id' => $session->id,
                'student_id' => $missing->id,
            ]);
        } finally {
            Carbon::setTestNow();
            @unlink($tmp);
        }
    }
}
