<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddFkClassesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Lớp có school_year_id không tồn tại
        DB::table('classes')
            ->whereNotIn('school_year_id', fn($q) => $q->select('id')->from('nam_hoc'))
            ->delete();

        // Lớp có grade_level_id không tồn tại
        DB::table('classes')
            ->whereNotIn('grade_level_id', fn($q) => $q->select('id')->from('block'))
            ->delete();

        // ── Bước 2: Thêm FK ─────────────────────────────────────────

        Schema::table('classes', function (Blueprint $table) {
            $table->foreign('school_year_id')
                ->references('id')->on('nam_hoc')
                ->onDelete('cascade');

            $table->foreign('grade_level_id')
                ->references('id')->on('block')
                ->onDelete('restrict'); // Không cho xoá khối nếu còn lớp
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('classes', function (Blueprint $table) {
            $table->dropForeign(['school_year_id']);
            $table->dropForeign(['grade_level_id']);
        });
    }
}
