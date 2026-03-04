<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddParishIdToTeacherTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('teacher', function (Blueprint $table) {
            $table->unsignedBigInteger('parish_id')
                ->nullable()
                ->after('id');
        });

        // Map dữ liệu từ pid sang parish_id
        DB::statement('UPDATE teacher SET parish_id = pid WHERE parish_id IS NULL');

        // Thêm foreign key (sau khi đã có dữ liệu)
        Schema::table('teacher', function (Blueprint $table) {
            $table->foreign('parish_id')
                ->references('id')
                ->on('parishes')
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
        Schema::table('teacher', function (Blueprint $table) {
            $table->dropForeign(['parish_id']);
            $table->dropColumn('parish_id');
        });
    }
}
