<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // =====================================================
        // 1. GROUPS — Định nghĩa nhóm (GLV, ca đoàn, ...)
        // =====================================================
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parish_id')->constrained('parishes')->cascadeOnDelete();

            $table->string('name');                  // "Nhóm GLV", "Ca đoàn thiếu nhi"

            $table->tinyInteger('type');
            // 1 = nhóm GLV
            // 2 = ca đoàn thiếu nhi
            // 3 = ca đoàn người lớn
            // 4 = khác

            $table->string('member_type', 20);
            // 'teacher' → group_members.memberable là Teacher
            // 'student' → group_members.memberable là Student

            $table->boolean('is_active')->default(true);
            $table->string('note')->nullable();
            $table->timestamps();
        });

        // =====================================================
        // 2. GROUP_MEMBERS — Ai thuộc nhóm nào
        //    Polymorphic: teacher_id hoặc student_id
        //    Không lưu hồ sơ — hồ sơ đã có ở teachers/students
        // =====================================================
        Schema::create('group_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('groups')->cascadeOnDelete();

            // Polymorphic — morphTo Teacher hoặc Student
            $table->string('memberable_type', 50);   // 'teacher' | 'student'
            $table->unsignedBigInteger('memberable_id');

            $table->string('role', 50)->nullable();  // "ca trưởng", "thư ký", "thành viên"
            $table->date('joined_at')->nullable();
            $table->date('left_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Index cho morphTo
            $table->index(['memberable_type', 'memberable_id'], 'idx_memberable');

            // Mỗi người chỉ thuộc 1 nhóm 1 lần
            $table->unique(
                ['group_id', 'memberable_type', 'memberable_id'],
                'uq_group_member'
            );
        });

        // =====================================================
        // 3. GROUP_SESSIONS — Buổi sinh hoạt của nhóm
        //    Dùng chung cho GLV (họp, dạy) và ca đoàn (tập, lễ)
        //    KHÔNG lưu nam_hoc_id — báo cáo dùng date range từ nam_hoc
        // =====================================================
        Schema::create('group_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('groups')->cascadeOnDelete();
            $table->foreignId('parish_id')->constrained('parishes');

            $table->date('date');

            $table->tinyInteger('shift')->default(1);
            // 1 = ca sáng
            // 2 = ca chiều
            // 3 = ca tối

            $table->tinyInteger('type')->default(1);
            // GLV:      1=dạy giáo lý, 2=họp nhóm, 3=sinh hoạt
            // Ca đoàn:  1=tập hát,     2=thánh lễ, 3=biểu diễn

            $table->string('title')->nullable();     // "Họp GLV tháng 1", "Thánh lễ khai giảng"
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('note')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // Một nhóm không thể có 2 buổi trùng ngày + ca
            $table->unique(['group_id', 'date', 'shift'], 'uq_group_date_shift');

            $table->index(['group_id', 'date']);
            $table->index(['parish_id', 'date']);    // để query báo cáo theo date range
        });

        // =====================================================
        // 4. GROUP_ATTENDANCE_RECORDS — Điểm danh từng thành viên
        // =====================================================
        Schema::create('group_attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')
                ->constrained('group_sessions')
                ->cascadeOnDelete();

            $table->foreignId('member_id')
                ->constrained('group_members')
                ->cascadeOnDelete();

            $table->tinyInteger('status')->default(1);
            // 1 = có mặt
            // 2 = vắng có phép
            // 3 = vắng không phép
            // 4 = đi trễ

            $table->string('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // Mỗi thành viên chỉ có 1 record mỗi buổi
            $table->unique(['session_id', 'member_id'], 'uq_session_member');

            $table->index('member_id');              // để query chuyên cần 1 người
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('group_attendance_records');
        Schema::dropIfExists('group_sessions');
        Schema::dropIfExists('group_members');
        Schema::dropIfExists('groups');
    }
}
