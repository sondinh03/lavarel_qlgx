<?php

namespace Tests\Unit;

use App\Exports\AbsentTeachersWorkbookExport;
use App\Exports\TeacherAttendanceExport;
use App\Exports\TeacherAttendanceWorkbookExport;
use App\Http\Livewire\AttendanceManager;
use App\Models\TeacherAttendanceRecord;
use App\Models\TeacherAttendanceSession;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\Support\CatechistAuthFixture;
use Tests\TestCase;

class TeacherAttendanceExportTest extends TestCase
{
    use DatabaseTransactions;

    private CatechistAuthFixture $fx;

    private int $parishId;

    private int $namHocId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fx = CatechistAuthFixture::make();
        $this->parishId = (int) $this->fx->parishA->id;
        $this->namHocId = (int) $this->fx->yearA->id;
    }

    public function test_teacher_summary_export_has_three_sheets_and_cp_status(): void
    {
        $teacher = $this->fx->ordinaryTeacher;

        $teach = TeacherAttendanceSession::query()->create([
            'parish_id' => $this->parishId,
            'namhoc_id' => $this->namHocId,
            'date'      => now()->subDays(7)->toDateString(),
            'type'      => TeacherAttendanceSession::TYPE_TEACH,
            'status'    => TeacherAttendanceSession::STATUS_OPENING,
        ]);

        TeacherAttendanceSession::query()->create([
            'parish_id' => $this->parishId,
            'namhoc_id' => $this->namHocId,
            'date'      => now()->subDays(3)->toDateString(),
            'type'      => TeacherAttendanceSession::TYPE_CEREMONY,
            'status'    => TeacherAttendanceSession::STATUS_OPENING,
        ]);

        TeacherAttendanceRecord::query()->create([
            'session_id' => $teach->id,
            'teacher_id' => $teacher->id,
            'status'     => TeacherAttendanceRecord::STATUS_ABSENT_EXCUSED,
            'note'       => 'Bệnh',
        ]);

        $raw = Excel::raw(
            new TeacherAttendanceWorkbookExport($this->parishId, $this->namHocId),
            \Maatwebsite\Excel\Excel::XLSX
        );

        $tmp = tempnam(sys_get_temp_dir(), 'glv_att_') . '.xlsx';
        file_put_contents($tmp, $raw);

        try {
            $spreadsheet = IOFactory::load($tmp);
            $this->assertSame(['Đi dạy', 'Đi lễ', 'Họp'], $spreadsheet->getSheetNames());

            $sheet = $spreadsheet->getSheetByName('Đi dạy');
            $this->assertStringContainsString('GLV', (string) $sheet->getCell('A1')->getValue());
            $this->assertSame('STT', (string) $sheet->getCell('A4')->getValue());

            $foundCp = false;
            for ($row = 5; $row <= 30; $row++) {
                if ((string) $sheet->getCell('F' . $row)->getValue() === 'CP') {
                    $foundCp = true;
                    break;
                }
            }
            $this->assertTrue($foundCp, 'Expected at least one CP cell in Đi dạy sheet');
            $this->assertSame('E5', $sheet->getFreezePane());
        } finally {
            @unlink($tmp);
        }
    }

    public function test_absent_teachers_workbook_filters_by_type(): void
    {
        $teacher = $this->fx->ordinaryTeacher;
        $date = now()->toDateString();

        $session = TeacherAttendanceSession::query()->create([
            'parish_id' => $this->parishId,
            'namhoc_id' => $this->namHocId,
            'date'      => $date,
            'type'      => TeacherAttendanceSession::TYPE_MEETING,
            'status'    => TeacherAttendanceSession::STATUS_OPENING,
        ]);

        TeacherAttendanceRecord::query()->create([
            'session_id' => $session->id,
            'teacher_id' => $teacher->id,
            'status'     => TeacherAttendanceRecord::STATUS_ABSENT_UNEXCUSED,
            'note'       => null,
        ]);

        $raw = Excel::raw(
            new AbsentTeachersWorkbookExport(
                $this->parishId,
                $this->namHocId,
                $date,
                $date,
                TeacherAttendanceSession::TYPE_MEETING,
            ),
            \Maatwebsite\Excel\Excel::XLSX
        );

        $tmp = tempnam(sys_get_temp_dir(), 'glv_absent_') . '.xlsx';
        file_put_contents($tmp, $raw);

        try {
            $spreadsheet = IOFactory::load($tmp);
            $this->assertSame(['Họp'], $spreadsheet->getSheetNames());
            $sheet = $spreadsheet->getSheetByName('Họp');
            $this->assertStringContainsString('GLV vắng', (string) $sheet->getCell('A1')->getValue());
            $this->assertSame('KP', (string) $sheet->getCell('F5')->getValue());
        } finally {
            @unlink($tmp);
        }
    }

    public function test_livewire_export_teacher_attendance_downloads(): void
    {
        TeacherAttendanceSession::query()->create([
            'parish_id' => $this->parishId,
            'namhoc_id' => $this->namHocId,
            'date'      => now()->toDateString(),
            'type'      => TeacherAttendanceSession::TYPE_TEACH,
            'status'    => TeacherAttendanceSession::STATUS_OPENING,
        ]);

        Livewire::actingAs($this->fx->parishAdmin)
            ->test(AttendanceManager::class)
            ->set('subjectTarget', 'teachers')
            ->set('selectedNamHoc', $this->namHocId)
            ->call('exportAttendance')
            ->assertHasNoErrors()
            ->assertEmitted(
                'toast',
                'info',
                'File gồm 3 sheet: <strong>Đi dạy</strong>, <strong>Đi lễ</strong> và <strong>Họp</strong>.'
            )
            ->assertFileDownloaded();
    }

    public function test_single_type_sheet_export_has_expected_headers(): void
    {
        $raw = Excel::raw(
            new TeacherAttendanceExport(
                $this->parishId,
                $this->namHocId,
                TeacherAttendanceSession::TYPE_TEACH,
                'Đi dạy',
            ),
            \Maatwebsite\Excel\Excel::XLSX
        );

        $tmp = tempnam(sys_get_temp_dir(), 'glv_empty_') . '.xlsx';
        file_put_contents($tmp, $raw);

        try {
            $sheet = IOFactory::load($tmp)->getActiveSheet();
            $this->assertSame('Thông tin GLV', (string) $sheet->getCell('A3')->getValue());
            $this->assertSame('Tên', (string) $sheet->getCell('D4')->getValue());
            $this->assertSame('Giáo họ', (string) $sheet->getCell('E4')->getValue());
        } finally {
            @unlink($tmp);
        }
    }
}
