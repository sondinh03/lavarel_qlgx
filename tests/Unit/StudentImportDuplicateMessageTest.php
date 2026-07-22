<?php

namespace Tests\Unit;

use App\Models\StudentNew;
use App\Models\StudentsClass;
use App\Support\StudentImportDuplicateMessage;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Support\CatechistAuthFixture;
use Tests\TestCase;

class StudentImportDuplicateMessageTest extends TestCase
{
    use DatabaseTransactions;

    private CatechistAuthFixture $fx;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fx = CatechistAuthFixture::make();
    }

    public function test_profile_match_message_when_student_not_enrolled_in_current_year(): void
    {
        $student = $this->fx->studentAssigned;
        $student->load(['saint', 'classes.schoolYear']);

        $message = StudentImportDuplicateMessage::forProfileMatch(
            $student,
            $this->fx->yearA->id,
            $this->fx->classOtherSameParish->id,
            $this->fx->classOtherSameParish->name,
            $this->fx->yearA->name,
        );

        $this->assertStringContainsString('Đã có hồ sơ trong giáo xứ', $message);
        $this->assertStringContainsString($student->student_code, $message);
        $this->assertStringContainsString('Đang học lớp', $message);
        $this->assertStringContainsString($this->fx->classAssigned->name, $message);
        $this->assertStringContainsString('Không thể import tạo mới', $message);
    }

    public function test_profile_match_message_when_student_available_for_enrollment(): void
    {
        StudentsClass::query()
            ->where('student_id', $this->fx->studentAssigned->id)
            ->delete();

        $student = StudentNew::with(['saint', 'classes.schoolYear'])->findOrFail($this->fx->studentAssigned->id);

        $message = StudentImportDuplicateMessage::forProfileMatch(
            $student,
            $this->fx->yearA->id,
            $this->fx->classAssigned->id,
            $this->fx->classAssigned->name,
            $this->fx->yearA->name,
        );

        $this->assertStringContainsString('chưa được xếp vào lớp nào', $message);
        $this->assertStringContainsString('Học sinh có sẵn', $message);
    }

    public function test_profile_match_message_when_student_already_in_target_class(): void
    {
        $student = StudentNew::with(['saint', 'classes.schoolYear'])->findOrFail($this->fx->studentAssigned->id);

        $message = StudentImportDuplicateMessage::forProfileMatch(
            $student,
            $this->fx->yearA->id,
            $this->fx->classAssigned->id,
            $this->fx->classAssigned->name,
            $this->fx->yearA->name,
        );

        $this->assertStringContainsString('đã có trong lớp', $message);
        $this->assertStringContainsString('Điền mã học sinh', $message);
    }

    public function test_profile_match_message_when_student_only_in_previous_school_year(): void
    {
        StudentsClass::query()
            ->where('student_id', $this->fx->studentAssigned->id)
            ->delete();

        StudentsClass::query()->create([
            'student_id' => $this->fx->studentAssigned->id,
            'class_id'   => $this->fx->classOldYear->id,
            'status'     => StudentsClass::STATUS_ENROLLED,
        ]);

        $student = StudentNew::with(['saint', 'classes.schoolYear'])->findOrFail($this->fx->studentAssigned->id);

        $message = StudentImportDuplicateMessage::forProfileMatch(
            $student,
            $this->fx->yearA->id,
            $this->fx->classAssigned->id,
            $this->fx->classAssigned->name,
            $this->fx->yearA->name,
        );

        $this->assertStringContainsString('Lần ghi danh gần nhất', $message);
        $this->assertStringContainsString($this->fx->classOldYear->name, $message);
        $this->assertStringContainsString('năm học cũ', $message);
        $this->assertStringContainsString('Sao chép cấu trúc lớp', $message);
        $this->assertStringContainsString('Học sinh có sẵn', $message);
    }

    public function test_invalid_code_message(): void
    {
        $message = StudentImportDuplicateMessage::forInvalidCode('HDO-99-9999');

        $this->assertStringContainsString('không tồn tại trong giáo xứ', $message);
        $this->assertStringContainsString('bỏ trống cột mã', $message);
    }

    public function test_code_wrong_class_message(): void
    {
        $student = StudentNew::with(['saint', 'classes.schoolYear'])->findOrFail($this->fx->studentAssigned->id);

        $message = StudentImportDuplicateMessage::forCodeWrongClass(
            $student,
            (string) $student->student_code,
            $this->fx->yearA->id,
            $this->fx->classOtherSameParish->name,
        );

        $this->assertStringContainsString((string) $student->student_code, $message);
        $this->assertStringContainsString($this->fx->classAssigned->name, $message);
        $this->assertStringContainsString('không phải lớp', $message);
    }
}
