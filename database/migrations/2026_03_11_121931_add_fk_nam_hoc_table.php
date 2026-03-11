<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFkNamHocTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE `nam_hoc` MODIFY `parish_id` BIGINT UNSIGNED NOT NULL');

        // ── Bước 3: Xoá orphan ──────────────────────────────────────
        DB::table('nam_hoc')
            ->whereNotIn('parish_id', fn($q) => $q->select('id')->from('parishes'))
            ->delete();

        // ── Bước 4: Thêm FK ─────────────────────────────────────────
        Schema::table('nam_hoc', function (Blueprint $table) {
            $table->foreign('parish_id')
                ->references('id')->on('parishes')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('nam_hoc', function (Blueprint $table) {
            $table->dropForeign(['parish_id']);
        });

        DB::statement('ALTER TABLE `nam_hoc` MODIFY `parish_id` INT NOT NULL');
    }
}
