<?php

namespace Tests\Support;

use App\Models\CatechismClass;
use App\Models\ClassTeacher;
use App\Models\GradeLevel;
use App\Models\NamHoc;
use App\Models\ParishNew;
use App\Models\ScoreType;
use App\Models\StudentNew;
use App\Models\StudentsClass;
use App\Models\Teacher;
use App\Models\User;
use App\Support\CatechistPermissions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class CatechistAuthFixture
{
    public ParishNew $parishA;

    public ParishNew $parishB;

    public NamHoc $yearA;

    public NamHoc $yearB;

    public CatechismClass $classAssigned;

    public CatechismClass $classOtherSameParish;

    public CatechismClass $classOtherParish;

    public StudentNew $studentAssigned;

    public StudentNew $studentOtherSameParish;

    public StudentNew $studentOtherParish;

    public StudentsClass $pivotAssigned;

    public StudentsClass $pivotOtherSameParish;

    public ScoreType $scoreTypeAssigned;

    public ScoreType $scoreTypeOther;

    public User $ordinaryCatechist;

    public User $scoreManagerCatechist;

    public User $studentEditorCatechist;

    /** GLV có hồ sơ nhưng không được phân công lớp nào */
    public User $unassignedCatechist;

    /** GLV chỉ có phân công ở năm học cũ (đã nghỉ) */
    public User $oldYearCatechist;

    public User $catechismAdmin;

    public User $parishAdmin;

    public Teacher $ordinaryTeacher;

    public Teacher $unassignedTeacher;

    public Teacher $oldYearTeacher;

    /** Năm học cũ (đã kết thúc) của parish A */
    public NamHoc $yearAOld;

    /** Lớp thuộc năm học cũ */
    public CatechismClass $classOldYear;

    public static function make(): self
    {
        $fixture = new self();
        $fixture->boot();

        return $fixture;
    }

    protected function boot(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (CatechistPermissions::all() as $name) {
            Permission::findOrCreate($name, 'web');
        }

        $suffix = Str::lower(Str::random(8));

        $this->parishA = ParishNew::query()->create([
            'name' => 'Test Parish A ' . $suffix,
            'code' => 'TA' . strtoupper(substr($suffix, 0, 4)),
            'scores_entry_open' => true,
        ]);

        $this->parishB = ParishNew::query()->create([
            'name' => 'Test Parish B ' . $suffix,
            'code' => 'TB' . strtoupper(substr($suffix, 0, 4)),
            'scores_entry_open' => true,
        ]);

        $this->yearA = NamHoc::query()->create([
            'name' => 'NH-A-' . $suffix,
            'parish_id' => $this->parishA->id,
            'start_date_one' => now()->subMonths(2)->toDateString(),
            'end_date_one' => now()->addMonths(2)->toDateString(),
            'start_date_two' => now()->addMonths(3)->toDateString(),
            'end_date_two' => now()->addMonths(8)->toDateString(),
            'status' => NamHoc::STATUS_ACTIVE,
        ]);

        $this->yearB = NamHoc::query()->create([
            'name' => 'NH-B-' . $suffix,
            'parish_id' => $this->parishB->id,
            'start_date_one' => now()->subMonths(2)->toDateString(),
            'end_date_one' => now()->addMonths(2)->toDateString(),
            'start_date_two' => now()->addMonths(3)->toDateString(),
            'end_date_two' => now()->addMonths(8)->toDateString(),
            'status' => NamHoc::STATUS_ACTIVE,
        ]);

        // Năm học cũ đã kết thúc (tài khoản GLV năm cũ chỉ có phân công ở đây)
        $this->yearAOld = NamHoc::query()->create([
            'name' => 'NH-A-OLD-' . $suffix,
            'parish_id' => $this->parishA->id,
            'start_date_one' => now()->subMonths(14)->toDateString(),
            'end_date_one' => now()->subMonths(10)->toDateString(),
            'start_date_two' => now()->subMonths(9)->toDateString(),
            'end_date_two' => now()->subMonths(6)->toDateString(),
            'status' => NamHoc::STATUS_ACTIVE,
        ]);

        $gradeId = GradeLevel::query()->value('id') ?? 1;

        $this->classAssigned = CatechismClass::query()->create([
            'parish_id' => $this->parishA->id,
            'school_year_id' => $this->yearA->id,
            'grade_level_id' => $gradeId,
            'name' => 'Lop Assigned ' . $suffix,
            'is_active' => true,
        ]);

        $this->classOtherSameParish = CatechismClass::query()->create([
            'parish_id' => $this->parishA->id,
            'school_year_id' => $this->yearA->id,
            'grade_level_id' => $gradeId,
            'name' => 'Lop Other A ' . $suffix,
            'is_active' => true,
        ]);

        $this->classOtherParish = CatechismClass::query()->create([
            'parish_id' => $this->parishB->id,
            'school_year_id' => $this->yearB->id,
            'grade_level_id' => $gradeId,
            'name' => 'Lop Other B ' . $suffix,
            'is_active' => true,
        ]);

        $this->classOldYear = CatechismClass::query()->create([
            'parish_id' => $this->parishA->id,
            'school_year_id' => $this->yearAOld->id,
            'grade_level_id' => $gradeId,
            'name' => 'Lop Old Year ' . $suffix,
            'is_active' => true,
        ]);

        $this->studentAssigned = StudentNew::query()->create([
            'parish_id' => $this->parishA->id,
            'first_name' => 'An',
            'last_name' => 'Nguyen',
            'phone' => '0901' . random_int(100000, 999999),
            'is_active' => true,
        ]);

        $this->studentOtherSameParish = StudentNew::query()->create([
            'parish_id' => $this->parishA->id,
            'first_name' => 'Binh',
            'last_name' => 'Tran',
            'phone' => '0902' . random_int(100000, 999999),
            'is_active' => true,
        ]);

        $this->studentOtherParish = StudentNew::query()->create([
            'parish_id' => $this->parishB->id,
            'first_name' => 'Chi',
            'last_name' => 'Le',
            'phone' => '0903' . random_int(100000, 999999),
            'is_active' => true,
        ]);

        $this->pivotAssigned = StudentsClass::query()->create([
            'student_id' => $this->studentAssigned->id,
            'class_id' => $this->classAssigned->id,
            'status' => StudentsClass::STATUS_ENROLLED,
        ]);

        $this->pivotOtherSameParish = StudentsClass::query()->create([
            'student_id' => $this->studentOtherSameParish->id,
            'class_id' => $this->classOtherSameParish->id,
            'status' => StudentsClass::STATUS_ENROLLED,
        ]);

        StudentsClass::query()->create([
            'student_id' => $this->studentOtherParish->id,
            'class_id' => $this->classOtherParish->id,
            'status' => StudentsClass::STATUS_ENROLLED,
        ]);

        $this->scoreTypeAssigned = ScoreType::query()->create([
            'class_id' => $this->classAssigned->id,
            'semester' => 1,
            'type' => ScoreType::TYPE_15_PHUT,
            'name' => '15p',
            'order' => 1,
            'coefficient' => 1,
            'max_score' => 10,
            'is_active' => true,
        ]);

        $this->scoreTypeOther = ScoreType::query()->create([
            'class_id' => $this->classOtherSameParish->id,
            'semester' => 1,
            'type' => ScoreType::TYPE_15_PHUT,
            'name' => '15p-other',
            'order' => 1,
            'coefficient' => 1,
            'max_score' => 10,
            'is_active' => true,
        ]);

        $this->ordinaryCatechist = $this->makeUser('glv-ordinary-' . $suffix . '@test.local', 'catechist', $this->parishA->id);
        $this->scoreManagerCatechist = $this->makeUser('glv-scores-' . $suffix . '@test.local', 'catechist', $this->parishA->id);
        $this->studentEditorCatechist = $this->makeUser('glv-students-' . $suffix . '@test.local', 'catechist', $this->parishA->id);
        $this->unassignedCatechist = $this->makeUser('glv-unassigned-' . $suffix . '@test.local', 'catechist', $this->parishA->id);
        $this->oldYearCatechist = $this->makeUser('glv-oldyear-' . $suffix . '@test.local', 'catechist', $this->parishA->id);
        $this->catechismAdmin = $this->makeUser('cat-admin-' . $suffix . '@test.local', 'catechism_admin', $this->parishA->id);
        $this->parishAdmin = $this->makeUser('parish-admin-' . $suffix . '@test.local', 'parish_admin', $this->parishA->id);

        $this->scoreManagerCatechist->givePermissionTo(CatechistPermissions::MANAGE_PARISH_SCORES);
        $this->studentEditorCatechist->givePermissionTo(CatechistPermissions::EDIT_PARISH_STUDENTS);

        $this->ordinaryTeacher = Teacher::query()->create([
            'parish_id' => $this->parishA->id,
            'user_id' => $this->ordinaryCatechist->id,
            'first_name' => 'GLV',
            'last_name' => 'Thuong',
            'email' => $this->ordinaryCatechist->email,
            'is_active' => true,
        ]);

        $scoreManagerTeacher = Teacher::query()->create([
            'parish_id' => $this->parishA->id,
            'user_id' => $this->scoreManagerCatechist->id,
            'first_name' => 'GLV',
            'last_name' => 'Diem',
            'email' => $this->scoreManagerCatechist->email,
            'is_active' => true,
        ]);

        $studentEditorTeacher = Teacher::query()->create([
            'parish_id' => $this->parishA->id,
            'user_id' => $this->studentEditorCatechist->id,
            'first_name' => 'GLV',
            'last_name' => 'HS',
            'email' => $this->studentEditorCatechist->email,
            'is_active' => true,
        ]);

        $this->unassignedTeacher = Teacher::query()->create([
            'parish_id' => $this->parishA->id,
            'user_id' => $this->unassignedCatechist->id,
            'first_name' => 'GLV',
            'last_name' => 'ChuaPhanCong',
            'email' => $this->unassignedCatechist->email,
            'is_active' => true,
        ]);

        $this->oldYearTeacher = Teacher::query()->create([
            'parish_id' => $this->parishA->id,
            'user_id' => $this->oldYearCatechist->id,
            'first_name' => 'GLV',
            'last_name' => 'NamCu',
            'email' => $this->oldYearCatechist->email,
            'is_active' => true,
        ]);

        ClassTeacher::query()->create([
            'teacher_id' => $this->ordinaryTeacher->id,
            'class_id' => $this->classAssigned->id,
            'namhoc_id' => $this->yearA->id,
            'role' => ClassTeacher::ROLE_CHU_NHIEM,
            'status' => true,
        ]);

        // GLV quyền hỗ trợ quản trị vẫn phải có phân công trong năm hiện tại
        ClassTeacher::query()->create([
            'teacher_id' => $scoreManagerTeacher->id,
            'class_id' => $this->classAssigned->id,
            'namhoc_id' => $this->yearA->id,
            'role' => ClassTeacher::ROLE_PHO,
            'status' => true,
        ]);

        ClassTeacher::query()->create([
            'teacher_id' => $studentEditorTeacher->id,
            'class_id' => $this->classAssigned->id,
            'namhoc_id' => $this->yearA->id,
            'role' => ClassTeacher::ROLE_PHO,
            'status' => true,
        ]);

        // GLV năm cũ: chỉ có phân công ở năm học đã kết thúc
        ClassTeacher::query()->create([
            'teacher_id' => $this->oldYearTeacher->id,
            'class_id' => $this->classOldYear->id,
            'namhoc_id' => $this->yearAOld->id,
            'role' => ClassTeacher::ROLE_CHU_NHIEM,
            'status' => true,
        ]);
    }

    protected function makeUser(string $email, string $role, int $parishId): User
    {
        $user = User::query()->create([
            'name' => $email,
            'email' => $email,
            'password' => Hash::make('password'),
            'parish_id' => $parishId,
            'is_active' => true,
        ]);

        $user->assignRole($role);

        return $user->fresh();
    }
}
