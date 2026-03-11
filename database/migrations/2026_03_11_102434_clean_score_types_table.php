<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CleanScoreTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $deleted = DB::table('score_types')
            ->whereNotIn('class_id', function ($query) {
                $query->select('id')->from('classes');
            })
            ->delete();

        if ($deleted > 0) {
            \Illuminate\Support\Facades\Log::info(
                "[Migration] Đã xoá {$deleted} bản ghi orphan khỏi score_types"
            );
        }

        Schema::table('score_types', function (Blueprint $table) {
            $table->foreign('class_id')
                ->references('id')
                ->on('classes')
                ->onDelete('cascade');  // Xoá score_types khi xoá lớp
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('score_types', function (Blueprint $table) {
            $table->dropForeign(['class_id']);
        });
    }
}
